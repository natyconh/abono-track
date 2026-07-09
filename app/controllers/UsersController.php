<?php
// app/controllers/UsersController.php
// MODIFICADO: Integración con tabla usuarios_whatsapp_links

class UsersController extends Controller {

    private $userModel;
    private $usuarioWhatsappLinkModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if (!SessionHelper::isLoggedIn()) {
             $this->userModel = $this->model('UserModel');
             $this->usuarioWhatsappLinkModel = $this->model('UsuarioWhatsappLinkModel');
        } else {
            parent::__construct(); 
            $this->userModel = $this->model('UserModel');
            $this->usuarioWhatsappLinkModel = $this->model('UsuarioWhatsappLinkModel'); // Instanciar aquí también
        }
    }

    public function index() {
        $this->redirect('users/admin');
    }

    public function login() {
        $data = [
            'titulo' => 'Iniciar Sesión',
            'username' => '', 'password' => '', 'error' => '',
            'username_err' => '', 'password_err' => ''
        ];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            $data['username'] = trim($_POST['username']);
            $data['password'] = trim($_POST['password']);

            if (empty($data['username'])) $data['username_err'] = 'Ingrese usuario.';
            if (empty($data['password'])) $data['password_err'] = 'Ingrese contraseña.';

            if (empty($data['username_err']) && empty($data['password_err'])) {
                $user = $this->userModel->findUserByUsername($data['username']);

                if ($user && password_verify($data['password'], $user->password_hash)) {
                    if ($user->activo == 1) {
                        SessionHelper::createUserSession($user); 
                        // --- CAMBIO AQUÍ: Actualizamos la fecha en la BD ---
                        $this->userModel->updateLoginDate($user->id); 
                        SessionHelper::setFlash('¡Bienvenido de vuelta, ' . $user->username . '!', 'success');
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
        $this->protect(['Admin']); 

        $data = [
            'titulo' => 'Administrar Usuarios',
            'use_datatables' => true,
            'usuarios' => $this->userModel->getAllUsersWithDetails(), 
            'roles' => $this->userModel->getRoles(), 
            'trabajadores' => $this->userModel->getTrabajadoresSinUsuario(), 
            'breadcrumbs' => [
                ['label' => 'Administración', 'url' => URL_ROOT . '/admin'],
                ['label' => 'Usuarios'] 
            ]
        ];

        $this->view('users/admin', $data);
    }

    public function form($id = null) {
        $this->protect(['Admin']);
        
        $flashData = SessionHelper::getFlash('form_data');   
        $flashErrors = SessionHelper::getFlash('form_errors'); 
        
        $data = [
            'titulo' => 'Crear Usuario',
            'user' => null, 
            'roles' => $this->userModel->getRoles(),
            'trabajadores' => $this->userModel->getTrabajadoresSinUsuario(),
            'whatsapp_link' => null, // Dato adicional para el form
            'errors' => $flashErrors ?? [], 
            'action' => 'add',
            'breadcrumbs' => [
                ['label' => 'Administración', 'url' => URL_ROOT . '/admin'],
                ['label' => 'Usuarios', 'url' => URL_ROOT . '/users/admin'],
                ['label' => $id ? 'Editar' : 'Crear'] 
            ]
        ];

        // 1. Caso de Error (Flash Data)
        if ($flashData) {
            $data['user'] = (object) $flashData; 
            // Si hay whatsapp en el post fallido, lo recuperamos
            $data['whatsapp_link'] = (object)['numero_whatsapp' => $flashData['whatsapp'] ?? ''];

            if (!empty($flashData['id'])) {
                $data['action'] = 'edit';
                $data['titulo'] = 'Editar Usuario';
            }

        // 2. Caso Edición (Carga DB)
        } elseif ($id) {
            $data['titulo'] = 'Editar Usuario';
            $data['action'] = 'edit';
            $data['user'] = $this->userModel->findUserById($id); 
            
            if ($data['user']) {
                // Cargar datos de WhatsApp si existen
                $data['whatsapp_link'] = $this->usuarioWhatsappLinkModel->obtenerPorUsuarioId($id);
            } else {
                SessionHelper::setFlash('Usuario no encontrado.', 'danger');
                $this->redirect('users/admin');
            }
        }

        $this->view('users/form', $data);
    }

    public function add() {
        $this->protect(['Admin']);
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            $this->redirect('users/admin');
        }

        $data = [
            'trabajador_id' => filter_input(INPUT_POST, 'trabajador_id', FILTER_VALIDATE_INT),
            'username' => trim($_POST['username'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'confirm_password' => $_POST['confirm_password'] ?? '',
            'whatsapp' => trim($_POST['whatsapp'] ?? ''), // Capturamos el WhatsApp
            'rol_id' => filter_input(INPUT_POST, 'rol_id', FILTER_VALIDATE_INT),
            'activo' => isset($_POST['activo']) ? 1 : 0,
            'empresa_id' => $this->empresa_id 
        ];
        
        $errors = $this->validarUsuario($data);

        if ($data['password'] != $data['confirm_password']) $errors[] = 'Las contraseñas no coinciden.';
        if (empty($data['password'])) $errors[] = 'La contraseña es obligatoria.';

        if (!empty($errors)) {
            SessionHelper::set('form_data', $data);
            SessionHelper::set('form_errors', $errors);
            $this->redirect('users/form');
        }

        $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);

        // 1. Crear Usuario
        $newUserId = $this->userModel->register($data);

        if ($newUserId) {
            // 2. Si hay número, crear el vínculo en la otra tabla
            if (!empty($data['whatsapp'])) {
                $this->usuarioWhatsappLinkModel->guardarNumero($newUserId, $data['whatsapp']);
            }
            
            SessionHelper::setFlash('Usuario creado correctamente.', 'success');
        } else {
            SessionHelper::setFlash('Error al guardar en la base de datos.', 'danger');
        }
        $this->redirect('users/admin');
    }

    public function edit() {
        $this->protect(['Admin']);
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            $this->redirect('users/admin');
        }

        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        
        $data = [
            'id' => $id,
            'trabajador_id' => filter_input(INPUT_POST, 'trabajador_id', FILTER_VALIDATE_INT),
            'username' => trim($_POST['username'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'confirm_password' => $_POST['confirm_password'] ?? '', // Añadido para consistencia
            'whatsapp' => trim($_POST['whatsapp'] ?? ''),
            'rol_id' => filter_input(INPUT_POST, 'rol_id', FILTER_VALIDATE_INT),
            'activo' => isset($_POST['activo']) ? 1 : 0
        ];
        
        $errors = $this->validarUsuario($data, $id); 
        
        // Validación de pass solo si se escribió algo
        if (!empty($data['password']) && $data['password'] != $data['confirm_password']) {
            $errors[] = 'Las contraseñas no coinciden.';
        }

        if (!empty($errors)) {
            SessionHelper::set('form_data', $data);
            SessionHelper::set('form_errors', $errors);
            $this->redirect('users/form/' . $id);
        }

        if (!empty($data['password'])) {
            $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        if ($this->userModel->update($data)) {
            // Guardar/Actualizar WhatsApp (siempre, aunque sea para borrarlo si viene vacío)
            $this->usuarioWhatsappLinkModel->guardarNumero($id, $data['whatsapp']);

            SessionHelper::setFlash('Usuario actualizado correctamente.', 'success');
        } else {
            SessionHelper::setFlash('Error al actualizar en la base de datos.', 'danger');
        }
        $this->redirect('users/admin');
    }

    private function validarUsuario($data, $ignoreId = 0) {
        $errors = [];
        if (empty($data['trabajador_id'])) $errors[] = 'Debe seleccionar un trabajador.';
        if (empty($data['username'])) $errors[] = 'El nombre de usuario es obligatorio.';
        if (empty($data['rol_id'])) $errors[] = 'Debe seleccionar un rol.';

        $user_by_username = $this->userModel->findUserByUsername($data['username']);
        if ($user_by_username && $user_by_username->id != $ignoreId) {
            $errors[] = 'El nombre de usuario ya está en uso por otra cuenta.';
        }
        
        // Permitimos re-guardar el mismo trabajador si estamos editando el mismo usuario
        $user_by_trabajador = $this->userModel->findUserByTrabajadorId($data['trabajador_id'], $ignoreId);
        if ($user_by_trabajador) {
            $errors[] = 'El trabajador seleccionado ya tiene otra cuenta de usuario.';
        }
        return $errors;
    }

    public function delete($id = null) {
        $this->protect(['Admin']);
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
             $this->redirect('users/admin');
        }

        $id_a_borrar = filter_var($id, FILTER_VALIDATE_INT);
        if ($id_a_borrar == $this->usuario_id) { 
            SessionHelper::setFlash('Error: No puede borrar su propia cuenta.', 'danger');
            $this->redirect('users/admin');
        }

        if ($this->userModel->delete($id_a_borrar)) { 
            SessionHelper::setFlash('Usuario eliminado.', 'success');
        } else {
            SessionHelper::setFlash('Error al eliminar.', 'danger');
        }
        $this->redirect('users/admin');
    }

    public function perfil() {
        $this->protect();
        $vinculo_actual = $this->usuarioWhatsappLinkModel->obtenerVinculoPorUsuario();
        $flashErrors = SessionHelper::getFlash('form_errors');
        
        $data = [
            'titulo' => 'Mi Perfil',
            'vinculo' => $vinculo_actual,
            'errores' => $flashErrors ?? [] 
        ];
        
        $this->view('users/perfil', $data);
    }

    public function guardarPerfil() {
        $this->protect();
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            $this->redirect('users/perfil');
        }

        $numero_whatsapp = filter_input(INPUT_POST, 'numero_whatsapp', FILTER_SANITIZE_STRING);
        
        if (empty($numero_whatsapp)) {
            SessionHelper::set('form_errors', ['numero' => 'El número no puede estar vacío.']);
            $this->redirect('users/perfil');
        }

        try {
            $numero_limpio = ltrim(trim($numero_whatsapp), '+');
            if (strlen($numero_limpio) == 9) { 
                $numero_limpio = '56' . $numero_limpio;
            }

            $this->usuarioWhatsappLinkModel->crearVinculo($numero_limpio);
            SessionHelper::setFlash('Número de WhatsApp actualizado y verificado.', 'success');
        
        } catch (Exception $e) {
            SessionHelper::setFlash('Error al guardar el número: ' . $e->getMessage(), 'danger');
        }
        
        $this->redirect('users/perfil');
    }
}
?>