<?php
// Breadcrumb y sidebar los maneja header.php
?>

<!-- Cabecera -->
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 fw-bold mb-0" style="color:#1a6b3c;">Programas de Fertilización</h1>
        <p class="text-muted mb-0 small">Planificación de temporada por predio</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?php echo URL_ROOT; ?>/programa/comparar" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-bar-chart-steps"></i> Comparar vs Aplicado
        </a>
        <a href="<?php echo URL_ROOT; ?>/programa/create" class="btn btn-success btn-sm">
            <i class="bi bi-plus-circle-fill"></i> Nuevo Programa
        </a>
    </div>
</div>

<!-- Flash -->
<?php SessionHelper::displayFlash(); ?>

<!-- Tabla resumen -->
<?php if (empty($data['resumen'])): ?>
<div class="card border-0 shadow-sm">
    <div class="card-body text-center py-5">
        <i class="bi bi-calendar2-week text-muted" style="font-size: 3rem;"></i>
        <h5 class="mt-3 text-muted">Sin programas registrados</h5>
        <p class="text-muted small">Crea el primer programa de temporada para poder compararlo con las aplicaciones reales.</p>
        <a href="<?php echo URL_ROOT; ?>/programa/create" class="btn btn-success mt-2">
            <i class="bi bi-plus-circle-fill"></i> Crear Primer Programa
        </a>
    </div>
</div>
<?php else: ?>
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Predio</th>
                        <th>Temporada</th>
                        <th class="text-center">Semanas</th>
                        <th>Período</th>
                        <th class="text-end">N Total</th>
                        <th class="text-end">P Total</th>
                        <th class="text-end">K Total</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($data['resumen'] as $r): ?>
                    <tr>
                        <td class="fw-semibold"><?php echo htmlspecialchars($r->predio); ?></td>
                        <td>
                            <span class="badge" style="background:#e8f5e9;color:#1a6b3c;">
                                Temp. <?php echo htmlspecialchars($r->temporada); ?>
                            </span>
                        </td>
                        <td class="text-center"><?php echo $r->total_semanas; ?></td>
                        <td class="text-muted small">
                            <?php echo date('d/m/Y', strtotime($r->inicio)); ?> — <?php echo date('d/m/Y', strtotime($r->fin)); ?>
                        </td>
                        <td class="text-end" style="color:#1565c0;font-weight:600;"><?php echo number_format($r->total_n, 1); ?></td>
                        <td class="text-end" style="color:#e65100;font-weight:600;"><?php echo number_format($r->total_p, 1); ?></td>
                        <td class="text-end" style="color:#b71c1c;font-weight:600;"><?php echo number_format($r->total_k, 1); ?></td>
                        <td class="text-center">
                            <!-- Editar -->
                            <a href="<?php echo URL_ROOT; ?>/programa/edit/<?php echo $r->predio_id; ?>?temporada=<?php echo urlencode($r->temporada); ?>"
                               class="btn btn-sm btn-outline-warning me-1" title="Editar programa">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <!-- Comparar -->
                            <a href="<?php echo URL_ROOT; ?>/programa/comparar/<?php echo $r->predio_id; ?>?temporada=<?php echo urlencode($r->temporada); ?>"
                               class="btn btn-sm btn-outline-primary me-1" title="Comparar vs Aplicado">
                                <i class="bi bi-bar-chart-steps"></i>
                            </a>
                            <!-- Exportar CSV -->
                            <a href="<?php echo URL_ROOT; ?>/programa/exportarCSV/<?php echo $r->predio_id; ?>?temporada=<?php echo urlencode($r->temporada); ?>"
                               class="btn btn-sm btn-outline-success me-1" title="Exportar CSV">
                                <i class="bi bi-file-earmark-spreadsheet"></i>
                            </a>
                            <!-- Eliminar -->
                            <form method="POST" action="<?php echo URL_ROOT; ?>/programa/eliminar" class="d-inline"
                                  onsubmit="return confirm('\u00bfEliminar todo el programa de temporada <?php echo htmlspecialchars($r->temporada); ?> para <?php echo htmlspecialchars($r->predio); ?>?');">
                                <input type="hidden" name="predio_id" value="<?php echo $r->predio_id; ?>">
                                <input type="hidden" name="temporada" value="<?php echo htmlspecialchars($r->temporada); ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar programa">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>
