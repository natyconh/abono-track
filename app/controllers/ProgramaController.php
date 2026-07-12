<?php
/**
 * ProgramaController — Abono Track
 */
class ProgramaController extends Controller {

    private $programaModel;
    private $predioModel;
    private $cultivoModel;
    private $fertilizacionService;

    public function __construct() {
        parent::__construct();
        $this->protect();

        require_once APP_ROOT . '/models/ProgramaFertilizacionModel.php';
        require_once APP_ROOT . '/core/FertilizacionService.php';

        $this->programaModel        = new ProgramaFertilizacionModel($this->db, $this->usuario_id);
        $this->predioModel          = $this->model('PredioModel');
        $this->cultivoModel         = $this->model('CultivoModel');
        $this->fertilizacionService = new FertilizacionService();
    }

    // -----------------------------------------------------------------------
    // INDEX
    // -----------------------------------------------------------------------
    public function index() {
        $resumen = $this->programaModel->listarResumen();
        $predios = $this->predioModel->obtenerPuntosInyeccion();

        $data = [
            'titulo'      => 'Programas de Fertilización — Abono Track',
            'resumen'     => $resumen,
            'predios'     => $predios,
            'breadcrumbs' => [['label' => 'Programas de Fertilización']],
        ];
        $this->view('programa/index', $data);
    }

    // -----------------------------------------------------------------------
    // CREATE
    // -----------------------------------------------------------------------
    public function create() {
        $predios  = $this->predioModel->obtenerTodosLosPredios();
        $cultivos = $this->cultivoModel->obtenerTodos();
        $mes = (int)date('n');
        $temporadaDefault = $mes >= 9 ? date('Y') : (date('Y') - 1);

        $data = [
            'titulo'            => 'Nuevo Programa de Fertilización',
            'predios'           => $predios,
            'cultivos'          => $cultivos,
            'temporada_default' => $temporadaDefault,
            'breadcrumbs'       => [
                ['label' => 'Programas', 'url' => URL_ROOT . '/programa'],
                ['label' => 'Nuevo Programa'],
            ],
        ];
        $this->view('programa/create', $data);
    }

