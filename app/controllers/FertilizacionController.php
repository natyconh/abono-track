<?php
/**
 * Controlador Operativo de Fertirrigación — Abono Track
 * Maneja: Registro, Edición y Configuración
 */
class FertilizacionController extends Controller {

    private $fertilizacionService;
    private $fertilizanteModel;
    private $predioModel;
    private $configRiegoModel;

    public function __construct() {
        parent::__construct();

        require_once APP_ROOT . '/core/FertilizacionService.php';
        $this->fertilizacionService = new FertilizacionService($this->usuario_id);

        $this->fertilizanteModel = $this->model('FertilizanteModel');
        $this->predioModel       = $this->model('PredioModel');

        require_once APP_ROOT . '/models/ConfiguracionRiegoModel.php';
        $this->configRiegoModel = new ConfiguracionRiegoModel($this->db, $this->usuario_id);

        $this->protect(['Admin', 'Usuario_riego', 'Usuario_general']);
    }

    public function index() {
        $this->cargarFormulario();
    }

    public function editar($id) {
        if (!$id) $this->redirect('fertilizacion/historial');

        $registro = $this->fertilizacionService->obtenerCabezalPorId($id);

        if (!$registro) {
            SessionHelper::setFlash('Registro no encontrado.', 'danger');
            $this->redirect('fertilizacion/historial');
        }

        $this->cargarFormulario($registro);
    }

    private function cargarFormulario($registro = null) {
        $data = [
            'titulo'        => $registro ? 'Editar Aplicación #' . $registro->id : 'Registro de Fertirrigación — Abono Track',
            'predios'       => $this->predioModel->obtenerPuntosInyeccion(),
            'fertilizantes' => $this->fertilizanteModel->obtenerActivos(),
            'fecha_hoy'     => $registro ? $registro->fecha : date('Y-m-d'),
            'registro'      => $registro,
            'breadcrumbs'   => [
                ['label' => 'Historial', 'url' => URL_ROOT . '/fertilizacion/historial'],
                ['label' => $registro ? 'Editar' : 'Nuevo Registro'],
            ],
        ];
        $this->view('fertilizacion/registro', $data);
    }

    public function guardarRegistro() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') $this->redirect('fertilizacion/index');

        $id         = $_POST['id'] ?? null;
        $fecha      = $_POST['fecha'];
        $cabezal_id = $_POST['predio_cabezal_id'];
        $fertilizantes = $_POST['fertilizante_id'] ?? [];
        $cantidades    = $_POST['cantidad_aplicada'] ?? [];

        if (empty($fecha) || empty($cabezal_id)) {
            SessionHelper::setFlash('La fecha y el lugar son obligatorios.', 'danger');
            $this->redirect('fertilizacion/index');
            return;
        }

        $registros_exitosos = 0;
        $errores = 0;

        try {
            if ($id) {
                $datos = [
                    'usuario_id'       => $this->usuario_id,
                    'fecha'            => $fecha,
                    'predio_cabezal_id' => $cabezal_id,
                    'fertilizante_id'  => $fertilizantes[0],
                    'cantidad_aplicada' => $cantidades[0],
                ];
                if ($this->fertilizacionService->actualizarAplicacion($id, $datos)) {
                    SessionHelper::setFlash('Registro actualizado correctamente.', 'success');
                } else {
                    SessionHelper::setFlash('Error al actualizar el registro.', 'danger');
                }
            } else {
                for ($i = 0; $i < count($fertilizantes); $i++) {
                    if (empty($fertilizantes[$i]) || empty($cantidades[$i])) continue;

                    $datos = [
                        'usuario_id'        => $this->usuario_id,
                        'fecha'             => $fecha,
                        'predio_cabezal_id' => $cabezal_id,
                        'fertilizante_id'   => $fertilizantes[$i],
                        'cantidad_aplicada' => $cantidades[$i],
                    ];

                    $duplicado = $this->fertilizacionService->verificarDuplicado(
                        $datos['fecha'], $datos['predio_cabezal_id'], $datos['fertilizante_id']
                    );

                    if ($duplicado) { $errores++; continue; }

                    if ($this->fertilizacionService->registrarAplicacion($datos)) {
                        $registros_exitosos++;
                    } else {
                        $errores++;
                    }
                }

                if ($registros_exitosos > 0) {
                    $msg = "Se registraron {$registros_exitosos} productos en la mezcla correctamente.";
                    if ($errores > 0) $msg .= " (Hubo {$errores} omitidos por duplicidad o error).";
                    SessionHelper::setFlash($msg, 'success');
                } elseif ($errores > 0) {
                    SessionHelper::setFlash('No se pudo registrar la mezcla. Posibles duplicados.', 'warning');
                } else {
                    SessionHelper::setFlash('No se enviaron datos válidos.', 'info');
                }
            }
        } catch (Exception $e) {
            SessionHelper::setFlash('Excepción del sistema: ' . $e->getMessage(), 'danger');
        }

