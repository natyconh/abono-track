<div class="container-fluid mt-4">
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between bg-white py-3">
            <h4 class="mb-0 text-primary-dark-green fw-bold"><?php echo $data['titulo']; ?></h4>
            <a href="<?php echo URL_ROOT; ?>/entidadLegal/form" class="btn btn-accent-calendula"><i class="bi bi-plus-circle"></i> Nueva Entidad</a>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0 align-middle">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Razón Social</th>
                        <th>RUT</th>
                        <th>Código SAG (CSG)</th>
                        <th class="text-end pe-4">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($data['entidades'] as $e): ?>
                    <tr>
                        <td class="ps-4">
                            <div class="fw-bold"><?php echo htmlspecialchars($e->razon_social); ?></div>
                            <small class="text-muted"><?php echo htmlspecialchars($e->nombre_fantasia); ?></small>
                        </td>
                        <td><?php echo htmlspecialchars($e->rut); ?></td>
                        <td><span class="badge bg-info text-dark"><?php echo htmlspecialchars($e->codigo_sag); ?></span></td>
                        <td class="text-end pe-4">
                            <a href="<?php echo URL_ROOT; ?>/entidadLegal/form/<?php echo $e->id; ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            <form action="<?php echo URL_ROOT; ?>/entidadLegal/eliminar/<?php echo $e->id; ?>" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar?');">
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>