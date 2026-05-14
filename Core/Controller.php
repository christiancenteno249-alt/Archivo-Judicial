<?php
/**
 * Core/Controller.php
 * Clase base de la que heredan todos los controladores MVC.
 * Provee metodos comunes: renderizar vistas, verificar sesion y roles.
 */
class Controller {

    protected PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();

        // Iniciar sesion si no esta activa
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Renderiza una vista pasandole datos como variables locales.
     *
     * @param string $vista  Ruta relativa dentro de Views/ (ej: 'expediente/buscar')
     * @param array  $datos  Variables que estaran disponibles en la vista
     */
    protected function render(string $vista, array $datos = []): void {
        // Extraer el array como variables locales para la vista
        extract($datos);

        $ruta = __DIR__ . '/../Views/' . $vista . '.php';

        if (!file_exists($ruta)) {
            http_response_code(500);
            die("Vista no encontrada: {$vista}");
        }

        require $ruta;
    }

    /**
     * Verifica que el usuario este autenticado.
     * Si no hay sesion activa, redirige al login.
     */
    protected function requireAuth(): void {
        if (empty($_SESSION['usuario_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
    }

    /**
     * Verifica que el usuario tenga rol de administrador.
     * Si no, redirige al inicio.
     */
    protected function requireAdmin(): void {
        $this->requireAuth();
        if (($_SESSION['usuario_rol'] ?? '') !== 'admin') {
            header('Location: ' . BASE_URL . '/');
            exit;
        }
    }

    /**
     * Redirige a una URL y termina la ejecucion.
     */
    protected function redirect(string $url): void {
        header("Location: {$url}");
        exit;
    }

    /**
     * Registra una accion en el log de auditoria.
     * Replica la logica de auditoria_functions.php para uso en los controladores MVC.
     */
    protected function auditoria(string $accion, string $recurso = '', string $detalles = ''): void {
        $id_usuario = $_SESSION['usuario_id'] ?? null;

        $ip = 'DESCONOCIDA';
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        if ($ip === '::1') $ip = '127.0.0.1';

        try {
            $stmt = $this->db->prepare(
                "INSERT INTO auditoria_log (id_usuario, accion, recurso, detalles, ip_maquina, fecha_hora)
                 VALUES (:id_usuario, :accion, :recurso, :detalles, :ip_maquina, NOW())"
            );
            $stmt->execute([
                ':id_usuario' => $id_usuario,
                ':accion'     => $accion,
                ':recurso'    => $recurso,
                ':detalles'   => $detalles,
                ':ip_maquina' => $ip,
            ]);
        } catch (PDOException $e) {
            error_log("Error en auditoria MVC: " . $e->getMessage());
        }
    }
}
