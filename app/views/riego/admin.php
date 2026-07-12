<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-6 mx-auto">
            
            <div class="d-flex justify-content-between align-items-center mb-3">
                <a href="<?php echo URL_ROOT; ?>/riego/admin" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Volver al Historial
                </a>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom-0 pt-4 px-4">
                    <h4 class="mb-0 fw-bold text-primary-dark-green"><?php echo $data['titulo']; ?></h4>
                </div>
                <div class="card-body px-4 pb-4">
                    <form action="<?php echo URL_ROOT; ?>/riego/guardarAdmin" method="POST">
                        <input type="hidden" name="id" value="<?php echo $data['riego']->id; ?>">

                        <div class="mb-3">
                            <label for="fecha" class="form-label fw-bold">Fecha del Riego</label>
                            <input type="date" class="form-control" id="fecha" name="fecha" 
                                   value="<?php echo htmlspecialchars($data['riego']->fecha); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="predio_id" class="form-label fw-bold">Predio</label>
                            <select class="form-select" id="predio_id" name="predio_id" required>
                                <option value="">-- Seleccione un Predio --</option>
                                <?php foreach($data['predios'] as $predio): ?>
                                    <option value="<?php echo $predio->id; ?>" <?php echo ($data['riego']->predio_id == $predio->id) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($predio->nombre); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="tiempo_riego" class="form-label fw-bold">Tiempo de Riego (Minutos)</label>
                            <input type="number" class="form-control form-control-lg" id="tiempo_riego" name="tiempo_riego" 
                                   value="<?php echo htmlspecialchars($data['riego']->tiempo_riego); ?>" min="1" required>
                        </div>

                        <hr class="mt-4">
                        <div class="d-grid">
                            <button type="submit" class="btn btn-accent-calendula btn-lg">
                                <i class="bi bi-save me-2"></i> Guardar Registro
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>