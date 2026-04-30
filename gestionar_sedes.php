<?php
require_once "conexion.php";
require_once "auth_check.php";
require_once "auditoria_functions.php";

// Solo administradores pueden gestionar sedes
if ($_SESSION['usuario_rol'] !== 'admin') {
    header('Location: index.php');
    exit;
}

$mensaje = '';
$tipo_alerta = '';
$accion = $_GET['accion'] ?? 'listar';
$id_editar = $_GET['id'] ?? null;

// CREAR SEDE
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['crear_sede'])) {
    $nombre = trim($_POST['nombre_sede'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    
    if (empty($nombre)) {
        $mensaje = 'El nombre de la sede es obligatorio.';
        $tipo_alerta = 'warning';
    } else {
        try {
            // Verificar si ya existe
            $check = $pdo->prepare("SELECT COUNT(*) FROM sedes_deposito WHERE nombre_sede = :nombre");
            $check->execute([':nombre' => $nombre]);
            
            if ($check->fetchColumn() > 0) {
                $mensaje = 'Ya existe una sede con ese nombre.';
                $tipo_alerta = 'danger';
            } else {
                $sql = "INSERT INTO sedes_deposito (nombre_sede, direccion, descripcion, activo) 
                        VALUES (:nombre, :direccion, :descripcion, 1)";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':nombre' => $nombre,
                    ':direccion' => $direccion,
                    ':descripcion' => $descripcion
                ]);
                
                // Auditoria
                registrarAccion('CREAR_SEDE', $nombre, "Nueva sede creada: {$nombre}");
                
                $mensaje = 'Sede creada exitosamente.';
                $tipo_alerta = 'success';
                $accion = 'listar';
            }
        } catch (PDOException $e) {
            $mensaje = 'Error: ' . $e->getMessage();
            $tipo_alerta = 'danger';
        }
    }
}

// EDITAR SEDE
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['editar_sede'])) {
    $id = $_POST['id_sede'];
    $nombre = trim($_POST['nombre_sede'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    
    if (empty($nombre)) {
        $mensaje = 'El nombre de la sede es obligatorio.';
        $tipo_alerta = 'warning';
    } else {
        try {
            $sql = "UPDATE sedes_deposito 
                    SET nombre_sede = :nombre, direccion = :direccion, descripcion = :descripcion 
                    WHERE id_sede = :id";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nombre' => $nombre,
                ':direccion' => $direccion,
                ':descripcion' => $descripcion,
                ':id' => $id
            ]);
            
            // Auditoria
            registrarAccion('EDITAR_SEDE', $nombre, "Sede actualizada: {$nombre}");
            
            $mensaje = 'Sede actualizada exitosamente.';
            $tipo_alerta = 'success';
            $accion = 'listar';
        } catch (PDOException $e) {
            $mensaje = 'Error: ' . $e->getMessage();
            $tipo_alerta = 'danger';
        }
    }
}

// CAMBIAR ESTADO (Activar/Desactivar)
if (isset($_GET['toggle'])) {
    $id = $_GET['toggle'];
    
    try {
        // Obtener estado actual
        $stmt = $pdo->prepare("SELECT nombre_sede, activo FROM sedes_deposito WHERE id_sede = :id");
        $stmt->execute([':id' => $id]);
        $sede = $stmt->fetch();
        
        if ($sede) {
            $nuevo_estado = $sede['activo'] == 1 ? 0 : 1;
            $accion_texto = $nuevo_estado == 1 ? 'activada' : 'desactivada';
            
            $sql = "UPDATE sedes_deposito SET activo = :estado WHERE id_sede = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':estado' => $nuevo_estado, ':id' => $id]);
            
            // Auditoria
            registrarAccion('CAMBIAR_ESTADO_SEDE', $sede['nombre_sede'], "Sede {$accion_texto}: {$sede['nombre_sede']}");
            
            $mensaje = "Sede {$accion_texto} exitosamente.";
            $tipo_alerta = 'success';
        }
    } catch (PDOException $e) {
        $mensaje = 'Error: ' . $e->getMessage();
        $tipo_alerta = 'danger';
    }
}

