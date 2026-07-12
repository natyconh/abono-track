<?php
/**
 * ProgramaController — Abono Track
 * Gestiona los programas de fertilización de temporada.
 * Rutas: /programa/*
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

        $this->programaModel       = new ProgramaFertilizacionModel($this->db, $this->usuario_id);
        $this->predioModel         = $this->model('PredioModel');
        $this->cultivoModel        = $this->model('CultivoModel');
        $this->fertilizacionService = new FertilizacionService();
    }

    // -----------------------------------------------------------------------
    // INDEX — listado de programas
    // -----------------------------------------------------------------------

    public function index() {
        $resumen = $this->programaModel->listarResumen();
        $predios = $this->predioModel->obtenerPuntosInyeccion();

        $data = [
            'titulo'      => 'Programas de Fertilización — Abono Track',
            'resumen'     => $resumen,
            'predios'     => $predios,
            'breadcrumbs' => [
                ['label' => 'Programas de Fertilización'],
            ],
        ];
        $this->view('programa/index', $data);
    }

    // -----------------------------------------------------------------------
    // CREATE — formulario para nuevo programa
    // -----------------------------------------------------------------------

    public function create() {
        $predios  = $this->predioModel->obtenerTodos();
        $cultivos = $this->cultivoModel->obtenerTodos();

        // Temporada actual: si estamos entre sep-dic → temporada = año actual
        // Si ene-ago → temporada = año anterior
        $mes = (int)date('n');
        $temporadaDefault = $mes >= 9 ? date('Y') : (date('Y') - 1);

        $data = [
            'titulo'           => 'Nuevo Programa de Fertilización',
            'predios'          => $predios,
            'cultivos'         => $cultivos,
            'temporada_default'=> $temporadaDefault,
            'breadcrumbs'      => [
                ['label' => 'Programas', 'url' => URL_ROOT . '/programa'],
                ['label' => 'Nuevo Programa'],
            ],
        ];
        $this->view('programa/create', $data);
    }

    // -----------------------------------------------------------------------
    // STORE — guardar nuevo programa (POST)
    // -----------------------------------------------------------------------

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('programa');
        }

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

        $filas = [];
        for ($i = 0; $i < count($semanas); $i++) {
            if (empty($fechas[$i])) continue;
            // Micronutrientes: campo JSON libre desde textarea
            $microsRaw = trim($_POST['micronutrientes_objetivo'][$i] ?? '');
            $microsJson = null;
            if ($microsRaw !== '') {
                $decoded = json_decode($microsRaw, true);
                $microsJson = is_array($decoded) ? json_encode($decoded, JSON_UNESCAPED_UNICODE) : null;
            }
            $filas[] = [
                'predio_id'                  => $predioId,
                'cultivo_id'                 => $cultivoId,
                'temporada'                  => $temporada,
                'semana'                     => (int)$semanas[$i],
                'fecha_estimada'             => $fechas[$i],
                'n_objetivo'                 => (float)($ns[$i] ?? 0),
                'p_objetivo'                 => (float)($ps[$i] ?? 0),
                'k_objetivo'                 => (float)($ks[$i] ?? 0),
                'micronutrientes_objetivo'   => $microsJson,
                'observaciones'              => $obs[$i] ?? null,
            ];
        }

        if (empty($filas)) {
            SessionHelper::setFlash('No se enviaron semanas válidas.', 'warning');
            $this->redirect('programa/create');
            return;
        }

        try {
            if ($this->programaModel->crearMasivo($filas)) {
                SessionHelper::setFlash('Programa de ' . count($filas) . ' semanas guardado correctamente.', 'success');
            } else {
                SessionHelper::setFlash('Error al guardar el programa.', 'danger');
            }
        } catch (Exception $e) {
            SessionHelper::setFlash('Excepción: ' . $e->getMessage(), 'danger');
        }

        $this->redirect('programa');
    }

    // -----------------------------------------------------------------------
    // COMPARAR — comparación programa vs aplicado
    // -----------------------------------------------------------------------

    public function comparar($predioId = null) {
        $predios   = $this->predioModel->obtenerTodos();
        $temporada = $_GET['temporada'] ?? null;
        $datos     = [];
        $predio    = null;

        if ($predioId && $temporada) {
            // Buscar nombre del predio
            foreach ($predios as $p) {
                if ((int)$p->id === (int)$predioId) { $predio = $p; break; }
            }
            $datos = $this->fertilizacionService->compararProgramaVsAplicado($predioId, $temporada);
        }

        // Temporadas disponibles para el predio seleccionado
        $temporadas = $predioId ? $this->programaModel->listarTemporadas($predioId) : [];

        $data = [
            'titulo'       => 'Comparación Programa vs Aplicado — Abono Track',
            'predios'      => $predios,
            'predio_id'    => $predioId,
            'predio'       => $predio,
            'temporada'    => $temporada,
            'temporadas'   => $temporadas,
            'datos'        => $datos,
            'breadcrumbs'  => [
                ['label' => 'Programas', 'url' => URL_ROOT . '/programa'],
                ['label' => 'Comparar'],
            ],
        ];
        $this->view('programa/comparar', $data);
    }

    // -----------------------------------------------------------------------
    // DELETE — eliminar programa completo
    // -----------------------------------------------------------------------

    public function eliminar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('programa');
        }
        $predioId  = intval($_POST['predio_id']  ?? 0);
        $temporada = trim($_POST['temporada']    ?? '');

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
}
?>
