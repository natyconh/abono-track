<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-8 mx-auto">
            
            <div class="d-flex justify-content-between align-items-center mb-3">
                <a href="<?php echo URL_ROOT; ?>/sectores" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Volver al Listado
                </a>
            </div>

            <div class="card shadow-sm">
                <div class="card-header">
                    <h4 class="mb-0"><?php echo $data['titulo']; ?></h4>
                </div>
                <div class="card-body">
                    <form action="<?php echo URL_ROOT; ?>/sectores/guardar" method="POST">
                        
                        <input type="hidden" name="id" value="<?php echo $data['sector']->id; ?>">

                        <div class="mb-3">
                            <label for="predio_id" class="form-label">Predio Perteneciente: <span class="text-danger">*</span></label>
                            <select class="form-select" id="predio_id" name="predio_id" required>
                                <option value="">-- Seleccione un predio --</option>
                                <?php foreach($data['predios'] as $predio): ?>
                                    <option value="<?php echo $predio->id; ?>" <?php echo ($data['sector']->predio_id == $predio->id) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($predio->nombre); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre del Sector: <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo htmlspecialchars($data['sector']->nombre); ?>" required>
                        </div>

                        <!-- NUEVO CAMPO: PROPIEDAD LEGAL -->
                        <div class="mb-4 bg-light p-3 rounded border">
                            <label for="entidad_legal_id" class="form-label fw-bold text-primary-dark-green">
                                <i class="bi bi-briefcase-fill me-2"></i>Dueño de la Fruta (Razón Social)
                            </label>
                            <select class="form-select" id="entidad_legal_id" name="entidad_legal_id">
                                <option value="">-- Heredar del Predio / Usar Principal --</option>
                                <?php foreach($data['entidades'] as $e): ?>
                                    <option value="<?php echo $e->id; ?>" <?php echo ($data['sector']->entidad_legal_id == $e->id) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($e->razon_social); ?> (RUT: <?php echo $e->rut; ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">
                                Si este sector tiene un dueño legal distinto al resto del campo, selecciónelo aquí. 
                                Esto automatizará el RUT en las guías de despacho de cosecha.
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="unidad" class="form-label">Unidad:</label>
                                <input type="text" class="form-control" id="unidad" name="unidad" value="<?php echo htmlspecialchars($data['sector']->unidad ?? ''); ?>" placeholder="Ej: U-12">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="superficie" class="form-label">Superficie (Ha): <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" class="form-control" id="superficie" name="superficie" value="<?php echo htmlspecialchars($data['sector']->superficie ?? ''); ?>" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="cantidad_plantas" class="form-label">Nº Plantas:</label>
                                <input type="number" class="form-control" id="cantidad_plantas" name="cantidad_plantas" value="<?php echo htmlspecialchars($data['sector']->cantidad_plantas ?? ''); ?>">
                            </div>
                        </div>

                        <hr>
                        <div class="d-flex justify-content-end">
                            <a href="<?php echo URL_ROOT; ?>/sectores" class="btn btn-secondary me-2">Cancelar</a>
                            <button type="submit" class="btn btn-accent-calendula">Guardar Sector</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>