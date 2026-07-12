<?php
// public/index.php — Abono Track
// Punto de entrada único (Front Controller)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Zona horaria de la aplicación
date_default_timezone_set('America/Santiago');

// Configuración de la aplicación
require_once '../app/config/config.php';

// Helpers de sesión
require_once '../app/core/SessionHelper.php';

// Autoloader para clases del core
spl_autoload_register(function ($className) {
    $file = '../app/core/' . $className . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Iniciar el Router
$app = new App;
?>
