<?php
/**
 * Controllers/TribunalController.php
 * Controlador para la gestión administrativa del catálogo de tribunales.
 * Exclusivo para usuarios con rol 'admin'.
 */
class TribunalController extends Controller {

    private Tribunal $model;

    public function __construct() {
        parent::__construct();
        require_once __DIR__ . '/../Models/Tribunal.php';
        $this->model = new Tribunal($this->db);
    }

    /**
     * Muestra la interfaz de gestión de tribunales.
     */
    public function index(): void {
        $this->requireAuth();
        if (($_SESSION['usuario_rol'] ?? '') !== 'admin') {
            $this->redirect('/');
        }

        $tribunales = $this->model->all();
        $mensaje     = $_SESSION['flash_mensaje'] ?? '';
        $tipoAlerta  = $_SESSION['flash_tipo'] ?? '';
        unset($_SESSION['flash_mensaje'], $_SESSION['flash_tipo']);

        $this->render('tribunal/index', compact('tribunales', 'mensaje', 'tipoAlerta'));
    }

    /**
     * Guarda un nuevo tribunal en el catálogo.
     */
    public function guardar(): void {
        $this->requireAuth();
        if (($_SESSION['usuario_rol'] ?? '') !== 'admin') {
            $this->redirect('/');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre = trim($_POST['nombre_tribunal'] ?? '');
            if (empty($nombre)) {
                $_SESSION['flash_mensaje'] = 'El nombre del tribunal no puede estar vacío.';
                $_SESSION['flash_tipo']    = 'warning';
            } elseif ($this->model->existePorNombre($nombre)) {
                $_SESSION['flash_mensaje'] = 'Ya existe un tribunal con ese nombre en el catálogo.';
                $_SESSION['flash_tipo']    = 'danger';
            } else {
                $creado = $this->model->create($nombre);
                if ($creado) {
                    $this->auditoria('CREAR_TRIBUNAL_CATALOGO', "Tribunal: " . strtoupper($nombre), "Se agregó el tribunal al catálogo.");
                    $_SESSION['flash_mensaje'] = 'Tribunal agregado al catálogo con éxito.';
                    $_SESSION['flash_tipo']    = 'success';
                } else {
                    $_SESSION['flash_mensaje'] = 'No se pudo guardar el tribunal. Intenta de nuevo.';
                    $_SESSION['flash_tipo']    = 'danger';
                }
            }
        }
        $this->redirect('/tribunales');
    }

    /**
     * Edita un tribunal existente.
     */
    public function editar(): void {
        $this->requireAuth();
        if (($_SESSION['usuario_rol'] ?? '') !== 'admin') {
            $this->redirect('/');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id     = (int)($_POST['id_tribunal'] ?? 0);
            $nombre = trim($_POST['nombre_tribunal'] ?? '');

            if ($id <= 0 || empty($nombre)) {
                $_SESSION['flash_mensaje'] = 'Datos de edición inválidos.';
                $_SESSION['flash_tipo']    = 'warning';
            } elseif ($this->model->existePorNombre($nombre, $id)) {
                $_SESSION['flash_mensaje'] = 'Ya existe otro tribunal con ese nombre en el catálogo.';
                $_SESSION['flash_tipo']    = 'danger';
            } else {
                $original   = $this->model->find($id);
                $nombreViejo = $original ? $original['tribunal'] : '';
                $actualizado = $this->model->update($id, $nombre);
                if ($actualizado) {
                    $this->auditoria('EDITAR_TRIBUNAL_CATALOGO', "Tribunal ID: {$id}", "Nombre cambiado: '{$nombreViejo}' -> '" . strtoupper($nombre) . "'");
                    $_SESSION['flash_mensaje'] = 'Tribunal actualizado correctamente.';
                    $_SESSION['flash_tipo']    = 'success';
                } else {
                    $_SESSION['flash_mensaje'] = 'No se pudo actualizar el tribunal. Intenta de nuevo.';
                    $_SESSION['flash_tipo']    = 'danger';
                }
            }
        }
        $this->redirect('/tribunales');
    }

    /**
     * Elimina un tribunal del catálogo.
     */
    public function eliminar(): void {
        $this->requireAuth();
        if (($_SESSION['usuario_rol'] ?? '') !== 'admin') {
            $this->redirect('/');
        }

        $id = (int)($_GET['id'] ?? 0);
        if ($id > 0) {
            $tribunal = $this->model->find($id);
            if ($tribunal) {
                $eliminado = $this->model->delete($id);
                if ($eliminado) {
                    $this->auditoria('ELIMINAR_TRIBUNAL_CATALOGO', "Tribunal ID: {$id}", "Tribunal eliminado: '{$tribunal['tribunal']}'");
                    $_SESSION['flash_mensaje'] = 'Tribunal eliminado del catálogo con éxito.';
                    $_SESSION['flash_tipo']    = 'success';
                } else {
                    $_SESSION['flash_mensaje'] = 'No se pudo eliminar el tribunal.';
                    $_SESSION['flash_tipo']    = 'danger';
                }
            }
        }
        $this->redirect('/tribunales');
    }
}
