<?php
/**
 * Controlador para la generación de Reportes y Dashboards
 * Creado bajo el estándar v2.0 (Multi-Tenancy, DIP)
 */
class ReporteController extends Controller {
    
    private $riegoModel;
    private $climaModel;
    private $kpiModel;
    private $predioModel;
    private $riegoService; // Nueva propiedad

    public function __construct() {
        parent::__construct(); 
        $this->protect(['Admin', 'Usuario_general', 'Usuario_riego']);

        $this->riegoModel = $this->model('RiegoModel');
        $this->climaModel = $this->model('ClimaModel');
        $this->kpiModel = $this->model('KpiModel');
        $this->predioModel = $this->model('PredioModel');
        // INICIAR SERVICIO
        require_once APP_ROOT . '/core/RiegoService.php';
        $this->riegoService = new RiegoService($this->empresa_id);
    }

    /**
     * Muestra el "Hub" de reportes (HU-15)
     */
    public function index() {
        $data = [
            'titulo' => 'Central de Reportes',
            'breadcrumbs' => [
                ['label' => 'Reportes']
            ]
        ];
        $this->view('reporte/index', $data);
    }

/**
 * DASHBOARD GERENCIAL (Refactorizado con selector de fechas)
 */
public function gerencial() {
    // Obtener fechas desde GET o usar últimos 7 días por defecto
    $fecha_fin_input = $_GET['fecha_fin'] ?? null;
    $fecha_inicio_input = $_GET['fecha_inicio'] ?? null;
    
    // Si no hay rango personalizado, usar últimos 7 días
    if (!$fecha_fin_input || !$fecha_inicio_input) {
        $fecha_fin = date('Y-m-d', strtotime('-1 day')); // Ayer
        $fecha_inicio = date('Y-m-d', strtotime('-7 days')); // Hace 7 días
    } else {
        // Validar que el rango no supere 30 días
        $inicio = new DateTime($fecha_inicio_input);
        $fin = new DateTime($fecha_fin_input);
        $diff = $inicio->diff($fin)->days;
        
        if ($diff > 30) {
            // Limitar a 30 días desde la fecha inicio
            $fecha_inicio = $fecha_inicio_input;
            $fecha_fin = date('Y-m-d', strtotime($fecha_inicio_input . ' +30 days'));
        } else {
            $fecha_inicio = $fecha_inicio_input;
            $fecha_fin = $fecha_fin_input;
        }
    }
    
    // Obtenemos datos calculados
    $datos = $this->riegoService->obtenerDatosReporteGerencial($fecha_fin, $fecha_inicio);
    
    // Agregamos flags de vista
    $data = array_merge($datos, [
        'use_charts' => true,
        'es_publico' => false,
        'fecha_inicio' => $fecha_inicio,
        'fecha_fin' => $fecha_fin,
        'breadcrumbs' => [
            ['label' => 'Reportes', 'url' => URL_ROOT . '/reporte'],
            ['label' => 'Tablero Gerencial']
        ]
    ]);
    
    $this->view('reporte/gerencial', $data);
}

    /**
     * AJAX: Generar link para Gerencia
     */
    public function generarLinkGerencial() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') die('Acceso denegado');
    
        $fechaCorte = $_POST['fecha_corte'] ?? date('Y-m-d', strtotime('-1 day'));
        $fechaInicio = $_POST['fecha_inicio'] ?? date('Y-m-d', strtotime('-7 days'));
    
        $token = $this->riegoService->generarTokenGerencial(
            $this->usuario_id, 
            $fechaCorte, 
            $fechaInicio
        );
    
        if ($token) {
            $link = URL_ROOT . '/publico/reporte/' . $token;
            $this->respondJson(['success' => true, 'link' => $link]);
        } else {
            $this->respondJson(['success' => false, 'message' => 'Error al generar token.']);
        }
    }

