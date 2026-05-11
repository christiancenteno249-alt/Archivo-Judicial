<?php
// Diagnóstico completo de obtener_ubicacion.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Diagnóstico de Sistema de Ubicaciones</h2>";
echo "<hr>";

// 1. Verificar que el archivo existe
echo "<h3>1. Verificación de Archivos</h3>";
$archivos = ['obtener_ubicacion.php', 'test_ubicacion.php', 'conexion.php'];
foreach ($archivos as $archivo) {
    $existe = file_exists($archivo);
    $color = $existe ? 'green' : 'red';
    echo "<p style='color: $color;'>$archivo: " . ($existe ? '✓ Existe' : '✗ No existe') . "</p>";
}

// 2. Verificar conexión a base de datos
echo "<h3>2. Verificación de Conexión a BD</h3>";
try {
    require_once "conexion.php";
    echo "<p style='color: green;'>✓ Conexión a base de datos exitosa</p>";
    echo "<p>Driver: " . $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) . "</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error de conexión: " . $e->getMessage() . "</p>";
}

// 3. Verificar sesión
echo "<h3>3. Verificación de Sesión</h3>";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (isset($_SESSION['usuario_id'])) {
    echo "<p style='color: green;'>✓ Sesión activa - Usuario ID: " . $_SESSION['usuario_id'] . "</p>";
} else {
    echo "<p style='color: orange;'>⚠ No hay sesión activa</p>";
}

// 4. Probar consulta con un ID de ejemplo
echo "<h3>4. Prueba de Consulta SQL</h3>";
$id_prueba = $_GET['id'] ?? '6'; // Usar ID 6 como ejemplo
echo "<p>Probando con ID: <strong>$id_prueba</strong></p>";

try {
    $stmt = $pdo->prepare("
        SELECT 
            m.Id,
            m.n_expediente,
            m.demandante,
            m.demandado,
            m.id_sede,
            s.nombre_sede,
            s.direccion as sede_direccion,
            m.ubicacion_area,
            m.ubicacion_detalle,
            m.fecha_ultima_ubicacion
        FROM maestro m
        LEFT JOIN sedes_deposito s ON m.id_sede = s.id_sede
        WHERE m.Id = :id
        LIMIT 1
    ");
    
    $stmt->execute([':id' => $id_prueba]);
    $expediente = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($expediente) {
        echo "<p style='color: green;'>✓ Consulta exitosa</p>";
        echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px;'>";
        print_r($expediente);
        echo "</pre>";
    } else {
        echo "<p style='color: orange;'>⚠ No se encontró expediente con ID: $id_prueba</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Error en consulta: " . $e->getMessage() . "</p>";
}

// 5. Probar respuesta JSON
echo "<h3>5. Prueba de Respuesta JSON</h3>";
echo "<p>Simulando respuesta de obtener_ubicacion.php:</p>";

if (isset($expediente) && $expediente) {
    if (!empty($expediente['fecha_ultima_ubicacion'])) {
        $fecha = new DateTime($expediente['fecha_ultima_ubicacion']);
        $expediente['fecha_formateada'] = $fecha->format('d/m/Y H:i');
    } else {
        $expediente['fecha_formateada'] = 'No registrada';
    }
    
    $expediente['tiene_ubicacion'] = !empty($expediente['nombre_sede']);
    
    $respuesta = [
        'error' => false,
        'datos' => $expediente
    ];
    
    echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px;'>";
    echo json_encode($respuesta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    echo "</pre>";
}

// 6. Verificar headers
echo "<h3>6. Headers Actuales</h3>";
echo "<p>Content-Type actual: " . (headers_sent() ? "Ya enviados" : "No enviados aún") . "</p>";

// 7. Instrucciones
echo "<hr>";
echo "<h3>Instrucciones de Prueba</h3>";
echo "<ol>";
echo "<li>Accede a: <a href='test_ubicacion.php?id=6' target='_blank'>test_ubicacion.php?id=6</a></li>";
echo "<li>Accede a: <a href='obtener_ubicacion.php?id=6' target='_blank'>obtener_ubicacion.php?id=6</a></li>";
echo "<li>Compara las respuestas de ambos archivos</li>";
echo "<li>Si obtener_ubicacion.php está vacío, revisa el archivo en busca de BOM o espacios antes de &lt;?php</li>";
echo "</ol>";

echo "<hr>";
echo "<p><strong>Nota:</strong> Si ves este diagnóstico correctamente, el servidor PHP está funcionando.</p>";
