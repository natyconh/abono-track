<?php
// app/core/App.php — Abono Track
// Router principal: lee la URL y despacha al controlador y método correspondiente.
// Ejemplo: /predios/edit/5 → new PrediosController()->edit(5)

class App {

    protected $currentController = 'HomeController';
    protected $currentMethod     = 'index';
    protected $params            = [];

    public function __construct() {
        $url = $this->getUrl();

        // 1. Resolver Controlador
        if (isset($url[0])) {
            $controllerName = ucwords($url[0]) . 'Controller';
            if (file_exists(APP_ROOT . '/controllers/' . $controllerName . '.php')) {
                $this->currentController = $controllerName;
                unset($url[0]);
            }
        }

        require_once APP_ROOT . '/controllers/' . $this->currentController . '.php';
        $this->currentController = new $this->currentController();

        // 2. Resolver Método
        if (isset($url[1])) {
            if (method_exists($this->currentController, $url[1])) {
                $this->currentMethod = $url[1];
                unset($url[1]);
            }
        }

        // 3. Parámetros restantes
        $this->params = $url ? array_values($url) : [];

        // 4. Ejecutar
        call_user_func_array([$this->currentController, $this->currentMethod], $this->params);
    }

    public function getUrl() {
        if (isset($_GET['url'])) {
            $url = rtrim($_GET['url'], '/');
            $url = filter_var($url, FILTER_SANITIZE_URL);
            $url = explode('/', $url);
            return $url;
        }
        return [];
    }
}
?>
