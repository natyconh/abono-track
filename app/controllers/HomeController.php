<?php
// app/controllers/HomeController.php — Abono Track

class HomeController extends Controller {

    public function __construct() {
        parent::__construct();
        $this->protect();
    }

    public function index() {
        $db  = $this->db;
        $uid = $this->usuario_id;

        // KPI: predios activos de tipo cultivo
        $db->query("SELECT COUNT(*) AS total FROM predios WHERE usuario_id = :uid AND activo = 1 AND tipo_superficie = 'cultivo'");
        $db->bind(':uid', $uid);
        $total_predios = $db->single()->total ?? 0;

        // KPI: aplicaciones registradas hoy (en fertilizaciones_cabezal)
        $db->query("SELECT COUNT(*) AS total FROM fertilizaciones_cabezal WHERE usuario_id = :uid AND fecha = CURDATE()");
        $db->bind(':uid', $uid);
        $aplicaciones_hoy = $db->single()->total ?? 0;

        // KPI: aplicaciones en los últimos 7 días
        $db->query("SELECT COUNT(*) AS total FROM fertilizaciones_cabezal WHERE usuario_id = :uid AND fecha >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)");
        $db->bind(':uid', $uid);
        $aplicaciones_semana = $db->single()->total ?? 0;

        // Estado de programas activos por predio.
        // Objetivo = suma NPK planificado en programa_fertilizacion (semanas hasta hoy, estado activo).
        // Real     = suma NPK recibida en fertilizaciones_reales para ese predio destino,
        //            cruzando con fertilizaciones_cabezal para filtrar por usuario y rango de fechas.
        $db->query("
            SELECT
                p.id     AS predio_id,
                p.nombre AS predio,
                COALESCE(SUM(prog.n_objetivo + prog.p_objetivo + prog.k_objetivo), 0) AS total_objetivo,
                COALESCE((
                    SELECT SUM(fr.unidades_n + fr.unidades_p + fr.unidades_k)
                    FROM fertilizaciones_reales fr
                    JOIN fertilizaciones_cabezal fc
                        ON fc.id         = fr.fertilizacion_cabezal_id
                       AND fc.usuario_id = :uid2
                       AND fc.fecha BETWEEN (
                               SELECT MIN(pf2.fecha_estimada)
                               FROM programa_fertilizacion pf2
                               WHERE pf2.predio_id   = p.id
                                 AND pf2.usuario_id  = :uid3
                                 AND pf2.estado      = 'activo'
                           ) AND CURDATE()
                    WHERE fr.predio_destino_id = p.id
                ), 0) AS total_real
            FROM predios p
            JOIN programa_fertilizacion prog
                ON prog.predio_id      = p.id
               AND prog.usuario_id     = :uid4
               AND prog.estado         = 'activo'
               AND prog.fecha_estimada <= CURDATE()
            WHERE p.usuario_id = :uid5
              AND p.activo = 1
            GROUP BY p.id, p.nombre
            HAVING total_objetivo > 0
            ORDER BY (total_real / total_objetivo) ASC
        ");
        $db->bind(':uid',  $uid);
        $db->bind(':uid2', $uid);
        $db->bind(':uid3', $uid);
        $db->bind(':uid4', $uid);
        $db->bind(':uid5', $uid);
        $estado_programas_raw = $db->resultSet() ?: [];

        // Enriquecer con porcentaje, estado semántico, color e icono
        $estado_programas = array_map(function($row) {
            $objetivo = (float)$row->total_objetivo;
            $real     = (float)$row->total_real;
            $pct      = $objetivo > 0 ? (int)round(($real / $objetivo) * 100) : 0;
            $pct      = min($pct, 999);

            if ($pct >= 90) {
                $estado = 'Al día';   $color_text = '#437a22'; $color_bg = 'rgba(67,122,34,0.12)';  $icon = 'bi-check-circle-fill';
            } elseif ($pct >= 60) {
                $estado = 'Moderado'; $color_text = '#d19900'; $color_bg = 'rgba(209,153,0,0.12)';  $icon = 'bi-exclamation-circle-fill';
            } else {
                $estado = 'Atrasado'; $color_text = '#a12c7b'; $color_bg = 'rgba(161,44,123,0.12)'; $icon = 'bi-x-circle-fill';
            }

            return [
                'predio'     => $row->predio,
                'porcentaje' => $pct,
                'estado'     => $estado,
                'color_text' => $color_text,
                'color_bg'   => $color_bg,
                'icon'       => $icon,
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