/**
     * REPORTE SEMANAL (REFACTORIZADO CON SERVICE)
     */
    public function semanal() {
        $week_input = $_GET['week'] ?? date('Y-\WW'); 
        
        // 1. Delegar toda la lógica pesada al servicio
        $datosReporte = $this->riegoService->obtenerDatosReporteSemanal($week_input);

        // 2. Preparar datos para la vista
        $data = array_merge($datosReporte, [
            'titulo' => 'Reporte Semanal de Riego',
            'use_charts' => true,
            'breadcrumbs' => [
                ['label' => 'Reportes', 'url' => URL_ROOT . '/reporte'],
                ['label' => 'Semanal']
            ],
            'es_publico' => false // Flag para saber que somos admin
        ]);
        
        $this->view('reporte/semanal', $data);
    }

    /**
     * NUEVO: Endpoint AJAX para generar link (Llamado por JS)
     */
    public function generarLinkSemanal() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') die('Acceso denegado');

        $week = $_POST['week'] ?? null;
        $year = $_POST['year'] ?? null;

        if (!$week || !$year) {
            $this->respondJson(['success' => false, 'message' => 'Faltan parámetros.']);
            return;
        }

        // Llamamos al servicio para crear el token
        $token = $this->riegoService->generarTokenSemanal($this->usuario_id, $week, $year);

        if ($token) {
            $link = URL_ROOT . '/publico/reporte/' . $token;
            $this->respondJson(['success' => true, 'link' => $link]);
        } else {
            $this->respondJson(['success' => false, 'message' => 'Error BD al crear token.']);
        }
    }

    /**
     * Muestra el dashboard de Riego vs Clima
     */
    public function riegoClima() {
        $fecha_fin = date('Y-m-d');
        $fecha_inicio = date('Y-m-d', strtotime('-30 days'));
        if (isset($_GET['fecha_inicio']) && !empty($_GET['fecha_inicio'])) $fecha_inicio = $_GET['fecha_inicio'];
        if (isset($_GET['fecha_fin']) && !empty($_GET['fecha_fin'])) $fecha_fin = $_GET['fecha_fin'];
        
        $datos_clima = $this->climaModel->obtenerDatosAgrupadosPorFecha($fecha_inicio, $fecha_fin);
        $datos_riego = $this->riegoModel->obtenerMinutosAgrupadosPorFecha($fecha_inicio, $fecha_fin);
        
        $reporte_data = $this->prepararDatosParaGrafico($datos_clima, $datos_riego, $fecha_inicio, $fecha_fin);
        
        $data = [
            'titulo' => 'Reporte: Riego vs Clima',
            'use_charts' => true,
            'reporte_json' => json_encode($reporte_data),
            'fecha_inicio' => $fecha_inicio,
            'fecha_fin' => $fecha_fin,
            'breadcrumbs' => [
                ['label' => 'Reportes', 'url' => URL_ROOT . '/reporte'],
                ['label' => 'Riego vs Clima']
            ]
        ];
        $this->view('reporte/riegoClima', $data);
    }

    /**
     * Detalle por predio (Evolución)
     */
    public function detallePredio() {
        $predio_id = $_GET['predio_id'] ?? null;
        $fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');
        $fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-d', strtotime('-30 days'));

        $predios = $this->predioModel->obtenerPrediosAgricolas(); 
        if (!$predio_id && !empty($predios)) {
            $predio_id = $predios[0]->id;
        }

        $reporte_data = [];
        $nombre_predio = "Seleccione un predio";
        $pp_sistema = 0; 

        if ($predio_id) {
            $predio_obj = $this->predioModel->obtenerPredioPorId($predio_id);
            if ($predio_obj) {
                $nombre_predio = $predio_obj->nombre;
                $caudal_lh = (float)$predio_obj->caudal_lt_hora;
                $densidad = (int)$predio_obj->plantas_por_hectarea;

                if ($caudal_lh > 0 && $densidad > 0) {
                    $pp_sistema = ($caudal_lh * $densidad) / 10000;
                }
            }

            $datos_clima = $this->climaModel->obtenerDatosAgrupadosPorFecha($fecha_inicio, $fecha_fin);
            $datos_riego = $this->riegoModel->obtenerRiegoDiarioPorPredio($predio_id, $fecha_inicio, $fecha_fin);

            $reporte_data = $this->prepararDatosDetalle($datos_clima, $datos_riego, $fecha_inicio, $fecha_fin, $pp_sistema);
        }

        $data = [
            'titulo' => 'Evolución: ' . $nombre_predio,
            'subtitulo' => ($pp_sistema > 0) ? "PP Sistema: " . number_format($pp_sistema, 2) . " mm/h" : "Sin datos de precipitación (se muestran minutos)",
            'predios' => $predios,
            'predio_seleccionado' => $predio_id,
            'fecha_inicio' => $fecha_inicio,
            'fecha_fin' => $fecha_fin,
            'reporte_json' => json_encode($reporte_data),
            'use_charts' => true,
            'breadcrumbs' => [
                ['label' => 'Reportes', 'url' => URL_ROOT . '/reporte'],
                ['label' => 'Detalle por Predio']
            ]
        ];

        $this->view('reporte/detalle_predio', $data);
    }

    /**
     * Helper privado para fusionar datos
     */
    private function prepararDatosParaGrafico($clima, $riego, $inicio, $fin) {
        $clima_map = [];
        foreach ($clima as $dia) { $clima_map[$dia->fecha] = $dia; }
        
        $riego_map = [];
        foreach ($riego as $dia) { $riego_map[$dia->fecha] = $dia; }

        $labels = [];
        $evaporacion_data = [];
        $precipitacion_data = [];
        $riego_data = [];

        $fecha_actual = new DateTime($inicio);
        $fecha_fin_obj = new DateTime($fin);

        while ($fecha_actual <= $fecha_fin_obj) {
            $fecha_str = $fecha_actual->format('Y-m-d');
            $labels[] = $fecha_actual->format('d-m-Y');
            
            $evaporacion_data[] = $clima_map[$fecha_str]->total_lectura_mm ?? 0;
            $precipitacion_data[] = $clima_map[$fecha_str]->total_mm_medidos ?? 0;
            $riego_data[] = $riego_map[$fecha_str]->total_minutos ?? 0;

            $fecha_actual->modify('+1 day');
        }

        return [
            'labels' => $labels,
            'evaporacion' => $evaporacion_data,
            'precipitacion' => $precipitacion_data,
            'riego' => $riego_data
        ];
    }

    /**
     * Helper privado para datos detalle
     */
    private function prepararDatosDetalle($clima, $riego, $inicio, $fin, $pp_mm_hora) {
        $clima_map = [];
        foreach ($clima as $dia) $clima_map[$dia->fecha] = $dia;
        
        $riego_map = [];
        foreach ($riego as $dia) $riego_map[$dia->fecha] = $dia;

        $labels = [];
        $evaporacion = [];
        $riego_valores = []; 

        $fecha_actual = new DateTime($inicio);
        $fecha_fin_obj = new DateTime($fin);

        while ($fecha_actual <= $fecha_fin_obj) {
            $f = $fecha_actual->format('Y-m-d');
            $labels[] = $fecha_actual->format('d/m');
            
            $evaporacion[] = $clima_map[$f]->total_lectura_mm ?? 0;
            $minutos = $riego_map[$f]->tiempo_riego ?? 0;
            
            if ($pp_mm_hora > 0) {
                $mm_calculados = ($minutos / 60) * $pp_mm_hora;
                $riego_valores[] = round($mm_calculados, 2);
            } else {
                $riego_valores[] = (int)$minutos;
            }

            $fecha_actual->modify('+1 day');
        }

        return [
            'labels' => $labels,
            'evaporacion' => $evaporacion,
            'riego_valores' => $riego_valores,
            'unidad_riego' => ($pp_mm_hora > 0) ? 'mm' : 'min'
        ];
    }

    /**
     * Helper para definir el "Nudge" visual (Semáforo)
     */
    private function determinarEstadoHídrico($pct, $cfg = []) {
        // CORRECCIÓN VITAL: Validar existencia de claves antes de usarlas
        $u_bajo = isset($cfg['bajo']) ? (int)$cfg['bajo'] : 70;
        $u_opt_min = isset($cfg['opt_min']) ? (int)$cfg['opt_min'] : 90;
        $u_opt_max = isset($cfg['opt_max']) ? (int)$cfg['opt_max'] : 110;
        $u_exceso = isset($cfg['exceso']) ? (int)$cfg['exceso'] : 140;

        // Lógica de cascada
        if ($pct >= $u_opt_min && $pct <= $u_opt_max) {
            return ['color' => '#0f8164', 'class' => 'success', 'texto' => 'Óptimo', 'icono' => 'bi-check-circle-fill'];
        } elseif ($pct >= $u_bajo && $pct < $u_opt_min) {
            return ['color' => '#FFC759', 'class' => 'warning', 'texto' => 'Déficit Leve', 'icono' => 'bi-exclamation-circle-fill'];
        } elseif ($pct > $u_opt_max && $pct <= $u_exceso) {
            return ['color' => '#7EC4CF', 'class' => 'info', 'texto' => 'Sobre-riego Leve', 'icono' => 'bi-droplet-half'];
        } elseif ($pct < $u_bajo) {
            return ['color' => '#dc3545', 'class' => 'danger', 'texto' => 'Déficit Crítico', 'icono' => 'bi-exclamation-triangle-fill'];
        } else { 
            return ['color' => '#0d6efd', 'class' => 'primary', 'texto' => 'Exceso Crítico', 'icono' => 'bi-tsunami'];
        }
    }
    /**
 * AJAX: Obtener detalle de eventos de riego de un predio
 */
