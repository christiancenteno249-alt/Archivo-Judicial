<?php
/**
 * Controllers/UsuarioController.php
 * Maneja la gestion de usuarios del sistema (solo admin).
 */
class UsuarioController extends Controller {

    private Usuario $model;

    public function __construct() {
        parent::__construct();
        require_once __DIR__ . '/../Models/Usuario.php';
        $this->model = new Usuario($this->db);
    }

    public function index(): void {
        $this->requireAdmin();

        $mensaje    = '';
        $tipoAlerta = '';
        $accion     = $_GET['accion'] ?? 'listar';
        $idEditar   = isset($_GET['id']) ? (int)$_GET['id'] : null;

        // CREAR
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_usuario'])) {
            $nombre  = trim($_POST['nombre'] ?? '');
            $usuario = trim($_POST['usuario'] ?? '');
            $pass    = trim($_POST['password'] ?? '');
            $rol     = trim($_POST['rol'] ?? 'operador');

            if (empty($nombre) || empty($usuario) || empty($pass)) {
                $mensaje = 'Todos los campos son obligatorios.';
                $tipoAlerta = 'warning';
            } elseif ($this->model->existeNick($usuario)) {
                $mensaje = 'El nombre de usuario ya existe.';
                $tipoAlerta = 'danger';
            } else {
                $this->model->crear(compact('nombre','usuario','password','rol') + ['password' => $pass]);
                $this->auditoria('CREAR_USUARIO', $usuario, "Nuevo usuario creado: {$nombre} [{$rol}]");
                $mensaje = 'Usuario creado exitosamente.'; $tipoAlerta = 'success'; $accion = 'listar';
            }
        }

        // EDITAR
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_usuario'])) {
            $id      = (int)$_POST['id_usuario'];
            $nombre  = trim($_POST['nombre'] ?? '');
            $usuario = trim($_POST['usuario'] ?? '');
            $pass    = trim($_POST['password'] ?? '');
            $rol     = trim($_POST['rol'] ?? 'operador');

            if (empty($nombre) || empty($usuario)) {
                $mensaje = 'Nombre y usuario son obligatorios.'; $tipoAlerta = 'warning';
            } else {
                $this->model->actualizar($id, ['nombre' => $nombre, 'usuario' => $usuario, 'password' => $pass, 'rol' => $rol]);
                $this->auditoria('EDITAR_USUARIO', $usuario, "Usuario actualizado: {$nombre}");
                $mensaje = 'Usuario actualizado exitosamente.'; $tipoAlerta = 'success'; $accion = 'listar';
            }
        }

        // ELIMINAR (borrado logico)
        if (isset($_GET['eliminar'])) {
            $id = (int)$_GET['eliminar'];
            if ($id === (int)$_SESSION['usuario_id']) {
                $mensaje = 'No puedes eliminar tu propio usuario.'; $tipoAlerta = 'danger';
            } else {
                $this->model->desactivar($id);
                $this->auditoria('DESACTIVAR_USUARIO', "ID:{$id}", "Usuario desactivado");
                $mensaje = 'Usuario desactivado exitosamente.'; $tipoAlerta = 'success';
            }
        }

        $usuarios     = $this->model->obtenerTodos();
        $usuarioEditar = ($accion === 'editar' && $idEditar) ? $this->model->obtenerPorId($idEditar) : null;

        $this->render('usuario/index', compact('usuarios','usuarioEditar','accion','mensaje','tipoAlerta'));
    }
}