        $this->redirect('fertilizacion/historial');
    }

    public function verificarExistenciaAjax() {
        $fecha     = $_POST['fecha']    ?? '';
        $cabezal   = $_POST['cabezal']  ?? '';
        $producto  = $_POST['producto'] ?? '';
        $excludeId = $_POST['exclude_id'] ?? null;

        if (!$fecha || !$cabezal || !$producto) {
            $this->respondJson(['existe' => false]);
            return;
        }

        $registro = $this->fertilizacionService->verificarDuplicado($fecha, $cabezal, $producto, $excludeId);

        if ($registro) {
            $this->respondJson([
                'existe'  => true,
                'mensaje' => 'Atención: Ya existe un registro de ' . floatval($registro->cantidad_aplicada) . ' unidades para este producto en esta fecha y lugar.',
            ]);
        } else {
            $this->respondJson(['existe' => false]);
        }
    }

    public function historial() {
        $mes      = $_GET['mes']   ?? date('m');
        $year     = $_GET['year']  ?? date('Y');
        $orderBy  = $_GET['sort']  ?? 'fecha';
        $orderDir = $_GET['dir']   ?? 'DESC';

        $registros = $this->fertilizacionService->obtenerHistorialCabezal($mes, $year, $orderBy, $orderDir);
        $resumen   = $this->fertilizacionService->obtenerResumenMensual($mes, $year);

        $data = [
            'titulo'      => 'Bitácora de Fertilización — Abono Track',
            'mes_actual'  => $mes,
            'year_actual' => $year,
            'sort'        => $orderBy,
            'dir'         => $orderDir,
            'registros'   => $registros,
            'resumen'     => $resumen,
            'breadcrumbs' => [
                ['label' => 'Fertirrigación', 'url' => URL_ROOT . '/fertilizacion'],
                ['label' => 'Historial'],
            ],
        ];
        $this->view('fertilizacion/historial', $data);
    }

    public function reporteNutricional() {
        $mesActual  = date('n');
        $yearActual = date('Y');

        if ($mesActual >= 9) {
            $inicioTemporada = $yearActual . '-09-01';
            $finTemporada    = ($yearActual + 1) . '-08-31';
        } else {
            $inicioTemporada = ($yearActual - 1) . '-09-01';
            $finTemporada    = $yearActual . '-08-31';
        }

        $datosNutricionales = $this->fertilizacionService->obtenerReporteNutricionalTemporada($inicioTemporada, date('Y-m-d'));

        $data = [
            'titulo'           => 'Reporte Nutricional Acumulado — Abono Track',
            'inicio_temporada' => $inicioTemporada,
            'datos'            => $datosNutricionales,
            'breadcrumbs'      => [
                ['label' => 'Fertirrigación', 'url' => URL_ROOT . '/fertilizacion'],
                ['label' => 'Reporte Nutricional'],
            ],
        ];

        $this->view('fertilizacion/reporte_nutricional', $data);
    }

    public function exportarExcelNutricional() {
        $mesActual       = date('n');
        $yearActual      = date('Y');
        $inicioTemporada = ($mesActual >= 9) ? $yearActual . '-09-01' : ($yearActual - 1) . '-09-01';

        $datos    = $this->fertilizacionService->obtenerReporteNutricionalTemporada($inicioTemporada, date('Y-m-d'));
        $filename = 'Reporte_Nutricional_AbonoTrack_' . date('Y-m-d') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);

        $output = fopen('php://output', 'w');
        fputs($output, "\xEF\xBB\xBF");
        fputcsv($output, ['Sector/Predio', 'Cultivo', 'Superficie (Ha)', 'N (Unidades/Ha)', 'P (Unidades/Ha)', 'K (Unidades/Ha)', 'Total Extra (Unidades)'], ';');

        foreach ($datos as $row) {
            fputcsv($output, [
                $row->predio,
                $row->cultivo ?? 'N/A',
                number_format($row->hectareas, 2, ',', ''),
                number_format($row->n_ha,      2, ',', ''),
                number_format($row->p_ha,      2, ',', ''),
                number_format($row->k_ha,      2, ',', ''),
                number_format($row->total_extra, 2, ',', ''),
            ], ';');
        }

        fclose($output);
        exit();
    }

    public function generarLinkPublico() {
        $this->protect(['Admin']);

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $token = $this->fertilizacionService->generarTokenReporte($this->usuario_id);
            if ($token) {
                $link = URL_ROOT . '/publico/reporte/' . $token;
                $this->respondJson(['success' => true, 'link' => $link]);
            } else {
                $this->respondJson(['success' => false, 'message' => 'Error al generar token.']);
            }
        }
    }

    public function verDetalleDistribucion($cabezalId) {
        $detalle = $this->fertilizacionService->obtenerDetalleDistribucion($cabezalId);
        $this->respondJson(['success' => true, 'detalle' => $detalle]);
    }

    public function configuracion() {
        $this->protect(['Admin']);
        $predios = $this->predioModel->obtenerPuntosInyeccion();
        foreach ($predios as $p) {
            $p->distribuciones = $this->configRiegoModel->obtenerPorOrigen($p->id);
        }
        $data = [
            'titulo'  => 'Configuración de Distribución Hidráulica — Abono Track',
            'predios' => $predios,
        ];
        $this->view('fertilizacion/configuracion', $data);
    }

    public function getDistribuciones($origen) {
        $this->protect(['Admin']);
        $this->respondJson($this->configRiegoModel->obtenerPorOrigen($origen));
    }

    public function guardarDistribucion() {
        $this->protect(['Admin']);
        $origen     = $_POST['origen_id'];
        $destino    = $_POST['destino_id'];
        $porcentaje = $_POST['porcentaje'];
        if ($origen == $destino) {
            $this->respondJson(['success' => false, 'message' => 'Origen y destino iguales.']);
            return;
        }
        if ($this->configRiegoModel->guardarRelacion($origen, $destino, $porcentaje)) {
            $this->respondJson(['success' => true]);
        } else {
            $this->respondJson(['success' => false, 'message' => 'Error al guardar.']);
        }
    }

    public function eliminarDistribucion() {
        $this->protect(['Admin']);
        $id = $_POST['id'];
        if ($this->configRiegoModel->eliminarRelacion($id)) {
            $this->respondJson(['success' => true]);
        } else {
            $this->respondJson(['success' => false]);
        }
    }
}
?>
