<?php
// Recolectar keys de micronutrientes presentes
$microKeys = [];
if (!empty($data['datos'])) {
    foreach ($data['datos'] as $row) {
        if (!empty($row->micronutrientes)) {
            foreach (array_keys($row->micronutrientes) as $k) {
                if (!in_array($k, $microKeys)) $microKeys[] = $k;
            }
        }
    }
}
sort($microKeys);

// Indexar objetivos del programa por predio_id para acceso rápido
$objetivos = [];
if (!empty($data['objetivos'])) {
    foreach ($data['objetivos'] as $obj) {
        $objetivos[$obj->predio_id] = $obj;
    }
}

// Función para calcular porcentaje de cumplimiento
function pct($aplicado, $objetivo) {
    if (!$objetivo || $objetivo <= 0) return null;
    return round(($aplicado / $objetivo) * 100);
}
?>

<div class="container-fluid mt-4">

    <!-- CABECERA -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
        <div>
            <h3 class="fw-bold mb-0" style="color:#1a6b3c;">Reporte Nutricional</h3>
            <p class="text-muted mb-0 small">
                Acumulado desde <strong><?php echo date('d/m/Y', strtotime($data['inicio_temporada'])); ?></strong> hasta hoy
                &mdash; Temporada <?php echo date('Y', strtotime($data['inicio_temporada'])); ?>/<?php echo date('Y'); ?>
            </p>
        </div>
        <a href="<?php echo URL_ROOT; ?>/fertilizacion/historial" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Volver al Historial
        </a>
    </div>

    <?php
    // Contar alertas totales para el banner resumen
    $totalAlertas = 0;
    if (!empty($data['datos'])) {
        foreach ($data['datos'] as $row) {
            $obj = $objetivos[$row->predio_id] ?? null;
            if ($obj) {
                foreach (['n', 'p', 'k'] as $nut) {
                    $p = pct($row->{$nut . '_ha'}, $obj->{$nut . '_objetivo'} ?? null);
                    if ($p !== null && $p < 80) $totalAlertas++;
                }
            }
        }
    }
    ?>

    <?php if ($totalAlertas > 0): ?>
    <div class="alert alert-warning d-flex align-items-center gap-2 mb-4" role="alert">
        <i class="bi bi-exclamation-triangle-fill fs-5"></i>
        <div>
            <strong><?php echo $totalAlertas; ?> nutriente<?php echo $totalAlertas > 1 ? 's' : ''; ?> con atraso</strong>
            respecto a la programación de temporada.
            <a href="<?php echo URL_ROOT; ?>/programa/comparar" class="alert-link ms-1">Ver comparativa detallada →</a>
        </div>
    </div>
    <?php endif; ?>

    <!-- TABLA NUTRICIONAL -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle table-sm mb-0">
                    <thead style="background-color:#1a4d2e;color:white;" class="text-center">
                        <tr>
                            <th class="align-middle" rowspan="2">Predio</th>
                            <th class="align-middle" rowspan="2">Cultivo</th>
                            <th class="align-middle" rowspan="2">Sup. (Ha)</th>
                            <th colspan="3" class="border-bottom-0">Macronutrientes aplicados (kg/ha)</th>
                            <?php if (!empty($objetivos)): ?>
                            <th colspan="3" class="border-bottom-0" style="background:#0f3628;">Objetivo programa (kg/ha)</th>
                            <?php endif; ?>
                            <?php if (!empty($microKeys)): ?>
                            <th colspan="<?php echo count($microKeys); ?>" class="border-bottom-0">Micronutrientes (total)</th>
                            <?php endif; ?>
                        </tr>
                        <tr>
                            <th style="background:#198754" class="text-white">N</th>
                            <th style="background:#e65100" class="text-white">P</th>
                            <th style="background:#c62828" class="text-white">K</th>
                            <?php if (!empty($objetivos)): ?>
                            <th style="background:#0f3628;" class="text-white">N obj.</th>
                            <th style="background:#0f3628;" class="text-white">P obj.</th>
                            <th style="background:#0f3628;" class="text-white">K obj.</th>
                            <?php endif; ?>
                            <?php foreach ($microKeys as $mk): ?>
                            <th class="bg-secondary text-white"><?php echo htmlspecialchars($mk); ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($data['datos'])): ?>
                        <tr>
                            <td colspan="<?php echo 6 + (empty($objetivos) ? 0 : 3) + count($microKeys); ?>" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                No hay aplicaciones registradas en esta temporada.
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($data['datos'] as $row):
                            $obj = $objetivos[$row->predio_id] ?? null;
                        ?>
                        <tr>
                            <td class="fw-semibold"><?php echo htmlspecialchars($row->predio); ?></td>
                            <td class="small text-muted"><?php echo htmlspecialchars($row->cultivo ?? '—'); ?></td>
                            <td class="text-center font-monospace"><?php echo number_format($row->hectareas, 2, ',', '.'); ?></td>

                            <?php
                            // N aplicado
                            $pN = $obj ? pct($row->n_ha, $obj->n_objetivo ?? null) : null;
                            ?>
                            <td class="text-center fw-bold">
                                <span style="color:#198754"><?php echo number_format($row->n_ha, 1, ',', '.'); ?></span>
                                <?php if ($pN !== null && $pN < 80): ?>
                                <span class="badge ms-1 <?php echo $pN < 50 ? 'bg-danger' : 'bg-warning text-dark'; ?>" title="<?php echo $pN; ?>% del objetivo">
                                    <?php echo $pN; ?>%
                                </span>
                                <?php endif; ?>
                            </td>

                            <?php
                            // P aplicado
                            $pP = $obj ? pct($row->p_ha, $obj->p_objetivo ?? null) : null;
                            ?>
                            <td class="text-center fw-bold">
                                <span style="color:#e65100"><?php echo number_format($row->p_ha, 1, ',', '.'); ?></span>
                                <?php if ($pP !== null && $pP < 80): ?>
                                <span class="badge ms-1 <?php echo $pP < 50 ? 'bg-danger' : 'bg-warning text-dark'; ?>" title="<?php echo $pP; ?>% del objetivo">
                                    <?php echo $pP; ?>%
                                </span>
                                <?php endif; ?>
                            </td>

                            <?php
                            // K aplicado
                            $pK = $obj ? pct($row->k_ha, $obj->k_objetivo ?? null) : null;
                            ?>
                            <td class="text-center fw-bold">
                                <span style="color:#c62828"><?php echo number_format($row->k_ha, 1, ',', '.'); ?></span>
                                <?php if ($pK !== null && $pK < 80): ?>
                                <span class="badge ms-1 <?php echo $pK < 50 ? 'bg-danger' : 'bg-warning text-dark'; ?>" title="<?php echo $pK; ?>% del objetivo">
                                    <?php echo $pK; ?>%
                                </span>
                                <?php endif; ?>
                            </td>

                            <?php if (!empty($objetivos)): ?>
                            <td class="text-center small text-muted"><?php echo $obj ? number_format($obj->n_objetivo ?? 0, 1, ',', '.') : '—'; ?></td>
                            <td class="text-center small text-muted"><?php echo $obj ? number_format($obj->p_objetivo ?? 0, 1, ',', '.') : '—'; ?></td>
                            <td class="text-center small text-muted"><?php echo $obj ? number_format($obj->k_objetivo ?? 0, 1, ',', '.') : '—'; ?></td>
                            <?php endif; ?>

                            <?php foreach ($microKeys as $mk): ?>
                            <td class="text-end small text-muted">
                                <?php
                                $val = $row->micronutrientes[$mk] ?? 0;
                                echo $val > 0 ? number_format($val, 2, ',', '.') : '<span class="opacity-25">—</span>';
                                ?>
                            </td>
                            <?php endforeach; ?>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Nota técnica -->
    <div class="mt-3 small text-muted d-flex align-items-start gap-2">
        <i class="bi bi-info-circle-fill mt-1"></i>
        <div>
            N, P y K expresados en <em>kg por Hectárea (kg/ha)</em>.
            Micronutrientes en unidades totales acumuladas.
            Los porcentajes de alerta se calculan contra el objetivo acumulado del programa de temporada activo.
            <strong>Naranja</strong> = aplicado entre 50–79% del objetivo &mdash; <strong>Rojo</strong> = menos del 50%.
        </div>
    </div>

</div>
