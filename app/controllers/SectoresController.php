<?php
/**
 * Controlador Sectores (Actualizado)
 * Ahora gestiona Entidad Legal (Dueño de la fruta).
 */
class SectoresController extends Controller {
    
    private $sectorModel;
    private $predioModel;
    private $entidadModel; // Nuevo

    public function __construct() {
        parent::__construct();
        $this->protect(['Admin']); 
        $this->sectorModel = $this->model('SectorModel');
        $this->predioModel = $this->model('PredioModel');
        // Cargar modelo de Entidades para el select
        $this->entidadModel = $this->model('EntidadLegalModel');
    }

    public function index() {
        $data = [
            'titulo' => 'Administración de Sectores (Cuarteles)',
            'sectores' => $this->sectorModel->obtenerTodosLosSectores(),
            'breadcrumbs' => [['label' => 'Administración', 'url' => URL_ROOT . '/admin'], ['label' => 'Sectores']]
        ];
        $this->view('sectores/index', $data);
    }

    public function form($id = null) {
        $data = [
            'titulo' => 'Crear Nuevo Sector',
            'sector' => (object) [
                'id' => null, 'nombre' => '', 'predio_id' => null, 
                'unidad' => '', 'superficie' => '', 'cantidad_plantas' => '',
                'entidad_legal_id' => '' // Nuevo campo
            ],
            'predios' => $this->predioModel->obtenerPrediosAgricolas(),
            'entidades' => $this->entidadModel->obtenerTodas(), // Lista para el select
            'breadcrumbs' => [['label' => 'Sectores', 'url' => URL_ROOT . '/sectores'], ['label' => $id ? 'Editar' : 'Crear']],
            'errores' => []
        ];

        if ($id) {
            $sector = $this->sectorModel->obtenerSectorPorId($id);
            if ($sector) { $data['titulo'] = 'Editar Sector'; $data['sector'] = $sector; } 
            else { $this->redirect('sectores/index'); }
        }

        $this->view('sectores/form', $data);
    }

    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') $this->redirect('sectores/index');

        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        $id = $_POST['id'] ?? null;
        
        $datos = [
            'nombre' => trim($_POST['nombre']),
            'predio_id' => (int)$_POST['predio_id'],
            'unidad' => trim($_POST['unidad']),
            'superficie' => trim($_POST['superficie']),
            'cantidad_plantas' => (int)$_POST['cantidad_plantas'],
            'entidad_legal_id' => !empty($_POST['entidad_legal_id']) ? (int)$_POST['entidad_legal_id'] : null
        ];

        // Validaciones simples
        if (empty($datos['nombre'])) {
             // ... manejo de error ...
             $this->redirect('sectores/form');
        }

        try {
            if ($id) {
                $this->sectorModel->actualizarSector($id, $datos);
                SessionHelper::setFlash('Sector actualizado.', 'success');
            } else {
                $this->sectorModel->crearSector($datos);
                SessionHelper::setFlash('Sector creado.', 'success');
            }
            $this->redirect('sectores/index');

        } catch (Exception $e) {
            SessionHelper::setFlash('Error: ' . $e->getMessage(), 'danger');
            $this->redirect('sectores/form/' . $id ?? '');
        }
    }

    public function eliminar($id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->sectorModel->eliminarSector($id);
            SessionHelper::setFlash('Sector desactivado.', 'success');
        }
        $this->redirect('sectores/index');
    }
}
?>