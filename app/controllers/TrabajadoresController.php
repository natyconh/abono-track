<?php
/**
 * Controlador para la Administración de Trabajadores
 */
class TrabajadoresController extends Controller {
    
    private $trabajadorModel;

    public function __construct() {
        parent::__construct();
        $this->protect(['Admin']); // Solo administradores pueden gestionar trabajadores
        $this->trabajadorModel = $this->model('TrabajadorModel');
    }

    public function index() {
        $data = [
            'titulo' => 'Administración de Trabajadores',
            'trabajadores' => $this->trabajadorModel->obtenerTodosLosTrabajadores(),
            'breadcrumbs' => [
                ['label' => 'Administración', 'url' => URL_ROOT . '/admin'],
                ['label' => 'Trabajadores']
            ]
        ];
        $this->view('trabajadores/index', $data);
    }

    public function form($id = null) {
        $data = [
            'titulo' => 'Registrar Nuevo Trabajador',
            'trabajador' => (object) [
                'id' => null,
                'rut' => '',
                'nombre_completo' => '',
                'cargo' => '',
                'activo' => 1
            ],
            'breadcrumbs' => [
                ['label' => 'Administración', 'url' => URL_ROOT . '/admin'],
                ['label' => 'Trabajadores', 'url' => URL_ROOT . '/trabajadores'],
                ['label' => $id ? 'Editar' : 'Crear']
            ],
            'errores' => []
        ];

        if ($id) {
            $trabajador = $this->trabajadorModel->obtenerTrabajadorPorId($id);
            if ($trabajador) {
                $data['titulo'] = 'Editar Trabajador';
                $data['trabajador'] = $trabajador;
            } else {
                SessionHelper::setFlash('Trabajador no encontrado.', 'danger');
                $this->redirect('trabajadores/index');
            }
        }

        $this->view('trabajadores/form', $data);
    }

    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            $this->redirect('trabajadores/index');
        }

        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        $id = $_POST['id'] ?? null;
        
        $datos = [
            'rut' => trim($_POST['rut']),
            'nombre_completo' => trim($_POST['nombre_completo']),
            'cargo' => trim($_POST['cargo']),
            'activo' => isset($_POST['activo']) ? 1 : 0
        ];

        // Validaciones
        $errores = [];
        if (empty($datos['rut'])) $errores['rut'] = 'El RUT es obligatorio.';
        if (empty($datos['nombre_completo'])) $errores['nombre_completo'] = 'El nombre es obligatorio.';
        
        // Validar RUT único
        if ($this->trabajadorModel->existeRut($datos['rut'], $id)) {
            $errores['rut'] = 'Este RUT ya está registrado en el sistema.';
        }

        if (!empty($errores)) {
            $data = [
                'titulo' => $id ? 'Editar Trabajador' : 'Registrar Nuevo Trabajador',
                'trabajador' => (object) array_merge(['id' => $id], $datos),
                'errores' => $errores,
                'breadcrumbs' => [
                    ['label' => 'Administración', 'url' => URL_ROOT . '/admin'],
                    ['label' => 'Trabajadores', 'url' => URL_ROOT . '/trabajadores'],
                    ['label' => 'Error']
                ]
            ];
            $this->view('trabajadores/form', $data);
            return;
        }

        try {
            if ($id) {
                $this->trabajadorModel->actualizarTrabajador($id, $datos);
                SessionHelper::setFlash('Trabajador actualizado correctamente.', 'success');
            } else {
                $this->trabajadorModel->crearTrabajador($datos);
                SessionHelper::setFlash('Trabajador registrado correctamente.', 'success');
            }
            $this->redirect('trabajadores/index');

        } catch (Exception $e) {
            SessionHelper::setFlash('Error al guardar: ' . $e->getMessage(), 'danger');
            $this->redirect('trabajadores/form/' . $id ?? '');
        }
    }

    public function eliminar($id) {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
             $this->redirect('trabajadores/index');
        }

        try {
            $this->trabajadorModel->desactivarTrabajador($id);
            SessionHelper::setFlash('Trabajador desactivado. No podrá acceder ni ser asignado.', 'success');
        } catch (Exception $e) {
            SessionHelper::setFlash('Error: ' . $e->getMessage(), 'danger');
        }
        $this->redirect('trabajadores/index');
    }
}
?>