// OBTENER LISTA DE SEDES
$sedes = [];
try {
    $stmt = $pdo->query("SELECT * FROM sedes_deposito ORDER BY activo DESC, nombre_sede ASC");
    $sedes = $stmt->fetchAll();
} catch (PDOException $e) {
    $mensaje = 'Error al cargar sedes: ' . $e->getMessage();
    $tipo_alerta = 'danger';
}

// OBTENER DATOS DE SEDE A EDITAR
$sede_editar = null;
if ($accion === 'editar' && $id_editar) {
    $stmt = $pdo->prepare("SELECT * FROM sedes_deposito WHERE id_sede = :id");
    $stmt->execute([':id' => $id_editar]);
    $sede_editar = $stmt->fetch();
}

// CONTAR EXPEDIENTES POR SEDE
$expedientes_por_sede = [];
try {
    $stmt = $pdo->query("
        SELECT s.id_sede, s.nombre_sede, COUNT(m.Id) as total
        FROM sedes_deposito s
        LEFT JOIN maestro m ON s.id_sede = m.id_sede
        GROUP BY s.id_sede, s.nombre_sede
    ");
    while ($row = $stmt->fetch()) {
        $expedientes_por_sede[$row['id_sede']] = $row['total'];
    }
} catch (PDOException $e) {
    // Silencioso
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Sedes</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    
    <style>
        :root {
            --institucional-blue: #004085;
            --sede-blue: #0056b3;
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
            background-color: #FFFFFF;
        }
        .container {
            padding-top: 100px;
        }
        .card-sedes {
            background: #FFFFFF;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0,64,133,0.15);
            border: none;
        }
        .card-header-custom {
            background: linear-gradient(135deg, #004085 0%, #0056b3 100%);
            color: white;
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
            padding: 20px;
        }
        .table thead {
            background-color: var(--sede-blue);
            color: white;
        }
        .badge-activo {
            background-color: #0056b3;
        }
        .badge-inactivo {
            background-color: #757575;
        }
        
        /* Truncado de texto con tooltip */
        .truncate-text {
            max-width: 250px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            display: inline-block;
            vertical-align: middle;
            cursor: help;
        }
        .truncate-direccion {
            max-width: 200px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            display: block;
        }
    </style>
</head>
<body>

<div class="container">
    
    <!-- Boton de Retorno -->
    <div class="mb-4">
        <a href="gestionar_ubicaciones.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-2"></i>Volver a Ubicaciones
        </a>
        <a href="index.php" class="btn btn-outline-secondary">
            <i class="bi bi-house me-2"></i>Menu Principal
        </a>
    </div>
    
    <!-- Alertas -->
    <?php if (!empty($mensaje)): ?>
        <div class="alert alert-<?= $tipo_alerta ?> alert-dismissible fade show" role="alert">
            <?= $mensaje ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if ($accion === 'crear' || $accion === 'editar'): ?>
    
    <!-- FORMULARIO CREAR/EDITAR -->
    <div class="card card-sedes mb-4">
        <div class="card-header-custom">
            <h4 class="mb-0">
                <i class="bi bi-<?= $accion === 'crear' ? 'plus-circle' : 'pencil-square' ?> me-2"></i>
                <?= $accion === 'crear' ? 'Crear Nueva Sede' : 'Editar Sede' ?>
            </h4>
        </div>
        <div class="card-body p-4">
            <form method="POST" action="gestionar_sedes.php">
                <?php if ($accion === 'editar'): ?>
                    <input type="hidden" name="id_sede" value="<?= $sede_editar['id_sede'] ?>">
                <?php endif; ?>
                
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label fw-bold">Nombre de la Sede *</label>
                        <input type="text" class="form-control" name="nombre_sede" required 
                               maxlength="255"
                               placeholder="Ej: Galpon Palo Negro - Deposito Central"
                               value="<?= $accion === 'editar' ? htmlspecialchars($sede_editar['nombre_sede']) : '' ?>">
                        <small class="text-muted">Maximo 255 caracteres</small>
                    </div>
                    
                    <div class="col-md-12">
                        <label class="form-label fw-bold">Direccion Completa</label>
                        <textarea class="form-control" name="direccion" rows="2" 
                                  placeholder="Ej: Zona Industrial Palo Negro, Sector Valle Lindo, Frente al Cementerio Municipal..."><?= $accion === 'editar' ? htmlspecialchars($sede_editar['direccion']) : '' ?></textarea>
                    </div>
                    
                    <div class="col-md-12">
                        <label class="form-label fw-bold">Descripcion</label>
                        <textarea class="form-control" name="descripcion" rows="3" 
                                  placeholder="Descripcion detallada de la sede, su proposito y caracteristicas..."><?= $accion === 'editar' ? htmlspecialchars($sede_editar['descripcion']) : '' ?></textarea>
                    </div>
                </div>
                
                <div class="mt-4 d-flex gap-2">
                    <button type="submit" name="<?= $accion === 'crear' ? 'crear_sede' : 'editar_sede' ?>" class="btn btn-primary">
                        <i class="bi bi-save me-2"></i><?= $accion === 'crear' ? 'Crear Sede' : 'Guardar Cambios' ?>
                    </button>
                    <a href="gestionar_sedes.php" class="btn btn-secondary">
                        <i class="bi bi-x-circle me-2"></i>Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <?php endif; ?>
    
    <!-- LISTA DE SEDES -->
    <div class="card card-sedes">
        <div class="card-header-custom d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><i class="bi bi-building me-2"></i>Sedes de Deposito</h4>
            <a href="gestionar_sedes.php?accion=crear" class="btn btn-light btn-sm">
                <i class="bi bi-plus-circle me-1"></i>Nueva Sede
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">Nombre de la Sede</th>
                            <th>Direccion</th>
                            <th>Expedientes</th>
                            <th>Estado</th>
                            <th class="pe-4 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($sedes) > 0): ?>
                            <?php foreach ($sedes as $sede): ?>
                            <tr>
                                <td class="ps-4" title="<?= htmlspecialchars($sede['nombre_sede']) ?>">
                                    <strong class="truncate-text"><?= htmlspecialchars($sede['nombre_sede']) ?></strong>
                                    <?php if (!empty($sede['descripcion'])): ?>
                                        <br><small class="text-muted truncate-text" title="<?= htmlspecialchars($sede['descripcion']) ?>"><?= htmlspecialchars(substr($sede['descripcion'], 0, 80)) ?>...</small>
                                    <?php endif; ?>
                                </td>
                                <td title="<?= htmlspecialchars($sede['direccion'] ?? 'Sin direccion') ?>">
                                    <?php if (!empty($sede['direccion'])): ?>
                                        <small class="truncate-direccion"><?= htmlspecialchars($sede['direccion']) ?></small>
                                    <?php else: ?>
                                        <span class="text-muted">Sin direccion</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-info">
                                        <?= $expedientes_por_sede[$sede['id_sede']] ?? 0 ?> expedientes
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-<?= $sede['activo'] == 1 ? 'activo' : 'inactivo' ?>">
                                        <?= $sede['activo'] == 1 ? 'Activa' : 'Inactiva' ?>
                                    </span>
                                </td>
                                <td class="pe-4 text-center">
                                    <div class="btn-group" role="group">
                                        <a href="gestionar_sedes.php?accion=editar&id=<?= $sede['id_sede'] ?>" 
                                           class="btn btn-sm btn-warning" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="gestionar_sedes.php?toggle=<?= $sede['id_sede'] ?>" 
                                           class="btn btn-sm btn-<?= $sede['activo'] == 1 ? 'secondary' : 'success' ?>" 
                                           data-confirm-message="Estas seguro de <?= $sede['activo'] == 1 ? 'desactivar' : 'activar' ?> esta sede?"
                                           data-confirm-title="Confirmar cambio de estado"
                                           data-confirm-ok="Si, continuar"
                                           data-confirm-cancel="No"
                                           title="<?= $sede['activo'] == 1 ? 'Desactivar' : 'Activar' ?>">
                                            <i class="bi bi-<?= $sede['activo'] == 1 ? 'toggle-off' : 'toggle-on' ?>"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1"></i>
                                    <p class="mt-3">No hay sedes registradas</p>
                                    <a href="gestionar_sedes.php?accion=crear" class="btn btn-primary">
                                        <i class="bi bi-plus-circle me-2"></i>Crear Primera Sede
                                    </a>
                                </td>
                            </tr>
                        <?php endif; ?>
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





