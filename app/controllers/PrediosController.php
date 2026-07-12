<?php
/**
 * Controlador para la Administración de Predios — Abono Track
 */
class PrediosController extends Controller {

    private $predioModel;
    private $cultivoModel;

    public function __construct() {
        parent::__construct();
        $this->protect();
        $this->predioModel  = $this->model('PredioModel');
        $this->cultivoModel = $this->model('CultivoModel');
    }

    public function index() {
        $data = [
            'titulo'      => 'Administración de Predios — Abono Track',
            // obtenerTodosLosPredios incluye cultivos Y cabezales de riego
            'predios'     => $this->predioModel->obtenerTodosLosPredios(),
            'breadcrumbs' => [
                ['label' => 'Predios'],
            ],
        ];
        $this->view('predios/index', $data);
    }

    public function form($id = null) {
        $data = [
            'titulo'  => 'Crear Nuevo Predio',
            'predio'  => (object) [
                'id'                   => null,
                'nombre'               => '',
                'cultivo_id'           => '',
                'tipo_superficie'      => 'cultivo',
                'superficie_total'     => '',
                'año_plantacion'       => date('Y'),
                'tipo_emisor'          => '',
                'caudal_lt_hora'       => '',
                'plantas_por_hectarea' => '',
                'cantidad_plantas'     => '',
                'umbral_bajo'          => 75,
                'umbral_optimo_min'    => 90,
                'umbral_optimo_max'    => 110,
                'umbral_exceso'        => 130,
            ],
            'cultivos'    => $this->cultivoModel->obtenerActivos(),
            'breadcrumbs' => [
                ['label' => 'Predios', 'url' => URL_ROOT . '/predios'],
                ['label' => $id ? 'Editar' : 'Crear'],
            ],
            'errores' => [],
        ];

        if ($id) {
            $predio = $this->predioModel->obtenerPredioPorId($id);
            if ($predio) {
                $data['titulo'] = 'Editar Predio';
                $data['predio'] = $predio;
            } else {
                SessionHelper::setFlash('Predio no encontrado.', 'danger');
                $this->redirect('predios/index');
            }
        }

        $this->view('predios/form', $data);
    }

    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') $this->redirect('predios/index');

        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        $id    = $_POST['id'] ?? null;

        $datos = [
            'nombre'               => trim($_POST['nombre']),
            'cultivo_id'           => empty($_POST['cultivo_id']) ? null : (int)$_POST['cultivo_id'],
            'tipo_superficie'      => $_POST['tipo_superficie'] ?? 'cultivo',
            'superficie_total'     => empty($_POST['superficie_total'])     ? null : trim($_POST['superficie_total']),
            'año_plantacion'       => empty($_POST['año_plantacion'])       ? null : (int)$_POST['año_plantacion'],
            'tipo_emisor'          => empty($_POST['tipo_emisor'])          ? null : trim($_POST['tipo_emisor']),
            'caudal_lt_hora'       => empty($_POST['caudal_lt_hora'])       ? null : trim($_POST['caudal_lt_hora']),
            'plantas_por_hectarea' => empty($_POST['plantas_por_hectarea']) ? null : (int)$_POST['plantas_por_hectarea'],
            'cantidad_plantas'     => empty($_POST['cantidad_plantas'])     ? null : (int)$_POST['cantidad_plantas'],
            'umbral_bajo'          => empty($_POST['umbral_bajo'])          ? 75   : (int)$_POST['umbral_bajo'],
            'umbral_optimo_min'    => empty($_POST['umbral_optimo_min'])    ? 90   : (int)$_POST['umbral_optimo_min'],
            'umbral_optimo_max'    => empty($_POST['umbral_optimo_max'])    ? 110  : (int)$_POST['umbral_optimo_max'],
            'umbral_exceso'        => empty($_POST['umbral_exceso'])        ? 130  : (int)$_POST['umbral_exceso'],
        ];

        $errores = [];
        if (empty($datos['nombre'])) $errores['nombre'] = 'El nombre es obligatorio.';

        if (!empty($datos['superficie_total']) && !empty($datos['plantas_por_hectarea']) && empty($datos['cantidad_plantas'])) {
            $datos['cantidad_plantas'] = round($datos['superficie_total'] * $datos['plantas_por_hectarea']);
        }

        if ($datos['umbral_bajo'] >= $datos['umbral_optimo_min']) {
            $errores['umbrales'] = 'El umbral Crítico debe ser menor al Óptimo.';
        }

        if (!empty($errores)) {
            $data = [
                'titulo'   => $id ? 'Editar Predio' : 'Crear Nuevo Predio',
                'predio'   => (object) array_merge(['id' => $id], $datos),
                'cultivos' => $this->cultivoModel->obtenerActivos(),
                'errores'  => $errores,
            ];
            $this->view('predios/form', $data);
            return;
        }

        try {
            if ($id) {
                $this->predioModel->actualizarPredio($id, $datos);
                SessionHelper::setFlash('Predio actualizado exitosamente.', 'success');
            } else {
                $this->predioModel->crearPredio($datos);
                SessionHelper::setFlash('Predio creado exitosamente.', 'success');
            }
            $this->redirect('predios/index');
        } catch (Exception $e) {
            SessionHelper::setFlash('Error: ' . $e->getMessage(), 'danger');
            $this->redirect('predios/form/' . $id ?? '');
        }
    }

    public function eliminar($id) {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') $this->redirect('predios/index');
        try {
            $this->predioModel->eliminarPredio($id);
            SessionHelper::setFlash('Predio desactivado.', 'success');
        } catch (Exception $e) {
            SessionHelper::setFlash('Error al desactivar.', 'danger');
        }
        $this->redirect('predios/index');
    }
}
?>
