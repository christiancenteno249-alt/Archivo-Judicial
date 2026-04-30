<?php
require_once "conexion.php";
require_once "auth_check.php";

// Solo administradores pueden acceder
if ($_SESSION['usuario_rol'] !== 'admin') {
    header('Location: index.php');
    exit;
}

$mensaje = '';
$tipo_alerta = '';
$accion = $_GET['accion'] ?? 'listar';
$id_editar = $_GET['id'] ?? null;

// CREAR USUARIO
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['crear_usuario'])) {
    $nombre = trim($_POST['nombre'] ?? '');
    $usuario = trim($_POST['usuario'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $rol = trim($_POST['rol'] ?? 'operador');
    
    if (empty($nombre) || empty($usuario) || empty($password)) {
        $mensaje = 'Todos los campos son obligatorios.';
        $tipo_alerta = 'warning';
    } else {
        try {
            // Verificar si el usuario ya existe
            $check = $pdo->prepare("SELECT COUNT(*) FROM usuarios_sistema WHERE usuario_nick = :usuario");
            $check->execute([':usuario' => $usuario]);
            
            if ($check->fetchColumn() > 0) {
                $mensaje = 'El nombre de usuario ya existe.';
                $tipo_alerta = 'danger';
            } else {
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                
                $sql = "INSERT INTO usuarios_sistema (nombre_full, usuario_nick, password_hash, rol) 
                        VALUES (:nombre, :usuario, :password_hash, :rol)";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':nombre' => $nombre,
                    ':usuario' => $usuario,
                    ':password_hash' => $password_hash,
                    ':rol' => $rol
                ]);
                
                $mensaje = 'Usuario creado exitosamente.';
                $tipo_alerta = 'success';
            }
        } catch (PDOException $e) {
            $mensaje = 'Error: ' . $e->getMessage();
            $tipo_alerta = 'danger';
        }
    }
}

