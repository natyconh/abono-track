<?php
/**
 * Controlador de Acceso Público
 * Maneja visualización de reportes compartidos mediante Token
 */
class PublicoController extends Controller {
    
    private $fertilizacionService; 
    private $riegoService;
    private $dbConnection;

    public function __construct() {
        // No llamamos a parent::__construct() o evitamos validación de sesión aquí
        $this->db = Database::getInstance();
    }

    public function reporte($token) {
        if (empty($token)) die("Token no proporcionado.");

        // 1. Instancia temporal para validar el token (aún no sabemos la empresa)
        require_once APP_ROOT . '/core/RiegoService.php';
        $tempService = new RiegoService(null); 
        $tokenData = $tempService->validarToken($token);

        if (!$tokenData) {
            $this->view('publico/error', ['mensaje' => 'Enlace expirado o inválido.']);
            return;
        }

        // --- CORRECCIÓN AQUÍ ---
        // Extraemos explícitamente el ID de la empresa del token validado
        $empresaId = $tokenData->empresa_id; 
        // -----------------------

        // 2. Enrutador de Reportes
        if ($tokenData->tipo_reporte === 'nutricional_temporada') {
            
            require_once APP_ROOT . '/core/FertilizacionService.php';
            $this->fertilizacionService = new FertilizacionService($empresaId);
            $this->mostrarReporteNutricional($empresaId, $tokenData->nombre_empresa);

        } elseif ($tokenData->tipo_reporte === 'riego_semanal') {
            
            // Pasamos el $empresaId recuperado
            $this->riegoService = new RiegoService($empresaId);
            
            $params = json_decode($tokenData->params ?? '{}');
            $week = $params->week ?? null;
            $year = $params->year ?? null;

            if ($week && $year) {
                $weekInput = sprintf('%04d-W%02d', $year, $week);
                $this->mostrarReporteRiego($weekInput, $tokenData->nombre_empresa);
            } else {
                die("Error: Token incompleto (Faltan datos de fecha).");
            }

        } elseif ($tokenData->tipo_reporte === 'riego_gerencial') {
            
            // Pasamos el $empresaId recuperado
            $this->riegoService = new RiegoService($empresaId);
            
            $params = json_decode($tokenData->params ?? '{}');
            $fechaCorte = $params->fecha_corte ?? null;

            if ($fechaCorte) {
                $this->mostrarReporteGerencial($fechaCorte, $tokenData->nombre_empresa);
            } else {
                die("Error: Token incompleto (Falta fecha de corte).");
            }

        } else {
            die("Tipo de reporte no soportado.");
        }
    }

    private function mostrarReporteRiego($weekInput, $nombreEmpresa) {
        // Reutilizamos la lógica del servicio
        $datosReporte = $this->riegoService->obtenerDatosReporteSemanal($weekInput);

        $data = array_merge($datosReporte, [
            'titulo' => 'Reporte Riego - ' . $nombreEmpresa,
            'es_publico' => true, // Flag importante para la vista
            'empresa' => $nombreEmpresa,
            // Sobrescribimos breadcrumbs para que no tengan links internos
            'breadcrumbs' => [
                ['label' => $nombreEmpresa],
                ['label' => 'Reporte Semanal Externo']
            ]
        ]);

        // Reutilizamos la misma vista que creaste
        $this->view('reporte/semanal', $data);
    }

    private function mostrarReporteNutricional($empresaId, $nombreEmpresa) {
        // Lógica de fechas (Temporada)
        $mesActual = date('n');
        $yearActual = date('Y');
        if ($mesActual >= 9) {
            $inicioTemporada = $yearActual . '-09-01';
        } else {
            $inicioTemporada = ($yearActual - 1) . '-09-01';
        }
        $finTemporada = date('Y-m-d');

        $datos = $this->fertilizacionService->obtenerReporteNutricionalTemporada($inicioTemporada, $finTemporada);

        $data = [
            'titulo' => 'Reporte Nutricional - ' . $nombreEmpresa,
            'inicio_temporada' => $inicioTemporada,
            'datos' => $datos,
            'es_publico' => true, // Flag para ajustar la vista
            'empresa' => $nombreEmpresa
        ];

        // Usamos una vista wrapper que incluye el layout público
        $this->view('publico/reporte_nutricional', $data);
    }
    private function mostrarReporteGerencial($fechaCorte, $nombreEmpresa) {
        $datos = $this->riegoService->obtenerDatosReporteGerencial($fechaCorte);

        $data = array_merge($datos, [
            'titulo' => 'Eficiencia Hídrica - ' . $nombreEmpresa,
            'es_publico' => true,
            'empresa' => $nombreEmpresa
        ]);

        $this->view('reporte/gerencial', $data);
    }
}
?>