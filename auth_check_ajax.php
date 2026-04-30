<?php
// Verificación de autenticación compatible con AJAX
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Si no hay sesión activa
if (!isset($_SESSION['usuario_id'])) {
    // Detectar si es una petición AJAX
    $esAjax = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';
    
    if ($esAjax) {
        // Para AJAX: devolver JSON
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode([
            'error' => true,
            'mensaje' => 'Sesión no válida. Por favor, inicia sesión nuevamente.',
            'requiere_login' => true,
            'redirect_url' => 'login.php'
        ]);
        exit;
    } else {
        // Para peticiones normales: redirigir
        header('Location: login.php');
        exit;
    }
}

// Función para cerrar sesión
function logout() {
    session_start();
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit;
}