public function obtenerDetalleModalPredio() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $this->respondJson(['success' => false, 'message' => 'Método no permitido']);
        return;
    }

    $predio_id = $_POST['predio_id'] ?? null;
    $fecha_inicio = $_POST['fecha_inicio'] ?? null;
    $fecha_fin = $_POST['fecha_fin'] ?? null;

    if (!$predio_id || !$fecha_inicio || !$fecha_fin) {
        $this->respondJson(['success' => false, 'message' => 'Parámetros incompletos']);
        return;
    }

    // Obtener eventos de riego
    $eventos = $this->riegoModel->obtenerEventosDetallePredio($predio_id, $fecha_inicio, $fecha_fin);
    
    // Obtener datos de clima para el gráfico
    $datos_clima = $this->climaModel->obtenerDatosAgrupadosPorFecha($fecha_inicio, $fecha_fin);
    
    // Calcular PP del sistema (del primer evento, todos deberían tener los mismos datos de predio)
    $pp_sistema = 0;
    if (!empty($eventos)) {
        $primer_evento = $eventos[0];
        $caudal_lh = (float)$primer_evento->caudal_lt_hora;
        $densidad = (int)$primer_evento->plantas_por_hectarea;
        if ($caudal_lh > 0 && $densidad > 0) {
            $pp_sistema = ($caudal_lh * $densidad) / 10000;
        }
    }
    
    // Preparar datos para gráfico (reutilizar helper)
    $datos_riego_map = [];
    foreach ($eventos as $ev) {
        $datos_riego_map[$ev->fecha] = $ev;
    }
    
    $grafico_data = $this->prepararDatosDetalleModal($datos_clima, $datos_riego_map, $fecha_inicio, $fecha_fin, $pp_sistema);
    
    // Preparar tabla de eventos
    $tabla_eventos = [];
    foreach ($eventos as $ev) {
        $mm_regados = 0;
        if ($pp_sistema > 0) {
            $mm_regados = ($ev->tiempo_riego / 60) * $pp_sistema;
        }
        
        $tabla_eventos[] = [
            'fecha' => date('d/m/Y', strtotime($ev->fecha)),
            'minutos' => $ev->tiempo_riego,
            'mm' => round($mm_regados, 2),
            'usuario' => $ev->nombre_usuario ?? 'N/A'
        ];
    }

    $this->respondJson([
        'success' => true,
        'nombre_predio' => !empty($eventos) ? $eventos[0]->nombre_predio : 'Predio',
        'pp_sistema' => $pp_sistema,
        'grafico' => $grafico_data,
        'eventos' => $tabla_eventos
    ]);
}

