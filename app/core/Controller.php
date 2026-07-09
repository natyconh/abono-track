<?php
// app/core/Controller.php
// Este es el controlador PADRE.
// AHORA maneja la inyección de dependencias del contexto.

abstract class Controller {

    protected $db; // Contenedor para la instancia de Database
    protected $empresa_id; // ID de la empresa del usuario logueado
    protected $usuario_id; // ID del usuario logueado

    public function __construct() {
        // Instanciamos la BD una sola vez
        $this->db = Database::getInstance();
        
        // Cargamos el contexto del usuario (ID de empresa y de usuario)
        // Esto estará disponible para todos los controladores que hereden
        $this->empresa_id = SessionHelper::getUserEmpresaId();
        $this->usuario_id = SessionHelper::getUserId();
    }

    /**
     * Carga el Modelo.
     * MODIFICADO: Ahora inyecta el wrapper de BD y el contexto.
     */
    public function model($model) {
        require_once APP_ROOT . '/models/' . $model . '.php';
        // Inyectamos la instancia de BD, el id de empresa y el id de usuario
        return new $model($this->db, $this->empresa_id, $this->usuario_id);
    }

    // Cargar Vista (con el layout header/footer)
    public function view($view, $data = []) {
        extract($data);
        $viewFile = APP_ROOT . '/views/' . $view . '.php';
        if (file_exists($viewFile)) {
            require_once '../app/views/layout/header.php';
            require_once $viewFile;
            require_once '../app/views/layout/footer.php';
        } else {
            die('La vista "' . $view . '" no existe.');
        }
    }

    // Vista especial para páginas sin el layout (ej. Login)
    public function standaloneView($view, $data = []) {
        extract($data);
        $viewFile = APP_ROOT . '/views/' . $view . '.php';
        if (file_exists($viewFile)) {
            require_once $viewFile;
        } else {
            die('La vista "' . $view . '" no existe.');
        }
    }

    // Helper de redirección
    public function redirect($url) {
        header('Location: ' . URL_ROOT . '/' . $url);
        exit;
    }

    /**
     * Helper de protección de rutas (RBAC)
     * MODIFICADO: Ahora se llama desde el __construct del controlador hijo.
     */
    protected function protect($allowedRoles = []) {
        if (!SessionHelper::isLoggedIn()) {
            SessionHelper::setFlash('Debe iniciar sesión para acceder a esta página.', 'danger');
            $this->redirect('users/login');
        }
        if (!empty($allowedRoles)) {
            if (!SessionHelper::hasRole($allowedRoles)) {
                SessionHelper::setFlash('Acceso no autorizado. Permiso insuficiente.', 'danger');
                $this->redirect('home/index');
            }
        }
    }

    /**
     * ¡NUEVO! (Fase 2.3)
     * Helper para estandarizar respuestas JSON/API
     */
    protected function respondJson($data, $statusCode = 200) {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }
}
?>