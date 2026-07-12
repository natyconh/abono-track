<?php
// app/views/home/index.php — Abono Track
// Dashboard mejorado con KPIs, accesos rápidos y estado del sistema
?>

<!-- Bienvenida -->
<div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-3">
    <div>
        <h4 class="fw-bold mb-1">¡Hola, <?php echo htmlspecialchars($data['nombre_bienvenida']); ?>! 👋</h4>
        <p class="text-muted mb-0">Panel de control de fertirrigación — <?php echo date('l, d \d\e F \d\e Y'); ?></p>
    </div>
    <a href="<?php echo URL_ROOT; ?>/fertilizacion/create" class="btn btn-sm btn-primary-agro">
        <i class="bi bi-plus-circle me-1"></i> Nueva Aplicación
    </a>
</div>

<!-- KPI Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-muted small">Predios activos</span>
                    <span class="badge bg-success-soft text-success"><i class="bi bi-map"></i></span>
                </div>
                <div class="fs-3 fw-bold text-dark"><?php echo htmlspecialchars($data['total_predios'] ?? '--'); ?></div>
                <div class="text-muted" style="font-size:0.75rem;">registrados en el sistema</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-muted small">Cultivos</span>
                    <span class="badge bg-primary-soft text-primary-agro"><i class="bi bi-tree"></i></span>
                </div>
                <div class="fs-3 fw-bold text-dark"><?php echo htmlspecialchars($data['total_cultivos'] ?? '--'); ?></div>
                <div class="text-muted" style="font-size:0.75rem;">tipos configurados</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-muted small">Fertilizantes</span>
                    <span class="badge bg-warning-soft text-warning"><i class="bi bi-bucket"></i></span>
                </div>
                <div class="fs-3 fw-bold text-dark"><?php echo htmlspecialchars($data['total_fertilizantes'] ?? '--'); ?></div>
                <div class="text-muted" style="font-size:0.75rem;">en catálogo</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-muted small">Aplicaciones hoy</span>
                    <span class="badge bg-info-soft text-info"><i class="bi bi-eyedropper"></i></span>
                </div>
                <div class="fs-3 fw-bold text-dark"><?php echo htmlspecialchars($data['aplicaciones_hoy'] ?? '0'); ?></div>
                <div class="text-muted" style="font-size:0.75rem;">registradas hoy</div>
            </div>
        </div>
    </div>
</div>

<!-- Accesos Rápidos -->
<div class="row g-3 mb-4">
    <div class="col-12">
        <h6 class="text-muted fw-bold text-uppercase mb-3" style="font-size: 0.75rem; letter-spacing: 0.08em;">Accesos rápidos</h6>
    </div>
    <div class="col-md-4">
        <a href="<?php echo URL_ROOT; ?>/riego" class="text-decoration-none">
            <div class="card border-0 shadow-sm hover-card h-100">
                <div class="card-body p-4 d-flex align-items-start gap-3">
                    <div class="flex-shrink-0 p-2 rounded-3" style="background: rgba(126,196,207,0.15);">
                        <i class="bi bi-droplet-half fs-4" style="color: var(--vertical-water);"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1">Registro de Riego</h6>
                        <p class="card-text text-muted small mb-0">Ingresa los tiempos de riego diarios por predio.</p>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="<?php echo URL_ROOT; ?>/fertilizacion" class="text-decoration-none">
            <div class="card border-0 shadow-sm hover-card h-100">
                <div class="card-body p-4 d-flex align-items-start gap-3">
                    <div class="flex-shrink-0 p-2 rounded-3" style="background: rgba(15,129,100,0.12);">
                        <i class="bi bi-eyedropper fs-4" style="color: var(--vertical-agro);"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1">Fertirrigación</h6>
                        <p class="card-text text-muted small mb-0">Registra aplicaciones y revisa programas de temporada.</p>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="<?php echo URL_ROOT; ?>/fertilizacion/reporteNutricional" class="text-decoration-none">
            <div class="card border-0 shadow-sm hover-card h-100">
                <div class="card-body p-4 d-flex align-items-start gap-3">
                    <div class="flex-shrink-0 p-2 rounded-3" style="background: rgba(255,199,89,0.15);">
                        <i class="bi bi-bar-chart-line-fill fs-4" style="color: var(--ryzoma-yellow);"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1">Reporte NPK</h6>
                        <p class="card-text text-muted small mb-0">Consulta el balance nutricional acumulado.</p>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

<!-- Panel de Administración -->
<div class="row g-3">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="fw-bold mb-0" style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--ryzoma-charcoal);">Gestión de catálogos</h6>
            </div>
            <div class="card-body py-3">
                <div class="d-flex flex-wrap gap-2">
                    <a href="<?php echo URL_ROOT; ?>/predios" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-map me-1"></i> Predios / Cuarteles
                    </a>
                    <a href="<?php echo URL_ROOT; ?>/fertilizante" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-bucket me-1"></i> Fertilizantes
                    </a>
                    <a href="<?php echo URL_ROOT; ?>/cultivos" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-tree me-1"></i> Cultivos
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.hover-card {
    transition: transform 0.18s ease, box-shadow 0.18s ease;
    cursor: pointer;
}
.hover-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.10) !important;
}
.bg-success-soft { background-color: rgba(67,122,34,0.12); }
.bg-primary-soft  { background-color: rgba(15,129,100,0.12); }
.bg-warning-soft  { background-color: rgba(255,199,89,0.18); }
.bg-info-soft     { background-color: rgba(126,196,207,0.18); }
.text-primary-agro { color: var(--vertical-agro) !important; }
.btn-primary-agro {
    background-color: var(--vertical-agro);
    color: #fff;
    border: none;
    font-weight: 600;
    font-size: 0.85rem;
    padding: 0.4rem 1rem;
    border-radius: 0.5rem;
    transition: background-color 0.18s ease;
}
.btn-primary-agro:hover {
    background-color: #0c6650;
    color: #fff;
}
</style>
