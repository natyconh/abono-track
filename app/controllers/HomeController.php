<?php
// app/controllers/HomeController.php — Abono Track
// Dashboard principal de la aplicación.

class HomeController extends Controller {

    public function __construct() {
        parent::__construct();
        $this->protect();
    }

    public function index() {
        $db  = $this->db;
        $uid = $this->usuario_id;

        // KPI: total predios activos tipo cultivo del usuario
        $db->query("SELECT COUNT(*) AS total FROM predios WHERE usuario_id = :uid AND activo = 1 AND tipo_superficie = 'cultivo'");
        $db->bind(':uid', $uid);
        $total_predios = $db->single()->total ?? 0;

        // KPI: aplicaciones de fertirrigación registradas hoy
        $db->query("SELECT COUNT(*) AS total FROM fertilizaciones_cabezal WHERE usuario_id = :uid AND fecha = CURDATE()");
        $db->bind(':uid', $uid);
        $aplicaciones_hoy = $db->single()->total ?? 0;

        // KPI: aplicaciones de fertirrigación en los últimos 7 días
        $db->query("SELECT COUNT(*) AS total FROM fertilizaciones_cabezal WHERE usuario_id = :uid AND fecha >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)");
        $db->bind(':uid', $uid);
        $aplicaciones_semana = $db->single()->total ?? 0;

        // Estado de programas activos por predio (avance real vs. objetivo acumulado hasta hoy)
        $db->query("
            SELECT
                p.nombre AS predio,
                p.id     AS predio_id,
                COALESCE(
                    ROUND(
                        SUM(fdet.cantidad_aplicada_kg_ha) /
                        NULLIF(SUM(prog.n_objetivo + prog.p_objetivo + prog.k_objetivo), 0) * 100
                    ), 0
                ) AS porcentaje_avance
            FROM predios p
            JOIN programa_fertilizacion prog
                ON prog.predio_id  = p.id
               AND prog.usuario_id = :uid
               AND prog.estado     = 'activo'
               AND prog.fecha_estimada <= CURDATE()
            LEFT JOIN fertilizaciones_cabezal fc
                ON fc.predio_id  = p.id
               AND fc.usuario_id = :uid2
               AND fc.fecha      >= (
                   SELECT MIN(fecha_estimada)
                   FROM programa_fertilizacion
                   WHERE predio_id = p.id AND usuario_id = :uid3 AND estado = 'activo'
               )
            LEFT JOIN fertilizacion_detalle fdet
                ON fdet.fertilizacion_id = fc.id
            WHERE p.usuario_id = :uid4
              AND p.activo = 1
            GROUP BY p.id, p.nombre
            ORDER BY porcentaje_avance ASC
        ");
        $db->bind(':uid',  $uid);
        $db->bind(':uid2', $uid);
        $db->bind(':uid3', $uid);
        $db->bind(':uid4', $uid);
        $estado_programas_raw = $db->resultSet() ?: [];

        // Enriquecer con estado semántico y color
        $estado_programas = array_map(function($row) {
            $pct = (int)($row->porcentaje_avance ?? 0);
            if ($pct >= 90) {
                $estado = 'Al día';   $color = '#437a22'; $bg = 'rgba(67,122,34,0.12)'; $icon = 'bi-check-circle-fill';
            } elseif ($pct >= 60) {
                $estado = 'Moderado'; $color = '#d19900'; $bg = 'rgba(209,153,0,0.12)';  $icon = 'bi-exclamation-circle-fill';
            } else {
                $estado = 'Atrasado'; $color = '#a12c7b'; $bg = 'rgba(161,44,123,0.12)'; $icon = 'bi-x-circle-fill';
            }
            return [
                'predio'      => $row->predio,
                'porcentaje'  => $pct,
                'estado'      => $estado,
                'color_text'  => $color,
                'color_bg'    => $bg,
                'icon'        => $icon,
            ];
        }, $estado_programas_raw);

        $data = [
            'titulo'              => 'Dashboard — Abono Track',
            'nombre_bienvenida'   => SessionHelper::getUserName(),
            'total_predios'       => $total_predios,
            'aplicaciones_hoy'    => $aplicaciones_hoy,
            'aplicaciones_semana' => $aplicaciones_semana,
            'estado_programas'    => $estado_programas,
        ];

        $this->view('home/index', $data);
    }
}
?>
