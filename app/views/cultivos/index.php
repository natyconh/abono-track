<?php
// _legacy/abono-track/app/views/cultivos/index.php
?>
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-10 mx-auto">

            <?php SessionHelper::displayFlash(); ?>

            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center bg-white py-3">
                    <h4 class="mb-0 text-primary-dark-green fw-bold"><?php echo $data['titulo']; ?></h4>
                    <a href="<?php echo URL_ROOT; ?>/cultivos/form" class="btn btn-accent-calendula shadow-sm">
                        <i class="bi bi-plus-circle me-2"></i> Nuevo Cultivo
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Especie</th>
                                    <th>Variedad</th>
                                    <th class="text-end pe-4" style="width: 150px;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($data['cultivos'])): ?>
                                    <tr>
                                        <td colspan="3" class="text-center py-5 text-muted">
                                            <i class="bi bi-tree fs-1 d-block mb-2 opacity-50"></i>
                                            No hay cultivos registrados.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach($data['cultivos'] as $c): ?>
                                    <tr>
                                        <td class="ps-4 fw-bold text-primary-dark-green"><?php echo htmlspecialchars($c->nombre); ?></td>
                                        <td>
                                            <?php if(!empty($c->variedad)): ?>
                                                <span class="badge bg-light text-dark border"><?php echo htmlspecialchars($c->variedad); ?></span>
                                            <?php else: ?>
                                                <span class="text-muted small">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end pe-4">
                                            <a href="<?php echo URL_ROOT; ?>/cultivos/form/<?php echo $c->id; ?>" class="btn btn-outline-primary btn-sm" title="Editar">
                                                <i class="bi bi-pencil-fill"></i>
                                            </a>
                                            <form action="<?php echo URL_ROOT; ?>/cultivos/eliminar/<?php echo $c->id; ?>" method="POST" class="d-inline" onsubmit="return confirm('¿Desea desactivar este cultivo?');">
                                                <button type="submit" class="btn btn-outline-danger btn-sm border-start-0 rounded-end ms-0" title="Desactivar">
                                                    <i class="bi bi-trash-fill"></i>
                                                </button>
                                            </form>
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
