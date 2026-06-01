<?php
/**
 * Controllers/DelitoController.php
 * Controlador para la gestión administrativa del catálogo de delitos.
 */
class DelitoController extends Controller {

    private Delito $model;

    public function __construct() {
        parent::__construct();
        require_once __DIR__ . '/../Models/Delito.php';
        $this->model = new Delito($this->db);
    }

    /**
     * Muestra la interfaz de gestión de delitos.
     */
    public function index(): void {
        $this->requireAuth();
        if (($_SESSION['usuario_rol'] ?? '') !== 'admin') {
            $this->redirect('/');
        }

        $delitos = $this->model->all();
        $mensaje = $_SESSION['flash_mensaje'] ?? '';
        $tipoAlerta = $_SESSION['flash_tipo'] ?? '';
        unset($_SESSION['flash_mensaje'], $_SESSION['flash_tipo']);

        $this->render('delito/index', compact('delitos', 'mensaje', 'tipoAlerta'));
    }

    /**
     * Guarda un nuevo delito en el catálogo.
     */
    public function guardar(): void {
        $this->requireAuth();
        if (($_SESSION['usuario_rol'] ?? '') !== 'admin') {
            $this->redirect('/');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre = trim($_POST['nombre_delito'] ?? '');
            if (empty($nombre)) {
                $_SESSION['flash_mensaje'] = 'El nombre del delito no puede estar vacío.';
                $_SESSION['flash_tipo'] = 'warning';
            } else {
                $creado = $this->model->create($nombre);
                if ($creado) {
                    $this->auditoria('CREAR_DELITO_CATALOGO', "Delito: " . strtoupper($nombre), "Se agregó el delito al catálogo.");
                    $_SESSION['flash_mensaje'] = 'Delito agregado al catálogo con éxito.';
                    $_SESSION['flash_tipo'] = 'success';
                } else {
                    $_SESSION['flash_mensaje'] = 'El delito ya existe en el catálogo o no se pudo guardar.';
                    $_SESSION['flash_tipo'] = 'danger';
                }
            }
        }
        $this->redirect('/delitos');
    }

    /**
     * Edita un delito existente en el catálogo.
     */
    public function editar(): void {
        $this->requireAuth();
        if (($_SESSION['usuario_rol'] ?? '') !== 'admin') {
            $this->redirect('/');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)($_POST['id_delito'] ?? 0);
            $nombre = trim($_POST['nombre_delito'] ?? '');
            if ($id <= 0 || empty($nombre)) {
                $_SESSION['flash_mensaje'] = 'Datos de edición inválidos.';
                $_SESSION['flash_tipo'] = 'warning';
            } else {
                $delitoOriginal = $this->model->find($id);
                $originalNombre = $delitoOriginal ? $delitoOriginal['nombre_delito'] : '';
                $actualizado = $this->model->update($id, $nombre);
                if ($actualizado) {
                    $this->auditoria('EDITAR_DELITO_CATALOGO', "Delito ID: {$id}", "Nombre cambiado: '{$originalNombre}' -> '" . strtoupper($nombre) . "'");
                    $_SESSION['flash_mensaje'] = 'Delito actualizado correctamente.';
                    $_SESSION['flash_tipo'] = 'success';
                } else {
                    $_SESSION['flash_mensaje'] = 'El nombre del delito ya existe o no se pudo actualizar.';
                    $_SESSION['flash_tipo'] = 'danger';
                }
            }
        }
        $this->redirect('/delitos');
    }

    /**
     * Elimina un delito del catálogo.
     */
    public function eliminar(): void {
        $this->requireAuth();
        if (($_SESSION['usuario_rol'] ?? '') !== 'admin') {
            $this->redirect('/');
        }

        $id = (int)($_GET['id'] ?? 0);
        if ($id > 0) {
            $delito = $this->model->find($id);
            if ($delito) {
                $eliminado = $this->model->delete($id);
                if ($eliminado) {
                    $this->auditoria('ELIMINAR_DELITO_CATALOGO', "Delito ID: {$id}", "Delito eliminado: '{$delito['nombre_delito']}'");
                    $_SESSION['flash_mensaje'] = 'Delito eliminado del catálogo con éxito.';
                    $_SESSION['flash_tipo'] = 'success';
                } else {
                    $_SESSION['flash_mensaje'] = 'No se pudo eliminar el delito. Podría estar en uso.';
                    $_SESSION['flash_tipo'] = 'danger';
                }
            }
        }
        $this->redirect('/delitos');
    }
}
