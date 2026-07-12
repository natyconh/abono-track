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

        // KPI: total predios activos
        $db->query("SELECT COUNT(*) AS total FROM predios WHERE usuario_id = :uid AND activo = 1 AND tipo_superficie = 'cultivo'");
        $db->bind(':uid', $uid);
        $total_predios = $db->single()->total ?? 0;

        // KPI: total cultivos
        $db->query("SELECT COUNT(*) AS total FROM cultivos WHERE activo = 1");
        $total_cultivos = $db->single()->total ?? 0;

        // KPI: total fertilizantes activos
        $db->query("SELECT COUNT(*) AS total FROM fertilizantes WHERE activo = 1");
        $total_fertilizantes = $db->single()->total ?? 0;

        // KPI: aplicaciones de fertirrigación registradas hoy
        $db->query("SELECT COUNT(*) AS total FROM fertilizacion_cabezal WHERE usuario_id = :uid AND fecha = CURDATE()");
        $db->bind(':uid', $uid);
        $aplicaciones_hoy = $db->single()->total ?? 0;

        $data = [
            'titulo'               => 'Dashboard — Abono Track',
            'nombre_bienvenida'    => SessionHelper::getUserName(),
            'total_predios'        => $total_predios,
            'total_cultivos'       => $total_cultivos,
            'total_fertilizantes'  => $total_fertilizantes,
            'aplicaciones_hoy'     => $aplicaciones_hoy,
        ];

        $this->view('home/index', $data);
    }
}
?>
