<?php
// Breadcrumb y sidebar los maneja header.php

// Serializar las semanas existentes a JSON para el JS
$semanasJS = [];
foreach ($data['semanas'] as $s) {
    $micros = [];
    if (!empty($s->micronutrientes_objetivo)) {
        $dec = json_decode($s->micronutrientes_objetivo, true);
        if (is_array($dec)) $micros = $dec;
    }
    $semanasJS[] = [
        'semana'         => (int)$s->semana,
        'fecha_estimada' => $s->fecha_estimada,
        'n'              => (float)$s->n_objetivo,
        'p'              => (float)$s->p_objetivo,
        'k'              => (float)$s->k_objetivo,
        'micros'         => $micros,
        'obs'            => $s->observaciones ?? '',
    ];
}
?>

<style>
.micro-grid { display:flex; flex-wrap:wrap; gap:4px; min-width:220px; }
.micro-item { display:flex; flex-direction:column; align-items:center; width:52px; }
.micro-item label { font-size:.68rem; font-weight:600; color:#555; margin-bottom:1px; text-transform:uppercase; letter-spacing:.03em; }
.micro-item input { width:52px; text-align:center; font-size:.8rem; padding:2px 4px; border:1px solid #ced4da; border-radius:4px; background:#fff; }
.micro-item input:focus { outline:none; border-color:#1a6b3c; box-shadow:0 0 0 2px rgba(26,107,60,.15); }
.btn-micro-mas { font-size:.7rem; padding:1px 6px; border:1px dashed #aaa; border-radius:4px; background:transparent; color:#666; cursor:pointer; align-self:flex-end; margin-top:14px; white-space:nowrap; }
.btn-micro-mas:hover { background:#f0f0f0; }
.micro-extra { display:none; }
.micro-extra.visible { display:flex; flex-wrap:wrap; gap:4px; }
</style>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 fw-bold mb-0" style="color:#1a6b3c;">Editar Programa de Fertilización</h1>
        <p class="text-muted small mb-0">Modificar semanas del programa existente</p>
    </div>
    <span class="badge fs-6" style="background:#e8f5e9;color:#1a6b3c;">
        Temporada <?php echo htmlspecialchars($data['temporada']); ?>
    </span>
</div>

<form method="POST" action="<?php echo URL_ROOT; ?>/programa/update" id="formPrograma">
    <input type="hidden" name="predio_id" value="<?php echo $data['predio_id']; ?>">
    <input type="hidden" name="temporada" value="<?php echo htmlspecialchars($data['temporada']); ?>">

    <!-- Datos generales (solo cultivo editable; predio+temporada fijos) -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-semibold border-bottom">
            <i class="bi bi-info-circle text-success"></i> Datos Generales
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Predio</label>
                    <select class="form-select" disabled>
                        <?php foreach ($data['predios'] as $p): ?>
                        <option value="<?php echo $p->id; ?>" <?php echo ((int)$p->id === (int)$data['predio_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($p->nombre); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">No se puede cambiar desde aquí</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Cultivo</label>
                    <select name="cultivo_id" class="form-select">
                        <option value="">— Sin cultivo —</option>
                        <?php foreach ($data['cultivos'] as $c): ?>
                        <option value="<?php echo $c->id; ?>" <?php echo ((int)$c->id === (int)$data['cultivo_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($c->nombre); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Temporada</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($data['temporada']); ?>" disabled>
                    <div class="form-text">No se puede cambiar desde aquí</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de semanas -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white d-flex align-items-center justify-content-between">
            <span class="fw-semibold"><i class="bi bi-table text-success"></i> Semanas del Programa</span>
            <button type="button" class="btn btn-sm btn-outline-success" id="btnAgregarSemana">
                <i class="bi bi-plus-circle"></i> Agregar semana
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0" id="tablaPrograma">
                    <thead class="table-light">
                        <tr>
                            <th style="width:60px">#Sem.</th>
                            <th>Fecha Estimada</th>
                            <th class="text-end">N (kg/ha)</th>
                            <th class="text-end">P (kg/ha)</th>
                            <th class="text-end">K (kg/ha)</th>
                            <th>Micronutrientes (kg/ha)</th>
                            <th>Observaciones</th>
                            <th style="width:50px"></th>
                        </tr>
                    </thead>
                    <tbody id="semanasTbody"></tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-success" id="btnGuardar">
            <i class="bi bi-check2-circle"></i> Guardar Cambios
        </button>
        <a href="<?php echo URL_ROOT; ?>/programa" class="btn btn-outline-secondary">
            <i class="bi bi-x-circle"></i> Cancelar
        </a>
    </div>
</form>

<script>
const tbody       = document.getElementById('semanasTbody');
const MICRO_PRINC = ['Ca','Mg','Fe'];
const MICRO_EXT   = ['Zn','Mn','B','Cu','Mo'];
const MICRO_TODOS = [...MICRO_PRINC, ...MICRO_EXT];

// Semanas existentes inyectadas desde PHP
const semanasIniciales = <?php echo json_encode($semanasJS, JSON_UNESCAPED_UNICODE); ?>;

function buildMicroJson(tr) {
    const obj = {};
    MICRO_TODOS.forEach(k => {
        const inp = tr.querySelector(`.micro-input[data-key="${k}"]`);
        if (!inp) return;
        const v = parseFloat(inp.value);
        if (!isNaN(v) && v > 0) obj[k] = v;
    });
    return Object.keys(obj).length ? JSON.stringify(obj) : '';
}
function syncHidden(tr) {
    const h = tr.querySelector('.micro-hidden');
    if (h) h.value = buildMicroJson(tr);
}

function microCeldaHTML(micros = {}) {
    const princHTML = MICRO_PRINC.map(k => `
        <div class="micro-item">
            <label>${k}</label>
            <input type="number" class="micro-input" data-key="${k}"
                   value="${micros[k] || ''}" min="0" step="0.01" placeholder="0">
        </div>`).join('');
    const extHTML = MICRO_EXT.map(k => `
        <div class="micro-item">
            <label>${k}</label>
            <input type="number" class="micro-input" data-key="${k}"
                   value="${micros[k] || ''}" min="0" step="0.01" placeholder="0">
        </div>`).join('');
    const tieneExtras = MICRO_EXT.some(k => micros[k] > 0);
    return `
        <div class="micro-grid">
            ${princHTML}
            <button type="button" class="btn-micro-mas">${tieneExtras ? '&minus; menos' : '+ más'}</button>
            <div class="micro-extra d-flex flex-wrap gap-1 w-100${tieneExtras ? ' visible' : ''}">
                ${extHTML}
            </div>
        </div>
        <input type="hidden" name="micronutrientes_objetivo[]" class="micro-hidden" value="">`;
}

function agregarFila(datos = {}) {
    const n = datos.semana ?? (tbody.querySelectorAll('tr').length + 1);
    const fecha = datos.fecha_estimada ?? new Date().toISOString().split('T')[0];

    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td><input type="number" name="semana[]" class="form-control form-control-sm"
                value="${n}" min="1" max="52" required style="width:65px"></td>
        <td><input type="date" name="fecha_estimada[]" class="form-control form-control-sm"
                value="${fecha}" required></td>
        <td><input type="number" name="n_objetivo[]" class="form-control form-control-sm text-end"
                value="${datos.n ?? 0}" min="0" step="0.01"></td>
        <td><input type="number" name="p_objetivo[]" class="form-control form-control-sm text-end"
                value="${datos.p ?? 0}" min="0" step="0.01"></td>
        <td><input type="number" name="k_objetivo[]" class="form-control form-control-sm text-end"
                value="${datos.k ?? 0}" min="0" step="0.01"></td>
        <td>${microCeldaHTML(datos.micros ?? {})}</td>
        <td><input type="text" name="observaciones[]" class="form-control form-control-sm"
                value="${(datos.obs ?? '').replace(/"/g, '&quot;')}" placeholder="Opcional"></td>
        <td>
            <button type="button" class="btn btn-sm btn-outline-danger btnEliminar" title="Eliminar">
                <i class="bi bi-x"></i>
            </button>
        </td>`;

    tbody.appendChild(tr);

    tr.querySelectorAll('.micro-input').forEach(inp => inp.addEventListener('input', () => syncHidden(tr)));
    syncHidden(tr); // sincronizar valores iniciales

    const btnMas = tr.querySelector('.btn-micro-mas');
    const extras = tr.querySelector('.micro-extra');
    btnMas.addEventListener('click', () => {
        const abierto = extras.classList.toggle('visible');
        btnMas.innerHTML = abierto ? '&minus; menos' : '+ más';
    });
    tr.querySelector('.btnEliminar').addEventListener('click', () => tr.remove());
}

// Cargar filas existentes
semanasIniciales.forEach(s => agregarFila(s));

document.getElementById('btnAgregarSemana').addEventListener('click', () => agregarFila());

document.getElementById('formPrograma').addEventListener('submit', () => {
    tbody.querySelectorAll('tr').forEach(tr => syncHidden(tr));
});
</script>
