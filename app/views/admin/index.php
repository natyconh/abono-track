<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-0 text-primary-dark-green fw-bold">Panel de Administración</h3>
            <p class="text-muted mb-0">Configuración maestra del sistema.</p>
        </div>
    </div>

    <!-- NAVEGACIÓN POR PESTAÑAS -->
    <ul class="nav nav-pills mb-4" id="adminTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab">
                <i class="bi bi-people-fill me-2"></i>General & Usuarios
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="campo-tab" data-bs-toggle="tab" data-bs-target="#campo" type="button" role="tab">
                <i class="bi bi-geo-alt-fill me-2"></i>Infraestructura
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="produccion-tab" data-bs-toggle="tab" data-bs-target="#produccion" type="button" role="tab">
                <i class="bi bi-box-seam-fill me-2"></i>Producción & Cosecha
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="catalogos-tab" data-bs-toggle="tab" data-bs-target="#catalogos" type="button" role="tab">
                <i class="bi bi-tags-fill me-2"></i>Catálogos Técnicos
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="auditoria-tab" data-bs-toggle="tab" data-bs-target="#auditoria" type="button" role="tab">
                <i class="bi bi-clipboard-check-fill me-2"></i>Auditoría
            </button>
        </li>
    </ul>

    <!-- CONTENIDO DE LAS PESTAÑAS -->
    <div class="tab-content" id="adminTabsContent">
        
        <!-- TAB 1: GENERAL -->
        <div class="tab-pane fade show active" id="general" role="tabpanel">
            <div class="row g-4">
                <div class="col-md-6 col-lg-4">
                    <a href="<?php echo URL_ROOT; ?>/users/admin" class="text-decoration-none">
                        <div class="card action-card h-100 text-center p-4 border-start border-4 border-primary">
                            <div class="card-body">
                                <i class="bi bi-person-gear-fill action-icon text-primary mb-3 d-block"></i>
                                <h5 class="card-title fw-bold text-dark">Usuarios de Sistema</h5>
                                <p class="card-text text-muted small">Accesos, contraseñas y roles.</p>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-6 col-lg-4">
                    <a href="<?php echo URL_ROOT; ?>/trabajadores" class="text-decoration-none">
                        <div class="card action-card h-100 text-center p-4 border-start border-4 border-success">
                            <div class="card-body">
                                <i class="bi bi-person-vcard-fill action-icon text-success mb-3 d-block"></i>
                                <h5 class="card-title fw-bold text-dark">Trabajadores</h5>
                                <p class="card-text text-muted small">Maestro de personal (RUT, Cargo).</p>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>

        <!-- TAB 2: INFRAESTRUCTURA -->
        <div class="tab-pane fade" id="campo" role="tabpanel">
            <div class="row g-4">
                <div class="col-md-6 col-lg-4">
                    <a href="<?php echo URL_ROOT; ?>/predios" class="text-decoration-none">
                        <div class="card action-card h-100 text-center p-4">
                            <div class="card-body">
                                <i class="bi bi-bounding-box action-icon text-secondary mb-3 d-block"></i>
                                <h5 class="card-title fw-bold text-dark">Predios</h5>
                                <p class="card-text text-muted small">Unidades productivas y superficies.</p>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-6 col-lg-4">
                    <a href="<?php echo URL_ROOT; ?>/sectores" class="text-decoration-none">
                        <div class="card action-card h-100 text-center p-4">
                            <div class="card-body">
                                <i class="bi bi-grid-3x3 action-icon text-secondary mb-3 d-block"></i>
                                <h5 class="card-title fw-bold text-dark">Sectores</h5>
                                <p class="card-text text-muted small">Sub-divisiones y cuarteles.</p>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-6 col-lg-4">
                    <a href="<?php echo URL_ROOT; ?>/instalaciones" class="text-decoration-none">
                        <div class="card action-card h-100 text-center p-4">
                            <div class="card-body">
                                <i class="bi bi-buildings-fill action-icon text-secondary mb-3 d-block"></i>
                                <h5 class="card-title fw-bold text-dark">Instalaciones</h5>
                                <p class="card-text text-muted small">Casetas, bodegas y puntos fijos.</p>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-6 col-lg-4">
                    <a href="<?php echo URL_ROOT; ?>/fertilizacion/configuracion" class="text-decoration-none">
                        <div class="card action-card h-100 text-center p-4 border-start border-4 border-info">
                            <div class="card-body">
                                <i class="bi bi-diagram-3-fill action-icon text-info mb-3 d-block"></i>
                                <h5 class="card-title fw-bold text-dark">Conexiones de Riego</h5>
                                <p class="card-text text-muted small">Cabezales y distribución hidráulica.</p>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>

        <!-- TAB 3: PRODUCCIÓN (ACTUALIZADA) -->
        <div class="tab-pane fade" id="produccion" role="tabpanel">
            <div class="row g-4">
                
                <!-- Wizard -->
                <div class="col-md-12 mb-2">
                    <a href="<?php echo URL_ROOT; ?>/cosecha/configuracion" class="text-decoration-none">
                        <div class="card action-card h-100 p-4 border-start border-4 border-warning bg-light">
                            <div class="card-body d-flex align-items-center justify-content-between">
                                <div>
                                    <h5 class="card-title fw-bold text-dark mb-1"><i class="bi bi-magic me-2 text-warning"></i>Modelo Operativo de Cosecha (Wizard)</h5>
                                    <p class="card-text text-muted small mb-0">Configure cómo se pesa la fruta y la trazabilidad legal.</p>
                                </div>
                                <i class="bi bi-chevron-right text-muted"></i>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Razones Sociales -->
                <div class="col-md-6 col-lg-4">
                    <a href="<?php echo URL_ROOT; ?>/entidadLegal" class="text-decoration-none">
                        <div class="card action-card h-100 text-center p-4">
                            <div class="card-body">
                                <i class="bi bi-briefcase-fill action-icon text-primary mb-3 d-block"></i>
                                <h5 class="card-title fw-bold text-dark">Razones Sociales</h5>
                                <p class="card-text text-muted small">Gestión de RUTs y Códigos SAG.</p>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Destinos -->
                <div class="col-md-6 col-lg-4">
                    <a href="<?php echo URL_ROOT; ?>/cosechaDestino" class="text-decoration-none">
                        <div class="card action-card h-100 text-center p-4">
                            <div class="card-body">
                                <i class="bi bi-truck action-icon text-success mb-3 d-block"></i>
                                <h5 class="card-title fw-bold text-dark">Destinos y Clientes</h5>
                                <p class="card-text text-muted small">Packings, Ferias y Poderes de Compra.</p>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- IMPORTADOR (NUEVO) -->
                <div class="col-md-6 col-lg-4">
                    <a href="<?php echo URL_ROOT; ?>/cosechaImport" class="text-decoration-none">
                        <div class="card action-card h-100 text-center p-4 border-2 border-success border-opacity-25">
                            <div class="card-body">
                                <i class="bi bi-file-earmark-spreadsheet-fill action-icon text-success mb-3 d-block"></i>
                                <h5 class="card-title fw-bold text-dark">Carga Masiva (Excel)</h5>
                                <p class="card-text text-muted small">Importar historial de cosechas antiguas.</p>
                            </div>
                        </div>
                    </a>
                </div>

            </div>
        </div>

        <!-- TAB 4: CATÁLOGOS -->
        <div class="tab-pane fade" id="catalogos" role="tabpanel">
            <div class="row g-4">
                <div class="col-md-6 col-lg-3">
                    <a href="<?php echo URL_ROOT; ?>/cultivos" class="text-decoration-none">
                        <div class="card action-card h-100 text-center p-4 border-top border-4 border-success bg-light-green">
                            <div class="card-body">
                                <i class="bi bi-tree-fill action-icon text-success mb-3 d-block"></i>
                                <h5 class="card-title fw-bold text-dark">Cultivos y Especies</h5>
                                <p class="card-text text-muted small">Paltos, Cítricos, Variedades.</p>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-6 col-lg-3">
                    <a href="<?php echo URL_ROOT; ?>/fertilizante" class="text-decoration-none">
                        <div class="card action-card h-100 text-center p-4">
                            <div class="card-body">
                                <i class="bi bi-bucket-fill action-icon text-warning mb-3 d-block"></i>
                                <h5 class="card-title fw-bold text-dark">Fertilizantes</h5>
                                <p class="card-text text-muted small">Productos, NPK y densidades.</p>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-6 col-lg-3">
                    <a href="<?php echo URL_ROOT; ?>/puntoTipo" class="text-decoration-none">
                        <div class="card action-card h-100 text-center p-4">
                            <div class="card-body">
                                <i class="bi bi-pin-map-fill action-icon text-danger mb-3 d-block"></i>
                                <h5 class="card-title fw-bold text-dark">Tipos de Puntos</h5>
                                <p class="card-text text-muted small">Categorías GIS (Plagas, Fallas).</p>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-6 col-lg-3">
                    <a href="<?php echo URL_ROOT; ?>/solicitudCategoria" class="text-decoration-none">
                        <div class="card action-card h-100 text-center p-4">
                            <div class="card-body">
                                <i class="bi bi-tags-fill action-icon text-secondary mb-3 d-block"></i>
                                <h5 class="card-title fw-bold text-dark">Categorías Solicitud</h5>
                                <p class="card-text text-muted small">Clasificación de tickets.</p>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>

        <!-- TAB 5: AUDITORÍA -->
        <div class="tab-pane fade" id="auditoria" role="tabpanel">
            <div class="row g-4">
                <div class="col-md-6">
                    <a href="<?php echo URL_ROOT; ?>/riego/admin" class="text-decoration-none">
                        <div class="card action-card h-100 text-center p-4">
                            <div class="card-body">
                                <i class="bi bi-droplet-half action-icon text-primary mb-3 d-block"></i>
                                <h5 class="card-title fw-bold text-dark">Historial de Riego</h5>
                                <p class="card-text text-muted small">Corregir y auditar registros diarios.</p>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-6">
                    <a href="<?php echo URL_ROOT; ?>/clima/admin" class="text-decoration-none">
                        <div class="card action-card h-100 text-center p-4">
                            <div class="card-body">
                                <i class="bi bi-sun-fill action-icon text-warning mb-3 d-block"></i>
                                <h5 class="card-title fw-bold text-dark">Historial de Clima</h5>
                                <p class="card-text text-muted small">Corregir datos de bandeja y lluvia.</p>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>