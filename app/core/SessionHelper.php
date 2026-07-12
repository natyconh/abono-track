<?php
// app/core/SessionHelper.php — Abono Track
// Helpers de sesión: autenticación, mensajes flash.

class SessionHelper {

    public static function setFlash($message, $type = 'info') {
        $_SESSION['flash_message'] = ['text' => $message, 'type' => $type];
    }

    public static function displayFlash() {
        if (isset($_SESSION['flash_message'])) {
            $msg  = $_SESSION['flash_message'];
            unset($_SESSION['flash_message']);
            $type = htmlspecialchars($msg['type']);
            echo "<div class='alert alert-{$type} alert-dismissible fade show' role='alert'>";
            echo htmlspecialchars($msg['text']);
            echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>';
            echo "</div>";
        }
    }

    public static function set($key, $value) {
        $_SESSION[$key] = $value;
    }

    public static function getFlash($key) {
        if (isset($_SESSION[$key])) {
            $value = $_SESSION[$key];
            unset($_SESSION[$key]);
            return $value;
        }
        return null;
    }

    public static function createUserSession($user) {
        session_regenerate_id(true);
        $_SESSION['loggedin']      = true;
        $_SESSION['usuario_id']    = $user->id;
        $_SESSION['username']      = $user->username;
        $_SESSION['nombre']        = $user->nombre ?? null;
    }

    public static function destroySession() {
        $_SESSION = [];
        session_destroy();
    }

    public static function isLoggedIn() {
        return (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true);
    }

    public static function getUserId() {
        return $_SESSION['usuario_id'] ?? null;
    }

    public static function getUserName() {
        return $_SESSION['nombre'] ?? $_SESSION['username'] ?? null;
    }
}
?>
