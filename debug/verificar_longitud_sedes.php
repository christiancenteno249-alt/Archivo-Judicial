<?php
require_once "conexion.php";
require_once "auth_check.php";

// Solo admin
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
    <title>Verificacion de Longitud de Sedes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/bootstrap-icons.css">
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="card shadow">
        <div class="card-header bg-success text-white">
            <h4 class="mb-0"><i class="bi bi-check-circle me-2"></i>Verificacion de Longitud de Nombres de Sede</h4>
        </div>
        <div class="card-body">
            
            <h5 class="mb-3">1. Estructura del Campo en Base de Datos</h5>
            <?php
            try {
                $stmt = $pdo->query("
                    SELECT 
                        COLUMN_NAME,
                        DATA_TYPE,
                        CHARACTER_MAXIMUM_LENGTH,
                        COLUMN_TYPE,
                        IS_NULLABLE,
                        COLUMN_KEY
                    FROM INFORMATION_SCHEMA.COLUMNS
                    WHERE TABLE_SCHEMA = DATABASE()
                    AND TABLE_NAME = 'sedes_deposito'
                    AND COLUMN_NAME = 'nombre_sede'
                ");
                
                $columna = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($columna) {
                    echo '<table class="table table-bordered">';
                    echo '<tr><th>Propiedad</th><th>Valor</th><th>Estado</th></tr>';
                    
                    $longitud = $columna['CHARACTER_MAXIMUM_LENGTH'];
                    $estado_longitud = $longitud >= 255 ? '<span class="badge bg-success"> OK</span>' : '<span class="badge bg-danger"> Muy corto</span>';
                    
                    echo '<tr>';
                    echo '<td><strong>Tipo de Dato</strong></td>';
                    echo '<td>' . $columna['COLUMN_TYPE'] . '</td>';
                    echo '<td>' . $estado_longitud . '</td>';
                    echo '</tr>';
                    
                    echo '<tr>';
                    echo '<td><strong>Longitud Maxima</strong></td>';
                    echo '<td>' . $longitud . ' caracteres</td>';
                    echo '<td>' . $estado_longitud . '</td>';
                    echo '</tr>';
                    
                    echo '<tr>';
                    echo '<td><strong>Permite NULL</strong></td>';
                    echo '<td>' . $columna['IS_NULLABLE'] . '</td>';
                    echo '<td><span class="badge bg-info">Info</span></td>';
                    echo '</tr>';
                    
                    echo '<tr>';
                    echo '<td><strong>Clave</strong></td>';
                    echo '<td>' . ($columna['COLUMN_KEY'] ?: 'Ninguna') . '</td>';
                    echo '<td><span class="badge bg-info">Info</span></td>';
                    echo '</tr>';
                    
                    echo '</table>';
                    
                    if ($longitud < 255) {
                        echo '<div class="alert alert-warning">';
                        echo '<i class="bi bi-exclamation-triangle me-2"></i>';
                        echo '<strong>ACCION REQUERIDA:</strong> El campo solo soporta ' . $longitud . ' caracteres. ';
                        echo 'Ejecuta el script <code>ampliar_campo_nombre_sede.sql</code> para ampliarlo a 255 caracteres.';
                        echo '</div>';
                    } else {
                        echo '<div class="alert alert-success">';
                        echo '<i class="bi bi-check-circle me-2"></i>';
                        echo '<strong> CORRECTO:</strong> El campo soporta hasta 255 caracteres.';
                        echo '</div>';
                    }
                } else {
                    echo '<div class="alert alert-danger"> No se encontro el campo nombre_sede</div>';
                }
            } catch (PDOException $e) {
                echo '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
            }
            ?>
            
            <hr class="my-4">
            
            <h5 class="mb-3">2. Sedes Actuales y Longitud de sus Nombres</h5>
            <?php
            try {
                $stmt = $pdo->query("
                    SELECT 
                        id_sede,
                        nombre_sede,
                        LENGTH(nombre_sede) as longitud_actual,
                        activo
                    FROM sedes_deposito
                    ORDER BY longitud_actual DESC
                ");
                
                $sedes = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (count($sedes) > 0) {
                    echo '<table class="table table-bordered table-hover">';
                    echo '<thead class="table-success">';
                    echo '<tr>';
                    echo '<th>ID</th>';
                    echo '<th>Nombre de la Sede</th>';
                    echo '<th>Longitud</th>';
                    echo '<th>Estado</th>';
                    echo '<th>Alerta</th>';
                    echo '</tr>';
                    echo '</thead>';
                    echo '<tbody>';
                    
                    foreach ($sedes as $sede) {
                        $longitud = $sede['longitud_actual'];
                        $alerta = '';
                        
                        if ($longitud > 100) {
                            $alerta = '<span class="badge bg-warning"> Nombre largo</span>';
                        } elseif ($longitud > 200) {
                            $alerta = '<span class="badge bg-danger"> Muy largo</span>';
                        } else {
                            $alerta = '<span class="badge bg-success"> OK</span>';
                        }
                        
                        $estado = $sede['activo'] == 1 ? '<span class="badge bg-success">Activa</span>' : '<span class="badge bg-secondary">Inactiva</span>';
                        
                        echo '<tr>';
                        echo '<td>' . $sede['id_sede'] . '</td>';
                        echo '<td><small>' . htmlspecialchars($sede['nombre_sede']) . '</small></td>';
                        echo '<td><strong>' . $longitud . '</strong> / 255</td>';
                        echo '<td>' . $estado . '</td>';
                        echo '<td>' . $alerta . '</td>';
                        echo '</tr>';
                    }
                    
                    echo '</tbody>';
                    echo '</table>';
                    
                    // Estadisticas
                    $max_longitud = max(array_column($sedes, 'longitud_actual'));
                    $promedio = round(array_sum(array_column($sedes, 'longitud_actual')) / count($sedes), 2);
                    
                    echo '<div class="alert alert-info">';
                    echo '<strong>Estadisticas:</strong><br>';
                    echo ' Nombre mas largo: <strong>' . $max_longitud . ' caracteres</strong><br>';
                    echo ' Promedio: <strong>' . $promedio . ' caracteres</strong><br>';
                    echo ' Total de sedes: <strong>' . count($sedes) . '</strong>';
                    echo '</div>';
                    
                } else {
                    echo '<div class="alert alert-warning">No hay sedes registradas</div>';
                }
            } catch (PDOException $e) {
                echo '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
            }
            ?>
            
            <hr class="my-4">
            
            <h5 class="mb-3">3. Prueba de Guardado</h5>
            <p>Para probar que los nombres largos se guardan correctamente:</p>
            <ol>
                <li>Ve a <a href="gestionar_sedes.php" target="_blank">Gestionar Sedes</a></li>
                <li>Edita una sede existente</li>
                <li>Cambia el nombre a uno muy largo (ej: "JUZGADO DE PRIMERA INSTANCIA EN LO CIVIL, MERCANTIL, TRANSITO Y BANCARIO DE LA CIRCUNSCRIPCION JUDICIAL DEL ESTADO ARAGUA - DEPOSITO CENTRAL DE EXPEDIENTES HISTORICOS Y ACTIVOS")</li>
                <li>Guarda los cambios</li>
                <li>Recarga esta pagina para verificar que se guardo completo</li>
            </ol>
            
            <hr class="my-4">
            
            <div class="d-flex gap-2">
                <a href="gestionar_sedes.php" class="btn btn-success">
                    <i class="bi bi-building me-2"></i>Ir a Gestionar Sedes
                </a>
                <a href="index.php" class="btn btn-secondary">
                    <i class="bi bi-house me-2"></i>Menu Principal
                </a>
            </div>
            
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>





