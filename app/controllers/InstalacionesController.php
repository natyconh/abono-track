<?php
/**
 * Controlador para la Administración de Instalaciones
 * MODIFICADO: Adaptado a Schema v2 y DIP/Multi-Tenancy
 */
class InstalacionesController extends Controller {
    
    private $instalacionModel;
    private $predioModel;
    private $sectorModel;

    public function __construct() {
        parent::__construct();
        $this->protect(['Admin']); 
        
        $this->instalacionModel = $this->model('InstalacionModel');
        $this->predioModel = $this->model('PredioModel');
        $this->sectorModel = $this->model('SectorModel');
    }

    public function index() {
        $instalaciones = $this->instalacionModel->obtenerTodasLasInstalaciones();
        $data = [
            'titulo' => 'Administración de Instalaciones',
            'instalaciones' => $instalaciones,
            'breadcrumbs' => [
                ['label' => 'Administración', 'url' => URL_ROOT . '/admin'],
                ['label' => 'Instalaciones'] // Página actual
            ]
        ];
        $this->view('instalaciones/index', $data);
    }

    public function form($id = null) {
        $data = [
            'titulo' => 'Crear Nueva Instalación',
            'instalacion' => (object) [ // Schema v2
                'id' => null,
                'nombre' => '',
                'predio_id' => null,
                'sector_id' => null,
                'latitud' => '',
                'longitud' => ''
            ],
            'predios' => $this->predioModel->obtenerPrediosAgricolas(),
            'sectores' => $this->sectorModel->obtenerSectoresActivos(),
            'errores' => [],
            'breadcrumbs' => [
                ['label' => 'Administración', 'url' => URL_ROOT . '/admin'],
                ['label' => 'Instalaciones', 'url' => URL_ROOT . '/instalaciones'],
                ['label' => $id ? 'Editar' : 'Crear'] // Página actual
            ],
            'load_maps' => true, // <-- ¡CAMBIO AÑADIDO! (Para cargar la API de Google Maps)
            'instalaciones' => [] // <-- ¡CAMBIO AÑADIDO! (Para que footer.php no falle al definir INSTALACIONES_DATA)
        ];

        if ($id) {
            $instalacion = $this->instalacionModel->obtenerInstalacionPorId($id);
            if ($instalacion) {
                $data['titulo'] = 'Editar Instalación';
                $data['instalacion'] = $instalacion;
            } else {
                SessionHelper::setFlash('Instalación no encontrada.', 'danger');
                $this->redirect('instalaciones/index');
            }
        }

        $this->view('instalaciones/form', $data);
    }

    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            $this->redirect('instalaciones/index');
        }

        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        $id = $_POST['id'] ?? null; // Schema v2
        
        $datos = [
            'nombre' => trim($_POST['nombre']),
            'predio_id' => empty($_POST['predio_id']) ? null : (int)$_POST['predio_id'],
            'sector_id' => empty($_POST['sector_id']) ? null : (int)$_POST['sector_id'],
            'latitud' => empty($_POST['latitud']) ? null : trim($_POST['latitud']),
            'longitud' => empty($_POST['longitud']) ? null : trim($_POST['longitud'])
        ];

        // Validación
        $errores = [];
        if (empty($datos['nombre'])) $errores['nombre'] = 'El nombre es obligatorio.';
        if (empty($datos['predio_id'])) $errores['predio_id'] = 'Debe seleccionar un predio.';
        if (!is_null($datos['latitud']) && !is_numeric($datos['latitud'])) $errores['latitud'] = 'La latitud debe ser un número.';
        if (!is_null($datos['longitud']) && !is_numeric($datos['longitud'])) $errores['longitud'] = 'La longitud debe ser un número.';

        if (!empty($errores)) {
            $data = [
                'titulo' => $id ? 'Editar Instalación' : 'Crear Nueva Instalación',
                'instalacion' => (object) array_merge(['id' => $id], $datos),
                'predios' => $this->predioModel->obtenerPrediosAgricolas(),
                'sectores' => $this->sectorModel->obtenerSectoresActivos(),
                'errores' => $errores
            ];
            $this->view('instalaciones/form', $data);
            return;
        }

        try {
            if ($id) {
                $this->instalacionModel->actualizarInstalacion($id, $datos);
                SessionHelper::setFlash('Instalación actualizada.', 'success');
            } else {
                $this->instalacionModel->crearInstalacion($datos);
                SessionHelper::setFlash('Instalación creada.', 'success');
            }
            $this->redirect('instalaciones/index');

        } catch (Exception $e) {
            SessionHelper::setFlash('Ocurrió un error: ' . $e->getMessage(), 'danger');
            $this->redirect('instalaciones/form/' . $id ?? '');
        }
    }

    public function eliminar($id) {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
             $this->redirect('instalaciones/index');
        }

        if (!$this->instalacionModel->obtenerInstalacionPorId($id)) {
            SessionHelper::setFlash('Instalación no encontrada.', 'danger');
            $this->redirect('instalaciones/index');
        }
        
        try {
            $this->instalacionModel->eliminarInstalacion($id); // Soft delete
            SessionHelper::setFlash('Instalación desactivada.', 'success');
        } catch (Exception $e) {
            SessionHelper::setFlash('No se pudo desactivar. Es posible que esté en uso.', 'danger');
        }
        $this->redirect('instalaciones/index');
    }
}
?>