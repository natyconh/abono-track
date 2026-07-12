<?php
/**
 * Controlador para Gestión de Cultivos (Maestro) — Abono Track
 */
class CultivosController extends Controller {

    private $cultivoModel;

    public function __construct() {
        parent::__construct();
        $this->protect(['Admin']);
        $this->cultivoModel = $this->model('CultivoModel');
    }

    public function index() {
        $data = [
            'titulo'      => 'Catálogo de Cultivos — Abono Track',
            'cultivos'    => $this->cultivoModel->obtenerTodos(),
            'breadcrumbs' => [
                ['label' => 'Administración', 'url' => URL_ROOT . '/admin'],
                ['label' => 'Cultivos'],
            ],
        ];
        $this->view('cultivos/index', $data);
    }

    public function form($id = null) {
        $data = [
            'titulo'   => 'Nuevo Cultivo',
            'cultivo'  => (object)['id' => null, 'nombre' => '', 'variedad' => ''],
            'breadcrumbs' => [
                ['label' => 'Cultivos', 'url' => URL_ROOT . '/cultivos'],
                ['label' => $id ? 'Editar' : 'Crear'],
            ],
        ];

        if ($id) {
            $cultivo = $this->cultivoModel->obtenerPorId($id);
            if (!$cultivo) $this->redirect('cultivos/index');
            $data['titulo']  = 'Editar Cultivo';
            $data['cultivo'] = $cultivo;
        }

        $this->view('cultivos/form', $data);
    }

    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') $this->redirect('cultivos/index');

        $id    = $_POST['id'] ?? null;
        $datos = [
            'nombre'   => trim($_POST['nombre']),
            'variedad' => trim($_POST['variedad']),
        ];

        if (empty($datos['nombre'])) {
            SessionHelper::setFlash('El nombre es obligatorio', 'danger');
            $this->redirect('cultivos/form/' . $id);
        }

        if ($id) {
            $this->cultivoModel->actualizar($id, $datos);
            SessionHelper::setFlash('Cultivo actualizado.', 'success');
        } else {
            $this->cultivoModel->crear($datos);
            SessionHelper::setFlash('Cultivo creado.', 'success');
        }
        $this->redirect('cultivos/index');
    }

    public function eliminar($id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->cultivoModel->eliminar($id);
            SessionHelper::setFlash('Cultivo desactivado.', 'success');
        }
        $this->redirect('cultivos/index');
    }
}
?>
