<?php
session_start();
require_once "conexion.php";
require_once "auditoria_functions.php";

// Si ya esta logueado, redirigir al index
if (isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}

$mensaje = '';
$tipo_alerta = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = trim($_POST['usuario'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    if (empty($usuario) || empty($password)) {
        $mensaje = 'Por favor ingresa usuario y contrasena.';
        $tipo_alerta = 'warning';
    } else {
        try {
            // Solo permitir login a usuarios activos (status = 1)
            $sql = "SELECT * FROM usuarios_sistema WHERE usuario_nick = :usuario AND status = 1 LIMIT 1";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':usuario' => $usuario]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password_hash'])) {
                // Login exitoso
                $_SESSION['usuario_id'] = $user['id_usuario'];
                $_SESSION['usuario_nombre'] = $user['nombre_full'];
                $_SESSION['usuario_nick'] = $user['usuario_nick'];
                $_SESSION['usuario_rol'] = $user['rol'];
                
                // REGISTRAR LOGIN EXITOSO EN AUDITORIA
                registrarAccion('LOGIN', $user['usuario_nick'], 'Inicio de sesion exitoso');
                
                header('Location: index.php');
                exit;
            } else {
                // REGISTRAR INTENTO FALLIDO EN AUDITORIA
                registrarAccion('INTENTO_FALLIDO', $usuario, 'Intento de login con credenciales incorrectas o usuario inactivo');
                
                $mensaje = 'Usuario o contrasena incorrectos, o usuario inactivo.';
                $tipo_alerta = 'danger';
            }
        } catch (PDOException $e) {
            $mensaje = 'Error en el sistema: ' . $e->getMessage();
            $tipo_alerta = 'danger';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesion - Sistema Judicial</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/bootstrap-icons.css">
    
    <style>
        :root {
            --institucional-blue: #1a237e;
        }
        body {
            background-image: url('/background.png');
            background-size: cover;
            background-position: top center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            max-width: 450px;
            width: 100%;
        }
        .login-header {
            background-color: var(--institucional-blue);
            color: white;
            padding: 30px;
            border-top-left-radius: 16px;
            border-top-right-radius: 16px;
            text-align: center;
        }
        .login-body {
            padding: 40px;
        }
        .btn-login {
            background-color: var(--institucional-blue);
            border: none;
            padding: 12px;
            font-weight: 600;
            font-size: 1.1rem;
        }
        .btn-login:hover {
            background-color: #0d47a1;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="login-header">
        <i class="bi bi-shield-lock fs-1 mb-3"></i>
        <h3 class="mb-0">Sistema de Archivo Judicial</h3>
        <p class="mb-0 mt-2">Iniciar Sesion</p>
    </div>
    
    <div class="login-body">
        <?php if (!empty($mensaje)): ?>
            <div class="alert alert-<?= $tipo_alerta ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($mensaje) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="login.php">
            <div class="mb-4">
                <label for="usuario" class="form-label fw-bold">Usuario</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                    <input type="text" class="form-control" id="usuario" name="usuario" required autofocus>
                </div>
            </div>
            
            <div class="mb-4">
                <label for="password" class="form-label fw-bold">Contrasena</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
            </div>
            
            <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-login">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Ingresar
                </button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>





