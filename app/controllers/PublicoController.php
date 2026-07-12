<?php
/**
 * Controlador de Acceso Público — Abono Track
 * Maneja visualización de reportes compartidos mediante Token
 */
class PublicoController extends Controller {

    private $fertilizacionService;
    private $riegoService;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function reporte($token) {
        if (empty($token)) die('Token no proporcionado.');

        require_once APP_ROOT . '/core/RiegoService.php';
        $tempService = new RiegoService(null);
        $tokenData   = $tempService->validarToken($token);

        if (!$tokenData) {
            $this->view('publico/error', ['mensaje' => 'Enlace expirado o inválido.']);
            return;
        }

        $usuarioId = $tokenData->usuario_id;

        if ($tokenData->tipo_reporte === 'nutricional_temporada') {
            require_once APP_ROOT . '/core/FertilizacionService.php';
            $this->fertilizacionService = new FertilizacionService($usuarioId);
            $this->mostrarReporteNutricional($usuarioId, $tokenData->nombre_empresa);

        } elseif ($tokenData->tipo_reporte === 'riego_semanal') {
            $this->riegoService = new RiegoService($usuarioId);
            $params  = json_decode($tokenData->params ?? '{}');
            $week    = $params->week ?? null;
            $year    = $params->year ?? null;
            if ($week && $year) {
                $weekInput = sprintf('%04d-W%02d', $year, $week);
                $this->mostrarReporteRiego($weekInput, $tokenData->nombre_empresa);
            } else {
                die('Error: Token incompleto (Faltan datos de fecha).');
            }

        } elseif ($tokenData->tipo_reporte === 'riego_gerencial') {
            $this->riegoService = new RiegoService($usuarioId);
            $params     = json_decode($tokenData->params ?? '{}');
            $fechaCorte = $params->fecha_corte ?? null;
            if ($fechaCorte) {
                $this->mostrarReporteGerencial($fechaCorte, $tokenData->nombre_empresa);
            } else {
                die('Error: Token incompleto (Falta fecha de corte).');
            }

        } else {
            die('Tipo de reporte no soportado.');
        }
    }

    private function mostrarReporteRiego($weekInput, $nombreEmpresa) {
        $datosReporte = $this->riegoService->obtenerDatosReporteSemanal($weekInput);
        $data = array_merge($datosReporte, [
            'titulo'      => 'Reporte Riego — ' . $nombreEmpresa . ' | Abono Track',
            'es_publico'  => true,
            'empresa'     => $nombreEmpresa,
            'breadcrumbs' => [
                ['label' => $nombreEmpresa],
                ['label' => 'Reporte Semanal Externo'],
            ],
        ]);
        $this->view('reporte/semanal', $data);
    }

    private function mostrarReporteNutricional($usuarioId, $nombreEmpresa) {
        $mesActual  = date('n');
        $yearActual = date('Y');
        $inicioTemporada = ($mesActual >= 9) ? $yearActual . '-09-01' : ($yearActual - 1) . '-09-01';
        $finTemporada    = date('Y-m-d');

        $datos = $this->fertilizacionService->obtenerReporteNutricionalTemporada($inicioTemporada, $finTemporada);

        $data = [
            'titulo'           => 'Reporte Nutricional — ' . $nombreEmpresa . ' | Abono Track',
            'inicio_temporada' => $inicioTemporada,
            'datos'            => $datos,
            'es_publico'       => true,
            'empresa'          => $nombreEmpresa,
        ];

        $this->view('publico/reporte_nutricional', $data);
    }

    private function mostrarReporteGerencial($fechaCorte, $nombreEmpresa) {
        $datos = $this->riegoService->obtenerDatosReporteGerencial($fechaCorte);
        $data  = array_merge($datos, [
            'titulo'     => 'Eficiencia Hídrica — ' . $nombreEmpresa . ' | Abono Track',
            'es_publico' => true,
            'empresa'    => $nombreEmpresa,
        ]);
        $this->view('reporte/gerencial', $data);
    }
}
?>
