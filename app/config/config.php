<?php
// app/config/config.php — Abono Track
// IMPORTANTE: No subir este archivo con credenciales reales a repositorios públicos.
// En producción usa variables de entorno o un archivo .env (fuera del webroot).

// --- Base de Datos ---
define('DB_HOST', 'localhost');
define('DB_USER', 'root');          // Cambiar en producción
define('DB_PASS', '');              // Cambiar en producción
define('DB_NAME', 'abono_track');   // Nombre de la BD local

// --- URLs ---
// En local (WAMP/XAMPP): 'http://localhost/abono-track/public'
// En shared hosting en raíz: 'https://tudominio.com'
define('URL_ROOT', 'http://localhost/abono-track/public');
define('SITE_NAME', 'Abono Track');

// --- Rutas del sistema ---
// APP_ROOT apunta a la carpeta /app
define('APP_ROOT', dirname(dirname(__FILE__)));
