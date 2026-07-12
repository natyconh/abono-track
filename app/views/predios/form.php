<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-10 mx-auto">
            
            <div class="d-flex justify-content-between align-items-center mb-3">
                <a href="<?php echo URL_ROOT; ?>/predios" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Volver al Listado
                </a>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom-0 pt-4 px-4">
                    <h4 class="mb-0 fw-bold text-primary-dark-green"><?php echo $data['titulo']; ?></h4>
                </div>
                <div class="card-body px-4 pb-4">
                    <form action="<?php echo URL_ROOT; ?>/predios/guardar" method="POST">
                        
                        <input type="hidden" name="id" value="<?php echo $data['predio']->id; ?>">

                        <!-- SECCIÓN 1: DATOS GENERALES -->
                        <h6 class="text-uppercase text-muted fw-bold mb-3 border-bottom pb-2">Información General</h6>
                        
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="nombre" class="form-label">Nombre del Predio <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nombre" name="nombre" 
                                       value="<?php echo htmlspecialchars($data['predio']->nombre); ?>" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="tipo_superficie" class="form-label">Tipo de Superficie</label>
                                <select class="form-select" id="tipo_superficie" name="tipo_superficie">
                                    <option value="cultivo" <?php echo ($data['predio']->tipo_superficie == 'cultivo') ? 'selected' : ''; ?>>
                                        🌱 Cultivo Agrícola
                                    </option>
                                    <option value="cabezal_virtual" <?php echo ($data['predio']->tipo_superficie == 'cabezal_virtual') ? 'selected' : ''; ?>>
                                        💧 Cabezal de Riego (Virtual)
                                    </option>
                                    <option value="infraestructura" <?php echo ($data['predio']->tipo_superficie == 'infraestructura') ? 'selected' : ''; ?>>
                                        🏗️ Infraestructura / Otro
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="cultivo_id" class="form-label">Cultivo / Especie</label>
                                <select class="form-select" id="cultivo_id" name="cultivo_id">
                                    <option value="">-- Sin Cultivo Asignado --</option>
                                    <?php foreach($data['cultivos'] as $cultivo): ?>
                                        <option value="<?php echo $cultivo->id; ?>" 
                                            <?php echo ($data['predio']->cultivo_id == $cultivo->id) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($cultivo->nombre . ' ' . $cultivo->variedad); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="año_plantacion" class="form-label">Año de Plantación</label>
                                <input type="number" class="form-control" id="año_plantacion" name="año_plantacion" 
                                       value="<?php echo htmlspecialchars($data['predio']->año_plantacion); ?>" 
                                       placeholder="Ej: 2020">
                            </div>
                        </div>

                        <!-- SECCIÓN 2: DATOS AGRONÓMICOS -->
                        <h6 class="text-uppercase text-muted fw-bold mt-4 mb-3 border-bottom pb-2">Datos Agronómicos (Calculadora)</h6>
                        
                        <div class="row g-3 p-3 bg-light rounded-3 mb-3">
                            <div class="col-md-4">
                                <label for="superficie_total" class="form-label fw-bold text-secondary">Superficie (Ha)</label>
                                <input type="number" step="0.01" class="form-control" 
                                       id="superficie_total" name="superficie_total" 
                                       value="<?php echo htmlspecialchars($data['predio']->superficie_total); ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="plantas_por_hectarea" class="form-label fw-bold text-secondary">Densidad (Pl/Ha)</label>
                                <input type="number" class="form-control" 
                                       id="plantas_por_hectarea" name="plantas_por_hectarea" 
                                       value="<?php echo htmlspecialchars($data['predio']->plantas_por_hectarea); ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="cantidad_plantas" class="form-label fw-bold text-success">Total Plantas</label>
                                <div class="input-group">
                                    <input type="number" class="form-control border-success text-success fw-bold" 
                                           id="cantidad_plantas" name="cantidad_plantas" 
                                           value="<?php echo htmlspecialchars($data['predio']->cantidad_plantas); ?>"
                                           placeholder="Auto-calc">
                                    <button class="btn btn-outline-success" type="button" id="btnCalcular" title="Recalcular">
                                        <i class="bi bi-calculator"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="tipo_emisor" class="form-label">Tipo Emisor</label>
                                <select class="form-select" id="tipo_emisor" name="tipo_emisor">
                                    <option value="">-- Seleccione --</option>
                                    <?php 
                                    $emisores = ['gotero', 'microaspersor', 'cinta', 'pivote', 'surco'];
                                    foreach($emisores as $emi): ?>
                                        <option value="<?php echo $emi; ?>" <?php echo ($data['predio']->tipo_emisor == $emi) ? 'selected' : ''; ?>>
                                            <?php echo ucfirst($emi); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="caudal_lt_hora" class="form-label">Caudal Emisor (L/h por planta)</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" class="form-control" 
                                           id="caudal_lt_hora" name="caudal_lt_hora" 
                                           value="<?php echo htmlspecialchars($data['predio']->caudal_lt_hora); ?>">
                                    <span class="input-group-text bg-light text-muted">L/h</span>
                                </div>
                            </div>
                        </div>

                        <!-- SECCIÓN 3: CONFIGURACIÓN HÍDRICA (UMBRALES) -->
                        <h6 class="text-uppercase text-muted fw-bold mt-4 mb-3 border-bottom pb-2">
                            <i class="bi bi-sliders me-1"></i> Configuración de Alertas Hídricas
                        </h6>
                        <div class="alert alert-light border shadow-sm">
                            <p class="small text-muted mb-3">
                                Defina los porcentajes de reposición (Riego / Evaporación) que dispararán las alertas en el dashboard.
                            </p>
                            
                            <div class="row g-2 align-items-center text-center">
                                <div class="col-md-3">
                                    <label class="form-label text-danger fw-bold small">Déficit Crítico (&lt;)</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-danger text-white"><i class="bi bi-exclamation-octagon"></i></span>
                                        <input type="number" class="form-control text-center fw-bold" name="umbral_bajo" 
                                               value="<?php echo htmlspecialchars($data['predio']->umbral_bajo ?? 75); ?>">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label text-success fw-bold small">Mínimo Óptimo (&ge;)</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-success text-white"><i class="bi bi-check-circle"></i></span>
                                        <input type="number" class="form-control text-center fw-bold border-success" name="umbral_optimo_min" 
                                               value="<?php echo htmlspecialchars($data['predio']->umbral_optimo_min ?? 90); ?>">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label text-success fw-bold small">Máximo Óptimo (&le;)</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-success text-white"><i class="bi bi-check-circle"></i></span>
                                        <input type="number" class="form-control text-center fw-bold border-success" name="umbral_optimo_max" 
                                               value="<?php echo htmlspecialchars($data['predio']->umbral_optimo_max ?? 110); ?>">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label text-primary fw-bold small">Exceso Crítico (&gt;)</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-primary text-white"><i class="bi bi-tsunami"></i></span>
                                        <input type="number" class="form-control text-center fw-bold" name="umbral_exceso" 
                                               value="<?php echo htmlspecialchars($data['predio']->umbral_exceso ?? 130); ?>">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                            </div>
                            <?php if(!empty($data['errores']['umbrales'])): ?>
                                <div class="text-danger small mt-2 fw-bold text-center">
                                    <i class="bi bi-x-circle"></i> <?php echo $data['errores']['umbrales']; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <hr class="mt-4">
                        <div class="d-flex justify-content-end">
                            <a href="<?php echo URL_ROOT; ?>/predios" class="btn btn-secondary me-2">
                                <i class="bi bi-x-circle me-2"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-accent-calendula px-4">
                                <i class="bi bi-save me-2"></i> Guardar Predio
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const supInput = document.getElementById('superficie_total');
    const denInput = document.getElementById('plantas_por_hectarea');
    const totInput = document.getElementById('cantidad_plantas');
    const btnCalc  = document.getElementById('btnCalcular');

    function calcularMetricas() {
        const sup = parseFloat(supInput.value) || 0;
        const den = parseInt(denInput.value)   || 0;
        if (sup > 0 && den > 0) {
            totInput.value = Math.round(sup * den);
        }
    }

    supInput.addEventListener('input', calcularMetricas);
    denInput.addEventListener('input', calcularMetricas);
    if(btnCalc) btnCalc.addEventListener('click', calcularMetricas);
});
</script>
