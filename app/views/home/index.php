<?php
// 1. Lógica de Fecha y Saludo (Intacta)
$fecha_hoy = date('Y-m-d');
$formatter = new IntlDateFormatter('es_ES', IntlDateFormatter::LONG, IntlDateFormatter::NONE);
$fecha_texto = $formatter->format(new DateTime($fecha_hoy));

$hora = (int)date('G'); 
$saludo = "Hola";
if ($hora >= 6 && $hora < 12) { $saludo = "Buenos días"; } 
elseif ($hora >= 12 && $hora < 20) { $saludo = "Buenas tardes"; } 
else { $saludo = "Buenas noches"; }
?>

<section class="mb-5 fade-in-section">
    <div class="ryzoma-card border-0 shadow-sm" style="background: linear-gradient(to right, #ffffff, #f9f9f7);"> 
        <div class="card-body p-4 position-relative overflow-hidden">
            <div class="row align-items-center">
                <div class="col-8 col-md-9">
                    <h2 class="mb-1 fw-bold text-dark" style="font-size: clamp(1.2rem, 2vw, 1.8rem);">
                        ¡<?php echo $saludo; ?>, <?php echo htmlspecialchars($data['nombre_bienvenida']); ?>!
                    </h2>
                    <p class="mb-0 text-muted small text-capitalize">
                        <i class="bi bi-calendar-event me-1"></i> <?php echo $fecha_texto; ?>
                    </p>
                </div>
                
                <div class="col-4 col-md-3 text-end border-start">
                    <div id="weather-widget" class="d-flex flex-column align-items-end justify-content-center">
                        <i id="weather-icon" class="bi bi-sun-fill text-warning opacity-75" style="font-size: 2rem;"></i>
                        <span id="weather-temp" class="fw-bold text-dark mt-1">--°C</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const lat = -32.88; const lon = -71.25; // La Calera
    const weatherIcon = document.getElementById('weather-icon');
    const weatherTemp = document.getElementById('weather-temp');

    function getIconClass(wmoCode) {
        if (wmoCode === 0) return ['bi-sun-fill', 'text-warning'];
        if (wmoCode >= 1 && wmoCode <= 3) return ['bi-cloud-sun-fill', 'text-warning'];
        if (wmoCode >= 45 && wmoCode <= 48) return ['bi-cloud-haze2-fill', 'text-secondary'];
        if (wmoCode >= 51 && wmoCode <= 67) return ['bi-cloud-drizzle-fill', 'text-info'];
        if (wmoCode >= 71 && wmoCode <= 77) return ['bi-snow', 'text-info'];
        if (wmoCode >= 80 && wmoCode <= 82) return ['bi-cloud-rain-heavy-fill', 'text-primary'];
        if (wmoCode >= 95 && wmoCode <= 99) return ['bi-cloud-lightning-rain-fill', 'text-dark'];
        return ['bi-sun-fill', 'text-warning'];
    }

    fetch(`https://api.open-meteo.com/v1/forecast?latitude=${lat}&longitude=${lon}&current_weather=true`)
        .then(response => response.json())
        .then(data => {
            if (data.current_weather) {
                weatherTemp.textContent = `${data.current_weather.temperature}°C`;
                const [iconClass, colorClass] = getIconClass(data.current_weather.weathercode);
                weatherIcon.className = `bi ${iconClass} ${colorClass}`;
            }
        })
        .catch(e => console.error(e));
});
</script>

<div class="d-flex align-items-center mb-3">
    <h6 class="text-uppercase text-muted fw-bold small mb-0 ls-2">Registro de Operaciones</h6>
    <div class="flex-grow-1 ms-3 border-bottom"></div>
</div>

<div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 mb-5">
    
    <?php if ($data['puedeVerCosecha']): ?>
    <div class="col">
        <a href="<?php echo URL_ROOT; ?>/cosecha/registro" class="text-decoration-none">
            <div class="ryzoma-card p-4 h-100">
                <div class="d-flex align-items-center mb-3">
                    <div class="card-icon-box theme-harvest mb-0 me-3">
                        <i class="bi bi-basket3-fill"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">Registrar Cosecha</h6>
                        <span class="badge bg-warning text-dark bg-opacity-25 py-1 px-2 mt-1" style="font-size: 0.7rem;">Temporada Activa</span>
                    </div>
                </div>
                <p class="small text-muted mb-0">Ingreso de guías de despacho, trazabilidad y kilos cosechados de fruta.</p>
                <div class="card-action-link mt-3 text-warning">Ingresar <i class="bi bi-arrow-right-short"></i></div>
            </div>
        </a>
    </div>
    <?php endif; ?>
    
