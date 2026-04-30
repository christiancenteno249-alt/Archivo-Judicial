<?php
// 1. Ruta automática al archivo
$dbName = $_SERVER['DOCUMENT_ROOT'] . "/archivo_judicial/SCAJ.accdb";

// 2. Intentar conexión
try {
    // Usamos el driver que acabas de instalar
    $connStr = "odbc:Driver={Microsoft Access Driver (*.mdb, *.accdb)};Dbq=$dbName;";
    $db = new PDO($connStr);

    echo "<h1>¡CONEXIÓN EXITOSA!</h1>";
    echo "<h3>Tablas en el sistema:</h3><ul>";

    // 3. Consultar las tablas (esta es la parte mágica)
    // Usamos una consulta estándar de Access para listar tablas de usuario
    $sql = "SELECT Name FROM MSysObjects WHERE Type=1 AND Flags=0";
    $result = $db->query($sql);

    if ($result) {
        foreach ($result as $row) {
            echo "<li><strong>" . $row['Name'] . "</strong></li>";
        }
    } else {
        echo "<li>No se pudieron listar las tablas. Intenta con una consulta simple.</li>";
    }
    echo "</ul>";

} catch (PDOException $e) {
    echo "Error de conexión: " . $e->getMessage();
}
?>