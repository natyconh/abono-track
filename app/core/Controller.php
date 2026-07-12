<?php
// app/core/Controller.php — Abono Track
// Controlador base: gestiona DB, sesión y vistas. 
// ADAPTADO: protect() ya no evalúa roles, solo sesión activa.

abstract class Controller {

    protected $db;          
    protected $usuario_id;  

    public function __construct() {
        $this->db         = Database::getInstance();
        $this->usuario_id = SessionHelper::getUserId();
    }

    public function model($model) {
        require_once APP_ROOT . '/models/' . $model . '.php';
        return new $model($this->db, $this->usuario_id);
    }

    public function view($view, $data = []) {
        extract($data);
        $viewFile = APP_ROOT . '/views/' . $view . '.php';
        if (file_exists($viewFile)) {
            require_once '../app/views/layout/header.php';
            require_once $viewFile;
            require_once '../app/views/layout/footer.php';
        } else {
            die('Vista no encontrada: ' . htmlspecialchars($view));
        }
    }

    public function standaloneView($view, $data = []) {
        extract($data);
        $viewFile = APP_ROOT . '/views/' . $view . '.php';
        if (file_exists($viewFile)) {
            require_once $viewFile;
        } else {
            die('Vista no encontrada: ' . htmlspecialchars($view));
        }
    }

    public function redirect($url) {
        header('Location: ' . URL_ROOT . '/' . $url);
        exit;
    }

    /**
     * Protege rutas verificando únicamente que haya sesión iniciada.
     * Ignora el array $allowedRoles para evitar romper código legacy que lo enviaba.
     */
    protected function protect($allowedRoles = []) {
        if (!SessionHelper::isLoggedIn()) {
            SessionHelper::setFlash('Debe iniciar sesión para acceder.', 'danger');
            $this->redirect('users/login');
        }
    }

    protected function respondJson($data, $statusCode = 200) {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }
}
?>
