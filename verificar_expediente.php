<?php
// Configurar manejo de errores
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Función para enviar respuesta JSON y terminar
function enviarJSON($data) {
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode($data);
    exit;
}

// Iniciar buffer de salida
ob_start();

try {
    // Verificar sesión
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['usuario_id'])) {
        ob_end_clean();
        enviarJSON([
            'existe' => false,
            'error' => true,
            'mensaje' => 'Sesión no válida'
        ]);
    }
    
    // Obtener parámetro
    $n_expediente = trim($_POST['n_expediente'] ?? $_GET['n_expediente'] ?? '');
    
    if (empty($n_expediente)) {
        ob_end_clean();
        enviarJSON([
            'existe' => false,
            'error' => false,
            'mensaje' => 'Número de expediente vacío'
        ]);
    }
    
    // Conexión a base de datos (inline para evitar dependencias)
    $host = 'localhost';
    $dbname = 'db_archivo_judicial_test';
    $username = 'root';
    $password = '';
    
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $opciones = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    
    $pdo = new PDO($dsn, $username, $password, $opciones);
    
    // Consultar expediente
    $stmt = $pdo->prepare("SELECT Id, n_expediente, demandante, demandado, id_tribunal FROM maestro WHERE n_expediente = :n_expediente LIMIT 1");
    $stmt->execute([':n_expediente' => $n_expediente]);
    $expediente = $stmt->fetch(PDO::FETCH_ASSOC);
    
    ob_end_clean();
    
    if ($expediente) {
        enviarJSON([
            'existe' => true,
            'error' => false,
            'mensaje' => 'El expediente ya existe en el sistema',
            'datos' => [
                'id' => $expediente['Id'],
                'n_expediente' => $expediente['n_expediente'],
                'demandante' => $expediente['demandante'],
                'demandado' => $expediente['demandado'],
                'id_tribunal' => $expediente['id_tribunal']
            ]
        ]);
    } else {
        enviarJSON([
            'existe' => false,
            'error' => false,
            'mensaje' => 'El expediente no existe, puedes registrarlo'
        ]);
    }
    
} catch (PDOException $e) {
    ob_end_clean();
    enviarJSON([
        'existe' => false,
        'error' => true,
        'mensaje' => 'Error de base de datos: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    ob_end_clean();
    enviarJSON([
        'existe' => false,
        'error' => true,
        'mensaje' => 'Error del sistema: ' . $e->getMessage()
    ]);
} catch (Throwable $e) {
    ob_end_clean();
    enviarJSON([
        'existe' => false,
        'error' => true,
        'mensaje' => 'Error fatal: ' . $e->getMessage() . ' en línea ' . $e->getLine()
    ]);
}