    // -----------------------------------------------------------------------
    // STORE (POST)
    // -----------------------------------------------------------------------
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('programa'); }

        $predioId  = intval($_POST['predio_id'] ?? 0);
        $temporada = trim($_POST['temporada']   ?? '');
        $semanas   = $_POST['semana']            ?? [];
        $fechas    = $_POST['fecha_estimada']    ?? [];
        $ns        = $_POST['n_objetivo']        ?? [];
        $ps        = $_POST['p_objetivo']        ?? [];
        $ks        = $_POST['k_objetivo']        ?? [];
        $obs       = $_POST['observaciones']     ?? [];
        $cultivoId = intval($_POST['cultivo_id'] ?? 0) ?: null;

        if (!$predioId || !$temporada || empty($semanas)) {
            SessionHelper::setFlash('Faltan datos obligatorios (predio, temporada, semanas).', 'danger');
            $this->redirect('programa/create');
            return;
        }

        $filas = $this->buildFilas($predioId, $cultivoId, $temporada, $semanas, $fechas, $ns, $ps, $ks, $obs);

        if (empty($filas)) {
            SessionHelper::setFlash('No se enviaron semanas válidas.', 'warning');
            $this->redirect('programa/create');
            return;
        }

        try {
            $this->programaModel->crearMasivo($filas);
            SessionHelper::setFlash('Programa de ' . count($filas) . ' semanas guardado correctamente.', 'success');
        } catch (Exception $e) {
            SessionHelper::setFlash('Excepción: ' . $e->getMessage(), 'danger');
        }
        $this->redirect('programa');
    }

    // -----------------------------------------------------------------------
    // EDIT — formulario con datos precargados
    // -----------------------------------------------------------------------
    public function edit($predioId = null) {
        $temporada = trim($_GET['temporada'] ?? '');

        if (!$predioId || !$temporada) {
            SessionHelper::setFlash('Parámetros insuficientes para editar.', 'warning');
            $this->redirect('programa');
            return;
        }

        $semanas  = $this->programaModel->obtenerSemanas($predioId, $temporada);
        if (empty($semanas)) {
            SessionHelper::setFlash('No se encontró el programa solicitado.', 'danger');
            $this->redirect('programa');
            return;
        }

        $predios  = $this->predioModel->obtenerTodosLosPredios();
        $cultivos = $this->cultivoModel->obtenerTodos();
        $predio   = null;
        foreach ($predios as $p) {
            if ((int)$p->id === (int)$predioId) { $predio = $p; break; }
        }

        $data = [
            'titulo'     => 'Editar Programa — ' . ($predio->nombre ?? '') . ' / Temp. ' . $temporada,
            'predios'    => $predios,
            'cultivos'   => $cultivos,
            'predio_id'  => (int)$predioId,
            'temporada'  => $temporada,
            'semanas'    => $semanas,
            'cultivo_id' => $semanas[0]->cultivo_id ?? null,
            'breadcrumbs' => [
                ['label' => 'Programas', 'url' => URL_ROOT . '/programa'],
                ['label' => 'Editar'],
            ],
        ];
        $this->view('programa/edit', $data);
    }

    // -----------------------------------------------------------------------
    // UPDATE (POST)
    // -----------------------------------------------------------------------
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('programa'); }

        $predioId  = intval($_POST['predio_id'] ?? 0);
        $temporada = trim($_POST['temporada']   ?? '');
        $semanas   = $_POST['semana']            ?? [];
        $fechas    = $_POST['fecha_estimada']    ?? [];
        $ns        = $_POST['n_objetivo']        ?? [];
        $ps        = $_POST['p_objetivo']        ?? [];
        $ks        = $_POST['k_objetivo']        ?? [];
        $obs       = $_POST['observaciones']     ?? [];
        $cultivoId = intval($_POST['cultivo_id'] ?? 0) ?: null;

        if (!$predioId || !$temporada || empty($semanas)) {
            SessionHelper::setFlash('Faltan datos obligatorios.', 'danger');
            $this->redirect('programa/edit/' . $predioId . '?temporada=' . urlencode($temporada));
            return;
        }

        $filas = $this->buildFilas($predioId, $cultivoId, $temporada, $semanas, $fechas, $ns, $ps, $ks, $obs);

        if (empty($filas)) {
            SessionHelper::setFlash('No se enviaron semanas válidas.', 'warning');
            $this->redirect('programa/edit/' . $predioId . '?temporada=' . urlencode($temporada));
            return;
        }

        try {
            $this->programaModel->actualizarMasivo($predioId, $temporada, $filas);
            SessionHelper::setFlash('Programa actualizado correctamente (' . count($filas) . ' semanas).', 'success');
        } catch (Exception $e) {
            SessionHelper::setFlash('Error al actualizar: ' . $e->getMessage(), 'danger');
        }
        $this->redirect('programa');
    }

    // -----------------------------------------------------------------------
    // COMPARAR
    // -----------------------------------------------------------------------
    public function comparar($predioId = null) {
        // Solo predios reales (tipo_superficie = 'cultivo')
        $predios   = $this->predioModel->obtenerPrediosAgricolas();
        $temporada = $_GET['temporada'] ?? null;
        $datos     = [];
        $predio    = null;

        // Auto-selección: si no viene predio en URL, usar el primero con programa
        if (!$predioId && !empty($predios)) {
            $resumen = $this->programaModel->listarResumen();
            if (!empty($resumen)) {
                // Buscar el primer resumen cuyo predio_id esté en predios reales
                $predioIdsReales = array_column($predios, 'id');
                foreach ($resumen as $r) {
                    if (in_array($r->predio_id, $predioIdsReales)) {
                        $predioId  = $r->predio_id;
                        $temporada = $r->temporada;
                        break;
                    }
                }
            }
        }

        // Buscar objeto predio
        foreach ($predios as $p) {
            if ((int)$p->id === (int)$predioId) { $predio = $p; break; }
        }

        // Auto-selección de temporada si no vino en URL
        $temporadas = $predioId ? $this->programaModel->listarTemporadas($predioId) : [];
        if ($predioId && !$temporada && !empty($temporadas)) {
            $temporada = $temporadas[0]->temporada;
        }

        if ($predioId && $temporada) {
            $datos = $this->fertilizacionService->compararProgramaVsAplicado($predioId, $temporada);
        }

        $data = [
            'titulo'      => 'Comparación Programa vs Aplicado — Abono Track',
            'predios'     => $predios,
            'predio_id'   => $predioId,
            'predio'      => $predio,
            'temporada'   => $temporada,
            'temporadas'  => $temporadas,
            'datos'       => $datos,
            'breadcrumbs' => [
                ['label' => 'Programas', 'url' => URL_ROOT . '/programa'],
                ['label' => 'Comparar'],
            ],
        ];
        $this->view('programa/comparar', $data);
    }

    // -----------------------------------------------------------------------
    // DELETE
    // -----------------------------------------------------------------------
    public function eliminar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('programa'); }
        $predioId  = intval($_POST['predio_id'] ?? 0);
        $temporada = trim($_POST['temporada']   ?? '');

        if (!$predioId || !$temporada) {
            SessionHelper::setFlash('Datos insuficientes para eliminar.', 'danger');
            $this->redirect('programa');
            return;
        }

        if ($this->programaModel->eliminarPrograma($predioId, $temporada)) {
            SessionHelper::setFlash('Programa eliminado correctamente.', 'success');
        } else {
            SessionHelper::setFlash('No se pudo eliminar el programa.', 'danger');
        }
        $this->redirect('programa');
    }

    // -----------------------------------------------------------------------
    // EXPORTAR CSV
    // -----------------------------------------------------------------------
    public function exportarCSV($predioId = null) {
        $temporada = $_GET['temporada'] ?? null;
        if (!$predioId || !$temporada) {
            SessionHelper::setFlash('Parámetros insuficientes para exportar.', 'warning');
            $this->redirect('programa');
            return;
        }

        $filas = $this->programaModel->obtenerPorPredioTemporada($predioId, $temporada);
        if (empty($filas)) {
            SessionHelper::setFlash('No hay datos para exportar.', 'info');
            $this->redirect('programa');
            return;
        }

        $filename = 'Programa_Fertilizacion_' . preg_replace('/[^a-z0-9]/i', '_', $filas[0]->predio ?? 'predio') . '_' . $temporada . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);
        $out = fopen('php://output', 'w');
        fputs($out, "\xEF\xBB\xBF");
        fputcsv($out, ['Semana', 'Fecha Estimada', 'N Objetivo', 'P Objetivo', 'K Objetivo', 'Micronutrientes', 'Observaciones'], ';');
        foreach ($filas as $f) {
            fputcsv($out, [
                $f->semana,
                $f->fecha_estimada,
                number_format($f->n_objetivo, 2, ',', ''),
                number_format($f->p_objetivo, 2, ',', ''),
                number_format($f->k_objetivo, 2, ',', ''),
                $f->micronutrientes_objetivo ?? '',
                $f->observaciones ?? '',
            ], ';');
        }
        fclose($out);
        exit();
    }

    // -----------------------------------------------------------------------
    // HELPER privado: construir array de filas desde POST
    // -----------------------------------------------------------------------
    private function buildFilas($predioId, $cultivoId, $temporada, $semanas, $fechas, $ns, $ps, $ks, $obs): array {
        $filas = [];
        for ($i = 0; $i < count($semanas); $i++) {
            if (empty($fechas[$i])) continue;
            $microsRaw  = trim($_POST['micronutrientes_objetivo'][$i] ?? '');
            $microsJson = null;
            if ($microsRaw !== '') {
                $decoded    = json_decode($microsRaw, true);
                $microsJson = is_array($decoded) ? json_encode($decoded, JSON_UNESCAPED_UNICODE) : null;
            }
            $filas[] = [
                'predio_id'                => $predioId,
                'cultivo_id'               => $cultivoId,
                'temporada'                => $temporada,
                'semana'                   => (int)$semanas[$i],
                'fecha_estimada'           => $fechas[$i],
                'n_objetivo'               => (float)($ns[$i] ?? 0),
                'p_objetivo'               => (float)($ps[$i] ?? 0),
                'k_objetivo'               => (float)($ks[$i] ?? 0),
                'micronutrientes_objetivo' => $microsJson,
                'observaciones'            => $obs[$i] ?? null,
            ];
        }
        return $filas;
    }
}
?>
