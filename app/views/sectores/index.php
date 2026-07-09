<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-10 mx-auto">
            
            <?php SessionHelper::displayFlash(); ?>

            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><?php echo $data['titulo']; ?></h4>
                    <a href="<?php echo URL_ROOT; ?>/sectores/form" class="btn btn-accent-calendula">
                        <i class="bi bi-plus-circle me-2"></i> Crear Nuevo Sector
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                    <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Nombre Sector</th>
                                    <th>Predio Perteneciente</th>
                                    <th>Unidad</th>
                                    <th>Superficie (Ha)</th>
                                    <th>Nº Plantas</th>
                                    <th>Estado</th>
                                    <th style="width: 150px;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($data['sectores'])): ?>
                                    <tr>
                                        <td colspan="7" class="text-center">No hay sectores registrados.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach($data['sectores'] as $sector): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($sector->nombre); ?></td>
                                        <td><?php echo htmlspecialchars($sector->nombre_predio); ?></td>
                                        <td><?php echo htmlspecialchars($sector->unidad); ?></td>
                                        <td><?php echo htmlspecialchars($sector->superficie); ?></td>
                                        <td><?php echo htmlspecialchars($sector->cantidad_plantas); ?></td>
                                        <td>
                                            <?php if ($sector->activo): ?>
                                                <span class="badge bg-success">Activo</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Inactivo</span>
                                                <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="<?php echo URL_ROOT; ?>/sectores/form/<?php echo $sector->id; ?>" class="btn btn-primary btn-sm" title="Editar">
                                                <i class="bi bi-pencil-fill"></i>
                                            </a>
                                            
                                            <form action="<?php echo URL_ROOT; ?>/sectores/eliminar/<?php echo $sector->id; ?>" method="POST" class="d-inline" onsubmit="return confirm('¿Está seguro de que desea DESACTIVAR este sector?');">
                                                <button type="submit" class="btn btn-danger btn-sm" title="Desactivar">
                                                    <i class="bi bi-eye-slash-fill"></i>
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
