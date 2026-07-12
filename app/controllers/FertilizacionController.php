<?php
/**
 * Controlador Operativo de Fertirrigación — Abono Track
 * Maneja: Registro, Edición, Configuración Hidráulica, Historial y Reportes
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

        $this->protect();
    }

    // =========================================================
    //  VISTAS PRINCIPALES
    // =========================================================

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

    /**
     * Configuración Hidráulica — distribución porcentual entre cabezales y predios.
     * Ruta: /fertilizacion/configuracion
     */
    public function configuracion() {
        $predios = $this->predioModel->obtenerTodosConDistribuciones();

        $predios_fisicos = array_filter($predios, function($p) {
            return $p->tipo_superficie !== 'cabezal_virtual';
        });

        $data = [
            'titulo'          => 'Configuración Hidráulica — Abono Track',
            'predios'         => $predios,
            'predios_fisicos' => array_values($predios_fisicos),
            'breadcrumbs'     => [
                ['label' => 'Fertirrigación', 'url' => URL_ROOT . '/fertilizacion'],
                ['label' => 'Configuración Hidráulica'],
            ],
        ];

        $this->view('fertilizacion/configuracion', $data);
    }

    // =========================================================
    //  AJAX — CONFIGURACIÓN HIDRÁULICA
    // =========================================================

    /**
     * Crea un cabezal virtual (predio tipo cabezal_virtual) desde el modal.
     * POST /fertilizacion/crearCabezalRapido
     */
    public function crearCabezalRapido() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->respondJson(['success' => false, 'message' => 'Método no permitido']);
            return;
        }

        $nombre = trim($_POST['nombre_cabezal'] ?? '');

        if (empty($nombre)) {
            $this->respondJson(['success' => false, 'message' => 'El nombre del cabezal es obligatorio.']);
            return;
        }

        $datos = [
            'nombre'              => $nombre,
            'cultivo_id'          => null,
            'tipo_superficie'     => 'cabezal_virtual',
            'superficie_total'    => 0,
            'año_plantacion'      => null,
            'tipo_emisor'         => null,
            'caudal_lt_hora'      => 0,
            'plantas_por_hectarea'=> 0,
            'cantidad_plantas'    => 0,
            'umbral_bajo'         => 75,
            'umbral_optimo_min'   => 90,
            'umbral_optimo_max'   => 110,
            'umbral_exceso'       => 130,
        ];

        if ($this->predioModel->crearPredio($datos)) {
            $this->respondJson(['success' => true, 'message' => "Cabezal '{$nombre}' creado correctamente."]);
        } else {
            $this->respondJson(['success' => false, 'message' => 'Error al crear el cabezal en la base de datos.']);
        }
    }

    /**
     * Elimina (físicamente) un cabezal virtual si no tiene registros dependientes.
     * POST /fertilizacion/eliminarCabezalVirtual
     */
    public function eliminarCabezalVirtual() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->respondJson(['success' => false, 'message' => 'Método no permitido']);
            return;
        }

        $id = intval($_POST['id'] ?? 0);

        if (!$id) {
            $this->respondJson(['success' => false, 'message' => 'ID inválido.']);
            return;
        }

        // Verificar que pertenece al usuario y es cabezal_virtual
        $predio = $this->predioModel->obtenerPredioPorId($id);
        if (!$predio || $predio->tipo_superficie !== 'cabezal_virtual') {
            $this->respondJson(['success' => false, 'message' => 'Cabezal no encontrado o no es virtual.']);
            return;
        }

        // Intentar borrado físico; si tiene FK constraints, hacer soft-delete
        if ($this->predioModel->eliminarFisicamente($id)) {
            $this->respondJson(['success' => true, 'message' => 'Cabezal eliminado.']);
        } else {
            // Fallback: soft-delete (activo = 0)
            $this->predioModel->eliminarPredio($id);
            $this->respondJson(['success' => true, 'message' => 'Cabezal ocultado (tenía registros asociados).']);
        }
    }

    /**
     * Devuelve las distribuciones actuales de un predio origen.
     * GET /fertilizacion/getDistribuciones/{id}
     */
    public function getDistribuciones($origenId = null) {
        if (!$origenId) {
            $this->respondJson([]);
            return;
        }
        $distribuciones = $this->configRiegoModel->obtenerPorOrigen($origenId);
        $this->respondJson($distribuciones);
    }

    /**
     * Guarda (inserta o actualiza) una relación origen → destino con porcentaje.
     * POST /fertilizacion/guardarDistribucion
     */
    public function guardarDistribucion() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->respondJson(['success' => false, 'message' => 'Método no permitido']);
            return;
        }

        $origen_id  = intval($_POST['origen_id']  ?? 0);
        $destino_id = intval($_POST['destino_id'] ?? 0);
        $porcentaje = floatval($_POST['porcentaje'] ?? 0);

        if (!$origen_id || !$destino_id || $porcentaje <= 0) {
            $this->respondJson(['success' => false, 'message' => 'Datos incompletos o inválidos.']);
            return;
        }

        if ($origen_id === $destino_id) {
            $this->respondJson(['success' => false, 'message' => 'El origen y destino no pueden ser el mismo predio.']);
            return;
        }

        if ($this->configRiegoModel->guardarRelacion($origen_id, $destino_id, $porcentaje)) {
            $distribuciones = $this->configRiegoModel->obtenerPorOrigen($origen_id);
            $this->respondJson(['success' => true, 'distribuciones' => $distribuciones]);
        } else {
            $this->respondJson(['success' => false, 'message' => 'Error al guardar la distribución.']);
        }
    }

    /**
     * Elimina una relación de distribución por su ID.
     * POST /fertilizacion/eliminarDistribucion
     */
    public function eliminarDistribucion() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->respondJson(['success' => false, 'message' => 'Método no permitido']);
            return;
        }

        $id = intval($_POST['id'] ?? 0);

        if (!$id) {
            $this->respondJson(['success' => false, 'message' => 'ID inválido.']);
            return;
        }

        if ($this->configRiegoModel->eliminarRelacion($id)) {
            $this->respondJson(['success' => true]);
        } else {
            $this->respondJson(['success' => false, 'message' => 'No se pudo eliminar la relación.']);
        }
    }

    /**
     * Calcula la proporción de superficie entre predios seleccionados
     * para el Asistente Automático.
     * POST /fertilizacion/calcularProporcionSuperficie
     */
    public function calcularProporcionSuperficie() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->respondJson(['success' => false, 'message' => 'Método no permitido']);
            return;
        }

        $ids = $_POST['ids'] ?? [];
        if (empty($ids) || !is_array($ids)) {
            $this->respondJson(['success' => false, 'message' => 'No se enviaron predios.']);
            return;
        }

        $ids = array_map('intval', $ids);

        // Obtener superficie_total de cada predio seleccionado
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $this->db->query(
            "SELECT id, nombre, superficie_total
             FROM predios
             WHERE id IN ({$placeholders})
               AND usuario_id = ?
               AND activo = 1"
        );
        // Bind posicional manual
        $stmt = $this->db->getStatement();
        foreach ($ids as $i => $val) {
            $stmt->bindValue($i + 1, $val, PDO::PARAM_INT);
        }
        $stmt->bindValue(count($ids) + 1, $this->usuario_id, PDO::PARAM_INT);
        $stmt->execute();
        $predios = $stmt->fetchAll(PDO::FETCH_OBJ);

        $totalSuperficie = array_sum(array_column((array)$predios, 'superficie_total'));

        if ($totalSuperficie <= 0) {
            $this->respondJson(['success' => false, 'message' => 'Los predios seleccionados no tienen superficie registrada.']);
            return;
        }

        $proporciones = [];
        foreach ($predios as $p) {
            $proporciones[] = [
                'id'          => $p->id,
                'nombre'      => $p->nombre,
                'porcentaje'  => round(($p->superficie_total / $totalSuperficie) * 100, 2),
            ];
        }

        $this->respondJson(['success' => true, 'proporciones' => $proporciones]);
    }

    // =========================================================
    //  REGISTRO DE FERTIRRIGACIÓN
    // =========================================================

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
                    'usuario_id'        => $this->usuario_id,
                    'fecha'             => $fecha,
                    'predio_cabezal_id' => $cabezal_id,
                    'fertilizante_id'   => $fertilizantes[0],
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
        $fecha     = $_POST['fecha']      ?? '';
        $cabezal   = $_POST['cabezal']    ?? '';
        $producto  = $_POST['producto']   ?? '';
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

    // =========================================================
    //  HISTORIAL Y REPORTES
    // =========================================================

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
                number_format($row->hectareas,   2, ',', ''),
                number_format($row->n_ha,        2, ',', ''),
                number_format($row->p_ha,        2, ',', ''),
                number_format($row->k_ha,        2, ',', ''),
                number_format($row->total_extra, 2, ',', ''),
            ], ';');
        }

        fclose($output);
        exit();
    }

    public function generarLinkPublico() {
        $this->protect();
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

    public function eliminarRegistro($id) {
        if (!$id) {
            SessionHelper::setFlash('ID inválido.', 'danger');
            $this->redirect('fertilizacion/historial');
        }

        $resultado = $this->fertilizacionService->eliminarAplicacion($id);

        if ($resultado) {
            SessionHelper::setFlash('Registro eliminado.', 'success');
        } else {
            SessionHelper::setFlash('No se pudo eliminar el registro.', 'danger');
        }

        $this->redirect('fertilizacion/historial');
    }
}
?>
