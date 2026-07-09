<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            
            <?php SessionHelper::displayFlash(); ?>

            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><?php echo $data['titulo']; ?></h4>
                    <a href="<?php echo URL_ROOT; ?>/trabajadores/form" class="btn btn-accent-calendula">
                        <i class="bi bi-person-plus-fill me-2"></i> Nuevo Trabajador
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0 align-middle">
                            <thead class="bg-primary-dark-green text-white">
                                <tr>
                                    <th class="ps-4 py-3">RUT</th>
                                    <th class="py-3">Nombre Completo</th>
                                    <th class="py-3">Cargo</th>
                                    <th class="py-3 text-center">Estado</th>
                                    <th class="py-3 pe-4 text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($data['trabajadores'])): ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">
                                            <i class="bi bi-people fs-1 d-block mb-2"></i>
                                            No hay trabajadores registrados.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach($data['trabajadores'] as $t): ?>
                                    <tr>
                                        <td class="ps-4 font-monospace"><?php echo htmlspecialchars($t->rut); ?></td>
                                        <td class="fw-bold text-secondary"><?php echo htmlspecialchars($t->nombre_completo); ?></td>
                                        <td><?php echo htmlspecialchars($t->cargo ?? '-'); ?></td>
                                        <td class="text-center">
                                            <?php if ($t->activo): ?>
                                                <span class="badge bg-success-subtle text-success border border-success">Activo</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Inactivo</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="pe-4 text-end">
                                            <div class="btn-group btn-group-sm">
                                                <a href="<?php echo URL_ROOT; ?>/trabajadores/form/<?php echo $t->id; ?>" class="btn btn-outline-primary" title="Editar">
                                                    <i class="bi bi-pencil-fill"></i>
                                                </a>
                                                <?php if ($t->activo): ?>
                                                <form action="<?php echo URL_ROOT; ?>/trabajadores/eliminar/<?php echo $t->id; ?>" method="POST" onsubmit="return confirm('¿Desea DESACTIVAR a este trabajador? Esto podría afectar cuentas de usuario asociadas.');">
                                                    <button type="submit" class="btn btn-outline-danger border-start-0 rounded-end" title="Desactivar">
                                                        <i class="bi bi-person-x-fill"></i>
                                                    </button>
                                                </form>
                                                <?php endif; ?>
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