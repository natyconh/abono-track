<?php
// public/index.php

// Iniciar la sesión en el punto de entrada más temprano posible
// Rescatado de: header.php, login.php, procesar_login.php, etc.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Forzar zona horaria para toda la aplicación
date_default_timezone_set('America/Santiago'); 
// --------------------------
// Cargar el Autoloader de COMPOSER 
require_once '../vendor/autoload.php';
// 1. Cargar la configuración
require_once '../app/config/config.php';

// 2. Cargar Helpers
require_once '../app/core/SessionHelper.php'; // ¡Nuevo!

// 3. Cargar el autoloader para las clases del núcleo (core)
spl_autoload_register(function ($className) {
    $file = '../app/core/' . $className . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// 4. Iniciar el Router (nuestra clase App)
$app = new App;

?>