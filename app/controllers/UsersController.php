<?php
// app/controllers/UsersController.php — Abono Track (MVP)

class UsersController extends Controller {

    private $userModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        parent::__construct();
        $this->userModel = $this->model('UserModel');
    }

    public function index() {
        $this->redirect('users/admin');
    }

    public function login() {
        $data = [
            'titulo'       => 'Iniciar Sesión — Abono Track',
            'username'     => '',
            'password'     => '',
            'error'        => '',
        ];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            $username = trim($_POST['username'] ?? '');
            $password = trim($_POST['password'] ?? '');

            if (empty($username) || empty($password)) {
                $data['error'] = 'Por favor, ingrese usuario y contraseña.';
            } else {
                $user = $this->userModel->findUserByUsername($username);

                if ($user && password_verify($password, $user->password_hash)) {
                    if ($user->activo == 1) {
                        SessionHelper::createUserSession($user);
                        $this->userModel->updateLoginDate($user->id);
                        $this->redirect('home/index');
                    } else {
                        $data['error'] = 'Su cuenta está deshabilitada.';
                    }
                } else {
                    $data['error'] = 'Usuario o contraseña incorrecta.';
                }
            }
        }

        if (SessionHelper::isLoggedIn()) {
            $this->redirect('home/index');
        }

        $this->standaloneView('users/login', $data);
    }

    public function logout() {
        SessionHelper::destroySession();
        $this->redirect('users/login');
    }

    public function admin() {
        $this->protect();
        $data = [
            'titulo'       => 'Gestión de Usuarios',
            'usuarios'     => $this->userModel->getAllUsersWithDetails(),
            'breadcrumbs'  => [['label' => 'Administración', 'url' => URL_ROOT . '/admin'], ['label' => 'Usuarios']],
        ];
        $this->view('users/admin', $data);
    }

    // Nota: Para este MVP, simplificamos la creación a un formulario directo
    public function guardar() {
        $this->protect();
        if ($_SERVER['REQUEST_METHOD'] != 'POST') $this->redirect('users/admin');

        $data = [
            'username'      => trim($_POST['username']),
            'nombre'        => trim($_POST['nombre']),
            'password_hash' => password_hash($_POST['password'], PASSWORD_DEFAULT),
            'activo'        => 1
        ];

        if ($this->userModel->register($data)) {
            SessionHelper::setFlash('Usuario creado correctamente.', 'success');
        } else {
            SessionHelper::setFlash('Error al guardar el usuario.', 'danger');
        }
        $this->redirect('users/admin');
    }

    public function delete($id) {
        $this->protect();
        if ($this->userModel->delete($id)) {
            SessionHelper::setFlash('Usuario eliminado.', 'success');
        }
        $this->redirect('users/admin');
    }
}
?>
