<?php
// app/views/predios/form.php
// Formulario de creación/edición de predios y cabezales
?>
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-10 mx-auto">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <a href="<?php echo URL_ROOT; ?>/predios" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Volver al Listado
                </a>
            </div>

            <?php if (!empty($data['errores'])): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach($data['errores'] as $key => $error): ?>
                            <?php if ($key !== 'umbrales'): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

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
                                <input type="text" class="form-control <?php echo !empty($data['errores']['nombre']) ? 'is-invalid' : ''; ?>"
                                       id="nombre" name="nombre"
                                       value="<?php echo htmlspecialchars($data['predio']->nombre); ?>" required>
                                <?php if (!empty($data['errores']['nombre'])): ?>
                                    <div class="invalid-feedback"><?php echo htmlspecialchars($data['errores']['nombre']); ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-6">
                                <label for="tipo_superficie" class="form-label">Tipo de Superficie</label>
                                <select class="form-select" id="tipo_superficie" name="tipo_superficie">
                                    <option value="cultivo" <?php echo ($data['predio']->tipo_superficie == 'cultivo') ? 'selected' : ''; ?>>
                                        Cultivo Agrícola
                                    </option>
                                    <option value="cabezal_virtual" <?php echo ($data['predio']->tipo_superficie == 'cabezal_virtual') ? 'selected' : ''; ?>>
                                        Cabezal de Riego
                                    </option>
                                    <option value="infraestructura" <?php echo ($data['predio']->tipo_superficie == 'infraestructura') ? 'selected' : ''; ?>>
                                        Infraestructura / Otro
                                    </option>
                                </select>
                                <div id="hint-cabezal" class="form-text text-primary d-none">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Punto de inyección del sistema de riego. Aparece como opción al registrar fertirrigación.
                                </div>
                                <div id="hint-cultivo" class="form-text text-muted">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Sector productivo con cultivo asignado. Participa en reportes nutricionales.
                                </div>
                                <div id="hint-infra" class="form-text text-muted d-none">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Superficie no productiva (caminos, instalaciones, etc.).
                                </div>
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
                                       value="<?php echo htmlspecialchars($data['predio']->año_plantacion ?? date('Y')); ?>"
                                       min="1900" max="<?php echo date('Y'); ?>">
                            </div>
                        </div>

                        <!-- SECCIÓN 2: PARÁMETROS DE RIEGO -->
                        <h6 class="text-uppercase text-muted fw-bold mt-4 mb-3 border-bottom pb-2">Parámetros de Riego</h6>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="superficie_total" class="form-label">Superficie Total (Ha)</label>
                                <input type="number" step="0.01" class="form-control" id="superficie_total"
                                       name="superficie_total"
                                       value="<?php echo htmlspecialchars($data['predio']->superficie_total ?? ''); ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="tipo_emisor" class="form-label">Tipo de Emisor</label>
                                <input type="text" class="form-control" id="tipo_emisor" name="tipo_emisor"
                                       value="<?php echo htmlspecialchars($data['predio']->tipo_emisor ?? ''); ?>"
                                       placeholder="Gotero, microaspersor…">
                            </div>
                            <div class="col-md-4">
                                <label for="caudal_lt_hora" class="form-label">Caudal por Emisor (L/h)</label>
                                <input type="number" step="0.1" class="form-control" id="caudal_lt_hora"
                                       name="caudal_lt_hora"
                                       value="<?php echo htmlspecialchars($data['predio']->caudal_lt_hora ?? ''); ?>">
                            </div>
                        </div>

                        <div class="row g-3 mt-1">
                            <div class="col-md-6">
                                <label for="plantas_por_hectarea" class="form-label">Densidad (Pl/Ha)</label>
                                <input type="number" class="form-control" id="plantas_por_hectarea"
                                       name="plantas_por_hectarea"
                                       value="<?php echo htmlspecialchars($data['predio']->plantas_por_hectarea ?? ''); ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="cantidad_plantas" class="form-label">Total de Plantas</label>
                                <input type="number" class="form-control" id="cantidad_plantas" name="cantidad_plantas"
                                       value="<?php echo htmlspecialchars($data['predio']->cantidad_plantas ?? ''); ?>"
                                       placeholder="Opcional">
                                <div class="form-text">Se calcula automáticamente desde Sup × Densidad si se deja vacío.</div>
                            </div>
                        </div>

                        <!-- SECCIÓN 3: UMBRALES DE RIEGO -->
                        <h6 class="text-uppercase text-muted fw-bold mt-4 mb-3 border-bottom pb-2">Umbrales de Riego (%)</h6>

                        <?php if (!empty($data['errores']['umbrales'])): ?>
                            <div class="alert alert-warning py-2">
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                <?php echo htmlspecialchars($data['errores']['umbrales']); ?>
                            </div>
                        <?php endif; ?>

                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="umbral_bajo" class="form-label">
                                    <span class="badge bg-danger me-1">Crítico</span>
                                </label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="umbral_bajo" name="umbral_bajo"
                                           value="<?php echo (int)($data['predio']->umbral_bajo ?? 75); ?>" min="0" max="200">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label for="umbral_optimo_min" class="form-label">
                                    <span class="badge bg-success me-1">Óptimo</span> Mín
                                </label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="umbral_optimo_min" name="umbral_optimo_min"
                                           value="<?php echo (int)($data['predio']->umbral_optimo_min ?? 90); ?>" min="0" max="200">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label for="umbral_optimo_max" class="form-label">
                                    <span class="badge bg-success me-1">Óptimo</span> Máx
                                </label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="umbral_optimo_max" name="umbral_optimo_max"
                                           value="<?php echo (int)($data['predio']->umbral_optimo_max ?? 110); ?>" min="0" max="200">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label for="umbral_exceso" class="form-label">
                                    <span class="badge bg-warning text-dark me-1">Exceso</span>
                                </label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="umbral_exceso" name="umbral_exceso"
                                           value="<?php echo (int)($data['predio']->umbral_exceso ?? 130); ?>" min="0" max="300">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 d-flex gap-2">
                            <button type="submit" class="btn btn-accent-calendula">
                                <i class="bi bi-floppy-fill me-1"></i> Guardar
                            </button>
                            <a href="<?php echo URL_ROOT; ?>/predios" class="btn btn-outline-secondary">Cancelar</a>
                        </div>

                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
(function() {
    const sel      = document.getElementById('tipo_superficie');
    const hints    = {
        cultivo:          document.getElementById('hint-cultivo'),
        cabezal_virtual:  document.getElementById('hint-cabezal'),
        infraestructura:  document.getElementById('hint-infra'),
    };

    function actualizarHint() {
        Object.entries(hints).forEach(function([key, el]) {
            el.classList.toggle('d-none', sel.value !== key);
        });
    }

    sel.addEventListener('change', actualizarHint);
    actualizarHint();
})();
</script>
