<?php
require_once "conexion.php";

echo "<h2>Verificacion de Estructura de Base de Datos</h2>";

// 1. Verificar columnas en maestro
echo "<h3>1. Columnas en tabla 'maestro':</h3>";
try {
    $stmt = $pdo->query("DESCRIBE maestro");
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td><strong>" . $row['Field'] . "</strong></td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . ($row['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Buscar especificamente las columnas de ubicacion
    $columnas_ubicacion = ['id_sede', 'ubicacion_area', 'ubicacion_detalle', 'fecha_ultima_ubicacion'];
    echo "<h4>Columnas de ubicacion encontradas:</h4><ul>";
    $stmt = $pdo->query("DESCRIBE maestro");
    $columnas_existentes = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if (in_array($row['Field'], $columnas_ubicacion)) {
            $columnas_existentes[] = $row['Field'];
            echo "<li style='color: green;'> " . $row['Field'] . "</li>";
        }
    }
    echo "</ul>";
    
    $columnas_faltantes = array_diff($columnas_ubicacion, $columnas_existentes);
    if (count($columnas_faltantes) > 0) {
        echo "<h4 style='color: red;'> Columnas FALTANTES:</h4><ul>";
        foreach ($columnas_faltantes as $col) {
            echo "<li style='color: red;'>" . $col . "</li>";
        }
        echo "</ul>";
        echo "<p><strong>ACCION REQUERIDA:</strong> Ejecuta el script AGREGAR_COLUMNAS_DIRECTO.sql en phpMyAdmin</p>";
    } else {
        echo "<p style='color: green;'><strong> Todas las columnas de ubicacion existen</strong></p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

// 2. Verificar tabla sedes_deposito
echo "<hr><h3>2. Tabla 'sedes_deposito':</h3>";
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'sedes_deposito'");
    if ($stmt->rowCount() > 0) {
        echo "<p style='color: green;'> La tabla existe</p>";
        
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM sedes_deposito");
        $total = $stmt->fetch()['total'];
        echo "<p>Total de sedes: <strong>" . $total . "</strong></p>";
        
        if ($total > 0) {
            $stmt = $pdo->query("SELECT * FROM sedes_deposito");
            echo "<table border='1' cellpadding='5'>";
            echo "<tr><th>ID</th><th>Nombre</th><th>Activo</th></tr>";
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "<tr>";
                echo "<td>" . $row['id_sede'] . "</td>";
                echo "<td>" . $row['nombre_sede'] . "</td>";
                echo "<td>" . ($row['activo'] == 1 ? ' Activo' : ' Inactivo') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p style='color: orange;'> La tabla existe pero esta vacia</p>";
        }
    } else {
        echo "<p style='color: red;'> La tabla NO existe</p>";
        echo "<p><strong>ACCION REQUERIDA:</strong> Ejecuta el script AGREGAR_COLUMNAS_DIRECTO.sql en phpMyAdmin</p>";
    }
} catch (PDOException $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

// 3. Verificar Foreign Keys
echo "<hr><h3>3. Foreign Keys:</h3>";
try {
    $stmt = $pdo->query("
        SELECT 
            CONSTRAINT_NAME,
            COLUMN_NAME,
            REFERENCED_TABLE_NAME,
            REFERENCED_COLUMN_NAME
        FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'maestro'
        AND COLUMN_NAME = 'id_sede'
        AND REFERENCED_TABLE_NAME IS NOT NULL
    ");
    
    if ($stmt->rowCount() > 0) {
        echo "<p style='color: green;'> Foreign Key configurada</p>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<p>Constraint: <strong>" . $row['CONSTRAINT_NAME'] . "</strong></p>";
        }
    } else {
        echo "<p style='color: orange;'> No hay Foreign Key configurada (esto es opcional)</p>";
    }
} catch (PDOException $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h3>Resumen:</h3>";
echo "<p>Base de datos: <strong>" . $pdo->query("SELECT DATABASE()")->fetchColumn() . "</strong></p>";
echo "<p><a href='gestionar_ubicaciones.php'>Volver a Gestion de Ubicaciones</a></p>";
?>




