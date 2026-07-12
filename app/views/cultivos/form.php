<?php
// _legacy/abono-track/app/views/cultivos/form.php
?>
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-6 mx-auto">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <a href="<?php echo URL_ROOT; ?>/cultivos" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
                <h5 class="mb-0 text-muted"><?php echo $data['titulo']; ?></h5>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <form action="<?php echo URL_ROOT; ?>/cultivos/guardar" method="POST">

                        <input type="hidden" name="id" value="<?php echo $data['cultivo']->id; ?>">

                        <div class="mb-4">
                            <label for="nombre" class="form-label fw-bold text-primary-dark-green">Especie / Nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-lg" id="nombre" name="nombre"
                                   value="<?php echo htmlspecialchars($data['cultivo']->nombre); ?>"
                                   placeholder="Ej: Palto, Cítrico, Nogal" required autofocus>
                            <div class="form-text">El nombre genérico de la planta.</div>
                        </div>

                        <div class="mb-4">
                            <label for="variedad" class="form-label fw-bold text-primary-dark-green">Variedad (Opcional)</label>
                            <input type="text" class="form-control" id="variedad" name="variedad"
                                   value="<?php echo htmlspecialchars($data['cultivo']->variedad); ?>"
                                   placeholder="Ej: Hass, Eureka, Serr">
                            <div class="form-text">Especifique si el manejo agronómico cambia por variedad.</div>
                        </div>

                        <hr>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-accent-calendula btn-lg fw-bold">
                                <i class="bi bi-save me-2"></i> Guardar Cultivo
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