// EDITAR USUARIO
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['editar_usuario'])) {
    $id = $_POST['id_usuario'];
    $nombre = trim($_POST['nombre'] ?? '');
    $usuario = trim($_POST['usuario'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $rol = trim($_POST['rol'] ?? 'operador');
    
    if (empty($nombre) || empty($usuario)) {
        $mensaje = 'Nombre y usuario son obligatorios.';
        $tipo_alerta = 'warning';
    } else {
        try {
            if (!empty($password)) {
                // Actualizar con nueva contrasena
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $sql = "UPDATE usuarios_sistema 
                        SET nombre_full = :nombre, usuario_nick = :usuario, password_hash = :password_hash, rol = :rol 
                        WHERE id_usuario = :id";
                $params = [
                    ':nombre' => $nombre,
                    ':usuario' => $usuario,
                    ':password_hash' => $password_hash,
                    ':rol' => $rol,
                    ':id' => $id
                ];
            } else {
                // Actualizar sin cambiar contrasena
                $sql = "UPDATE usuarios_sistema 
                        SET nombre_full = :nombre, usuario_nick = :usuario, rol = :rol 
                        WHERE id_usuario = :id";
                $params = [
                    ':nombre' => $nombre,
                    ':usuario' => $usuario,
                    ':rol' => $rol,
                    ':id' => $id
                ];
            }
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
            $mensaje = 'Usuario actualizado exitosamente.';
            $tipo_alerta = 'success';
            $accion = 'listar';
        } catch (PDOException $e) {
            $mensaje = 'Error: ' . $e->getMessage();
            $tipo_alerta = 'danger';
        }
    }
}

// ELIMINAR USUARIO (Borrado Logico)
if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    
    // No permitir eliminar el propio usuario
    if ($id == $_SESSION['usuario_id']) {
        $mensaje = 'No puedes eliminar tu propio usuario.';
        $tipo_alerta = 'danger';
    } else {
        try {
            // Borrado logico: cambiar status a 0 en lugar de DELETE
            $sql = "UPDATE usuarios_sistema SET status = 0 WHERE id_usuario = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':id' => $id]);
            
            $mensaje = 'Usuario desactivado exitosamente.';
            $tipo_alerta = 'success';
        } catch (PDOException $e) {
            $mensaje = 'Error: ' . $e->getMessage();
            $tipo_alerta = 'danger';
        }
    }
}

// OBTENER LISTA DE USUARIOS (solo activos, status = 1)
$usuarios = [];
try {
    $stmt = $pdo->query("SELECT * FROM usuarios_sistema WHERE status = 1 ORDER BY fecha_registro DESC");
    $usuarios = $stmt->fetchAll();
} catch (PDOException $e) {
    $mensaje = 'Error al cargar usuarios: ' . $e->getMessage();
    $tipo_alerta = 'danger';
}

// OBTENER DATOS DEL USUARIO A EDITAR (solo si esta activo)
$usuario_editar = null;
if ($accion === 'editar' && $id_editar) {
    $stmt = $pdo->prepare("SELECT * FROM usuarios_sistema WHERE id_usuario = :id AND status = 1");
    $stmt->execute([':id' => $id_editar]);
    $usuario_editar = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    
    <style>
        :root {
            --institucional-blue: #1a237e;
        }
        body {
            background-image: url('BACKGROUND (1).png');
            background-size: cover;
            background-position: top center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            min-height: 100vh;
            padding-bottom: 50px;
        }
        .container {
            padding-top: 100px;
        }
        .card-usuarios {
            background: white;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.15);
            border: none;
        }
        .card-header-custom {
            background-color: var(--institucional-blue);
            color: white;
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
            padding: 20px;
        }
        .table thead {
            background-color: var(--institucional-blue);
            color: white;
        }
        .badge-admin {
            background-color: #d32f2f;
        }
        .badge-operador {
            background-color: #1976d2;
        }
    </style>
</head>
<body>

<div class="container">
    
    <!-- Boton de Retorno -->
    <div class="mb-4">
        <a href="index.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-2"></i>Volver al Menu
        </a>
    </div>
    
    <!-- Alertas -->
    <?php if (!empty($mensaje)): ?>
        <div class="alert alert-<?= $tipo_alerta ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($mensaje) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if ($accion === 'crear' || $accion === 'editar'): ?>
    
    <!-- FORMULARIO CREAR/EDITAR -->
    <div class="card card-usuarios mb-4">
        <div class="card-header-custom">
            <h4 class="mb-0">
                <i class="bi bi-person-<?= $accion === 'crear' ? 'plus' : 'gear' ?> me-2"></i>
                <?= $accion === 'crear' ? 'Crear Nuevo Usuario' : 'Editar Usuario' ?>
            </h4>
        </div>
        <div class="card-body p-4">
            <form method="POST" action="gestionar_usuarios.php">
                <?php if ($accion === 'editar'): ?>
                    <input type="hidden" name="id_usuario" value="<?= $usuario_editar['id_usuario'] ?>">
                <?php endif; ?>
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Nombre Completo *</label>
                        <input type="text" class="form-control" name="nombre" required 
                               value="<?= $accion === 'editar' ? htmlspecialchars($usuario_editar['nombre_full']) : '' ?>">
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Usuario (Nick) *</label>
                        <input type="text" class="form-control" name="usuario" required 
                               value="<?= $accion === 'editar' ? htmlspecialchars($usuario_editar['usuario_nick']) : '' ?>">
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label fw-bold">
                            Contrasena <?= $accion === 'crear' ? '*' : '(dejar vacio para no cambiar)' ?>
                        </label>
                        <input type="password" class="form-control" name="password" 
                               <?= $accion === 'crear' ? 'required' : '' ?>>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Rol *</label>
                        <select class="form-select" name="rol" required>
                            <option value="operador" <?= ($accion === 'editar' && $usuario_editar['rol'] === 'operador') ? 'selected' : '' ?>>
                                Operador
                            </option>
                            <option value="admin" <?= ($accion === 'editar' && $usuario_editar['rol'] === 'admin') ? 'selected' : '' ?>>
                                Administrador
                            </option>
                        </select>
                    </div>
                </div>
                
                <div class="mt-4 d-flex gap-2">
                    <button type="submit" name="<?= $accion === 'crear' ? 'crear_usuario' : 'editar_usuario' ?>" class="btn btn-primary">
                        <i class="bi bi-save me-2"></i><?= $accion === 'crear' ? 'Crear Usuario' : 'Guardar Cambios' ?>
                    </button>
                    <a href="gestionar_usuarios.php" class="btn btn-secondary">
                        <i class="bi bi-x-circle me-2"></i>Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <?php endif; ?>
    
    <!-- LISTA DE USUARIOS -->
    <div class="card card-usuarios">
        <div class="card-header-custom d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><i class="bi bi-people me-2"></i>Usuarios del Sistema</h4>
            <a href="gestionar_usuarios.php?accion=crear" class="btn btn-light btn-sm">
                <i class="bi bi-person-plus me-1"></i>Nuevo Usuario
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">ID</th>
                            <th>Nombre Completo</th>
                            <th>Usuario</th>
                            <th>Rol</th>
                            <th>Fecha Registro</th>
                            <th class="pe-4 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $user): ?>
                        <tr>
                            <td class="ps-4"><?= $user['id_usuario'] ?></td>
                            <td><?= htmlspecialchars($user['nombre_full']) ?></td>
                            <td><code><?= htmlspecialchars($user['usuario_nick']) ?></code></td>
                            <td>
                                <span class="badge badge-<?= $user['rol'] ?>">
                                    <?= $user['rol'] === 'admin' ? 'Administrador' : 'Operador' ?>
                                </span>
                            </td>
                            <td><?= date('d/m/Y H:i', strtotime($user['fecha_registro'])) ?></td>
                            <td class="pe-4 text-center">
                                <a href="gestionar_usuarios.php?accion=editar&id=<?= $user['id_usuario'] ?>" 
                                   class="btn btn-sm btn-warning" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <?php if ($user['id_usuario'] != $_SESSION['usuario_id']): ?>
                                <a href="gestionar_usuarios.php?eliminar=<?= $user['id_usuario'] ?>" 
                                   class="btn btn-sm btn-danger" 
                                   data-confirm-message="Estas seguro de eliminar este usuario?"
                                   data-confirm-title="Eliminar usuario"
                                   data-confirm-ok="Si, eliminar"
                                   data-confirm-cancel="Cancelar"
                                   title="Eliminar">
                                    <i class="bi bi-trash"></i>
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/app-alerts.js"></script>
</body>
</html>





