<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h4 class="mb-0"><?php echo $data['titulo']; ?></h4>
                </div>
                <div class="card-body">
                    <form action="<?php echo URL_ROOT; ?>/instalaciones/guardar" method="POST">
                        <input type="hidden" name="id" value="<?php echo $data['instalacion']->id; ?>">

                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre Instalación: <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?php echo (!empty($data['errores']['nombre'])) ? 'is-invalid' : ''; ?>" 
                                   id="nombre" name="nombre" value="<?php echo htmlspecialchars($data['instalacion']->nombre); ?>">
                            <div class="invalid-feedback"><?php echo $data['errores']['nombre'] ?? ''; ?></div>
                        </div>

                        <div class="mb-3">
                            <label for="predio_id" class="form-label">Predio: <span class="text-danger">*</span></label>
                            <select class="form-select <?php echo (!empty($data['errores']['predio_id'])) ? 'is-invalid' : ''; ?>" 
                                    id="predio_id" name="predio_id">
                                <option value="">-- Seleccione un predio --</option>
                                <?php foreach($data['predios'] as $predio): ?>
                                    <option value="<?php echo $predio->id; ?>" <?php echo ($data['instalacion']->predio_id == $predio->id) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($predio->nombre); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback"><?php echo $data['errores']['predio_id'] ?? ''; ?></div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="sector_id" class="form-label">Sector (Opcional):</label>
                            <select class="form-select" id="sector_id" name="sector_id">
                                <option value="">-- Seleccione un sector (opcional) --</option>
                                <?php foreach($data['sectores'] as $sector): ?>
                                    <option value="<?php echo $sector->id; ?>" <?php echo ($data['instalacion']->sector_id == $sector->id) ? 'selected' : ''; ?>>
                                        (<?php echo htmlspecialchars($sector->nombre_predio); ?>) <?php echo htmlspecialchars($sector->nombre); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="latitud" class="form-label">Latitud (Opcional):</label>
                                <input type="text" class="form-control <?php echo (!empty($data['errores']['latitud'])) ? 'is-invalid' : ''; ?>" 
                                       id="latitud" name="latitud" value="<?php echo htmlspecialchars($data['instalacion']->latitud); ?>" placeholder="Ej: -33.123456">
                                <div class="invalid-feedback"><?php echo $data['errores']['latitud'] ?? ''; ?></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="longitud" class="form-label">Longitud (Opcional):</label>
                                <input type="text" class="form-control <?php echo (!empty($data['errores']['longitud'])) ? 'is-invalid' : ''; ?>" 
                                       id="longitud" name="longitud" value="<?php echo htmlspecialchars($data['instalacion']->longitud); ?>" placeholder="Ej: -71.123456">
                                <div class="invalid-feedback"><?php echo $data['errores']['longitud'] ?? ''; ?></div>
                            </div>
                        </div>
                        
                        <!-- ¡NUEVO! HTML del mapa copiado de puntos/form.php -->
                        <div class="mb-3">
                           <label class="form-label">...o seleccione un punto en el mapa:</label>
                           <div id="map-form-selector" style="height: 300px; width: 100%; border: 1px solid #ccc; border-radius: 0.5rem;">
                               <div class="text-muted p-3">Cargando mapa...</div>
                           </div>
                           <small class="text-muted">Haga clic en el mapa para establecer las coordenadas. (Si las ingresa manualmente, el mapa se centrará al perder el foco).</small>
                       </div>
                       <!-- Fin del HTML del mapa -->

                        <hr>                        
                        <div class="d-flex justify-content-between">
                            <a href="<?php echo URL_ROOT; ?>/instalaciones/index" class="btn btn-secondary">
                                <i class="bi bi-arrow-left me-2"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-accent-calendula"> 
                                <i class="bi bi-save me-2"></i> Guardar Instalación
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
