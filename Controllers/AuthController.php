<?php
/**
 * Controllers/AuthController.php
 * Controlador nativo para el inicio y cierre de sesión de usuarios.
 */
class AuthController extends Controller {

    /**
     * Muestra el formulario de login (GET) y procesa el inicio de sesión (POST).
     */
    public function login(): void {
        // Redirigir al dashboard si ya hay sesión activa
        if (!empty($_SESSION['usuario_id'])) {
            $this->redirect('/');
        }

        $mensaje = '';
        $tipoAlerta = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario = trim($_POST['usuario'] ?? '');
            $password = trim($_POST['password'] ?? '');

            if (empty($usuario) || empty($password)) {
                $mensaje = 'Por favor ingresa usuario y contraseña.';
                $tipoAlerta = 'warning';
            } else {
                try {
                    // Seleccionar usuario activo (status = 1)
                    $stmt = $this->db->prepare(
                        "SELECT * FROM usuarios_sistema WHERE usuario_nick = :usuario AND status = 1 LIMIT 1"
                    );
                    $stmt->execute([':usuario' => $usuario]);
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($user && password_verify($password, $user['password_hash'])) {
                        // Iniciar la sesión de forma segura
                        $_SESSION['usuario_id'] = $user['id_usuario'];
                        $_SESSION['usuario_nombre'] = $user['nombre_full'];
                        $_SESSION['usuario_nick'] = $user['usuario_nick'];
                        $_SESSION['usuario_rol'] = $user['rol'];

                        // Guardar información en auditoría
                        $this->auditoria('LOGIN', $user['usuario_nick'], 'Inicio de sesión exitoso');

                        $this->redirect('/');
                    } else {
                        // Registrar el intento fallido en auditoría de seguridad
                        $this->auditoria('INTENTO_FALLIDO', $usuario, 'Intento de login con credenciales incorrectas o usuario inactivo');
                        $mensaje = 'Usuario o contraseña incorrectos, o usuario inactivo.';
                        $tipoAlerta = 'danger';
                    }
                } catch (PDOException $e) {
                    $mensaje = 'Error en el sistema: ' . $e->getMessage();
                    $tipoAlerta = 'danger';
                }
            }
        }

        $this->render('auth/login', compact('mensaje', 'tipoAlerta'));
    }

    /**
     * Cierra la sesión activa del usuario de forma segura.
     */
    public function logout(): void {
        // Registrar en auditoría antes de limpiar la sesión
        if (!empty($_SESSION['usuario_nick'])) {
            $this->auditoria('LOGOUT', $_SESSION['usuario_nick'], 'Cierre de sesión');
        }

        // Destruir la sesión
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }

        header('Location: ' . BASE_URL . '/login');
        exit;
    }
}
