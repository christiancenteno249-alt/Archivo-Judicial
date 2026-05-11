<?php
require_once "conexion.php";
session_start();

// Simular un usuario logueado
$_SESSION['usuario_id'] = 1;
$_SESSION['usuario_nombre'] = 'Test';

require_once "auditoria_functions.php";

echo "<h3>Test de Auditoria</h3>";

// Verificar estructura de la tabla
echo "<h4>Estructura de auditoria_log:</h4>";
$stmt = $pdo->query("DESCRIBE auditoria_log");
echo "<table border='1'><tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th></tr>";
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "<tr><td>{$row['Field']}</td><td>{$row['Type']}</td><td>{$row['Null']}</td><td>{$row['Key']}</td></tr>";
}
echo "</table><br>";

// Intentar insertar un registro de prueba
echo "<h4>Intentando insertar registro de prueba...</h4>";
$resultado = registrarAccion('TEST', 'test_recurso', 'Prueba de auditoria');

if ($resultado) {
    echo "<p style='color: green;'> Registro insertado exitosamente</p>";
    
    // Mostrar el ultimo registro
    $ultimo = $pdo->query("SELECT * FROM auditoria_log ORDER BY id_log DESC LIMIT 1")->fetch();
    echo "<pre>";
    print_r($ultimo);
    echo "</pre>";
} else {
    echo "<p style='color: red;'> Error al insertar registro</p>";
    if (isset($_SESSION['debug_auditoria'])) {
        echo "<p>Error: " . $_SESSION['debug_auditoria'] . "</p>";
    }
}

