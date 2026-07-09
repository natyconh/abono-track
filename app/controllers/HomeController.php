<?php
// app/controllers/HomeController.php
// ACTUALIZADO: Incluye permisos específicos para el perfil de Riego

class HomeController extends Controller {

    public function __construct() {
        parent::__construct();
        $this->protect(); 
    }

    public function index() {
        $rol = SessionHelper::getUserRoleName();

        // Lógica de bienvenida
        $nombre_usuario = SessionHelper::getUserName();
        $nombre_trabajador = SessionHelper::getUserFullName();
        $nombre_bienvenida = !empty($nombre_trabajador) ? $nombre_trabajador : $nombre_usuario;

        $data = [
            'titulo' => 'Menú Principal',
            'nombre_bienvenida' => $nombre_bienvenida,
            
            // Permisos existentes
            'puedeVerCostos' => in_array($rol, ['Admin', 'Usuario_general']),
            'puedeIngresarPuntos' => in_array($rol, ['Admin', 'Usuario_general', 'Terreno']),
            'puedeVerGestionSolicitudes' => in_array($rol, ['Admin', 'Usuario_general']),
            'puedeVerAdmin' => in_array($rol, ['Admin']),
            'puedeVerRiego' => in_array($rol, ['Admin', 'Usuario_riego']),
            'puedeVerCosecha' => in_array($rol, ['Admin', 'Usuario_general', 'Terreno'])
        ];
        
        $this->view('home/index', $data);
    }
}
?>