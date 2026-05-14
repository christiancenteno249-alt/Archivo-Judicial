<?php
/**
 * Controllers/UbicacionController.php
 * Delega gestión de ubicaciones al archivo legacy gestionar_ubicaciones.php.
 * Este módulo (~47KB con lógica AJAX compleja) se migra en una fase futura.
 */
class UbicacionController extends Controller {

    public function index(): void {
        $this->requireAuth();
        require __DIR__ . '/../gestionar_ubicaciones.php';
        exit;
    }
}
