<?php
// app/views/layout/header.php
$current_url_path = $_GET['url'] ?? 'home/index';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <?php if (isset($use_datatables) && $use_datatables): ?>
        <link rel="stylesheet" href="https://cdn.datatables.net/2.0.3/css/dataTables.bootstrap5.css">
    <?php endif; ?>

    <link rel="stylesheet" href="<?php echo URL_ROOT; ?>/css/style.css?v=1.0">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>&#x1F33F;</text></svg>">

    <title><?php echo isset($titulo) ? $titulo . ' - Abono Track' : 'Abono Track'; ?></title>
</head>
<body class="bg-light-beige d-flex flex-column min-vh-100">

       <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg sticky-top">
        <div class="container-fluid">
            <button class="navbar-toggler d-lg-none me-2" type="button" id="sidebarToggle">
                <i class="bi bi-list text-primary-dark-green fs-2"></i>
            </button>

            <a class="navbar-brand" href="<?php echo URL_ROOT; ?>">
                abono<span class="brand-y">&middot;</span>track
                <span class="badge bg-light text-secondary ms-2 fw-normal border" style="font-size: 0.6rem; vertical-align: middle;">BETA</span>
            </a>

            <div class="ms-auto d-flex align-items-center gap-3">
                
                <!-- Botón "Acerca de" visible directamente en el navbar -->
                <button class="btn btn-sm btn-outline-success rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#aboutModal">
                    <i class="bi bi-info-circle me-1"></i> <span class="d-none d-md-inline">Acerca del Proyecto</span>
                </button>

                <div class="dropdown">
                    <a href="#" class="nav-link dropdown-toggle d-flex align-items-center gap-2" data-bs-toggle="dropdown">
                        <div class="d-none d-md-block text-end" style="line-height: 1.2;">
                            <small class="d-block fw-bold"><?php echo htmlspecialchars(SessionHelper::getUserName() ?? 'Usuario'); ?></small>
                        </div>
                        <i class="bi bi-person-circle fs-4 text-secondary"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end border-0 shadow">
                        <li><h6 class="dropdown-header"><?php echo htmlspecialchars(SessionHelper::getUserName() ?? 'Usuario'); ?></h6></li>
                        <li>
                            <a class="dropdown-item text-danger" href="<?php echo URL_ROOT; ?>/users/logout">
                                <i class="bi bi-box-arrow-right me-2"></i> Cerrar Sesi&oacute;n
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- Modal Acerca de -->
    <div class="modal fade" id="aboutModal" tabindex="-1" aria-labelledby="aboutModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-light border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-primary-dark-green" id="aboutModalLabel">
                        🌿 abono·track
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-3">
                    <p class="text-muted small mb-4">
                        Plataforma web orientada a la gestión y trazabilidad de programas de fertilización agrícola.
                    </p>
                    
                    <div class="mb-3">
                        <h6 class="fw-bold mb-1"><i class="bi bi-code-slash me-2"></i>Estudiantes</h6>
                        <ul class="list-unstyled ms-4 small mb-0">
                            <li>Cristian Manzano Ayala</li>
                            <li>Nathalia Castro Muñoz</li>
                        </ul>
                    </div>

                    <div class="mb-0">
                        <h6 class="fw-bold mb-1"><i class="bi bi-mortarboard me-2"></i>Contexto Académico</h6>
                        <ul class="list-unstyled ms-4 small mb-0 text-muted">
                            <li><strong>Institución:</strong> AIEP</li>
                            <li><strong>Carrera:</strong> Ing. de Ejecución en Informática</li>
                            <li><strong>Asignatura:</strong> Taller de Proyecto de Especialidad</li>
                            <li><strong>Profesor:</strong> Felipe Montenegro González</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Fin Modal -->

    <div class="d-flex flex-grow-1">

        <?php require_once APP_ROOT . '/views/layout/sidebar.php'; ?>

        <div class="sidebar-overlay d-none" id="overlay"></div>

        <main class="container-fluid my-4 p-4 flex-grow-1 bg-white shadow-sm rounded-lg mx-3">

            <?php if (isset($breadcrumbs) && !empty($breadcrumbs) && is_array($breadcrumbs)): ?>
                <nav aria-label="breadcrumb" style="--bs-breadcrumb-divider: '/'">
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="<?php echo URL_ROOT; ?>/home/index" class="text-decoration-none text-muted"><i class="bi bi-house-fill"></i></a></li>
                        <?php
                        $last = array_key_last($breadcrumbs);
                        foreach ($breadcrumbs as $k => $c):
                        ?>
                            <?php if ($k == $last): ?>
                                <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($c['label']); ?></li>
                            <?php else: ?>
                                <li class="breadcrumb-item"><a href="<?php echo $c['url']; ?>"><?php echo htmlspecialchars($c['label']); ?></a></li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ol>
                </nav>
            <?php endif; ?>

            <?php SessionHelper::displayFlash(); ?>