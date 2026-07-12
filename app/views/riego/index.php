<div class="container-fluid mt-4">
    <form action="<?php echo URL_ROOT; ?>/riego/guardar" method="POST" id="form-riego-masivo">
        
        <!-- BARRA SUPERIOR FIJA (Sticky) -->
        <div class="card shadow-sm mb-4 sticky-top border-0 bg-white" style="z-index: 100; top: 0.5rem;">
            <div class="card-body p-3">
                <div class="row align-items-center g-2">
                    <div class="col-12 col-md-auto">
                        <h5 class="mb-0 text-primary-dark-green"><i class="bi bi-calendar-check me-2"></i>Registro Diario</h5>
                    </div>
                    <div class="col flex-grow-1">
                        <input type="date" class="form-control form-control-lg border-2 border-primary" id="fecha" name="fecha" 
                            value="<?php echo htmlspecialchars($data['fecha_seleccionada']); ?>" required>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-accent-calendula btn-lg shadow-sm">
                            <i class="bi bi-save-fill me-2"></i> <span class="d-none d-sm-inline">Guardar Todo</span>
                        </button>
                    </div>
                </div>
                <div id="loading-indicator" class="progress mt-2" style="height: 3px; display: none;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-warning" role="progressbar" style="width: 100%"></div>
                </div>
            </div>
        </div>
        
        <?php SessionHelper::displayFlash(); ?>

        <!-- GRILLA DE PREDIOS -->
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-3">
            <?php if (empty($data['predios'])): ?>
                <div class="col-12">
                    <div class="alert alert-warning">No hay predios activos registrados en tu cuenta.</div>
                </div>
            <?php else: ?>
                <?php foreach($data['predios'] as $predio): ?>
                <div class="col">
                    <div class="card h-100 shadow-sm border-0 predio-card" id="card-predio-<?php echo $predio->id; ?>">
                        <div class="card-header bg-light border-0 d-flex justify-content-between align-items-center py-2">
                            <small class="fw-bold text-muted text-uppercase" style="font-size: 0.75rem;">Predio</small>
                            <span class="status-icon" id="status-icon-<?php echo $predio->id; ?>"></span>
                        </div>
                        
                        <div class="card-body p-3">
                            <h5 class="card-title mb-3 text-truncate" title="<?php echo htmlspecialchars($predio->nombre); ?>">
                                <?php echo htmlspecialchars($predio->nombre); ?>
                            </h5>
                            
                            <div class="form-floating mb-2">
                                <input type="number" class="form-control form-control-lg tiempo-input" 
                                       id="tiempo_<?php echo $predio->id; ?>" 
                                       name="tiempo_riego[<?php echo $predio->id; ?>]" 
                                       placeholder="0" min="0">
                                <label for="tiempo_<?php echo $predio->id; ?>">Minutos</label>
                            </div>
                            
                            <small class="text-muted d-block mt-2 fst-italic user-info-text" id="user-info-<?php echo $predio->id; ?>" style="font-size: 0.7rem; min-height: 1rem;"></small>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fechaInput = document.getElementById('fecha');
    const loader     = document.getElementById('loading-indicator');
    const predioCards = document.querySelectorAll('.predio-card');

    function cargarDatosDelDia() {
        const fecha = fechaInput.value;
        if(!fecha) return;

        loader.style.display = 'block';
        predioCards.forEach(card => {
            const input     = card.querySelector('.tiempo-input');
            const status    = card.querySelector('.status-icon');
            const info      = card.querySelector('.user-info-text');
            
            input.value = ''; 
            input.disabled = false;
            input.classList.remove('border-warning', 'bg-light');
            card.classList.remove('border-warning');
            status.innerHTML = ''; 
            info.textContent = '';
        });

        fetch(`<?php echo URL_ROOT; ?>/riego/obtenerDatosDia/${fecha}`)
            .then(r => r.json())
            .then(resp => {
                if(!resp.success) return;
                Object.keys(resp.datos).forEach(predioId => {
                    const registro = resp.datos[predioId];
                    const card     = document.getElementById(`card-predio-${predioId}`);
                    if(!card) return;

                    const input     = card.querySelector('.tiempo-input');
                    const status    = card.querySelector('.status-icon');
                    const info      = card.querySelector('.user-info-text');

                    input.value = registro.tiempo_riego;

                    // Si existe el dato, pintamos el cuadro para notar que estamos editando
                    input.classList.add('border-warning');
                    status.innerHTML = '<i class="bi bi-pencil-square text-warning" title="Modo Edición"></i>';
                    info.textContent = `Actualizado por: ${registro.usuario_nombre}`;
                });
            })
            .catch(err => console.error(err))
            .finally(() => { loader.style.display = 'none'; });
    }

    fechaInput.addEventListener('change', cargarDatosDelDia);
    cargarDatosDelDia();
});
</script>
