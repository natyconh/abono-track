<?php
// _legacy/abono-track/app/views/layout/sidebar.php
// Sidebar de Abono Track — menú reducido al dominio de abonado/riego
// Roles disponibles: Admin, Usuario
?>
<nav id="sidebar" class="bg-primary-dark-green p-3" style="min-width: 240px; transition: all 0.3s;">
    <div class="position-sticky d-flex flex-column h-100">

        <div class="text-white-50 small text-uppercase fw-bold mb-2 ps-3" style="font-size: 0.7rem; letter-spacing: 1px;">Menú Principal</div>

        <ul class="nav flex-column gap-1">

            <!-- Home -->
            <li class="nav-item">
                <a class="nav-link <?php echo (strpos($current_url_path, 'home') === 0) ? 'active' : ''; ?>" href="<?php echo URL_ROOT; ?>/home/index">
                    <i class="bi bi-grid-1x2-fill"></i> Dashboard
                </a>
            </li>

            <!-- SECCIÓN CATÁLOGOS -->
            <li class="my-2 border-top border-white border-opacity-10"></li>
            <div class="text-white-50 small text-uppercase fw-bold mb-2 ps-3" style="font-size: 0.7rem; letter-spacing: 1px;">Catálogos</div>

            <li class="nav-item">
                <a class="nav-link <?php echo (strpos($current_url_path, 'fertilizante') === 0) ? 'active' : ''; ?>" href="<?php echo URL_ROOT; ?>/fertilizante">
                    <i class="bi bi-bucket-fill"></i> Productos / Fertilizantes
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link <?php echo (strpos($current_url_path, 'cultivos') === 0) ? 'active' : ''; ?>" href="<?php echo URL_ROOT; ?>/cultivos">
                    <i class="bi bi-tree-fill"></i> Cultivos
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link <?php echo (strpos($current_url_path, 'predios') === 0) ? 'active' : ''; ?>" href="<?php echo URL_ROOT; ?>/predios">
                    <i class="bi bi-map-fill"></i> Predios / Cuarteles
                </a>
            </li>

            <!-- SECCIÓN OPERACIÓN -->
            <li class="my-2 border-top border-white border-opacity-10"></li>
            <div class="text-white-50 small text-uppercase fw-bold mb-2 ps-3" style="font-size: 0.7rem; letter-spacing: 1px;">Operación</div>

            <li class="nav-item">
                <a class="nav-link <?php echo (strpos($current_url_path, 'riego') === 0) ? 'active' : ''; ?>" href="<?php echo URL_ROOT; ?>/riego">
                    <i class="bi bi-droplet-fill"></i> Riego Diario
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link <?php echo (strpos($current_url_path, 'fertilizacion') === 0) ? 'active' : ''; ?>" href="<?php echo URL_ROOT; ?>/fertilizacion">
                    <i class="bi bi-eyedropper"></i> Fertirrigación
                </a>
            </li>

        </ul>

        <div class="mt-auto pt-4 text-center opacity-50">
            <small class="text-white" style="font-size: 0.7rem;">
                &copy; <?php echo date('Y'); ?> Abono Track<br>v0.1.0
            </small>
        </div>
    </div>
</nav>
