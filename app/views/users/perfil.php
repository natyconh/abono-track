<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-8 mx-auto">
            
            <?php SessionHelper::displayFlash(); ?>

            <div class="card shadow-sm">
                <div class="card-header">
                    <h4 class="mb-0"><?php echo $data['titulo']; ?> (<?php echo htmlspecialchars(SessionHelper::getUserName()); ?>)</h4>
                </div>
                <div class="card-body">
                    
                    <form action="<?php echo URL_ROOT; ?>/users/guardarPerfil" method="POST">
                        <div class="mb-3">
                            <label for="numero_whatsapp" class="form-label">Número de WhatsApp Vinculado</label>
                            <input type="text" 
                                   class="form-control <?php echo (!empty($data['errores']['numero'])) ? 'is-invalid' : ''; ?>" 
                                   id="numero_whatsapp" name="numero_whatsapp" 
                                   value="<?php echo htmlspecialchars($data['vinculo']->numero_whatsapp ?? ''); ?>"
                                   placeholder="Ej: +56912345678">
                            
                            <?php if ($data['vinculo'] && $data['vinculo']->estado == 'verificado'): ?>
                                <small class="text-success">
                                    <i class="bi bi-check-circle-fill"></i> 
                                    Número verificado el <?php echo date('d-m-Y', strtotime($data['vinculo']->fecha_verificacion)); ?>.
                                </small>
                            <?php else: ?>
                                <small class="text-muted">
                                    Ingrese su número (incluyendo código de país) para habilitar el chatbot.
                                </small>
                            <?php endif; ?>
                            <div class="invalid-feedback"><?php echo $data['errores']['numero'] ?? ''; ?></div>
                        </div>
                        
                        <hr>
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-accent-calendula">
                                <i class="bi bi-save me-2"></i> Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>