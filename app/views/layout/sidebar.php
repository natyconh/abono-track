<?php
// app/views/layout/sidebar.php
// Sidebar de Navegación Principal - Extraído para modularidad
// Se asume que $current_url_path está disponible desde el archivo que lo incluye (header.php)
?>
<nav id="sidebar" class="bg-primary-dark-green p-3" style="min-width: 240px; transition: all 0.3s;">
    <div class="position-sticky d-flex flex-column h-100">
        
        <div class="text-white-50 small text-uppercase fw-bold mb-2 ps-3" style="font-size: 0.7rem; letter-spacing: 1px;">Menú Principal</div>
        
        <ul class="nav flex-column gap-1">
            <li class="nav-item">
               <a class="nav-link <?php echo (strpos($current_url_path, 'home') === 0) ? 'active' : ''; ?>" href="<?php echo URL_ROOT; ?>/home/index">
                    <i class="bi bi-grid-1x2-fill"></i> Home
                </a>
            </li>

            <?php if (SessionHelper::hasRole(['Admin', 'Usuario_general', 'Usuario_riego'])): ?>
            <li class="nav-item">
              <a class="nav-link <?php echo (strpos($current_url_path, 'puntos') === 0) ? 'active' : ''; ?>" href="<?php echo URL_ROOT; ?>/puntos">
                    <i class="bi bi-geo-alt-fill"></i> Puntos (GIS)
                </a>
            </li>
            <?php endif; ?>

            <?php if (SessionHelper::hasRole(['Admin', 'Usuario_riego'])): ?>
            <li class="my-2 border-top border-white border-opacity-10"></li>
            <div class="text-white-50 small text-uppercase fw-bold mb-2 ps-3" style="font-size: 0.7rem; letter-spacing: 1px;">Riego & Clima</div>

            <li class="nav-item">
                <a class="nav-link <?php echo (strpos($current_url_path, 'clima') === 0 && strpos($current_url_path, 'admin') === false) ? 'active' : ''; ?>" href="<?php echo URL_ROOT; ?>/clima">
                    <i class="bi bi-cloud-sun-fill"></i> Bandejas y Precipitaciones
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo (strpos($current_url_path, 'riego') === 0 && strpos($current_url_path, 'miHistorial') === false && strpos($current_url_path, 'admin') === false) ? 'active' : ''; ?>" href="<?php echo URL_ROOT; ?>/riego">
                    <i class="bi bi-droplet-fill"></i> Riego diario
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo (strpos($current_url_path, 'fertilizacion') === 0 && strpos($current_url_path, 'historial') === false && strpos($current_url_path, 'configuracion') === false) ? 'active' : ''; ?>" href="<?php echo URL_ROOT; ?>/fertilizacion">
                    <i class="bi bi-bucket-fill"></i> Fertirrigación
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo (strpos($current_url_path, 'miHistorial') !== false) ? 'active' : ''; ?>" href="<?php echo URL_ROOT; ?>/riego/miHistorial">
                    <i class="bi bi-calendar-week-fill"></i> Bitácora de Riego
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo (strpos($current_url_path, 'fertilizacion/historial') !== false) ? 'active' : ''; ?>" href="<?php echo URL_ROOT; ?>/fertilizacion/historial">
                    <i class="bi bi-journal-album"></i> Bitácora Ferti.
                </a>
            </li>
            <?php endif; ?>

            <!-- SECCIÓN PRODUCCIÓN (ACTUALIZADA) -->
            <?php if (SessionHelper::hasRole(['Admin', 'Usuario_general', 'Terreno'])): ?>
            <li class="my-2 border-top border-white border-opacity-10"></li>
            <div class="text-white-50 small text-uppercase fw-bold mb-2 ps-3" style="font-size: 0.7rem; letter-spacing: 1px;">Producción</div>
            
            <li class="nav-item">
                <a class="nav-link <?php echo (strpos($current_url_path, 'cosecha/registro') !== false) ? 'active' : ''; ?>" href="<?php echo URL_ROOT; ?>/cosecha/registro">
                    <i class="bi bi-plus-circle-dotted"></i> Nueva Cosecha
                </a>
            </li>
            
            <!-- Nuevo: Enlace a bitacora de cosecha -->
            <?php if (SessionHelper::hasRole(['Admin', 'Usuario_general'])): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo (strpos($current_url_path, 'cosecha/index') === 0) ? 'active' : ''; ?>" href="<?php echo URL_ROOT; ?>/cosecha/index">
                    <i class="bi bi-graph-up-arrow text-warning"></i> Bitacora Cosecha
                </a>
            </li>
            <?php endif; ?>

            <?php if (SessionHelper::hasRole(['Admin', 'Usuario_general', 'Terreno'])): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo (strpos($current_url_path, 'avanceLabores') === 0) ? 'active' : ''; ?>" href="<?php echo URL_ROOT; ?>/avanceLabores">
                    <i class="bi bi-card-checklist text-success"></i> Avance de Labores
                </a>
            </li>
            <?php endif; ?>

            <?php endif; ?>

            <?php if (SessionHelper::hasRole(['Admin', 'Usuario_general', 'Usuario_riego'])): ?>
            <li class="my-2 border-top border-white border-opacity-10"></li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo (strpos($current_url_path, 'reporte') === 0 && strpos($current_url_path, 'reporte/labores_gerencial') === false && strpos($current_url_path, 'reporteCosecha') === false) ? 'active' : ''; ?>" href="<?php echo URL_ROOT; ?>/reporte">
                    <i class="bi bi-bar-chart-fill"></i> Central Reportes
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo (strpos($current_url_path, 'reporte/labores_gerencial') === 0) ? 'active text-white' : 'text-white-50'; ?> ps-4 py-1" href="<?php echo URL_ROOT; ?>/reporte/labores_gerencial" style="font-size: 0.85rem;">
                    <i class="bi bi-arrow-return-right me-2"></i> Tablero Labores
                </a>
            </li>
            <?php endif; ?>

            <?php if (SessionHelper::hasRole(['Admin'])): ?>
            <li class="mt-auto">
                <a class="nav-link <?php echo (strpos($current_url_path, 'admin') === 0) ? 'active' : ''; ?> mt-3 bg-white bg-opacity-10" href="<?php echo URL_ROOT; ?>/admin">
                   <i class="bi bi-sliders"></i> Configuración
                </a>
            </li>
            <?php endif; ?>
        </ul>

        <div class="mt-auto pt-4 text-center opacity-50">
            <small class="text-white" style="font-size: 0.7rem;">
                &copy; <?php echo date('Y'); ?> Ryzoma Agro<br>v0.5.5
            </small>
        </div>
    </div>
</nav>