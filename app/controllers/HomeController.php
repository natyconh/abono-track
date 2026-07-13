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

        // KPI: aplicaciones registradas hoy
        $db->query('SELECT COUNT(*) AS total FROM fertilizaciones_cabezal WHERE usuario_id = :uid AND fecha = CURDATE()');
        $db->bind(':uid', $uid);
        $aplicaciones_hoy = $db->single()->total ?? 0;

        // KPI: aplicaciones en los últimos 7 días
        $db->query('SELECT COUNT(*) AS total FROM fertilizaciones_cabezal WHERE usuario_id = :uid AND fecha >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)');
        $db->bind(':uid', $uid);
        $aplicaciones_semana = $db->single()->total ?? 0;

        // ---------------------------------------------------------------
        // Estado de programas activos por predio
        // Se resuelve en DOS queries simples para compatibilidad con el
        // wrapper Database.php (un $stmt a la vez, sin subconsultas complejas).
        //
        // Query A: NPK objetivo acumulado hasta hoy por predio (programa activo)
        // ---------------------------------------------------------------
        $db->query('
            SELECT
                pf.predio_id,
                p.nombre AS predio,
                SUM(pf.n_objetivo + pf.p_objetivo + pf.k_objetivo) AS total_objetivo
            FROM programa_fertilizacion pf
            JOIN predios p ON p.id = pf.predio_id
            WHERE pf.usuario_id    = :uid
              AND pf.estado        = :estado
              AND pf.fecha_estimada <= CURDATE()
              AND p.activo         = 1
            GROUP BY pf.predio_id, p.nombre
            HAVING total_objetivo > 0
        ');
        $db->bind(':uid',    $uid);
        $db->bind(':estado', 'activo');
        $filas_objetivo = $db->resultSet() ?: [];

        // ---------------------------------------------------------------
        // Query B: NPK real recibido por predio destino (fertilizaciones_reales
        //          cruzado con fertilizaciones_cabezal para filtrar por usuario).
        //          Sin subcorrelaciones: el rango de fecha lo limitamos a
        //          desde el inicio del año en curso como proxy conservador,
        //          suficiente para temporadas anuales o anuales extendidas.
        // ---------------------------------------------------------------
        $db->query('
            SELECT
                fr.predio_destino_id AS predio_id,
                SUM(fr.unidades_n + fr.unidades_p + fr.unidades_k) AS total_real
            FROM fertilizaciones_reales fr
            JOIN fertilizaciones_cabezal fc
                ON fc.id          = fr.fertilizacion_cabezal_id
               AND fc.usuario_id  = :uid
               AND fc.fecha       <= CURDATE()
            GROUP BY fr.predio_destino_id
        ');
        $db->bind(':uid', $uid);
        $filas_real = $db->resultSet() ?: [];

        // Indexar real por predio_id para cruce rápido en PHP
        $real_por_predio = [];
        foreach ($filas_real as $r) {
            $real_por_predio[$r->predio_id] = (float)$r->total_real;
        }

        // Calcular porcentaje y enriquecer
        $estado_programas = [];
        foreach ($filas_objetivo as $row) {
            $objetivo = (float)$row->total_objetivo;
            $real     = $real_por_predio[$row->predio_id] ?? 0.0;
            $pct      = $objetivo > 0 ? (int)round(($real / $objetivo) * 100) : 0;
            $pct      = min($pct, 999);

            if ($pct >= 90) {
                $estado = 'Al día';   $color_text = '#437a22'; $color_bg = 'rgba(67,122,34,0.12)';  $icon = 'bi-check-circle-fill';
            } elseif ($pct >= 60) {
                $estado = 'Moderado'; $color_text = '#d19900'; $color_bg = 'rgba(209,153,0,0.12)';  $icon = 'bi-exclamation-circle-fill';
            } else {
                $estado = 'Atrasado'; $color_text = '#a12c7b'; $color_bg = 'rgba(161,44,123,0.12)'; $icon = 'bi-x-circle-fill';
            }

            $estado_programas[] = [
                'predio'     => $row->predio,
                'porcentaje' => $pct,
                'estado'     => $estado,
                'color_text' => $color_text,
                'color_bg'   => $color_bg,
                'icon'       => $icon,
            ];
        }

        // Ordenar por porcentaje ascendente (más atrasados primero)
        usort($estado_programas, fn($a, $b) => $a['porcentaje'] <=> $b['porcentaje']);

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
