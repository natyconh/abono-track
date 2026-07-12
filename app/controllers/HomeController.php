<?php
// app/controllers/HomeController.php — Abono Track
// Dashboard principal de la aplicación.

class HomeController extends Controller {

    public function __construct() {
        parent::__construct();
        $this->protect();
    }

    public function index() {
        $data = [
            'titulo'                     => 'Dashboard Principal',
            'nombre_bienvenida'          => SessionHelper::getUserName(),
            // Para mantener compatibilidad con la vista index.php actual si usa estas variables
            'puedeVerCostos'             => true,
            'puedeIngresarPuntos'        => true,
            'puedeVerGestionSolicitudes' => true,
            'puedeVerAdmin'              => true,
            'puedeVerRiego'              => true,
            'puedeVerCosecha'            => true,
        ];

        $this->view('home/index', $data);
    }
}
?>
