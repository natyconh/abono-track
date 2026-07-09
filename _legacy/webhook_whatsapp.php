<?php
// public/webhook_whatsapp.php

// 1. LOG INMEDIATO DE ENTRADA (Para saber si Meta está llegando)
$logFile = __DIR__ . '/debug_entry.txt';
file_put_contents($logFile, "[".date('H:i:s')."] 🚀 Webhook tocado por Meta\n", FILE_APPEND);

try {
    // 2. Cargar Configuración
    if (!file_exists('../app/config/config.php')) {
        throw new Exception("No encuentro config.php");
    }
    require_once '../app/config/config.php';
    file_put_contents($logFile, "[".date('H:i:s')."] ✅ Config cargada\n", FILE_APPEND);

    // 3. Autoloader
    spl_autoload_register(function ($className) {
        $file = '../app/core/' . $className . '.php';
        if (file_exists($file)) {
            require_once $file;
        }
    });

    // 4. Cargar el Controlador manualmente
    $controllerPath = APP_ROOT . '/controllers/WebhookController.php';
    if (!file_exists($controllerPath)) {
        throw new Exception("No encuentro WebhookController.php en: " . $controllerPath);
    }
    require_once $controllerPath;
    file_put_contents($logFile, "[".date('H:i:s')."] ✅ Controlador cargado\n", FILE_APPEND);

    // 5. Instanciar y Ejecutar
    $webhook = new WebhookController();
    $webhook->handle();

} catch (Throwable $e) {
    // CAPTURAR CUALQUIER ERROR FATAL Y GUARDARLO
    $errorMsg = "[".date('H:i:s')."] 🔥 ERROR FATAL: " . $e->getMessage() . " en " . $e->getFile() . " linea " . $e->getLine() . "\n";
    file_put_contents($logFile, $errorMsg, FILE_APPEND);
    http_response_code(500);
}
?>