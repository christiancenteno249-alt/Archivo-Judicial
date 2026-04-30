<?php
// Archivo temporal para diagnosticar el problema de carga por lote
require_once "conexion.php";
require_once "auth_check.php";

echo "<h2>Debug Carga por Lote</h2>";
echo "<hr>";

// 1. Verificar si llegan datos POST
echo "<h3>1. Datos POST Recibidos:</h3>";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    echo "<pre style='background: #f5f5f5; padding: 10px;'>";
    print_r($_POST);
    echo "</pre>";
    
    // 2. Verificar datos específicos
    echo "<h3>2. Datos Específicos:</h3>";
    $expedientes_ids = $_POST['expedientes_seleccionados'] ?? [];
    $id_sede = trim($_POST['id_sede_lote'] ?? '');
    $ubicacion_area = trim($_POST['ubicacion_area_lote'] ?? '');
    $ubicacion_detalle = trim($_POST['ubicacion_detalle_lote'] ?? '');
    
    echo "<p><strong>Expedientes IDs:</strong> " . print_r($expedientes_ids, true) . "</p>";
    echo "<p><strong>ID Sede:</strong> '$id_sede'</p>";
    echo "<p><strong>Área:</strong> '$ubicacion_area'</p>";
    echo "<p><strong>Detalle:</strong> '$ubicacion_detalle'</p>";
    
    // 3. Verificar si la sede existe
    if (!empty($id_sede)) {
        echo "<h3>3. Verificación de Sede:</h3>";
        try {
            $stmtSede = $pdo->prepare("SELECT id_sede, nombre_sede FROM sedes_deposito WHERE id_sede = :id_sede");
            $stmtSede->execute([':id_sede' => $id_sede]);
            $sede = $stmtSede->fetch();
            
            if ($sede) {
                echo "<p style='color: green;'>✓ Sede encontrada: " . $sede['nombre_sede'] . "</p>";
            } else {
                echo "<p style='color: red;'>✗ Sede NO encontrada con ID: $id_sede</p>";
            }
        } catch (Exception $e) {
            echo "<p style='color: red;'>Error al buscar sede: " . $e->getMessage() . "</p>";
        }
    }
    
    // 4. Verificar expedientes
    if (!empty($expedientes_ids)) {
        echo "<h3>4. Verificación de Expedientes:</h3>";
        foreach ($expedientes_ids as $id_expediente) {
            $id_expediente = (int)$id_expediente;
            try {e
                $stmtExp = $pdo->prepare("SELECT Id, n_expediente FROM maestro WHERE Id = :id LIMIT 1");
                $stmtExp->execute([':id' => $id_expediente]);
                $expediente = $stmtExp->fetch();
                
                if ($expediente) {
                    echo "<p style='color: green;'>✓ Expediente ID $id_expediente: " . $expediente['n_expediente'] . "</p>";
                } else {
                    echo "<p style='color: red;'>✗ Expediente ID $id_expediente: NO encontrado</p>";
                }
            } catch (Exception $e) {
                echo "<p style='color: red;'>Error al buscar expediente ID $id_expediente: " . $e->getMessage() . "</p>";
            }
        }
    }
    
    // 5. Simular UPDATE (sin ejecutar)
    if (!empty($expedientes_ids) && !empty($id_sede)) {
        echo "<h3>5. SQL que se ejecutaría:</h3>";
        echo "<pre style='background: #e3f2fd; padding: 10px;'>";
        echo "UPDATE maestro \n";
        echo "SET id_sede = $id_sede, \n";
        echo "    ubicacion_area = '$ubicacion_area', \n";
        echo "    ubicacion_detalle = '$ubicacion_detalle',\n";
        echo "    fecha_ultima_ubicacion = NOW()\n";
        echo "WHERE Id IN (" . implode(', ', array_map('intval', $expedientes_ids)) . ")";
        echo "</pre>";
    }
    
} else {
    echo "<p>No se recibieron datos POST. Usa el formulario del gestor de ubicaciones.</p>";
}

echo "<hr>";
echo "<p><a href='gestionar_ubicaciones.php?modo=lote'>← Volver al Gestor de Ubicaciones</a></p>";
?>