<?php
// app/views/layout/sidebar.php
$cp = $_GET['url'] ?? 'home/index';
?>
<nav id="sidebar" class="bg-primary-dark-green p-3" style="min-width: 240px; transition: all 0.3s;">
    <div class="position-sticky d-flex flex-column h-100">

        <div class="text-white-50 small text-uppercase fw-bold mb-2 ps-3" style="font-size: 0.7rem; letter-spacing: 1px;">Menú Principal</div>

        <ul class="nav flex-column gap-1">

            <!-- Home / Dashboard -->
            <li class="nav-item">
                <a class="nav-link <?php echo (strpos($cp, 'home') === 0 || $cp === '') ? 'active' : ''; ?>" href="<?php echo URL_ROOT; ?>/home/index">
                    <i class="bi bi-grid-1x2-fill"></i> Dashboard
                </a>
            </li>

            <!-- SECCIÓN CATÁLOGOS -->
            <li class="my-2 border-top border-white border-opacity-10"></li>
            <div class="text-white-50 small text-uppercase fw-bold mb-2 ps-3" style="font-size: 0.7rem; letter-spacing: 1px;">Catálogos</div>

            <li class="nav-item">
                <a class="nav-link <?php echo (strpos($cp, 'fertilizante') === 0 && strpos($cp, 'fertilizacion') === false) ? 'active' : ''; ?>" href="<?php echo URL_ROOT; ?>/fertilizante">
                    <i class="bi bi-bucket-fill"></i> Fertilizantes
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link <?php echo (strpos($cp, 'cultivos') === 0) ? 'active' : ''; ?>" href="<?php echo URL_ROOT; ?>/cultivos">
                    <i class="bi bi-tree-fill"></i> Cultivos
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link <?php echo (strpos($cp, 'predios') === 0) ? 'active' : ''; ?>" href="<?php echo URL_ROOT; ?>/predios">
                    <i class="bi bi-map-fill"></i> Predios / Cuarteles
                </a>
            </li>

            <!-- SECCIÓN PLANIFICACIÓN -->
            <li class="my-2 border-top border-white border-opacity-10"></li>
            <div class="text-white-50 small text-uppercase fw-bold mb-2 ps-3" style="font-size: 0.7rem; letter-spacing: 1px;">Planificación</div>

            <li class="nav-item">
                <a class="nav-link <?php echo (strpos($cp, 'programa') === 0) ? 'active' : ''; ?>" href="<?php echo URL_ROOT; ?>/programa">
                    <i class="bi bi-calendar2-week-fill"></i> Programas
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo (strpos($cp, 'fertilizacion/configuracion') === 0) ? 'active' : ''; ?>" href="<?php echo URL_ROOT; ?>/fertilizacion/configuracion">
                    <i class="bi bi-diagram-3-fill"></i> Config. Hidráulica
                </a>
            </li>

            <!-- SECCIÓN OPERACIÓN -->
            <li class="my-2 border-top border-white border-opacity-10"></li>
            <div class="text-white-50 small text-uppercase fw-bold mb-2 ps-3" style="font-size: 0.7rem; letter-spacing: 1px;">Operación</div>

            <li class="nav-item">
                <a class="nav-link <?php echo ($cp === 'fertilizacion' || $cp === 'fertilizacion/index') ? 'active' : ''; ?>" href="<?php echo URL_ROOT; ?>/fertilizacion">
                    <i class="bi bi-eyedropper"></i> Fertirrigación
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link <?php echo (strpos($cp, 'fertilizacion/historial') === 0) ? 'active' : ''; ?>" href="<?php echo URL_ROOT; ?>/fertilizacion/historial">
                    <i class="bi bi-journal-text"></i> Bitácora
                </a>
            </li>


            <!-- SECCIÓN REPORTES -->
            <li class="my-2 border-top border-white border-opacity-10"></li>
            <div class="text-white-50 small text-uppercase fw-bold mb-2 ps-3" style="font-size: 0.7rem; letter-spacing: 1px;">Reportes</div>

            <li class="nav-item">
                <a class="nav-link <?php echo (strpos($cp, 'fertilizacion/reporteNutricional') === 0) ? 'active' : ''; ?>" href="<?php echo URL_ROOT; ?>/fertilizacion/reporteNutricional">
                    <i class="bi bi-bar-chart-line-fill"></i> Reporte NPK
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link <?php echo (strpos($cp, 'programa/comparar') === 0) ? 'active' : ''; ?>" href="<?php echo URL_ROOT; ?>/programa/comparar">
                    <i class="bi bi-bar-chart-steps"></i> Programa vs Aplicado
                </a>
            </li>

        </ul>

        <div class="mt-auto pt-4 text-center opacity-50">
            <small class="text-white" style="font-size: 0.7rem;">
                &copy; <?php echo date('Y'); ?> Abono Track<br>
                Por Cristian y Nathalia<br>
                v0.2.0
            </small>
        </div>
    </div>
</nav>