/**
 * Helper para preparar datos del modal (similar a prepararDatosDetalle)
 */
private function prepararDatosDetalleModal($clima, $riego_map, $inicio, $fin, $pp_mm_hora) {
    $clima_map = [];
    foreach ($clima as $dia) $clima_map[$dia->fecha] = $dia;

    $labels = [];
    $evaporacion = [];
    $riego_valores = [];

    $fecha_actual = new DateTime($inicio);
    $fecha_fin_obj = new DateTime($fin);

    while ($fecha_actual <= $fecha_fin_obj) {
        $f = $fecha_actual->format('Y-m-d');
        $labels[] = $fecha_actual->format('d/m');
        
        $evaporacion[] = $clima_map[$f]->total_lectura_mm ?? 0;
        
        if (isset($riego_map[$f])) {
            $minutos = $riego_map[$f]->tiempo_riego;
            if ($pp_mm_hora > 0) {
                $mm_calculados = ($minutos / 60) * $pp_mm_hora;
                $riego_valores[] = round($mm_calculados, 2);
            } else {
                $riego_valores[] = (int)$minutos;
            }
        } else {
            $riego_valores[] = 0;
        }

        $fecha_actual->modify('+1 day');
    }

    return [
        'labels' => $labels,
        'evaporacion' => $evaporacion,
        'riego_valores' => $riego_valores,
        'unidad_riego' => ($pp_mm_hora > 0) ? 'mm' : 'min'
    ];
}
/**
     * Reporte Gerencial de Labores (Vista Panorámica)
     */
    public function labores_gerencial() {
        // Instanciar el servicio de labores temporalmente aquí (o en el constructor)
        require_once APP_ROOT . '/core/AvanceLaborService.php';
        $laborService = new AvanceLaborService();

        // Obtener la semana actual ISO (Ej: "2026-W11")
        $fecha = new DateTime();
        $semana_actual = $fecha->format("Y-\WW"); 

        $dashboard_data = $laborService->obtenerDashboardGerencial($this->empresa_id, $semana_actual);

        $data = [
            'titulo' => 'Reporte General de Labores',
            'breadcrumbs' => [
                ['label' => 'Reportes', 'url' => URL_ROOT . '/reporte'],
                ['label' => 'Reporte Labores']
            ],
            'semana_actual' => $semana_actual,
            'dashboard' => $dashboard_data
        ];

        $this->view('reporte/gerencial_labores', $data);
    }
