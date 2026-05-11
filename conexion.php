<?php
/**
 * conexion.php
 * Conexion a la base de datos usando PDO para mayor seguridad y rendimiento.
 */

$host = 'localhost';
$dbname = 'archivo_judicial';
$username = 'chris';
$password = '04022002'; // Por defecto en XAMPP suele estar vacio

try {
    // Definimos el Data Source Name (DSN) con el charset para asegurar el correcto manejo de caracteres
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    
    // Opciones recomendadas para PDO
    $opciones = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,    // Lanzar excepciones en caso de error
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,          // Devolver resultados como array asociativo
        PDO::ATTR_EMULATE_PREPARES   => false,                     // Usar prepared statements nativos (mejora seguridad)
    ];
    
    // Crear la instancia de PDO
    $pdo = new PDO($dsn, $username, $password, $opciones);
    
} catch (PDOException $e) {
    // En produccion, es recomendable loguear esto en un archivo y mostrar un mensaje generico.
    die("Error de conexion a la Base de Datos: " . $e->getMessage());
}
