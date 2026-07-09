</main> <!-- Fin del contenedor principal -->
    </div> <!-- Fin del d-flex -->

    <!-- Footer "Clean Canvas" -->
    <footer class="py-4 mt-auto bg-white border-top" style="border-color: var(--ryzoma-grey-mid) !important;">
        <div class="container-fluid text-center">
            <div class="row align-items-center justify-content-center">
                <div class="col-12">
                    <p class="mb-0 small text-muted font-monospace" style="font-size: 0.75rem;">
                        <span class="fw-bold text-primary-dark-green">ryzoma</span> 
                        <span class="mx-2">|</span> 
                        &copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?> 
                        <span class="mx-2">·</span> 
                        v0.5.4
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <!-- Carga Condicional de JS (Lógica rescatada de footer.php) -->
    <?php if (isset($use_datatables) && $use_datatables): ?>
        <script src="https://cdn.datatables.net/2.0.3/js/dataTables.js"></script>
        <script src="https://cdn.datatables.net/2.0.3/js/dataTables.bootstrap5.js"></script>
        
        <!-- Script de inicialización para DataTables -->
        <script>
            $(document).ready(function() {
                $('#tablaUsuarios').DataTable({
                    language: {
                        url: 'https://cdn.datatables.net/plug-ins/2.0.8/i18n/es-ES.json'
                    },
                    responsive: true
                });
            });
        </script>
    <?php endif; ?>
    
    <?php if (isset($use_charts) && $use_charts): ?>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <?php endif; ?>
    
    <!-- Nuestro JS (incluye la lógica del sidebar rescatada de footer.php) -->
    <script src="<?php echo URL_ROOT; ?>/js/main.js"></script>
       
    <?php if (isset($load_maps) && $load_maps === true): ?>
    
    <script>
        // Pasa los datos del controlador ($puntos) a una variable JS global
        const PUNTOS_DATA = <?php echo isset($puntos) ? json_encode($puntos) : '[]'; ?>;
     // ¡NUEVO! Pasa los datos de las instalaciones para el formulario
     const INSTALACIONES_DATA = <?php echo isset($instalaciones) ? json_encode($instalaciones) : '[]'; ?>;
        // Pasa la URL raíz para que JS la use en peticiones AJAX
        const URL_ROOT = '<?php echo URL_ROOT; ?>';
    </script>

    <script>
        // Función helper para cargar un script y devolver una promesa
        function loadScript(src) {
            return new Promise((resolve, reject) => {
                const script = document.createElement('script');
                script.src = src;
                script.async = true;
                script.onload = resolve;
                script.onerror = () => reject(new Error(`Falló la carga del script: ${src}`));
                document.head.appendChild(script);
            });
        }

        // Función para inicializar todo el proceso
        async function initializeMapAndLibraries() {
            try {
                // 2a. Cargar la API de Google Maps (bootstrap)
                // (libraries=maps,marker es crucial para PinElement)
                const googleApiUrl = `https://maps.googleapis.com/maps/api/js?key=<?php echo GMAPS_API_KEY; ?>&libraries=maps,marker&callback=__googleMapsCallback`;
                
                // 2b. Crear una promesa para el callback de Google Maps
                await new Promise((resolve, reject) => {
                    window.__googleMapsCallback = resolve; // Google llamará a esto
                    loadScript(googleApiUrl).catch(reject);
                });
                console.log("Google Maps API (con 'marker') cargada.");

                // 2c. Cargar la librería MarkerClusterer
                await loadScript("https://unpkg.com/@googlemaps/markerclusterer/dist/index.min.js");
                console.log("MarkerClusterer cargado.");

                // 2d. Cargar NUESTRO script lógico
                // (Lo cargamos aquí para asegurar que PUNTOS_DATA ya existe)
                await loadScript("<?php echo URL_ROOT; ?>/js/puntos_map_logic.js");
                console.log("puntos_map_logic.js cargado.");

                // 2e. Ahora que todo está cargado, llamar a initMap
                // (initMap está definida en puntos_map_logic.js)
                initMap();

            } catch (error) {
                console.error("Error al cargar las librerías del mapa:", error);
                const mapDiv = document.getElementById('map');
                if (mapDiv) {
                    mapDiv.innerHTML = '<div class="alert alert-danger m-3" role="alert"><strong>Error de Carga:</strong> No se pudo inicializar el mapa.</div>';
                }
            }
        }
        
        // Iniciar el proceso de carga
        initializeMapAndLibraries();
    </script>
    
<?php endif; ?>
</body>
</html>
