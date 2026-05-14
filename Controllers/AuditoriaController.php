<?php
/**
 * Controllers/AuditoriaController.php
 * Delega la auditoría al archivo legacy auditoria.php que ya está bien probado.
 * La migración completa de auditoría puede hacerse en una fase futura.
 */
class AuditoriaController extends Controller {

    public function index(): void {
        $this->requireAdmin();
        require __DIR__ . '/../auditoria.php';
        exit;
    }
}
