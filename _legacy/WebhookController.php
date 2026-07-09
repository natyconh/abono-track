<?php
// app/controllers/WebhookController.php

class WebhookController {
    
    private $db;
    private $logFile;

    public function __construct() {
        $this->db = Database::getInstance();
        
        // Definimos la ruta del log de depuración
        $this->logFile = APP_ROOT . '/../public/debug_log.txt';
        
        // Carga manual de modelos
        require_once APP_ROOT . '/models/UsuarioWhatsappLinkModel.php';
        require_once APP_ROOT . '/models/SolicitudModel.php';
    }

    private function log($mensaje) {
        $fecha = date('Y-m-d H:i:s');
        file_put_contents($this->logFile, "[$fecha] $mensaje" . PHP_EOL, FILE_APPEND);
    }

    public function handle() {
        // 1. VERIFICACIÓN (GET)
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $mode = $_GET['hub_mode'] ?? '';
            $token = $_GET['hub_verify_token'] ?? '';
            $challenge = $_GET['hub_challenge'] ?? '';

            if ($mode === 'subscribe' && $token === META_VERIFY_TOKEN) {
                http_response_code(200);
                echo $challenge;
                $this->log("✅ Webhook verificado correctamente por Meta.");
                exit;
            }
        }

        // 2. RECEPCIÓN (POST)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            
            // Logueamos el JSON crudo para ver qué llega realmente
            // $this->log("📩 JSON Recibido: " . $input); 

            // Navegación segura por el JSON de Meta
            if (isset($data['entry'][0]['changes'][0]['value']['messages'][0])) {
                $mensajeData = $data['entry'][0]['changes'][0]['value']['messages'][0];
                
                $from_number = $mensajeData['from']; // Meta envía ej: "56969027990"
                $type = $mensajeData['type'];
                
                $this->log("➡️ Mensaje recibido de: $from_number | Tipo: $type");

                if ($type === 'text') {
                    $body = $mensajeData['text']['body'];
                    $this->procesarSolicitud($from_number, $body);
                }
            } else {
                // A veces llegan notificaciones de estado (sent, delivered, read) que no son mensajes
                // $this->log("ℹ️ Evento recibido sin mensaje (posible status update).");
            }
            
            http_response_code(200);
            echo 'EVENT_RECEIVED';
        }
    }

    private function procesarSolicitud($numero, $texto) {
        // PASO A: BUSCAR USUARIO
        $this->log("🔍 Buscando usuario con número: $numero");
        
        // Instanciamos el modelo de link.
        // OJO: Si el constructor pide empresa_id, hay que pasarlo o modificar el modelo para que sea opcional.
        // Asumo constructor vacío o con parámetros opcionales: new UsuarioWhatsappLinkModel($db);
        $linkModel = new UsuarioWhatsappLinkModel($this->db); 
        
        // Usamos el método para obtener contexto
        $contexto = $linkModel->obtenerContextoPorNumero($numero);

        if (!$contexto) {
            $this->log("❌ ERROR: Número no encontrado en la BD o no verificado.");
            $this->enviarRespuesta($numero, "Lo siento, tu número no está registrado en Ryzoma Agro.");
            return;
        }

        $this->log("✅ Usuario encontrado: ID {$contexto->usuario_id} | Empresa: {$contexto->empresa_id}");

        // PASO B: INSERTAR SOLICITUD
        try {
            $solicitudModel = new SolicitudModel(
                $this->db, 
                $contexto->empresa_id, 
                $contexto->usuario_id
            );

            $datos = ['descripcion' => $texto];
            $nuevo_id = $solicitudModel->crearDesdeChatbot($datos);

            if ($nuevo_id) {
                $this->log("✅ Solicitud creada con éxito. ID: $nuevo_id");
                $this->enviarRespuesta($numero, "✅ Solicitud #$nuevo_id recibida: '$texto'. Un técnico la revisará.");
            } else {
                $this->log("❌ ERROR SQL: El modelo devolvió false al intentar crear.");
                $this->enviarRespuesta($numero, "Error interno al guardar la solicitud.");
            }

        } catch (Exception $e) {
            $this->log("🔥 EXCEPCIÓN CRÍTICA: " . $e->getMessage());
        }
    }

    private function enviarRespuesta($to, $message) {
        $url = 'https://graph.facebook.com/' . META_API_VERSION . '/' . META_PHONE_ID . '/messages';
        $data = [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'text',
            'text' => ['body' => $message]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . META_WHATSAPP_TOKEN,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $res = curl_exec($ch);
        curl_close($ch);
        // $this->log("📤 Respuesta enviada a Meta: " . $res);
    }
}
?>