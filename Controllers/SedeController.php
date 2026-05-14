<?php
/**
 * Controllers/SedeController.php
 * Maneja la gestion de sedes de deposito (solo admin).
 */
class SedeController extends Controller {

    private Sede $model;

    public function __construct() {
        parent::__construct();
        require_once __DIR__ . '/../Models/Sede.php';
        $this->model = new Sede($this->db);
    }

    public function index(): void {
        $this->requireAdmin();

        $mensaje    = '';
        $tipoAlerta = '';
        $accion     = $_GET['accion'] ?? 'listar';
        $idEditar   = isset($_GET['id']) ? (int)$_GET['id'] : null;

        // CREAR
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_sede'])) {
            $nombre      = trim($_POST['nombre_sede'] ?? '');
            $direccion   = trim($_POST['direccion'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');

            if (empty($nombre)) {
                $mensaje = 'El nombre de la sede es obligatorio.'; $tipoAlerta = 'warning';
            } elseif ($this->model->existeNombre($nombre)) {
                $mensaje = 'Ya existe una sede con ese nombre.'; $tipoAlerta = 'danger';
            } else {
                $this->model->crear(['nombre' => $nombre, 'direccion' => $direccion, 'descripcion' => $descripcion]);
                $this->auditoria('CREAR_SEDE', $nombre, "Nueva sede creada: {$nombre}");
                $mensaje = 'Sede creada exitosamente.'; $tipoAlerta = 'success'; $accion = 'listar';
            }
        }

        // EDITAR
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_sede'])) {
            $id          = (int)$_POST['id_sede'];
            $nombre      = trim($_POST['nombre_sede'] ?? '');
            $direccion   = trim($_POST['direccion'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');

            if (empty($nombre)) {
                $mensaje = 'El nombre de la sede es obligatorio.'; $tipoAlerta = 'warning';
            } else {
                $this->model->actualizar($id, ['nombre' => $nombre, 'direccion' => $direccion, 'descripcion' => $descripcion]);
                $this->auditoria('EDITAR_SEDE', $nombre, "Sede actualizada: {$nombre}");
                $mensaje = 'Sede actualizada exitosamente.'; $tipoAlerta = 'success'; $accion = 'listar';
            }
        }

        // TOGGLE estado
        if (isset($_GET['toggle'])) {
            $id = (int)$_GET['toggle'];
            $nuevoEstado = $this->model->toggleEstado($id);
            if ($nuevoEstado !== null) {
                $texto = $nuevoEstado === 1 ? 'activada' : 'desactivada';
                $sede  = $this->model->obtenerPorId($id);
                $this->auditoria('CAMBIAR_ESTADO_SEDE', $sede['nombre_sede'] ?? "ID:{$id}", "Sede {$texto}");
                $mensaje = "Sede {$texto} exitosamente."; $tipoAlerta = 'success';
            }
        }

        $sedes                = $this->model->obtenerTodas();
        $expedientesPorSede   = $this->model->contarExpedientesPorSede();
        $sedeEditar           = ($accion === 'editar' && $idEditar) ? $this->model->obtenerPorId($idEditar) : null;

        $this->render('sede/index', compact('sedes','expedientesPorSede','sedeEditar','accion','mensaje','tipoAlerta'));
    }
}
