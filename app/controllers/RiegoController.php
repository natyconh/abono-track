<?php
// app/controllers/RiegoController.php — Abono Track
// Controlador para la Gestión de Riego. Sin roles, sin sectores.

class RiegoController extends Controller {

    private $riegoModel;
    private $predioModel;

    public function __construct() {
        parent::__construct();
        $this->protect(); // Protege solo verificando sesión activa
        $this->riegoModel  = $this->model('RiegoModel');
        $this->predioModel = $this->model('PredioModel');
    }

    public function index() {
        $fecha_carga = $_GET['fecha'] ?? date('Y-m-d');
        $data = [
            'titulo'             => 'Registro Diario de Riego',
            'fecha_seleccionada' => $fecha_carga,
            'predios'            => $this->predioModel->obtenerPrediosAgricolas(),
            'breadcrumbs'        => [['label' => 'Registro de Riego']],
        ];
        $this->view('riego/index', $data);
    }

    public function obtenerDatosDia($fecha) {
        if (empty($fecha)) {
            $this->respondJson(['success' => false]);
            return;
        }
        $registros = $this->riegoModel->obtenerRegistrosPorFecha($fecha);
        $respuesta = [];

        foreach ($registros as $predio_id => $fila) {
            // Sin control de roles, el dueño de la cuenta puede editarlo siempre
            $respuesta[$predio_id] = [
                'id_riego'       => $fila->id,
                'tiempo_riego'   => $fila->tiempo_riego,
                'usuario_nombre' => $fila->nombre_usuario
            ];
        }
        $this->respondJson(['success' => true, 'datos' => $respuesta]);
    }

    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') $this->redirect('riego/index');
        
        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
        $fecha   = $_POST['fecha'] ?? null;
        $tiempos = $_POST['tiempo_riego'] ?? [];

        if (empty($fecha)) {
            SessionHelper::setFlash('Error: La fecha es obligatoria.', 'danger');
            $this->redirect('riego/index');
            return;
        }

        $registros_procesados = 0;

        try {
            foreach ($tiempos as $predio_id => $tiempo) {
                if ($tiempo === '' || $tiempo === null) continue;
                
                $datos_riego = [
                    'fecha' => $fecha, 
                    'predio_id' => (int)$predio_id, 
                    'tiempo_riego' => (int)$tiempo
                ];
                
                $existente = $this->riegoModel->obtenerRegistroPorFechaYPredio($fecha, $predio_id);

                if ($existente) {
                    $this->riegoModel->actualizarRegistroRiego($existente->id, $datos_riego);
                } else {
                    $this->riegoModel->crearRegistroRiego($datos_riego);
                }
                $registros_procesados++;
            }

            if ($registros_procesados > 0) {
                SessionHelper::setFlash("Se guardaron/actualizaron $registros_procesados registros de riego correctamente.", 'success');
            } else {
                SessionHelper::setFlash('No se ingresaron datos nuevos.', 'info');
            }
        } catch (Exception $e) {
            SessionHelper::setFlash('Error general: ' . $e->getMessage(), 'danger');
        }

