<div class="container-fluid mt-4">
    
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h3 class="mb-0 fw-bold text-secondary"><?php echo $data['titulo']; ?></h3>
                <p class="text-muted small mb-0">Gestión de accesos y roles de la plataforma.</p>
            </div>
            <a href="<?php echo URL_ROOT; ?>/users/form" class="btn btn-accent-calendula shadow-sm">
                <i class="bi bi-person-plus-fill me-2"></i> Crear Nuevo Usuario
            </a>
        </div>
    </div>

    <?php SessionHelper::displayFlash(); ?>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0"> <div class="table-responsive p-3">
                <table id="tablaUsuarios" class="table table-hover align-middle w-100" data-order='[[ 4, "desc" ]]'>
                    <thead class="bg-primary-dark-green text-white">
                        <tr>
                            <th class="py-3 ps-3 rounded-start-top">Username</th>
                            <th class="py-3 d-none d-md-table-cell">Trabajador Asociado</th>
                            <th class="py-3">Rol</th>
                            <th class="py-3 text-center">Estado</th>
                            <th class="py-3">Última Conexión</th>
                            <th class="py-3 text-end pe-3 rounded-end-top">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['usuarios'] as $user): ?>
                            <tr>
                                <td class="ps-3 fw-bold text-primary-dark-green">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-light d-flex justify-content-center align-items-center me-2" 
                                             style="width: 35px; height: 35px; color: var(--vertical-agro);">
                                            <i class="bi bi-person-fill"></i>
                                        </div>
                                        <?php echo htmlspecialchars($user->username); ?>
                                    </div>
                                </td>
                                
                                <td class="d-none d-md-table-cell text-muted small">
                                    <?php echo htmlspecialchars($user->nombre_trabajador ?? 'Sin vincular'); ?>
                                </td>
                                
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        <?php echo htmlspecialchars($user->nombre_rol ?? 'N/A'); ?>
                                    </span>
                                </td>
                                
                                <td class="text-center">
                                    <?php if ($user->activo): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3">
                                            Activo
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-3">
                                            Inactivo
                                        </span>
                                    <?php endif; ?>
                                </td>
                                
                                <td class="text-nowrap">
                                    <?php if ($user->ultimo_login): ?>
                                        <div style="line-height: 1.1;">
                                            <span class="d-block fw-bold text-dark" style="font-size: 0.9rem;">
                                                <?php echo date('d/m/Y', strtotime($user->ultimo_login)); ?>
                                            </span>
                                            <small class="text-muted" style="font-size: 0.75rem;">
                                                <?php echo date('H:i', strtotime($user->ultimo_login)); ?> hrs
                                            </small>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted fst-italic small opacity-50">Nunca</span>
                                    <?php endif; ?>
                                </td>
                                
                                <td class="text-end pe-3 text-nowrap">
                                    <div class="btn-group btn-group-sm shadow-sm">
                                        <a href="<?php echo URL_ROOT; ?>/users/form/<?php echo $user->id; ?>" 
                                           class="btn btn-outline-primary" 
                                           data-bs-toggle="tooltip" title="Editar Usuario">
                                            <i class="bi bi-pencil-fill"></i>
                                        </a>
                                        
                                        <?php if ($user->id != SessionHelper::getUserId()): ?>
                                            <form action="<?php echo URL_ROOT; ?>/users/delete/<?php echo $user->id; ?>" method="POST" class="d-inline" onsubmit="return confirm('¿Está seguro que desea eliminar este usuario?');">
                                                <button type="submit" class="btn btn-outline-danger border-start-0" data-bs-toggle="tooltip" title="Eliminar">
                                                    <i class="bi bi-trash-fill"></i>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <button class="btn btn-outline-secondary border-start-0 disabled" title="No puedes borrarte a ti mismo">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
