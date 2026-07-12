<?php
// app/core/RiegoService.php — Abono Track
// Lógica de negocio para reportes de riego y eficiencia hídrica.
// ADAPTADO: eliminado empresa_id y dependencia de ClimaModel.

class RiegoService {

    private $db;
    private $riegoModel;

    public function __construct() {
        $this->db = Database::getInstance();
        require_once APP_ROOT . '/models/RiegoModel.php';
        $this->riegoModel = new RiegoModel($this->db, null);
    }

    /**
     * Datos para el Dashboard: eficiencia hídrica en un rango de fechas.
     */
    public function obtenerDatosReporteRiego($fechaInicio = null, $fechaFin = null) {
        if (!$fechaInicio || !$fechaFin) {
            $ayer        = new DateTime();
            $ayer->modify('-1 day');
            $fechaFin    = $ayer->format('Y-m-d');
            $inicio      = clone $ayer;
            $inicio->modify('-6 days');
            $fechaInicio = $inicio->format('Y-m-d');
        }

        $riegos  = $this->riegoModel->obtenerConsolidadoRiegoPorPeriodo($fechaInicio, $fechaFin);
        $reporte = [];

        foreach ($riegos as $r) {
            $pp_hora  = ($r->caudal_lt_hora * $r->plantas_por_hectarea) / 10000;
            $mm_regados = ($pp_hora > 0) ? ($r->total_minutos / 60) * $pp_hora : 0;

            $reporte[] = [
                'predio_id'    => $r->predio_id,
                'predio'       => $r->nombre_predio,
                'mm_riego'     => round($mm_regados, 1),
                'dias_regados' => $r->dias_regados,
            ];
        }

        usort($reporte, fn($a, $b) => strcmp($a['predio'], $b['predio']));

        $inicioDt = new DateTime($fechaInicio);
        $finDt    = new DateTime($fechaFin);
        $diasRango = $inicioDt->diff($finDt)->days + 1;

        return [
            'titulo'    => 'Resumen de Riego',
            'subtitulo' => $inicioDt->format('d M') . ' - ' . $finDt->format('d M') . " ($diasRango días)",
            'datos'     => $reporte,
        ];
    }
}
?>
