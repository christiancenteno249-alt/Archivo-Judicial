<?php
// Diagnóstico completo del problema de carga por lote
require_once "conexion.php";
require_once "auth_check.php";
require_once "auditoria_functions.php";

echo "<h2>Debug Completo - Carga por Lote</h2>";
echo "<hr>";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    echo "<h3>✅ 1. Datos POST Recibidos:</h3>";
    echo "<pre style='background: #f5f5f5; padding: 10px;'>";
    print_r($_POST);
    echo "</pre>";
    
    $expedientes_ids = $_POST['expedientes_seleccionados'] ?? [];
    $id_sede = trim($_POST['id_sede_lote'] ?? '');
    $ubicacion_area = trim($_POST['ubicacion_area_lote'] ?? '');
    $ubicacion_detalle = trim($_POST['ubicacion_detalle_lote'] ?? '');
    
    echo "<h3>✅ 2. Variables Procesadas:</h3>";
    echo "<p><strong>Expedientes IDs:</strong> " . json_encode($expedientes_ids) . "</p>";
    echo "<p><strong>ID Sede:</strong> '$id_sede'</p>";
    echo "<p><strong>Área:</strong> '$ubicacion_area'</p>";
    echo "<p><strong>Detalle:</strong> '$ubicacion_detalle'</p>";
    
    // Validación paso a paso
    echo "<h3>🔍 3. Validaciones:</h3>";
    
    if (empty($expedientes_ids)) {
        echo "<p style='color: red;'>❌ ERROR: No hay expedientes seleccionados</p>";
        exit;
    } else {
        echo "<p style='color: green;'>✅ Expedientes seleccionados: " . count($expedientes_ids) . "</p>";
    }
    
    if (empty($id_sede)) {
        echo "<p style='color: red;'>❌ ERROR: No hay sede seleccionada</p>";
        exit;
    } else {
        echo "<p style='color: green;'>✅ Sede seleccionada: $id_sede</p>";
    }
    
    // Verificar sede
    echo "<h3>🏢 4. Verificación de Sede:</h3>";
    try {
        $stmtSede = $pdo->prepare("SELECT id_sede, nombre_sede FROM sedes_deposito WHERE id_sede = :id_sede");
        $stmtSede->execute([':id_sede' => $id_sede]);
        $sede = $stmtSede->fetch();
        
        if ($sede) {
            $nombre_sede = $sede['nombre_sede'];
            echo "<p style='color: green;'>✅ Sede encontrada: $nombre_sede</p>";
        } else {
            echo "<p style='color: red;'>❌ ERROR: Sede no encontrada con ID: $id_sede</p>";
            exit;
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ ERROR al buscar sede: " . $e->getMessage() . "</p>";
        exit;
    }
    
    // Verificar expedientes
    echo "<h3>📋 5. Verificación de Expedientes:</h3>";
    $expedientes_validos = [];
    foreach ($expedientes_ids as $id_expediente) {
        $id_expediente = (int)$id_expediente;
        try {
            $stmtCheck = $pdo->prepare("SELECT Id, n_expediente FROM maestro WHERE Id = :id LIMIT 1");
            $stmtCheck->execute([':id' => $id_expediente]);
            $expediente_data = $stmtCheck->fetch();
            
            if ($expediente_data) {
                $expedientes_validos[] = $expediente_data;
                echo "<p style='color: green;'>✅ Expediente ID $id_expediente: " . $expediente_data['n_expediente'] . "</p>";
            } else {
                echo "<p style='color: red;'>❌ Expediente ID $id_expediente: NO encontrado</p>";
            }
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Error al verificar expediente ID $id_expediente: " . $e->getMessage() . "</p>";
        }
    }
    
    if (empty($expedientes_validos)) {
        echo "<p style='color: red;'>❌ ERROR: No hay expedientes válidos para procesar</p>";
        exit;
    }
    
    // Simular el proceso de actualización
    echo "<h3>🔄 6. Simulación de Actualización:</h3>";
    
    try {
        echo "<p>🔄 Iniciando transacción...</p>";
        $pdo->beginTransaction();
        
        $actualizados = 0;
        $expedientes_procesados = [];
        
        foreach ($expedientes_validos as $expediente_data) {
            $id_expediente = $expediente_data['Id'];
            $n_expediente = $expediente_data['n_expediente'];
            
            echo "<p>🔄 Procesando expediente: $n_expediente (ID: $id_expediente)</p>";
            
            // Preparar UPDATE
            $sqlUpdate = "UPDATE maestro 
                          SET id_sede = :id_sede, 
                              ubicacion_area = :ubicacion_area, 
                              ubicacion_detalle = :ubicacion_detalle,
                              fecha_ultima_ubicacion = NOW()
                          WHERE Id = :id";
            
            echo "<p style='background: #e3f2fd; padding: 5px; font-family: monospace;'>SQL: $sqlUpdate</p>";
            echo "<p style='background: #e8f5e8; padding: 5px;'>Parámetros: id_sede=$id_sede, area='$ubicacion_area', detalle='$ubicacion_detalle', id=$id_expediente</p>";
            
            $stmtUpdate = $pdo->prepare($sqlUpdate);
            $resultado = $stmtUpdate->execute([
                ':id_sede' => $id_sede,
                ':ubicacion_area' => $ubicacion_area,
                ':ubicacion_detalle' => $ubicacion_detalle,
                ':id' => $id_expediente
            ]);
            
            $filasAfectadas = $stmtUpdate->rowCount();
            
            if ($resultado) {
                echo "<p style='color: green;'>✅ UPDATE ejecutado correctamente</p>";
                echo "<p style='color: blue;'>📊 Filas afectadas: $filasAfectadas</p>";
                
                if ($filasAfectadas > 0) {
                    $actualizados++;
                    $expedientes_procesados[] = $id_expediente;
                    echo "<p style='color: green;'>✅ Expediente $n_expediente actualizado exitosamente</p>";
                } else {
                    echo "<p style='color: orange;'>⚠️ UPDATE ejecutado pero no afectó filas (posiblemente los datos ya eran iguales)</p>";
                }
            } else {
                echo "<p style='color: red;'>❌ ERROR en UPDATE</p>";
                $errorInfo = $stmtUpdate->errorInfo();
                echo "<p style='color: red;'>Error Info: " . print_r($errorInfo, true) . "</p>";
            }
            
            echo "<hr style='margin: 10px 0;'>";
        }
        
        echo "<h3>📊 7. Resultado Final:</h3>";
        echo "<p><strong>Expedientes procesados exitosamente:</strong> $actualizados</p>";
        echo "<p><strong>IDs procesados:</strong> " . json_encode($expedientes_procesados) . "</p>";
        
        // Confirmar transacción
        $pdo->commit();
        echo "<p style='color: green; font-weight: bold;'>✅ TRANSACCIÓN CONFIRMADA</p>";
        
        // Verificar que los cambios se guardaron
        echo "<h3>🔍 8. Verificación Post-Actualización:</h3>";
        foreach ($expedientes_procesados as $id_expediente) {
            $stmtVerify = $pdo->prepare("SELECT Id, n_expediente, id_sede, ubicacion_area, ubicacion_detalle, fecha_ultima_ubicacion FROM maestro WHERE Id = :id");
            $stmtVerify->execute([':id' => $id_expediente]);
            $verificacion = $stmtVerify->fetch();
            
            if ($verificacion) {
                echo "<p style='background: #f0f8ff; padding: 10px; border-left: 4px solid #007bff;'>";
                echo "<strong>Expediente:</strong> " . $verificacion['n_expediente'] . "<br>";
                echo "<strong>Sede:</strong> " . $verificacion['id_sede'] . "<br>";
                echo "<strong>Área:</strong> " . ($verificacion['ubicacion_area'] ?: 'Sin área') . "<br>";
                echo "<strong>Detalle:</strong> " . ($verificacion['ubicacion_detalle'] ?: 'Sin detalle') . "<br>";
                echo "<strong>Fecha actualización:</strong> " . $verificacion['fecha_ultima_ubicacion'];
                echo "</p>";
            }
        }
        
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
            echo "<p style='color: red; font-weight: bold;'>❌ TRANSACCIÓN REVERTIDA</p>";
        }
        echo "<p style='color: red;'>❌ ERROR PDO: " . $e->getMessage() . "</p>";
    }
    
} else {
    echo "<p>No se recibieron datos POST. Usa el formulario del gestor de ubicaciones.</p>";
}

echo "<hr>";
echo "<p><a href='gestionar_ubicaciones.php?modo=lote'>← Volver al Gestor de Ubicaciones</a></p>";
?>