/**
     * Exportación de Tablero Gerencial a Excel nativo (.xlsx)
     * Optimizada con Super-Cabeceras, Freeze Panes, Bloques Visuales y Tipos de Datos.
     */
    public function exportar_excel_labores() {
        require_once APP_ROOT . '/core/AvanceLaborService.php';
        $laborService = new AvanceLaborService();
        $semana_actual = (new DateTime())->format("Y-\WW"); 
        $dashboard = $laborService->obtenerDashboardGerencial($this->empresa_id, $semana_actual);

        require_once APP_ROOT . '/../vendor/autoload.php';
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);

        if (empty($dashboard)) {
            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle('Sin Datos');
            $sheet->setCellValue('A1', 'No hay labores activas en esta semana.');
        } else {
            foreach ($dashboard as $labor_nombre => $tarjetas) {
                $sheet = $spreadsheet->createSheet();
                $sheetName = substr(preg_replace('/[^a-zA-Z0-9\s]/', '', $labor_nombre), 0, 31);
                $sheet->setTitle($sheetName);

                // ================= 1. CABECERA EJECUTIVA =================
                $sheet->setCellValue('A1', 'Reporte Ejecutivo de Labor: ' . $labor_nombre);
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF0F8164'));
                
                $sheet->setCellValue('A2', 'Fecha de Generación: ' . date('d/m/Y H:i'));
                $sheet->setCellValue('A3', 'Semana Consolidada: ' . $semana_actual);

                // ================= 2. SUPER-CABECERAS AGRUPADAS (Fila 5) =================
                $sheet->mergeCells('A5:B5'); $sheet->setCellValue('A5', 'CONTEXTO OPERATIVO');
                $sheet->mergeCells('C5:E5'); $sheet->setCellValue('C5', 'KPIs DE EFICIENCIA');
                $sheet->mergeCells('F5:H5'); $sheet->setCellValue('F5', 'AVANCE ESTA SEMANA');
                $sheet->mergeCells('I5:M5'); $sheet->setCellValue('I5', 'ACUMULADO HISTÓRICO');

                // Estilo General Super-Cabeceras (Gris Oscuro)
                $superHeaderStyle = $sheet->getStyle('A5:M5');
                $superHeaderStyle->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
                $superHeaderStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF313131');
                $superHeaderStyle->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                // Estilo Específico: Resaltar "Esta Semana" con Amarillo Caléndula
                $sheet->getStyle('F5:H5')->getFill()->getStartColor()->setARGB('FFFFC759');
                $sheet->getStyle('F5:H5')->getFont()->getColor()->setARGB('FF313131'); // Texto oscuro para contraste

                // ================= 3. CABECERAS DE COLUMNA (Fila 6) =================
                // Nombres actualizados para mayor claridad ejecutiva
                $headers = [
                    'A' => 'Predio', 
                    'B' => 'Orden / Ciclo', 
                    'C' => 'Avance (%)', 
                    'D' => 'Hectáreas/Jornada', 
                    'E' => 'Plantas/Jornada',
                    'F' => 'Superficie (Ha)', 
                    'G' => 'Plantas', 
                    'H' => 'Jornadas', 
                    'I' => 'Acumulado (Ha)', 
                    'J' => 'Total Predio (Ha)', 
                    'K' => 'Acumulado (Plantas)', 
                    'L' => 'Total Predio (Plantas)', 
                    'M' => 'Total Jornadas'
                ];
                
                foreach ($headers as $col => $text) {
                    $sheet->setCellValue($col . '6', $text);
                    $style = $sheet->getStyle($col . '6');
                    $style->getFont()->setBold(true);
                    $style->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                    
                    // Aplicar paleta diferenciada al bloque de "Esta Semana"
                    if (in_array($col, ['F', 'G', 'H'])) {
                        $style->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFE082'); // Amarillo suave
                        $style->getFont()->getColor()->setARGB('FF313131'); // Texto oscuro
                    } else {
                        $style->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF0F8164'); // Verde Ryzoma
                        $style->getFont()->getColor()->setARGB('FFFFFFFF'); // Texto blanco
                    }
                    
                    $style->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)->getColor()->setARGB('FFFFFFFF');
                }

                // ================= 4. INYECTAR DATOS =================
                $row = 7;
                foreach ($tarjetas as $kpi) {
                    $sheet->setCellValue('A' . $row, $kpi['predio']);
                    $sheet->setCellValue('B' . $row, $kpi['orden_nombre']);
                    $sheet->setCellValue('C' . $row, $kpi['kpis']['avance_pct'] / 100); 
                    $sheet->setCellValue('D' . $row, $kpi['kpis']['ha_por_jornada']);
                    $sheet->setCellValue('E' . $row, $kpi['kpis']['plantas_por_jornada']);
                    
                    // Bloque Esta Semana
                    $sheet->setCellValue('F' . $row, $kpi['semana']['ha']);
                    $sheet->setCellValue('G' . $row, $kpi['semana']['plantas']);
                    $sheet->setCellValue('H' . $row, $kpi['semana']['jornadas']);
                    
                    // Bloque Acumulado (Nombres Nuevos)
                    $sheet->setCellValue('I' . $row, $kpi['acumulado']['ha']);
                    $sheet->setCellValue('J' . $row, $kpi['objetivo_ha']);
                    $sheet->setCellValue('K' . $row, $kpi['acumulado']['plantas']);
                    $sheet->setCellValue('L' . $row, $kpi['objetivo_plantas']);
                    $sheet->setCellValue('M' . $row, $kpi['acumulado']['jornadas']);
                    
                    $row++;
                }

                $lastRow = $row - 1;

                // ================= 5. FORMATOS, BORDES Y ALINEACIÓN =================
                // Formatos numéricos
                $sheet->getStyle('C7:C' . $lastRow)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_PERCENTAGE_00);
                $sheet->getStyle('G7:G' . $lastRow)->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle('E7:E' . $lastRow)->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle('K7:L' . $lastRow)->getNumberFormat()->setFormatCode('#,##0');

                $sheet->getStyle('C7:M' . $lastRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

                // Auto-ajuste de columnas
                $sheet->getColumnDimension('A')->setWidth(25);
                $sheet->getColumnDimension('B')->setWidth(22);
                foreach (range('C', 'M') as $columnID) {
                    $sheet->getColumnDimension($columnID)->setAutoSize(true);
                }

                // NUEVO: Dibujar Bordes Gruesos separadores de bloques (Pillars)
                $bordeGrueso = [
                    'borders' => [
                        'right' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM, 'color' => ['argb' => 'FF888888']]
                    ]
                ];
                // Limitamos los bloques a la derecha de B (Contexto), E (KPIs), H (Semana) y M (Acumulado)
                $sheet->getStyle('B5:B' . $lastRow)->applyFromArray($bordeGrueso);
                $sheet->getStyle('E5:E' . $lastRow)->applyFromArray($bordeGrueso);
                $sheet->getStyle('H5:H' . $lastRow)->applyFromArray($bordeGrueso);
                $sheet->getStyle('M5:M' . $lastRow)->applyFromArray($bordeGrueso);

                // Congelar paneles
                $sheet->freezePane('C7');
            }
        }

        $spreadsheet->setActiveSheetIndex(0);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Tablero_Labores_' . $semana_actual . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit;
    }
}
?>