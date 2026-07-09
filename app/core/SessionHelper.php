<?php
// app/core/SessionHelper.php

class SessionHelper {

    // --- Manejo de Mensajes Flash (Alertas visuales) ---

    public static function setFlash($message, $type = 'info') {
        $_SESSION['flash_message'] = [
            'text' => $message,
            'type' => $type
        ];
    }

    public static function displayFlash() {
        if (isset($_SESSION['flash_message'])) {
            $message = $_SESSION['flash_message'];
            unset($_SESSION['flash_message']);
            $alert_type = $message['type'];
            
            // Aquí ocurría el error porque $alert_type era un array
            echo "<div class='alert alert-{$alert_type} alert-dismissible fade show' role='alert'>";
            echo htmlspecialchars($message['text']);
            echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
            echo "</div>";
        }
    }

    // --- Manejo de Datos Temporales (Flash Data) ---

    /**
     * NUEVO: Guarda un valor en la sesión bajo una clave específica.
     * Útil para persistir errores o datos de formularios entre redirecciones.
     */
    public static function set($key, $value) {
        $_SESSION[$key] = $value;
    }

    public static function getFlash($key) {
        if (isset($_SESSION[$key])) {
            $value = $_SESSION[$key];
            unset($_SESSION[$key]); // Se borra después de leer (comportamiento Flash)
            return $value;
        }
        return null;
    }

    // --- Manejo de Autenticación ---

    public static function createUserSession($user) {
        session_regenerate_id(true); 
        $_SESSION['loggedin'] = true;
        $_SESSION['usuario_id'] = $user->id;
        $_SESSION['username'] = $user->username;
        $_SESSION['rol_id'] = $user->rol_id;
        $_SESSION['nombre_rol'] = $user->nombre_rol;
        $_SESSION['trabajador_id'] = $user->trabajador_id;
        $_SESSION['empresa_id'] = $user->empresa_id;
        $_SESSION['nombre_completo'] = $user->nombre_completo_trabajador ?? null;
    }

    public static function destroySession() {
        $_SESSION = array();
        session_destroy();
    }

    // --- Chequeos de Seguridad y Contexto ---

    public static function isLoggedIn() {
        return (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true);
    }

    public static function getUserId() {
        return $_SESSION['usuario_id'] ?? null;
    }

    public static function getUserEmpresaId() {
        return $_SESSION['empresa_id'] ?? null;
    }

    public static function getUserRoleName() {
        return $_SESSION['nombre_rol'] ?? 'Invitado';
    }

    public static function getUserRoleId() {
        return $_SESSION['rol_id'] ?? null;
    }

    public static function getUserFullName() {
        return $_SESSION['nombre_completo'] ?? null;
    }

    public static function getUserName() {
        return $_SESSION['username'] ?? null;
    }

    public static function hasRole($roles = []) {
        if (!self::isLoggedIn()) {
            return false;
        }
        $userRole = self::getUserRoleName();
        if (empty($roles)) {
            return true;
        }
        return in_array($userRole, $roles);
    }
}
?>