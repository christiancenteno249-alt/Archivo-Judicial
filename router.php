<?php
/**
 * router.php
 * Router para el servidor built-in de PHP.
 * Uso: php -S localhost:8000 router.php
 */

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = rtrim($uri, '/');

// Servir archivos estáticos reales (css, js, imágenes, etc.)
if ($uri !== '' && file_exists(__DIR__ . $uri) && !is_dir(__DIR__ . $uri)) {
    return false; // El servidor built-in lo sirve directamente
}

// Tabla de rutas: URL limpia => archivo PHP
$rutas = [
    ''            => 'index.php',
    '/inicio'     => 'index.php',
    '/consulta'   => 'buscador.php',
    '/registro'   => 'registrar.php',
    '/usuarios'   => 'gestionar_usuarios.php',
    '/auditoria'  => 'auditoria.php',
    '/respaldo'   => 'respaldar_bd.php',
    '/ubicaciones'=> 'gestionar_ubicaciones.php',
    '/sedes'      => 'gestionar_sedes.php',
    '/salir'      => 'logout.php',
];

// Rutas con parámetros dinámicos
if (preg_match('#^/expediente/(\d+)$#', $uri, $m)) {
    $_GET['id'] = $m[1];
    require __DIR__ . '/ver_historial.php';
    exit;
}

if (preg_match('#^/editar/(\d+)$#', $uri, $m)) {
    $_GET['id'] = $m[1];
    require __DIR__ . '/editar_registro.php';
    exit;
}

if (preg_match('#^/imprimir/(\d+)$#', $uri, $m)) {
    $_GET['id'] = $m[1];
    require __DIR__ . '/imprimir_expediente.php';
    exit;
}

// Rutas AJAX (verificaciones y consultas asíncronas)
if ($uri === '/verificar_expediente' || $uri === '/herramientas_diagnostico/verificar_expediente_v2.php') {
    require __DIR__ . '/herramientas_diagnostico/verificar_expediente_v2.php';
    exit;
}

if ($uri === '/obtener_ubicacion' || $uri === '/obtener_ubicacion.php') {
    require __DIR__ . '/obtener_ubicacion.php';
    exit;
}

// Rutas estáticas
if (array_key_exists($uri, $rutas)) {
    require __DIR__ . '/' . $rutas[$uri];
    exit;
}

// 404
http_response_code(404);
echo "<h1>404 - Página no encontrada</h1><a href='/'>Volver al inicio</a>";