        $this->redirect('riego/index');
    }

    public function admin() {
        $this->protect();

        $limit  = 30;
        $page   = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;
        $offset = ($page - 1) * $limit;

        $filtro_tipo  = $_GET['filtro_tipo'] ?? 'mes';
        $fecha_inicio = null;
        $fecha_fin    = null;

        if ($filtro_tipo === 'rango' && !empty($_GET['fecha_start']) && !empty($_GET['fecha_end'])) {
            $fecha_inicio = $_GET['fecha_start'];
            $fecha_fin    = $_GET['fecha_end'];

        } elseif ($filtro_tipo === 'semana' && !empty($_GET['semana'])) {
            $dto = new DateTime();
            $dto->setISODate((int)substr($_GET['semana'], 0, 4), (int)substr($_GET['semana'], 6));
            $fecha_inicio = $dto->format('Y-m-d');
            $dto->modify('+6 days');
            $fecha_fin = $dto->format('Y-m-d');

        } elseif ($filtro_tipo === 'mes') {
            $mes_ano      = $_GET['mes'] ?? date('Y-m');
            $fecha_inicio = $mes_ano . '-01';
            $fecha_fin    = date('Y-m-t', strtotime($fecha_inicio));
            $_GET['mes']  = $mes_ano;

        } elseif ($filtro_tipo === 'temporada' && !empty($_GET['temporada_year'])) {
            $year         = (int)$_GET['temporada_year'];
            $fecha_inicio = $year . '-07-01';
            $fecha_fin    = ($year + 1) . '-06-30';
        }

        $filtros_db = [
            'fecha_inicio' => $fecha_inicio,
            'fecha_fin'    => $fecha_fin,
            'predio_id'    => $_GET['predio_id'] ?? null,
        ];

        $historial   = $this->riegoModel->obtenerHistorialRiego($filtros_db, $limit, $offset);
        $total_rows  = $this->riegoModel->contarHistorialRiego($filtros_db);
        $total_pages = ceil($total_rows / $limit);

        $data = [
            'titulo'        => 'Historial de Registros de Riego',
            'historial'     => $historial,
            'predios'       => $this->predioModel->obtenerPrediosAgricolas(),
            'pagination'    => [
                'current_page' => $page,
                'total_pages'  => $total_pages,
                'total_rows'   => $total_rows,
            ],
            'filtros'        => $_GET,
            'use_datatables' => false,
            'breadcrumbs'    => [['label' => 'Historial de Riego']],
        ];

        $this->view('riego/admin', $data);
    }

    public function form($id = null) {
        $this->protect();
        $data = [
            'titulo'              => 'Crear Registro Manual de Riego',
            'riego'               => (object) ['id' => null, 'fecha' => date('Y-m-d'), 'predio_id' => null, 'tiempo_riego' => ''],
            'predios'             => $this->predioModel->obtenerPrediosAgricolas(),
            'errores'             => [],
        ];

        if ($id) {
            $riego = $this->riegoModel->obtenerRegistroPorId($id);
            if ($riego) {
                $data['titulo'] = 'Editar Registro de Riego #' . $id;
                $data['riego']  = $riego;
            } else {
                SessionHelper::setFlash('Registro no encontrado.', 'danger');
                $this->redirect('riego/admin');
            }
        }
        $this->view('riego/form', $data);
    }

    public function guardarAdmin() {
        $this->protect();
        if ($_SERVER['REQUEST_METHOD'] != 'POST') $this->redirect('riego/admin');
        
        $_POST  = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        $id     = $_POST['id'] ?? null;
        $datos  = [
            'fecha'        => $_POST['fecha'], 
            'predio_id'    => (int)$_POST['predio_id'], 
            'tiempo_riego' => (int)$_POST['tiempo_riego']
        ];

        try {
            if ($id) {
                $this->riegoModel->actualizarRegistroRiego($id, $datos);
                SessionHelper::setFlash('Registro actualizado.', 'success');
            } else {
                $existente = $this->riegoModel->obtenerRegistroPorFechaYPredio($datos['fecha'], $datos['predio_id']);
                if ($existente) {
                    $this->riegoModel->actualizarRegistroRiego($existente->id, $datos);
                    SessionHelper::setFlash('Registro existente fue sobrescrito.', 'warning');
                } else {
                    $this->riegoModel->crearRegistroRiego($datos);
                    SessionHelper::setFlash('Registro creado.', 'success');
                }
            }
            $this->redirect('riego/admin');
        } catch (Exception $e) {
            SessionHelper::setFlash('Error al guardar: ' . $e->getMessage(), 'danger');
            $this->redirect('riego/form/' . $id ?? '');
        }
    }

    public function eliminar($id) {
        $this->protect();
        if ($_SERVER['REQUEST_METHOD'] != 'POST') $this->redirect('riego/admin');
        try {
            if ($this->riegoModel->eliminarRegistroRiego($id)) {
                SessionHelper::setFlash('Registro de riego eliminado.', 'success');
            } else {
                throw new Exception('No se pudo eliminar el registro.');
            }
        } catch (Exception $e) {
            SessionHelper::setFlash('Error: ' . $e->getMessage(), 'danger');
        }
        $this->redirect('riego/admin');
    }

    public function verificarExistenciaRiego($fecha = null, $predio_id = 0) {
        $predio_id_sanitizado = (int)$predio_id;
        if (empty($fecha) || $predio_id_sanitizado <= 0) {
            $this->respondJson(['existe' => false]);
            return;
        }
        $registro = $this->riegoModel->obtenerRegistroPorFechaYPredio($fecha, $predio_id_sanitizado);
        if ($registro) {
            $this->respondJson([
                'existe'   => true,
                'registro' => $registro,
            ]);
        } else {
            $this->respondJson(['existe' => false]);
        }
    }

    public function miHistorial() {
        $this->protect();

        $mes  = $_GET['mes']  ?? date('m');
        $year = $_GET['year'] ?? date('Y');

        $resumen_dias   = $this->riegoModel->obtenerResumenMensual($mes, $year);
        $calendario_data = [];
        foreach ($resumen_dias as $dia) {
            $calendario_data[$dia->fecha] = $dia;
        }

        $data = [
            'titulo'          => 'Mi Historial de Riego — Abono Track',
            'mes_actual'      => $mes,
            'year_actual'     => $year,
            'calendario_data' => $calendario_data,
            'breadcrumbs'     => [
                ['label' => 'Registro de Riego', 'url' => URL_ROOT . '/riego'],
                ['label' => 'Mi Historial'],
            ],
        ];

        $this->view('riego/mi_historial', $data);
    }
}
?>