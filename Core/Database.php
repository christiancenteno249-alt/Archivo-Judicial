<?php
/**
 * Core/Database.php
 * Conexion centralizada a la base de datos usando el patron Singleton.
 * Garantiza que solo exista UNA instancia de PDO en toda la aplicacion.
 */
class Database {
    private static ?PDO $instance = null;

    private function __construct() {}
    private function __clone() {}

    /**
     * Devuelve la instancia unica de PDO.
     * Si no existe, la crea con los parametros de conexion.
     */
    public static function getConnection(): PDO {
        if (self::$instance === null) {
            $host   = 'localhost';
            $dbname = 'archivo_judicial';
            $user   = 'chris';
            $pass   = '04022002';

            $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";

            $opciones = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                self::$instance = new PDO($dsn, $user, $pass, $opciones);
            } catch (PDOException $e) {
                http_response_code(500);
                die(json_encode(['error' => 'Error de conexion a la base de datos.']));
            }
        }

        return self::$instance;
    }
}
