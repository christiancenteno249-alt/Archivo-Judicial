<?php
require_once "conexion.php";
require_once "auth_check.php";

// Solo admin puede ver diagnostico
if ($_SESSION['usuario_rol'] !== 'admin') {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnostico de Sedes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/bootstrap-icons.css">
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0"><i class="bi bi-bug me-2"></i>Diagnostico del Sistema de Ubicaciones</h4>
        </div>
        <div class="card-body">
            
            <h5 class="mb-3">1. Verificar Tabla sedes_deposito</h5>
            <?php
            try {
                $check = $pdo->query("SHOW TABLES LIKE 'sedes_deposito'");
                if ($check->rowCount() > 0) {
                    echo '<div class="alert alert-success"><i class="bi bi-check-circle me-2"></i> La tabla <code>sedes_deposito</code> existe</div>';
                    
                    // Contar sedes
                    $count = $pdo->query("SELECT COUNT(*) as total FROM sedes_deposito")->fetch();
                    echo '<p><strong>Total de sedes:</strong> ' . $count['total'] . '</p>';
                    
                    // Mostrar sedes
                    $sedes = $pdo->query("SELECT * FROM sedes_deposito")->fetchAll();
                    if (count($sedes) > 0) {
                        echo '<table class="table table-bordered">';
                        echo '<thead><tr><th>ID</th><th>Nombre</th><th>Direccion</th><th>Descripcion</th><th>Activo</th></tr></thead>';
                        echo '<tbody>';
                        foreach ($sedes as $sede) {
                            $activo = $sede['activo'] == 1 ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>';
                            echo '<tr>';
                            echo '<td>' . $sede['id_sede'] . '</td>';
                            echo '<td>' . htmlspecialchars($sede['nombre_sede']) . '</td>';
                            echo '<td>' . htmlspecialchars($sede['direccion']) . '</td>';
                            echo '<td>' . htmlspecialchars($sede['descripcion']) . '</td>';
                            echo '<td>' . $activo . '</td>';
                            echo '</tr>';
                        }
                        echo '</tbody></table>';
                    } else {
                        echo '<div class="alert alert-warning"><i class="bi bi-exclamation-triangle me-2"></i> La tabla existe pero esta vacia. Ejecuta el script SQL para insertar sedes.</div>';
                    }
                } else {
                    echo '<div class="alert alert-danger"><i class="bi bi-x-circle me-2"></i> La tabla <code>sedes_deposito</code> NO existe. Debes ejecutar el script SQL.</div>';
                }
            } catch (PDOException $e) {
                echo '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
            }
            ?>
            
            <hr class="my-4">
            
            <h5 class="mb-3">2. Verificar Columnas en tabla maestro</h5>
            <?php
            try {
                $columnas = ['id_sede', 'ubicacion_area', 'ubicacion_detalle', 'fecha_ultima_ubicacion'];
                $columnas_existentes = [];
                
                foreach ($columnas as $col) {
                    $check = $pdo->query("SHOW COLUMNS FROM maestro LIKE '$col'");
                    if ($check->rowCount() > 0) {
                        $columnas_existentes[] = $col;
                    }
                }
                
                if (count($columnas_existentes) === count($columnas)) {
                    echo '<div class="alert alert-success"><i class="bi bi-check-circle me-2"></i> Todas las columnas de ubicacion existen en la tabla <code>maestro</code></div>';
                    echo '<ul>';
                    foreach ($columnas_existentes as $col) {
                        echo '<li><code>' . $col . '</code></li>';
                    }
                    echo '</ul>';
                } else {
                    echo '<div class="alert alert-warning"><i class="bi bi-exclamation-triangle me-2"></i> Faltan columnas en la tabla <code>maestro</code></div>';
                    echo '<p><strong>Columnas existentes:</strong></p><ul>';
                    foreach ($columnas_existentes as $col) {
                        echo '<li><code>' . $col . '</code></li>';
                    }
                    echo '</ul>';
                    echo '<p><strong>Columnas faltantes:</strong></p><ul>';
                    foreach (array_diff($columnas, $columnas_existentes) as $col) {
                        echo '<li><code>' . $col . '</code></li>';
                    }
                    echo '</ul>';
                }
            } catch (PDOException $e) {
                echo '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
            }
            ?>
            
            <hr class="my-4">
            
            <h5 class="mb-3">3. Verificar Foreign Key</h5>
            <?php
            try {
                $fk = $pdo->query("
                    SELECT CONSTRAINT_NAME 
                    FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                    WHERE TABLE_SCHEMA = DATABASE() 
                    AND TABLE_NAME = 'maestro' 
                    AND COLUMN_NAME = 'id_sede'
                    AND REFERENCED_TABLE_NAME = 'sedes_deposito'
                ")->fetchAll();
                
                if (count($fk) > 0) {
                    echo '<div class="alert alert-success"><i class="bi bi-check-circle me-2"></i> Foreign Key configurada correctamente</div>';
                    foreach ($fk as $constraint) {
                        echo '<p><code>' . $constraint['CONSTRAINT_NAME'] . '</code></p>';
                    }
                } else {
                    echo '<div class="alert alert-warning"><i class="bi bi-exclamation-triangle me-2"></i> No se encontro Foreign Key entre maestro.id_sede y sedes_deposito.id_sede</div>';
                }
            } catch (PDOException $e) {
                echo '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
            }
            ?>
            
            <hr class="my-4">
            
            <div class="d-flex gap-2">
                <a href="gestionar_ubicaciones.php" class="btn btn-primary">
                    <i class="bi bi-arrow-left me-2"></i>Volver a Gestion de Ubicaciones
                </a>
                <a href="index.php" class="btn btn-secondary">
                    <i class="bi bi-house me-2"></i>Ir al Menu Principal
                </a>
            </div>
            
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>





