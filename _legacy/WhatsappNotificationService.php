<?php
// app/core/WhatsappNotificationService.php

class WhatsappNotificationService {
    
    private $db;
    private $empresa_id;
    private $linkModel;

    public function __construct($empresa_id) {
        $this->db = Database::getInstance();
        $this->empresa_id = $empresa_id;

        require_once APP_ROOT . '/models/UsuarioWhatsappLinkModel.php';
        $this->linkModel = new UsuarioWhatsappLinkModel($this->db, $empresa_id);
    }

    public function notificarUsuario($usuario_id, $mensaje) {
        $vinculo = $this->linkModel->obtenerPorUsuarioId($usuario_id);

        if (!$vinculo || empty($vinculo->numero_whatsapp) || $vinculo->estado !== 'verificado') {
            return false;
        }

        return $this->enviarMensajeRaw($vinculo->numero_whatsapp, $mensaje);
    }

    /**
     * Envía un mensaje usando Meta Cloud API (Antes Twilio)
     */
    public function enviarMensajeRaw($numero_destino, $mensaje_texto) {
        // 1. Limpieza del número
        // Meta requiere solo dígitos. Si tu BD tiene 'whatsapp:+569...', límpialo.
        // Asumimos que $numero_destino debe quedar como '569XXXXXXXX'
        $numero_destino = preg_replace('/[^0-9]/', '', $numero_destino);

        // 2. Construcción del Payload (JSON)
        $data = [
            'messaging_product' => 'whatsapp',
            'to' => $numero_destino,
            'type' => 'text',
            'text' => [
                'body' => $mensaje_texto
            ]
        ];

        // 3. Configuración cURL para Meta
        $url = 'https://graph.facebook.com/' . META_API_VERSION . '/' . META_PHONE_ID . '/messages';
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . META_WHATSAPP_TOKEN,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Meta devuelve 200 OK en éxito
        if ($http_code >= 200 && $http_code < 300) {
            return true;
        } else {
            // Sugerencia: Guardar logs de error si falla
            // file_put_contents(APP_ROOT.'/../logs/whatsapp_error.log', $response . PHP_EOL, FILE_APPEND);
            return false;
        }
    }
}
?>