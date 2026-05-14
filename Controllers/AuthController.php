<?php
/**
 * Controllers/AuthController.php
 * Maneja login y logout. Delega a los archivos legacy estables.
 * El sistema de autenticación (login.php) ya está bien probado —
 * lo usamos directamente en lugar de reescribirlo.
 */
class AuthController extends Controller {

    /**
     * Muestra el formulario de login y procesa el POST.
     * Delega al archivo legacy login.php que ya maneja toda la lógica.
     */
    public function login(): void {
        // Si ya hay sesion activa, redirigir al inicio
        if (!empty($_SESSION['usuario_id'])) {
            $this->redirect('/');
        }

        // El login.php legacy maneja formulario + validación + sesión
        require __DIR__ . '/../login.php';
        exit;
    }

    /**
     * Cierra la sesion del usuario.
     * Delega al archivo legacy logout.php.
     */
    public function logout(): void {
        require __DIR__ . '/../logout.php';
        exit;
    }
}
