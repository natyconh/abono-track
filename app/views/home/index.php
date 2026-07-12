<?php
// app/views/home/index.php — Abono Track
?>
<div class="container-fluid mt-4">
    <!-- Bienvenida -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0 bg-primary-dark-green text-white">
                <div class="card-body p-4 d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-1">¡Hola, <?php echo htmlspecialchars($data['nombre_bienvenida']); ?>!</h3>
                        <p class="mb-0 opacity-75">Bienvenido a tu panel de control de fertirrigación.</p>
                    </div>
                    <i class="bi bi-tree fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Accesos Rápidos -->
    <div class="row g-4">
        <!-- Registro Diario -->
        <div class="col-md-4">
            <a href="<?php echo URL_ROOT; ?>/riego" class="text-decoration-none">
                <div class="card h-100 shadow-sm border-0 hover-card">
                    <div class="card-body text-center p-4">
                        <div class="mb-3 fs-1 text-primary-dark-green"><i class="bi bi-droplet-half"></i></div>
                        <h5 class="card-title fw-bold">Registro de Riego</h5>
                        <p class="card-text text-muted small">Ingresa los tiempos de riego diarios por predio.</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- Fertirrigación -->
        <div class="col-md-4">
            <a href="<?php echo URL_ROOT; ?>/fertilizacion" class="text-decoration-none">
                <div class="card h-100 shadow-sm border-0 hover-card">
                    <div class="card-body text-center p-4">
                        <div class="mb-3 fs-1 text-success"><i class="bi bi-eyedropper"></i></div>
                        <h5 class="card-title fw-bold">Fertirrigación</h5>
                        <p class="card-text text-muted small">Registra aplicaciones y revisa programas.</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- Reportes -->
        <div class="col-md-4">
            <a href="<?php echo URL_ROOT; ?>/fertilizacion/reporteNutricional" class="text-decoration-none">
                <div class="card h-100 shadow-sm border-0 hover-card">
                    <div class="card-body text-center p-4">
                        <div class="mb-3 fs-1 text-warning"><i class="bi bi-bar-chart-line-fill"></i></div>
                        <h5 class="card-title fw-bold">Reportes NPK</h5>
                        <p class="card-text text-muted small">Consulta el balance nutricional acumulado.</p>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Info de Configuración -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom-0 pt-3">
                    <h6 class="text-muted fw-bold text-uppercase small">Administración</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex gap-3">
                        <a href="<?php echo URL_ROOT; ?>/predios" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-map me-1"></i> Gestionar Predios
                        </a>
                        <a href="<?php echo URL_ROOT; ?>/fertilizante" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-bucket me-1"></i> Catálogo Fertilizantes
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.hover-card:hover {
    transform: translateY(-5px);
    transition: all 0.2s ease;
    background-color: #f8f9fa;
}
.bg-primary-dark-green {
    background-color: #1a3a2a !important;
}
</style>