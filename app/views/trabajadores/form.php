<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-8 mx-auto">
            
            <div class="d-flex justify-content-between align-items-center mb-3">
                <a href="<?php echo URL_ROOT; ?>/trabajadores" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Volver al Listado
                </a>
            </div>

            <div class="card shadow-sm">
                <div class="card-header">
                    <h4 class="mb-0"><?php echo $data['titulo']; ?></h4>
                </div>
                <div class="card-body">
                    <form action="<?php echo URL_ROOT; ?>/trabajadores/guardar" method="POST">
                        
                        <input type="hidden" name="id" value="<?php echo $data['trabajador']->id; ?>">

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="rut" class="form-label">RUT: <span class="text-danger">*</span></label>
                                <input type="text" class="form-control <?php echo (!empty($data['errores']['rut'])) ? 'is-invalid' : ''; ?>" 
                                       id="rut" name="rut" value="<?php echo htmlspecialchars($data['trabajador']->rut); ?>"
                                       placeholder="12.345.678-9">
                                <div class="invalid-feedback"><?php echo $data['errores']['rut'] ?? ''; ?></div>
                            </div>

                            <div class="col-md-8 mb-3">
                                <label for="nombre_completo" class="form-label">Nombre Completo: <span class="text-danger">*</span></label>
                                <input type="text" class="form-control <?php echo (!empty($data['errores']['nombre_completo'])) ? 'is-invalid' : ''; ?>" 
                                       id="nombre_completo" name="nombre_completo" value="<?php echo htmlspecialchars($data['trabajador']->nombre_completo); ?>"
                                       placeholder="Nombre y Apellidos">
                                <div class="invalid-feedback"><?php echo $data['errores']['nombre_completo'] ?? ''; ?></div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="cargo" class="form-label">Cargo / Función:</label>
                            <input type="text" class="form-control" id="cargo" name="cargo" 
                                   value="<?php echo htmlspecialchars($data['trabajador']->cargo); ?>"
                                   placeholder="Ej: Jefe de Huerto, Operario Riego, Temporero">
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="activo" name="activo" value="1" 
                                       <?php echo ($data['trabajador']->activo) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="activo">Trabajador Activo</label>
                            </div>
                            <small class="text-muted">Desactivar si ya no trabaja en la empresa.</small>
                        </div>

                        <hr>
                        <div class="d-flex justify-content-end">
                            <a href="<?php echo URL_ROOT; ?>/trabajadores" class="btn btn-secondary me-2">
                                <i class="bi bi-x-circle me-2"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-accent-calendula">
                                <i class="bi bi-save me-2"></i> Guardar Trabajador
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>