<?php
class EntidadLegalController extends Controller {
    private $entidadModel;

    public function __construct() {
        parent::__construct();
        $this->protect(['Admin']);
        $this->entidadModel = $this->model('EntidadLegalModel');
    }

    public function index() {
        $data = [
            'titulo' => 'Maestro de Razones Sociales',
            'entidades' => $this->entidadModel->obtenerTodas(),
            'breadcrumbs' => [['label' => 'Configuración', 'url' => URL_ROOT.'/admin'], ['label' => 'Razones Sociales']]
        ];
        $this->view('entidades_legales/index', $data);
    }

    public function form($id = null) {
        $data = [
            'titulo' => 'Nueva Razón Social',
            'entidad' => (object)['id'=>null,'rut'=>'','razon_social'=>'','nombre_fantasia'=>'','codigo_sag'=>'','direccion'=>''],
            'breadcrumbs' => [['label' => 'Razones Sociales', 'url' => URL_ROOT.'/entidadLegal'], ['label' => $id?'Editar':'Crear']]
        ];

        if ($id) {
            $e = $this->entidadModel->obtenerPorId($id);
            if ($e) { $data['titulo'] = 'Editar Razón Social'; $data['entidad'] = $e; }
        }
        $this->view('entidades_legales/form', $data);
    }

    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') $this->redirect('entidadLegal/index');
        $id = $_POST['id'] ?? null;
        $datos = [
            'rut' => trim($_POST['rut']),
            'razon_social' => trim($_POST['razon_social']),
            'nombre_fantasia' => trim($_POST['nombre_fantasia']),
            'codigo_sag' => trim($_POST['codigo_sag']),
            'direccion' => trim($_POST['direccion'])
        ];

        if(empty($datos['razon_social'])) { SessionHelper::setFlash('Nombre obligatorio', 'danger'); $this->redirect('entidadLegal/form'); }

        if ($id) { $this->entidadModel->actualizar($id, $datos); SessionHelper::setFlash('Actualizado', 'success'); }
        else { $this->entidadModel->crear($datos); SessionHelper::setFlash('Creado', 'success'); }
        
        $this->redirect('entidadLegal/index');
    }

    public function eliminar($id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->entidadModel->eliminar($id);
            SessionHelper::setFlash('Eliminado', 'success');
        }
        $this->redirect('entidadLegal/index');
    }
}
?>