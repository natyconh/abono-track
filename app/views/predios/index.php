<?php
// app/views/predios/index.php
// Lista todos los predios: cultivos agrícolas y cabezales de riego
?>
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-lg-12 mx-auto">

            <?php SessionHelper::displayFlash(); ?>

            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><?php echo $data['titulo']; ?></h4>
                    <a href="<?php echo URL_ROOT; ?>/predios/form" class="btn btn-accent-calendula">
                        <i class="bi bi-plus-circle me-2"></i> Nuevo Predio
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0 align-middle">
                            <thead class="bg-primary-dark-green text-white">
                                <tr>
                                    <th class="ps-4 py-3">Nombre</th>
                                    <th class="py-3 text-center">Tipo</th>
                                    <th class="py-3 text-center">Cultivo</th>
                                    <th class="py-3 text-center">Año</th>
                                    <th class="py-3 text-end">Superficie (Ha)</th>
                                    <th class="py-3 text-end">Caudal (L/h)</th>
                                    <th class="py-3 text-center">PP (mm/h)</th>
                                    <th class="py-3 text-center">Estado</th>
                                    <th class="py-3 pe-4 text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($data['predios'])): ?>
                                    <tr>
                                        <td colspan="9" class="text-center py-5 text-muted">
                                            <i class="bi bi-map fs-1 d-block mb-2"></i>
                                            No hay predios registrados aún.
                                            <div class="mt-2">
                                                <a href="<?php echo URL_ROOT; ?>/predios/form" class="btn btn-sm btn-accent-calendula">
                                                    <i class="bi bi-plus-circle me-1"></i> Crear el primero
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach($data['predios'] as $predio):
                                        $den       = (int)($predio->plantas_por_hectarea ?? 0);
                                        $caudal    = (float)($predio->caudal_lt_hora ?? 0);
                                        $pp        = ($den > 0 && $caudal > 0) ? ($den * $caudal / 10000) : 0;
                                        $esCabezal = ($predio->tipo_superficie === 'cabezal_virtual');
                                        $esInfra   = ($predio->tipo_superficie === 'infraestructura');
                                    ?>
                                    <tr>
                                        <td class="ps-4 fw-bold text-secondary">
                                            <?php if ($esCabezal): ?>
                                                <i class="bi bi-diagram-3-fill text-primary me-1" title="Cabezal de riego"></i>
                                            <?php endif; ?>
                                            <?php echo htmlspecialchars($predio->nombre); ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($esCabezal): ?>
                                                <span class="badge bg-primary-subtle text-primary border border-primary">
                                                    <i class="bi bi-droplet-half me-1"></i>Cabezal
                                                </span>
                                            <?php elseif ($esInfra): ?>
                                                <span class="badge bg-secondary-subtle text-secondary border border-secondary">
                                                    <i class="bi bi-building me-1"></i>Infra.
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-success-subtle text-success border border-success">
                                                    <i class="bi bi-tree me-1"></i>Cultivo
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center text-muted small">
                                            <?php echo !empty($predio->nombre_cultivo) ? htmlspecialchars($predio->nombre_cultivo) : '<span class="opacity-50">—</span>'; ?>
                                        </td>
                                        <td class="text-center"><?php echo htmlspecialchars($predio->año_plantacion ?? '—'); ?></td>
                                        <td class="text-end font-monospace">
                                            <?php echo $predio->superficie_total ? number_format($predio->superficie_total, 2, ',', '.') : '<span class="text-muted opacity-50">—</span>'; ?>
                                        </td>
                                        <td class="text-end text-primary">
                                            <?php echo $caudal > 0 ? number_format($caudal, 1) . ' <small>L/h</small>' : '<span class="text-muted opacity-50">—</span>'; ?>
                                        </td>
                                        <td class="text-center fw-bold">
                                            <?php if ($pp > 0): ?>
                                                <span class="badge bg-info text-dark border">
                                                    <i class="bi bi-cloud-drizzle"></i> <?php echo number_format($pp, 2); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted opacity-50">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($predio->activo): ?>
                                                <span class="badge bg-success-subtle text-success border border-success">Activo</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Inactivo</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="pe-4 text-end">
                                            <div class="btn-group btn-group-sm">
                                                <a href="<?php echo URL_ROOT; ?>/predios/form/<?php echo $predio->id; ?>" class="btn btn-outline-primary" title="Editar">
                                                    <i class="bi bi-pencil-fill"></i>
                                                </a>
                                                <form action="<?php echo URL_ROOT; ?>/predios/eliminar/<?php echo $predio->id; ?>" method="POST"
                                                      onsubmit="return confirm('¿Desactivar el predio &quot;<?php echo htmlspecialchars($predio->nombre, ENT_QUOTES); ?>&quot;?');">
                                                    <button type="submit" class="btn btn-outline-danger border-start-0 rounded-end" title="Desactivar">
                                                        <i class="bi bi-eye-slash-fill"></i>
                                                    </button>
                                                </form>
                                            </div>
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
    </div>
</div>