<?php if ($data['puedeVerRiego']): ?>
    <div class="col">
        <div class="ryzoma-card p-4 h-100 d-flex flex-column">
            
            <div class="d-flex align-items-center mb-3">
                <div class="card-icon-box theme-water mb-0 me-3">
                    <i class="bi bi-droplet-half"></i> </div>
                <div>
                    <h6 class="fw-bold mb-0 text-dark">Riego & Nutrición</h6>
                </div>
            </div>
            
            <p class="small text-muted mb-4">
                Gestión diaria. Registra los tiempos de riego y las aplicaciones de fertilizante por sector.
            </p>

            <div class="mt-auto pt-3 border-top border-light">
                
                <a href="<?php echo URL_ROOT; ?>/riego" class="d-flex align-items-center text-decoration-none mb-3 action-link-hover">
                    <i class="bi bi-arrow-return-right me-2 text-primary"></i>
                    <span class="fw-bold" style="color: var(--vertical-agro);">Registrar Riego</span>
                </a>

                <a href="<?php echo URL_ROOT; ?>/fertilizacion/registro" class="d-flex align-items-center text-decoration-none action-link-hover">
                    <i class="bi bi-flower1 me-2 text-success"></i>
                    <span class="fw-bold text-secondary">Registrar Fertirrigación</span>
                </a>
            </div>

        </div>
    </div>
    
    <style>
        .action-link-hover:hover span { text-decoration: underline; color: var(--vertical-agro) !important; }
        .action-link-hover { transition: transform 0.2s; }
        .action-link-hover:hover { transform: translateX(5px); }
    </style>

    <div class="col">
        <a href="<?php echo URL_ROOT; ?>/clima" class="text-decoration-none">
            <div class="ryzoma-card p-4 h-100">
                <div class="d-flex align-items-center mb-3">
                    <div class="card-icon-box theme-water mb-0 me-3" style="background-color: #e3f2fd; color: #0288d1;">
                        <i class="bi bi-cloud-sun-fill"></i>
                    </div>
                    <h6 class="fw-bold mb-0 text-dark">Bandeja y Clima</h6>
                </div>
                <p class="small text-muted mb-0">Registro diario de evaporación (Bandeja) y precipitaciones.</p>
            </div>
        </a>
    </div>
    <?php endif; ?>

</div>

<div class="d-flex align-items-center mb-3">
    <h6 class="text-uppercase text-muted fw-bold small mb-0 ls-2">Gestión y Control</h6>
    <div class="flex-grow-1 ms-3 border-bottom"></div>
</div>

<div class="row row-cols-1 row-cols-md-3 g-4 mb-5">

    <div class="col">
        <a href="<?php echo URL_ROOT; ?>/reporte" class="text-decoration-none">
            <div class="ryzoma-card p-4 h-100" style="border-left: 4px solid var(--vertical-agro);">
                <div class="d-flex align-items-center mb-3">
                    <div class="card-icon-box mb-0 me-3" style="background-color: var(--vertical-agro); color: white;">
                        <i class="bi bi-bar-chart-line-fill"></i>
                    </div>
                    <h6 class="fw-bold mb-0 text-dark">Central de Reportes</h6>
                </div>
                <p class="small text-muted mb-0">Acceso consolidado a KPIs de eficiencia hídrica, nutrición y cosecha.</p>
                <div class="card-action-link mt-3 text-dark">Ver KPIs <i class="bi bi-arrow-right-short"></i></div>
            </div>
        </a>
    </div>

    <div class="col">
        <a href="<?php echo URL_ROOT; ?>/puntos" class="text-decoration-none">
            <div class="ryzoma-card p-4 h-100">
                <div class="d-flex align-items-center mb-3">
                    <div class="card-icon-box theme-analisis mb-0 me-3">
                        <i class="bi bi-geo-alt-fill"></i>
                    </div>
                    <h6 class="fw-bold mb-0 text-dark">Puntos GIS</h6>
                </div>
                <p class="small text-muted mb-0">Reporte de incidencias geolocalizadas en el mapa.</p>
            </div>
        </a>
    </div>

    <div class="col">
        <a href="<?php echo URL_ROOT; ?>/riego/miHistorial" class="text-decoration-none">
            <div class="ryzoma-card p-4 h-100">
                <div class="d-flex align-items-center mb-3">
                    <div class="card-icon-box theme-analisis mb-0 me-3">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <h6 class="fw-bold mb-0 text-dark">Bitácora de Riego</h6>
                </div>
                <p class="small text-muted mb-0">Registros anteriores y alertas de días faltantes.</p>
            </div>
        </a>
    </div>

    <div class="col">
        <a href="<?php echo URL_ROOT; ?>/fertilizacion/historial" class="text-decoration-none">
            <div class="ryzoma-card p-4 h-100">
                <div class="d-flex align-items-center mb-3">
                    <div class="card-icon-box theme-analisis mb-0 me-3">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <h6 class="fw-bold mb-0 text-dark">Bitácora de Fertilización</h6>
                </div>
                <p class="small text-muted mb-0">Historial de registros anteriores.</p>
            </div>
        </a>
    </div>
    <div class="col">
        <a href="<?php echo URL_ROOT; ?>/avanceLabores" class="text-decoration-none">
            <div class="ryzoma-card p-4 h-100">
                <div class="d-flex align-items-center mb-3">
                    <div class="card-icon-box bg-success bg-opacity-10 text-success mb-0 me-3">
                        <i class="bi bi-card-checklist"></i>
                    </div>
                    <h6 class="fw-bold mb-0 text-dark">Avance de Labores</h6>
                </div>
                <p class="small text-muted mb-0">Registro semanal de actividades y jornadas.</p>
            </div>
        </a>
    </div>

    <?php if ($data['puedeVerAdmin']): ?>
    <div class="col">
        <a href="<?php echo URL_ROOT; ?>/admin" class="text-decoration-none">
            <div class="ryzoma-card p-4 h-100 bg-light">
                <div class="d-flex align-items-center mb-3">
                    <div class="card-icon-box bg-white border mb-0 me-3 text-secondary">
                        <i class="bi bi-gear-fill"></i>
                    </div>
                    <h6 class="fw-bold mb-0 text-secondary">Administración</h6>
                </div>
                <p class="small text-muted mb-0">Configuración de usuarios, sectores y parámetros globales.</p>
            </div>
        </a>
    </div>
    <?php endif; ?>

</div>