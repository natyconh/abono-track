<!-- app/views/users/form.php -->
<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1><?php echo $titulo; ?></h1>
        <a href="<?php echo URL_ROOT; ?>/users/admin" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>

    <?php 
        // Muestra errores de validación
        if (!empty($errors)) {
            echo '<div class="alert alert-danger">';
            foreach ($errors as $error) {
                echo '<div>' . htmlspecialchars($error) . '</div>';
            }
            echo '</div>';
        }
    ?>

    <div class="card shadow-sm rounded-lg">
        <div class="card-body">
            <form action="<?php echo URL_ROOT; ?>/users/<?php echo ($action == 'edit' ? 'edit' : 'add'); ?>" method="POST">
                
                <?php if ($action == 'edit'): ?>
                    <input type="hidden" name="id" value="<?php echo $user->id; ?>">
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="username" class="form-label">Nombre de Usuario (*)</label>
                        <input type="text" class="form-control" id="username" name="username" 
                               value="<?php echo htmlspecialchars($user->username ?? ''); ?>" required>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="whatsapp" class="form-label">
                            <i class="bi bi-whatsapp text-success"></i> WhatsApp Vinculado
                        </label>
                        <input type="text" class="form-control" id="whatsapp" name="whatsapp" 
                               value="<?php echo htmlspecialchars($whatsapp_link->numero_whatsapp ?? $user->whatsapp ?? ''); ?>" 
                               placeholder="Ej: +56912345678">
                        <small class="text-muted">Usado para notificaciones y chatbot.</small>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label">Contraseña</label>
                        <input type="password" class="form-control" id="password" name="password" <?php echo ($action == 'add') ? 'required' : ''; ?>>
                        <?php if ($action != 'add'): ?>
                            <small class="text-muted">Dejar en blanco para no cambiar la contraseña.</small>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="confirm_password" class="form-label">Confirmar Contraseña</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                               <?php echo ($action == 'add') ? 'required' : ''; ?>>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="trabajador_id" class="form-label">Trabajador Asignado (*)</label>
                        <select class="form-select" id="trabajador_id" name="trabajador_id" required>
                            <option value="">Seleccione...</option>
                            
                            <!-- Si editamos, añadimos al trabajador actual a la lista aunque tenga usuario -->
                            <?php if (isset($user->trabajador_id) && !empty($user->trabajador_id)): ?>
                                <option value="<?php echo $user->trabajador_id; ?>" selected>
                                    (Actual) <?php echo htmlspecialchars($user->nombre_trabajador ?? 'Trabajador Actual'); ?>
                                </option>
                            <?php endif; ?>

                            <?php foreach ($trabajadores as $trabajador): ?>
                                <option value="<?php echo $trabajador->id; ?>" <?php echo ($trabajador->id == ($user->trabajador_id ?? null)) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($trabajador->nombre_completo); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="rol_id" class="form-label">Rol de Sistema (*)</label>
                        <select class="form-select" id="rol_id" name="rol_id" required>
                            <option value="">Seleccione...</option>
                            <?php foreach ($roles as $rol): ?>
                                <option value="<?php echo $rol->id; ?>" <?php echo ($rol->id == ($user->rol_id ?? null)) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($rol->nombre); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-12 mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="activo" name="activo" value="1" <?php echo (($user->activo ?? 0) == 1) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="activo">Usuario Activo (Permitir Acceso)</label>
                        </div>
                    </div>
                </div>
                
                <hr>

                <div class="d-flex justify-content-end">
                    <a href="<?php echo URL_ROOT; ?>/users/admin" class="btn btn-secondary me-2">Cancelar</a>
                    <button type="submit" class="btn btn-accent-calendula">Guardar Usuario</button>
                </div>
                
            </form>
        </div>
    </div>
</div>