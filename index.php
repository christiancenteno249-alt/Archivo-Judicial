<?php
/**
 * index.php — Front Controller MVC
 *
 * Punto de entrada unico del sistema.
 * 1. Registra un Autoloader para Core/, Models/, Controllers/
 * 2. Intenta despachar la peticion al enrutador MVC (Core/App.php)
 * 3. Si la ruta no existe en MVC, hace fallback al sistema legacy
 */

// ─── 0. DEFINIR RUTA BASE (Soporte para subcarpetas en XAMPP) ──────────────
$base_dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
define('BASE_URL', $base_dir === '/' ? '' : $base_dir);

// ─── 1. AUTOLOADER ───────────────────────────────────────────────────────────
spl_autoload_register(function (string $clase): void {
    $directorios = [
        __DIR__ . '/Core/',
        __DIR__ . '/Models/',
        __DIR__ . '/Controllers/',
    ];
    foreach ($directorios as $dir) {
        $archivo = $dir . $clase . '.php';
        if (file_exists($archivo)) {
            require_once $archivo;
            return;
        }
    }
});

// ─── 2. TABLA DE FALLBACK LEGACY REMOVIDA (SISTEMA 100% MVC) ──────────────────

// ─── 3. PARSEAR URI ──────────────────────────────────────────────────────────
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = ltrim(rtrim($uri, '/'), '/');

// ─── 4. SERVIR ARCHIVOS ESTATICOS REALES ─────────────────────────────────────
// Esto no deberia ocurrir via Apache (lo maneja .htaccess), pero si se usa
// el servidor built-in de PHP (router.php) es necesario.
$archivoFisico = __DIR__ . '/' . $uri;
if ($uri !== '' && file_exists($archivoFisico) && is_file($archivoFisico) &&
    !preg_match('/\.php$/', $uri)) {
    return false;
}

// ─── 5. INTENTAR ENRUTADOR MVC ───────────────────────────────────────────────
try {
    $app = new App();
    $app->run();
} catch (Throwable $e) {
    if ($e->getCode() === 404) {
        http_response_code(404);
        echo "<h1>404 - Página no encontrada</h1><a href='" . BASE_URL . "/'>Volver al inicio</a>";
    } else {
        http_response_code(500);
        echo "<h1>Error del sistema</h1><p>" . htmlspecialchars($e->getMessage()) . "</p>";
    }
}
