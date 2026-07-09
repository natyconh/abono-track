<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $data['titulo']; ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo URL_ROOT; ?>/css/style.css">

    <style>
        /* Forzar fondo beige de marca */
        body { background-color: var(--ryzoma-beige) !important; }
        /* Ajuste para que los encabezados coincidan con el estilo privado */
        .table-header-clean th { 
            font-weight: 700; 
            font-size: 0.85rem; 
            letter-spacing: 0.05em;
            color: var(--ryzoma-charcoal);
        }
    </style>
</head>
<body>
    </nav>

    <div class="container mt-4">
        
        <div class="alert bg-white border-0 shadow-sm mb-4 d-flex align-items-center" role="alert" style="border-left: 4px solid var(--vertical-water) !important;">
            <i class="bi bi-shield-lock text-accent-sky-blue fs-4 me-3"></i>
            <div class="text-muted small">
                Reporte compartido de forma segura por: <br>
                <strong class="text-dark fs-6"><?php echo htmlspecialchars($data['empresa']); ?></strong>
                <span class="badge bg-warning text-dark border border-white border-opacity-25 rounded-pill shadow-sm">
                VISTA EXTERNA
            </span>
            </div>
        </div>

        <div class="card shadow border-0 mb-5 overflow-hidden rounded-4">
            
            <div class="card-header bg-white py-4 px-4 border-bottom-0">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                    <div>
                        <h3 class="mb-1 fw-bold text-primary-dark-green brand-font">Reporte Nutricional</h3>
                        <p class="text-muted mb-0 small">Resumen de Temporada <?php echo date('Y'); ?></p>
                    </div>
                    
                    <button class="btn btn-outline-secondary w-100 w-md-auto d-flex align-items-center justify-content-center gap-2 rounded-pill px-4" onclick="window.print()">
                        <i class="bi bi-printer-fill"></i> 
                        <span>Imprimir</span>
                    </button>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light table-header-clean text-uppercase border-bottom">
                            <tr>
                                <th class="py-3 ps-4 text-muted">Sector</th>
                                <th class="py-3 text-muted">Cultivo</th>
                                <th class="text-center py-3 text-muted">Sup.<br><small>(Ha)</small></th>
                                
                                <th class="text-center py-3 border-start text-success">
                                    N <br><small class="opacity-75 text-muted fw-normal">(U/Ha)</small>
                                </th>
                                
                                <th class="text-center py-3 text-warning text-opacity-75" style="color: #d68c00 !important;">
                                    P <br><small class="opacity-75 text-muted fw-normal">(U/Ha)</small>
                                </th>
                                
                                <th class="text-center py-3 text-danger">
                                    K <br><small class="opacity-75 text-muted fw-normal">(U/Ha)</small>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white">
                            <?php foreach($data['datos'] as $row): ?>
                            <tr>
                                <td class="ps-4">
                                    <span class="fw-bold text-dark d-block"><?php echo htmlspecialchars($row->predio); ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-secondary border border-light-subtle rounded-pill fw-normal">
                                        <?php echo htmlspecialchars($row->cultivo ?? '-'); ?>
                                    </span>
                                </td>
                                <td class="text-center font-monospace text-muted">
                                    <?php echo number_format($row->hectareas, 2, ',', '.'); ?>
                                </td>
                                
                                <td class="text-center border-start">
                                    <span class="fw-bold text-success fs-6">
                                        <?php echo number_format($row->n_ha, 1, ',', '.'); ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="fw-bold text-warning text-dark fs-6">
                                        <?php echo number_format($row->p_ha, 1, ',', '.'); ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="fw-bold text-danger fs-6">
                                        <?php echo number_format($row->k_ha, 1, ',', '.'); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="card-footer bg-light text-center py-3 border-top">
                <small class="text-muted opacity-75">
                    Generado el <?php echo date('d/m/Y'); ?> vía Ryzoma Agro
                </small>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>