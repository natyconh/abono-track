<style>
.micro-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
    min-width: 220px;
}
.micro-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 52px;
}
.micro-item label {
    font-size: 0.68rem;
    font-weight: 600;
    color: #555;
    margin-bottom: 1px;
    text-transform: uppercase;
    letter-spacing: 0.03em;
}
.micro-item input {
    width: 52px;
    text-align: center;
    font-size: 0.8rem;
    padding: 2px 4px;
    border: 1px solid #ced4da;
    border-radius: 4px;
    background: #fff;
}
.micro-item input:focus {
    outline: none;
    border-color: #1a6b3c;
    box-shadow: 0 0 0 2px rgba(26,107,60,0.15);
}
.btn-micro-mas {
    font-size: 0.7rem;
    padding: 1px 6px;
    border: 1px dashed #aaa;
    border-radius: 4px;
    background: transparent;
    color: #666;
    cursor: pointer;
    align-self: flex-end;
    margin-top: 14px;
    white-space: nowrap;
}
.btn-micro-mas:hover { background: #f0f0f0; }
.micro-extra { display: none; }
.micro-extra.visible { display: flex; flex-wrap: wrap; gap: 4px; }
</style>

<div class="d-flex align-items-center mb-4">
    <h1 class="h3 fw-bold mb-0" style="color:#1a6b3c;">Nuevo Programa de Fertilización</h1>
</div>

<form method="POST" action="<?php echo URL_ROOT; ?>/programa/store" id="formPrograma">

    <!-- Cabecera del programa -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-semibold border-bottom">
            <i class="bi bi-info-circle text-success"></i> Datos Generales
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Predio <span class="text-danger">*</span></label>
                    <select name="predio_id" class="form-select" required>
                        <option value="">— Seleccionar predio —</option>
                        <?php foreach ($data['predios'] as $p): ?>
                        <option value="<?php echo $p->id; ?>"><?php echo htmlspecialchars($p->nombre); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Cultivo</label>
                    <select name="cultivo_id" class="form-select">
                        <option value="">— Opcional —</option>
                        <?php foreach ($data['cultivos'] as $c): ?>
                        <option value="<?php echo $c->id; ?>"><?php echo htmlspecialchars($c->nombre); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Temporada <span class="text-danger">*</span></label>
                    <input type="text" name="temporada" class="form-control"
                           value="<?php echo $data['temporada_default']; ?>"
                           placeholder="ej: 2026" required maxlength="20">
                    <div class="form-text">Año de inicio de temporada</div>
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
                    <tbody id="semanasTbody">
                        <!-- Filas generadas por JS -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-success" id="btnGuardar">
            <i class="bi bi-check2-circle"></i> Guardar Programa
        </button>
        <a href="<?php echo URL_ROOT; ?>/programa" class="btn btn-outline-secondary">
            <i class="bi bi-x-circle"></i> Cancelar
        </a>
    </div>
</form>

<script>
const tbody = document.getElementById('semanasTbody');
let contadorSemanas = 0;

const MICRO_PRINCIPALES = ['Ca', 'Mg', 'Fe'];
const MICRO_EXTRAS = ['Zn', 'Mn', 'B', 'Cu', 'Mo'];
const MICRO_TODOS = [...MICRO_PRINCIPALES, ...MICRO_EXTRAS];

function buildMicroJson(tr) {
    const obj = {};
    MICRO_TODOS.forEach(key => {
        const input = tr.querySelector(`.micro-input[data-key="${key}"]`);
        if (!input) return;
        const val = parseFloat(input.value);
        if (!isNaN(val) && val > 0) obj[key] = val;
    });
    return Object.keys(obj).length > 0 ? JSON.stringify(obj) : '';
}

function syncMicroHidden(tr) {
    const hidden = tr.querySelector('.micro-hidden');
    if (hidden) hidden.value = buildMicroJson(tr);
}

function microCeldaHTML() {
    let principalesHTML = MICRO_PRINCIPALES.map(k => `
        <div class="micro-item">
            <label>${k}</label>
            <input type="number" class="micro-input" data-key="${k}"
                   value="" min="0" step="0.01" placeholder="0">
        </div>`).join('');

    let extrasHTML = MICRO_EXTRAS.map(k => `
        <div class="micro-item">
            <label>${k}</label>
            <input type="number" class="micro-input" data-key="${k}"
                   value="" min="0" step="0.01" placeholder="0">
        </div>`).join('');

    return `
        <div class="micro-grid">
            ${principalesHTML}
            <button type="button" class="btn-micro-mas" title="Ver más micronutrientes">
                + más
            </button>
            <div class="micro-extra d-flex flex-wrap gap-1 w-100">
                ${extrasHTML}
            </div>
        </div>
        <input type="hidden" name="micronutrientes_objetivo[]" class="micro-hidden" value="">`;
}

function agregarFilaSemana(num) {
    contadorSemanas++;
    const n = num || contadorSemanas;

    const hoy = new Date();
    const lunes = new Date(hoy);
    lunes.setDate(hoy.getDate() - hoy.getDay() + 1 + (n - 1) * 7);
    const fechaISO = lunes.toISOString().split('T')[0];

    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td><input type="number" name="semana[]" class="form-control form-control-sm"
                value="${n}" min="1" max="52" required style="width:65px"></td>
        <td><input type="date" name="fecha_estimada[]" class="form-control form-control-sm"
                value="${fechaISO}" required></td>
        <td><input type="number" name="n_objetivo[]" class="form-control form-control-sm text-end"
                value="0" min="0" step="0.01"></td>
        <td><input type="number" name="p_objetivo[]" class="form-control form-control-sm text-end"
                value="0" min="0" step="0.01"></td>
        <td><input type="number" name="k_objetivo[]" class="form-control form-control-sm text-end"
                value="0" min="0" step="0.01"></td>
        <td>${microCeldaHTML()}</td>
        <td><input type="text" name="observaciones[]" class="form-control form-control-sm"
                placeholder="Opcional"></td>
        <td>
            <button type="button" class="btn btn-sm btn-outline-danger btnEliminarFila"
                    title="Eliminar semana">
                <i class="bi bi-x"></i>
            </button>
        </td>`;

    tbody.appendChild(tr);

    tr.querySelectorAll('.micro-input').forEach(inp => {
        inp.addEventListener('input', () => syncMicroHidden(tr));
    });

    const btnMas = tr.querySelector('.btn-micro-mas');
    const extras = tr.querySelector('.micro-extra');
    btnMas.addEventListener('click', () => {
        const abierto = extras.classList.toggle('visible');
        btnMas.textContent = abierto ? '− menos' : '+ más';
    });

    tr.querySelector('.btnEliminarFila').addEventListener('click', () => tr.remove());
}

for (let i = 1; i <= 4; i++) agregarFilaSemana(i);

document.getElementById('btnAgregarSemana').addEventListener('click', () => {
    const totalFilas = tbody.querySelectorAll('tr').length;
    agregarFilaSemana(totalFilas + 1);
});

document.getElementById('formPrograma').addEventListener('submit', () => {
    tbody.querySelectorAll('tr').forEach(tr => syncMicroHidden(tr));
});
</script>
