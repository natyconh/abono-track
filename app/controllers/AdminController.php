<?php
/**
 * Controlador para el Dashboard de Administración General
 * MODIFICADO: Adaptado a DIP
 */
class AdminController extends Controller {

    public function __construct() {
        // Carga el contexto (db, empresa_id, usuario_id)
        parent::__construct(); 
        // Protege todo el controlador
        $this->protect(['Admin']);
    }

    public function index() {
        $data = [
            'titulo' => 'Panel de Administración'
        ];
        $this->view('admin/index', $data);
    }
}