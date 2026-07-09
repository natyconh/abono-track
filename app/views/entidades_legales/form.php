<div class="container-fluid mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header"><h4 class="mb-0"><?php echo $data['titulo']; ?></h4></div>
                <div class="card-body">
                    <form action="<?php echo URL_ROOT; ?>/entidadLegal/guardar" method="POST">
                        <input type="hidden" name="id" value="<?php echo $data['entidad']->id; ?>">
                        
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label">RUT <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="rut" value="<?php echo htmlspecialchars($data['entidad']->rut); ?>" required placeholder="11.222.333-K">
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">Razón Social <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="razon_social" value="<?php echo htmlspecialchars($data['entidad']->razon_social); ?>" required>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Nombre Fantasía (Opcional)</label>
                                <input type="text" class="form-control" name="nombre_fantasia" value="<?php echo htmlspecialchars($data['entidad']->nombre_fantasia); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-primary">Código SAG (CSG)</label>
                                <input type="text" class="form-control border-primary" name="codigo_sag" value="<?php echo htmlspecialchars($data['entidad']->codigo_sag); ?>" placeholder="Importante para exportación">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Dirección Legal</label>
                            <input type="text" class="form-control" name="direccion" value="<?php echo htmlspecialchars($data['entidad']->direccion); ?>">
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="<?php echo URL_ROOT; ?>/entidadLegal" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-accent-calendula">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>