<?php
/**
 * Modelo para la gestión de KPIs (kpi_resumenes_semanales)
 * ADAPTADO para Abono Track: sin multi-empresa, filtro por usuario_id.
 */
class KpiModel {
    private $db;
    private $usuario_id;

    public function __construct($db, $usuario_id) {
        $this->db         = $db;
        $this->usuario_id = $usuario_id;
    }

    public function getResumenSemanalPorPredio($year, $week_number) {
        $sql = "SELECT 
                    k.*,
                    p.nombre as nombre_predio
                FROM kpi_resumenes_semanales k
                LEFT JOIN predios p ON k.predio_id = p.id
                WHERE k.usuario_id  = :usuario_id
                  AND k.year        = :year
                  AND k.week_number = :week_number
                ORDER BY p.nombre ASC";
        $this->db->query($sql);
        $this->db->bind(':usuario_id',   $this->usuario_id);
        $this->db->bind(':year',         $year);
        $this->db->bind(':week_number',  $week_number);
        return $this->db->resultSet();
    }
}
?>
