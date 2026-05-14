<?php
/**
 * Controllers/RespaldoController.php
 * Delega el respaldo al archivo legacy respaldar_bd.php.
 */
class RespaldoController extends Controller {

    public function index(): void {
        $this->requireAdmin();
        require __DIR__ . '/../respaldar_bd.php';
        exit;
    }
}
