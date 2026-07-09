<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-10 mx-auto">
            
            <?php SessionHelper::displayFlash(); ?>

            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><?php echo $data['titulo']; ?></h4>
                    <a href="<?php echo URL_ROOT; ?>/instalaciones/form" class="btn btn-accent-calendula"> 
                    <i class="bi bi-plus-circle me-2"></i> Crear Nueva
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Nombre Instalación</th>
                                    <th>Predio</th>
                                    <th>Sector (Opcional)</th>
                                    <th>Coordenadas (Lat, Long)</th>
                                    <th>Estado</th>
                                    <th style="width: 150px;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($data['instalaciones'])): ?>
                                    <tr>
                                       <td colspan="6" class="text-center">No hay instalaciones registradas.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach($data['instalaciones'] as $item): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item->nombre); ?></td>
                                        <td><?php echo htmlspecialchars($item->nombre_predio); ?></td>
                                        <td><?php echo htmlspecialchars($item->nombre_sector ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($item->latitud ?? '-'); ?>, <?php echo htmlspecialchars($item->longitud ?? '-'); ?></td>
                                       <td>
                                           <?php if ($item->activo): ?>
                                              <span class="badge bg-success">Activo</span>
                                           <?php else: ?>
                                               <span class="badge bg-secondary">Inactivo</span>
                                           <?php endif; ?>
                                       </td>
                                        <td>
                                        <a href="<?php echo URL_ROOT; ?>/instalaciones/form/<?php echo $item->id; ?>" class="btn btn-primary btn-sm" title="Editar">
                                                <i class="bi bi-pencil-fill"></i>
                                            </a>
                                             <form action="<?php echo URL_ROOT; ?>/instalaciones/eliminar/<?php echo $item->id; ?>" method="POST" class="d-inline" onsubmit="return confirm('¿Seguro que desea DESACTIVAR esta instalación?');">
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
