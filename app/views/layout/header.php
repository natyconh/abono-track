<?php
// app/views/layout/header.php
// Header "Clean Canvas" con Logo Tipográfico Ryzoma
// Actualizado: Sidebar extraído a componente separado
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
    
    <link rel="stylesheet" href="<?php echo URL_ROOT; ?>/css/style.css?v=3.0">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🌱</text></svg>">
    
    <title><?php echo isset($titulo) ? $titulo . ' - Ryzoma' : 'Ryzoma | Agro'; ?></title>
</head>
<body class="bg-light-beige d-flex flex-column min-vh-100">

    <!-- Header "Clean Canvas" -->
    <nav class="navbar navbar-expand-lg navbar-dark bg sticky-top">
        <div class="container-fluid">
            <button class="navbar-toggler d-lg-none me-2" type="button" id="sidebarToggle">
                <i class="bi bi-list text-primary-dark-green fs-2"></i>
            </button>

            <a class="navbar-brand" href="<?php echo URL_ROOT; ?>">
                r<span class="brand-y">y</span>zoma
                <span class="badge bg-light text-secondary ms-2 fw-normal border" style="font-size: 0.6rem; vertical-align: middle;">AGRO</span>
            </a>
            
            <div class="ms-auto dropdown">
                <a href="#" class="nav-link dropdown-toggle d-flex align-items-center gap-2" data-bs-toggle="dropdown">
                    <div class="d-none d-md-block text-end" style="line-height: 1.2;">
                        <small class="d-block fw-bold"><?php echo htmlspecialchars(SessionHelper::getUserName() ?? 'Usuario'); ?></small>
                        <small class="text-muted" style="font-size: 0.75rem;"><?php echo htmlspecialchars(SessionHelper::getUserRoleName()); ?></small>
                    </div>
                    <i class="bi bi-person-circle fs-4 text-secondary"></i>
                </a>
                <ul class="dropdown-menu dropdown-menu-end border-0 shadow">
                    <li><h6 class="dropdown-header">Cuenta</h6></li>
                    <li><a class="dropdown-item" href="<?php echo URL_ROOT; ?>/users/perfil"><i class="bi bi-person-gear me-2"></i> Mi Perfil</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item text-danger" href="<?php echo URL_ROOT; ?>/users/logout">
                            <i class="bi bi-box-arrow-right me-2"></i> Cerrar Sesión
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <div class="d-flex flex-grow-1">
        
        <!-- Sidebar Navigation (Refactorizado) -->
        <?php require_once APP_ROOT . '/views/layout/sidebar.php'; ?>
        
<div class="sidebar-overlay d-none" id="overlay"></div>

        <?php 
        // LÓGICA PARA AISLAR EL MAPA
        // Verificamos si la URL actual corresponde al mapa
        $is_map_view = (strpos($current_url_path, 'puntos/mapa') !== false);

        // Si es el mapa: Usamos un contenedor flexible limpio (sin márgenes ni padding)
        // Si NO es el mapa: Usamos el contenedor original con tarjeta blanca y sombras
        $main_classes = $is_map_view 
            ? 'flex-grow-1 p-0 m-0 position-relative' 
            : 'container-fluid my-4 p-4 flex-grow-1 bg-white shadow-sm rounded-lg mx-3';
        ?>

        <main class="<?php echo $main_classes; ?>">
            
            <?php if (isset($breadcrumbs) && !empty($breadcrumbs) && is_array($breadcrumbs)): ?>
                <nav aria-label="breadcrumb" class="<?php echo $is_map_view ? 'px-3 pt-3' : ''; ?>" style="--bs-breadcrumb-divider: '/';">
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