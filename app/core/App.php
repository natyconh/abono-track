<?php
// app/core/App.php
// Esta es la clase principal del "Router".
// Se encarga de leer la URL y cargar el controlador y método adecuados.
// E.g., /public/users/login -> new UsersController()->login();

class App {
    
    // Propiedades por defecto
    protected $currentController = 'HomeController'; // Controlador por defecto
    protected $currentMethod = 'index'; // Método por defecto
    protected $params = []; // Parámetros (ej. /users/edit/5 -> [5])

    public function __construct() {
        // 1. Obtener y procesar la URL
        $url = $this->getUrl();

        // 2. Buscar el Controlador (Parte 1 de la URL)
        // Comprobar si existe el archivo del controlador en app/controllers/
        // E.g., 'users' -> 'UsersController'
        if (isset($url[0])) {
            $controllerName = ucwords($url[0]) . 'Controller'; // Ej: 'Users' . 'Controller'
            if (file_exists(APP_ROOT . '/controllers/' . $controllerName . '.php')) {
                // Si existe, lo establecemos como controlador actual
                $this->currentController = $controllerName;
                // "Sacamos" el controlador del array de URL
                unset($url[0]);
            }
        }

        // 3. Cargar el Controlador
        require_once APP_ROOT . '/controllers/' . $this->currentController . '.php';
        // Instanciar el controlador
        // E.g., $this->currentController = new UsersController();
        $this->currentController = new $this->currentController();

        // 4. Buscar el Método (Parte 2 de la URL)
        // Comprobar si se pasó un método
        if (isset($url[1])) {
            // Comprobar si el método existe en el controlador instanciado
            if (method_exists($this->currentController, $url[1])) {
                $this->currentMethod = $url[1]; // Ej: 'login'
                // "Sacamos" el método del array de URL
                unset($url[1]);
            }
        }

        // 5. Obtener los Parámetros (Resto de la URL)
        // Lo que queda en $url son los parámetros
        $this->params = $url ? array_values($url) : []; // Ej: [5]

        // 6. Llamar al método con los parámetros
        // E.g., call_user_func_array([new UsersController(), 'login'], []);
        call_user_func_array([$this->currentController, $this->currentMethod], $this->params);
    }

    /**
     * Obtiene la URL del parámetro 'url' (gracias a .htaccess),
     * la limpia y la divide en un array.
     */
    public function getUrl() {
        if (isset($_GET['url'])) {
            $url = rtrim($_GET['url'], '/'); // Quitar / del final
            $url = filter_var($url, FILTER_SANITIZE_URL); // Limpiar la URL
            $url = explode('/', $url); // Dividir en array
            return $url;
        }
        return []; // Retorna un array vacío si no hay URL (irá al HomeController@index)
    }
}
?>