<div class="container-fluid mt-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-0 fw-bold" style="color: var(--vertical-agro);"><?php echo $data['titulo']; ?></h3>
            <nav aria-label="breadcrumb" class="mt-1">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="<?php echo URL_ROOT; ?>/home" class="text-muted text-decoration-none"><i class="bi bi-house-door"></i></a></li>
                    <li class="breadcrumb-item"><a href="<?php echo URL_ROOT; ?>/fertilizacion" class="text-muted text-decoration-none">Fertirrigación</a></li>
                    <li class="breadcrumb-item active text-muted">Historial</li>
                </ol>
            </nav>
        </div>
        <a href="<?php echo URL_ROOT; ?>/fertilizacion/index" class="btn btn-accent-calendula shadow-sm">
            <i class="bi bi-plus-circle me-2"></i> Registrar Nuevo
        </a>
    </div>

    <?php SessionHelper::displayFlash(); ?>

    <?php
        // Preparar datos para el resumen del período en JS
        $mes  = $data['mes_actual'];
        $year = $data['year_actual'];

        // Nombre del mes en español para el selector
        $meses_es = ['enero','febrero','marzo','abril','mayo','junio',
                     'julio','agosto','septiembre','octubre','noviembre','diciembre'];
        $nombre_mes_actual = ucfirst($meses_es[(int)$mes - 1]) . ' ' . $year;

        // Codificar los registros en JSON para el resumen JS-side
        $registros_json = json_encode(array_map(function($r) {
            return [
                'fecha'          => $r->fecha,
                'nombre_cabezal' => $r->nombre_cabezal,
                'nombre_comercial'=> $r->nombre_comercial,
                'cantidad'       => (float)$r->cantidad_aplicada,
                'tipo_unidad'    => $r->tipo_unidad,
            ];
        }, $data['registros'] ?? []));
    ?>

    <!-- Resumen del Período -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2 py-3">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-calculator-fill" style="color: var(--vertical-agro); font-size: 1.1rem;"></i>
                <span class="fw-bold" style="font-size: 1rem;">Resumen del Período</span>
            </div>
            <!-- Selector de tipo de período -->
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <div class="btn-group btn-group-sm" role="group" id="selectorPeriodo">
                    <button type="button" class="btn btn-outline-secondary active" data-periodo="mes">
                        <i class="bi bi-calendar-month me-1"></i>Mes
                    </button>
                    <button type="button" class="btn btn-outline-secondary" data-periodo="semana">
                        <i class="bi bi-calendar-week me-1"></i>Semana
                    </button>
                    <button type="button" class="btn btn-outline-secondary" data-periodo="temporada">
                        <i class="bi bi-calendar-range me-1"></i>Temporada
                    </button>
                </div>
                <span class="badge rounded-pill text-bg-light border fw-normal" id="labelPeriodo"><?php echo $nombre_mes_actual; ?></span>
            </div>
        </div>
        <div class="card-body p-0">
            <div id="resumenContenido">
                <!-- Se rellena por JS -->
                <div class="text-center py-4 text-muted small" id="resumenEmpty" style="display:none;">
                    <i class="bi bi-inbox fs-3 d-block mb-1 opacity-25"></i>
                    Sin aplicaciones en este período.
                </div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0" id="tablaResumen">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Cabezal de Inyección</th>
                                <th>Producto</th>
                                <th class="text-end pe-3">Total Aplicado</th>
                            </tr>
                        </thead>
                        <tbody id="resumenBody">
                            <tr><td colspan="3" class="text-center py-3 text-muted small">Cargando…</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtro de Mes -->
    <div class="card shadow-sm mb-4 border-0">
        <div class="card-body py-2 d-flex justify-content-between align-items-center">
            <?php
                $prevMonth = date('m', mktime(0,0,0,$mes-1,1,$year));
                $prevYear  = date('Y', mktime(0,0,0,$mes-1,1,$year));
                $nextMonth = date('m', mktime(0,0,0,$mes+1,1,$year));
                $nextYear  = date('Y', mktime(0,0,0,$mes+1,1,$year));
            ?>
            <a href="?mes=<?php echo $prevMonth; ?>&year=<?php echo $prevYear; ?>" class="btn btn-outline-secondary btn-sm border-0">
                <i class="bi bi-chevron-left"></i> Anterior
            </a>
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-calendar-month text-muted"></i>
                <span class="fw-bold text-capitalize fs-5"><?php echo $nombre_mes_actual; ?></span>
            </div>
            <a href="?mes=<?php echo $nextMonth; ?>&year=<?php echo $nextYear; ?>" class="btn btn-outline-secondary btn-sm border-0">
                Siguiente <i class="bi bi-chevron-right"></i>
            </a>
        </div>
    </div>

    <!-- Tabla Bitácora -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-end">
            <a href="<?php echo URL_ROOT; ?>/fertilizacion/reporteNutricional" class="btn btn-outline-success btn-sm">
                <i class="bi bi-bar-chart-fill me-1"></i> Ver Reporte Nutricional (NPK/Ha)
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-secondary">
                        <tr>
                            <?php
                                $baseLink = "?mes={$data['mes_actual']}&year={$data['year_actual']}";
                                $newDir = ($data['dir'] == 'ASC') ? 'DESC' : 'ASC';
                                $icon   = ($data['dir'] == 'ASC')
                                    ? '<i class="bi bi-arrow-up-short"></i>'
                                    : '<i class="bi bi-arrow-down-short"></i>';
                            ?>
                            <th class="ps-4 py-3">
                                <a href="<?php echo $baseLink; ?>&sort=fecha&dir=<?php echo $newDir; ?>" class="text-decoration-none text-secondary">
                                    Fecha <?php echo ($data['sort'] == 'fecha') ? $icon : ''; ?>
                                </a>
                            </th>
                            <th class="py-3">
                                <a href="<?php echo $baseLink; ?>&sort=cabezal&dir=<?php echo $newDir; ?>" class="text-decoration-none text-secondary">
                                    Punto de Inyección <?php echo ($data['sort'] == 'cabezal') ? $icon : ''; ?>
                                </a>
                            </th>
                            <th class="py-3">
                                <a href="<?php echo $baseLink; ?>&sort=producto&dir=<?php echo $newDir; ?>" class="text-decoration-none text-secondary">
                                    Producto <?php echo ($data['sort'] == 'producto') ? $icon : ''; ?>
                                </a>
                            </th>
                            <th class="text-end py-3">Cantidad Total</th>
                            <th class="text-center py-3">Registrado por</th>
                            <th class="text-end pe-4 py-3" style="min-width:100px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($data['registros'])): ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-bucket fs-1 d-block mb-2 opacity-25"></i>
                                    No hay aplicaciones registradas en este mes.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($data['registros'] as $reg):
                                $badgeClass = 'bg-secondary'; $icon = 'bi-box-seam';
                                if ($reg->tipo_producto == 'fertilizante')  { $badgeClass = 'bg-success';           $icon = 'bi-flower1'; }
                                if ($reg->tipo_producto == 'biostimulante') { $badgeClass = 'bg-info text-dark';    $icon = 'bi-stars'; }
                                if ($reg->tipo_producto == 'enmienda')      { $badgeClass = 'bg-warning text-dark'; $icon = 'bi-layers'; }
                            ?>
                            <tr>
                                <td class="ps-4 fw-bold text-secondary"><?php echo date('d/m/Y', strtotime($reg->fecha)); ?></td>
                                <td><i class="bi bi-diagram-3 text-primary me-1"></i><?php echo htmlspecialchars($reg->nombre_cabezal); ?></td>
                                <td>
                                    <span class="badge <?php echo $badgeClass; ?> me-1"><i class="bi <?php echo $icon; ?>"></i></span>
                                    <?php echo htmlspecialchars($reg->nombre_comercial); ?>
                                </td>
                                <td class="text-end fw-bold font-monospace">
                                    <?php echo number_format($reg->cantidad_aplicada, 2, ',', '.'); ?>
                                    <small class="text-muted"><?php echo $reg->tipo_unidad; ?></small>
                                </td>
                                <td class="text-center text-muted small"><?php echo htmlspecialchars($reg->nombre_usuario ?? '-'); ?></td>
                                <td class="text-end pe-4">
                                    <!-- Botón editar únicamente (botón ojo eliminado: endpoint no existe) -->
                                    <a href="<?php echo URL_ROOT; ?>/fertilizacion/editar/<?php echo $reg->id; ?>"
                                       class="btn btn-sm btn-outline-primary border-0" title="Editar Registro">
                                        <i class="bi bi-pencil-fill"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    // Registros ya cargados desde PHP — no se hace fetch adicional
    var registros = <?php echo $registros_json; ?>;
    var mesActual = <?php echo (int)$mes; ?>;
    var yearActual = <?php echo (int)$year; ?>;

    // Período activo: 'mes' | 'semana' | 'temporada'
    var periodoActivo = 'mes';

    var labelPeriodo = document.getElementById('labelPeriodo');
    var resumenBody  = document.getElementById('resumenBody');
    var resumenEmpty = document.getElementById('resumenEmpty');
    var tablaResumen = document.getElementById('tablaResumen');

    // Determinar inicio de temporada: septiembre del año anterior si mes < 9
    function inicioTemporada() {
        return mesActual >= 9 ? yearActual + '-09-01' : (yearActual - 1) + '-09-01';
    }

    // Lunes de la semana de hoy
    function inicioSemanaActual() {
        var hoy = new Date();
        var dia = hoy.getDay() || 7; // domingo = 7
        var lunes = new Date(hoy);
        lunes.setDate(hoy.getDate() - (dia - 1));
        return lunes.toISOString().slice(0, 10);
    }

    function filtrarRegistros(periodo) {
        if (!registros || registros.length === 0) return [];
        if (periodo === 'mes') {
            // Todos los del mes ya cargados = todos los registros
            return registros;
        } else if (periodo === 'semana') {
            var lunes = inicioSemanaActual();
            var hoy   = new Date().toISOString().slice(0, 10);
            return registros.filter(function(r) {
                return r.fecha >= lunes && r.fecha <= hoy;
            });
        } else if (periodo === 'temporada') {
            var inicio = inicioTemporada();
            return registros.filter(function(r) { return r.fecha >= inicio; });
        }
        return registros;
    }

    function agruparResumen(lista) {
        var mapa = {};
        lista.forEach(function(r) {
            var key = r.nombre_cabezal + '||' + r.nombre_comercial + '||' + r.tipo_unidad;
            if (!mapa[key]) {
                mapa[key] = { cabezal: r.nombre_cabezal, producto: r.nombre_comercial, unidad: r.tipo_unidad, total: 0 };
            }
            mapa[key].total += r.cantidad;
        });
        return Object.values(mapa);
    }

    function formatNum(n) {
        return n.toFixed(2).replace('.', ',');
    }

    function renderResumen(periodo) {
        var lista = filtrarRegistros(periodo);
        var agrupado = agruparResumen(lista);

        if (agrupado.length === 0) {
            tablaResumen.style.display = 'none';
            resumenEmpty.style.display = '';
        } else {
            tablaResumen.style.display = '';
            resumenEmpty.style.display = 'none';
            resumenBody.innerHTML = agrupado.map(function(row) {
                return '<tr>' +
                    '<td class="ps-3 fw-semibold text-secondary">' + row.cabezal + '</td>' +
                    '<td>' + row.producto + '</td>' +
                    '<td class="text-end pe-3 font-monospace" style="color:var(--vertical-agro);">' +
                        formatNum(row.total) + ' <small class="text-muted">' + row.unidad + '</small>' +
                    '</td>' +
                    '</tr>';
            }).join('');
        }

        // Actualizar label del período
        var meses = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
        if (periodo === 'mes') {
            labelPeriodo.textContent = meses[mesActual - 1].charAt(0).toUpperCase() + meses[mesActual - 1].slice(1) + ' ' + yearActual;
        } else if (periodo === 'semana') {
            labelPeriodo.textContent = 'Semana actual';
        } else {
            var ini = inicioTemporada();
            var finYear = mesActual >= 9 ? yearActual + 1 : yearActual;
            labelPeriodo.textContent = 'Temporada ' + ini.slice(0, 4) + '/' + finYear;
        }
    }

    // Botones del selector
    document.querySelectorAll('#selectorPeriodo button').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.querySelectorAll('#selectorPeriodo button').forEach(function(b) { b.classList.remove('active'); });
            btn.classList.add('active');
            periodoActivo = btn.dataset.periodo;
            renderResumen(periodoActivo);
        });
    });

    // Render inicial
    renderResumen('mes');
})();
</script>
