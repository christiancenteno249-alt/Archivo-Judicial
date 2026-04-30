<?php
header('Content-Type: application/json; charset=UTF-8');

// Debug: mostrar qué se recibió
$debug_info = [
    'POST' => $_POST,
    'GET' => $_GET,
    'REQUEST_METHOD' => $_SERVER['REQUEST_METHOD'] ?? 'unknown'
];

// Test básico primero
if (!isset($_POST['n_expediente']) && !isset($_GET['n_expediente'])) {
    echo json_encode([
        'existe' => false,
        'error' => true,
        'mensaje' => 'No se recibió parámetro',
        'debug' => $debug_info
    ]);
    exit;
}

// Iniciar sesión
session_start();

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['existe' => false, 'error' => true, 'mensaje' => 'Sin sesión']);
    exit;
}

$n_expediente = trim($_POST['n_expediente'] ?? $_GET['n_expediente'] ?? '');

if (empty($n_expediente)) {
    echo json_encode(['existe' => false, 'error' => false, 'mensaje' => 'Expediente vacío']);
    exit;
}

try {
    $pdo = new PDO(
        'mysql:host=localhost;dbname=db_archivo_judicial_test;charset=utf8mb4',
        'root',
        '',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    $stmt = $pdo->prepare("SELECT Id, n_expediente, demandante, demandado, id_tribunal FROM maestro WHERE n_expediente = ? LIMIT 1");
    $stmt->execute([$n_expediente]);
    $expediente = $stmt->fetch();
    
    if ($expediente) {
        echo json_encode([
            'existe' => true,
            'error' => false,
            'mensaje' => 'Expediente existe',
            'datos' => $expediente
        ]);
    } else {
        echo json_encode([
            'existe' => false,
            'error' => false,
            'mensaje' => 'Expediente no existe'
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'existe' => false,
        'error' => true,
        'mensaje' => $e->getMessage()
    ]);
}
