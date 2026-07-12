<?php require_once APP_ROOT . '/views/layout/header.php'; ?>
<?php require_once APP_ROOT . '/views/layout/sidebar.php'; ?>

<div class="content-wrapper flex-grow-1 p-4">

    <!-- Breadcrumb -->
    <?php if (!empty($data['breadcrumbs'])): ?>
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo URL_ROOT; ?>/home/index"><i class="bi bi-house-fill"></i></a></li>
            <?php foreach ($data['breadcrumbs'] as $i => $bc): ?>
            <li class="breadcrumb-item <?php echo ($i === array_key_last($data['breadcrumbs'])) ? 'active' : ''; ?>">
                <?php if (!empty($bc['url'])): ?><a href="<?php echo $bc['url']; ?>"><?php endif; ?>
                <?php echo htmlspecialchars($bc['label']); ?>
                <?php if (!empty($bc['url'])): ?></a><?php endif; ?>
            </li>
            <?php endforeach; ?>
        </ol>
    </nav>
    <?php endif; ?>

    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 fw-bold mb-0" style="color:#1a6b3c;">Programa vs. Aplicado</h1>
            <p class="text-muted small mb-0">Desviación semana a semana entre el plan nutricional y las aplicaciones reales</p>
        </div>
        <?php if (!empty($data['predio_id']) && !empty($data['temporada'])): ?>
        <a href="<?php echo URL_ROOT; ?>/programa/exportarCSV/<?php echo $data['predio_id']; ?>?temporada=<?php echo urlencode($data['temporada']); ?>"
           class="btn btn-outline-success btn-sm">
            <i class="bi bi-file-earmark-spreadsheet"></i> Exportar CSV
        </a>
        <?php endif; ?>
    </div>

    <!-- Filtros -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="<?php echo URL_ROOT; ?>/programa/comparar/<?php echo $data['predio_id'] ?? ''; ?>" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Predio</label>
                    <select name="predio_id_redirect" class="form-select" id="selectPredio"
                            onchange="window.location='<?php echo URL_ROOT; ?>/programa/comparar/'+this.value">
                        <option value="">— Seleccionar predio —</option>
                        <?php foreach ($data['predios'] as $p): ?>
                        <option value="<?php echo $p->id; ?>" <?php echo ($data['predio_id'] == $p->id) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($p->nombre); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Temporada</label>
                    <select name="temporada" class="form-select">
                        <option value="">— Seleccionar —</option>
                        <?php foreach ($data['temporadas'] as $t): ?>
                        <option value="<?php echo htmlspecialchars($t->temporada); ?>"
                                <?php echo ($data['temporada'] === $t->temporada) ? 'selected' : ''; ?>>
                            Temporada <?php echo htmlspecialchars($t->temporada); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> Ver</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Resultados -->
    <?php if (empty($data['datos']) && !empty($data['predio_id'])): ?>
    <div class="alert alert-info">
        <i class="bi bi-info-circle"></i>
        No hay programa registrado para este predio y temporada, o aún no hay aplicaciones en el período.
        <a href="<?php echo URL_ROOT; ?>/programa/create" class="alert-link">Crear programa ahora</a>.
    </div>
    <?php elseif (!empty($data['datos'])): ?>

    <!-- Resumen de totales -->
    <?php
    $totObjN = array_sum(array_column($data['datos'], 'obj_n'));
    $totObjP = array_sum(array_column($data['datos'], 'obj_p'));
    $totObjK = array_sum(array_column($data['datos'], 'obj_k'));
    $totRealN = array_sum(array_column($data['datos'], 'real_n'));
    $totRealP = array_sum(array_column($data['datos'], 'real_p'));
    $totRealK = array_sum(array_column($data['datos'], 'real_k'));
    ?>
    <div class="row g-3 mb-4">
        <?php
        $kpis = [
            ['N',  $totObjN,  $totRealN, '#1565c0'],
            ['P',  $totObjP,  $totRealP, '#e65100'],
            ['K',  $totObjK,  $totRealK, '#b71c1c'],
        ];
        foreach ($kpis as [$nut, $obj, $real, $col]):
            $dev = $obj > 0 ? round((($real - $obj) / $obj) * 100, 1) : null;
            $devClass = $dev === null ? 'text-muted' : ($dev < -10 ? 'text-danger' : ($dev > 10 ? 'text-warning' : 'text-success'));
        ?>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="fw-bold fs-5" style="color:<?php echo $col; ?>"><?php echo $nut; ?></span>
                        <span class="text-muted small">Total temporada</span>
                    </div>
                    <div class="row text-center">
                        <div class="col">
                            <div class="small text-muted">Objetivo</div>
                            <div class="fw-bold"><?php echo number_format($obj, 1); ?></div>
                        </div>
                        <div class="col">
                            <div class="small text-muted">Aplicado</div>
                            <div class="fw-bold"><?php echo number_format($real, 1); ?></div>
                        </div>
                        <div class="col">
                            <div class="small text-muted">Desviación</div>
                            <div class="fw-bold <?php echo $devClass; ?>">
                                <?php echo $dev !== null ? ($dev > 0 ? '+' : '') . $dev . '%' : 'N/A'; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Tabla semana a semana -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-semibold">
            <i class="bi bi-table text-success"></i>
            Detalle por semana —
            <?php echo htmlspecialchars($data['predio']->nombre ?? ''); ?>
            | Temporada <?php echo htmlspecialchars($data['temporada']); ?>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Sem.</th>
                            <th>Fecha Est.</th>
                            <th class="text-end" style="color:#1565c0">Obj. N</th>
                            <th class="text-end" style="color:#1565c0">Real N</th>
                            <th class="text-end" style="color:#1565c0">Δ N</th>
                            <th class="text-end" style="color:#e65100">Obj. P</th>
                            <th class="text-end" style="color:#e65100">Real P</th>
                            <th class="text-end" style="color:#e65100">Δ P</th>
                            <th class="text-end" style="color:#b71c1c">Obj. K</th>
                            <th class="text-end" style="color:#b71c1c">Real K</th>
                            <th class="text-end" style="color:#b71c1c">Δ K</th>
                            <th>Obs.</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($data['datos'] as $fila):
                        function devBadge($dev) {
                            if ($dev === null) return '<span class="text-muted">—</span>';
                            $cls = $dev < -10 ? 'danger' : ($dev > 10 ? 'warning' : 'success');
                            $sign = $dev > 0 ? '+' : '';
                            return "<span class=\"badge bg-{$cls}\">{$sign}{$dev}%</span>";
                        }
                    ?>
                    <tr>
                        <td class="fw-semibold"><?php echo $fila['semana']; ?></td>
                        <td class="text-muted small"><?php echo date('d/m/Y', strtotime($fila['fecha_estimada'])); ?></td>
                        <!-- N -->
                        <td class="text-end"><?php echo number_format($fila['obj_n'], 2); ?></td>
                        <td class="text-end"><?php echo number_format($fila['real_n'], 2); ?></td>
                        <td class="text-end"><?php echo devBadge($fila['dev_n']); ?></td>
                        <!-- P -->
                        <td class="text-end"><?php echo number_format($fila['obj_p'], 2); ?></td>
                        <td class="text-end"><?php echo number_format($fila['real_p'], 2); ?></td>
                        <td class="text-end"><?php echo devBadge($fila['dev_p']); ?></td>
                        <!-- K -->
                        <td class="text-end"><?php echo number_format($fila['obj_k'], 2); ?></td>
                        <td class="text-end"><?php echo number_format($fila['real_k'], 2); ?></td>
                        <td class="text-end"><?php echo devBadge($fila['dev_k']); ?></td>
                        <td class="text-muted small"><?php echo htmlspecialchars($fila['observaciones'] ?? ''); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="text-center text-muted py-5">
        <i class="bi bi-bar-chart-steps" style="font-size:3rem;"></i>
        <p class="mt-3">Selecciona un predio y una temporada para ver la comparación.</p>
    </div>
    <?php endif; ?>
</div>

<?php require_once APP_ROOT . '/views/layout/footer.php'; ?>
