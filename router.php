<?php
/**
 * router.php
 * Router para el servidor built-in de PHP (php -S localhost:8000 router.php).
 *
 * Estrategia: delega todo al Front Controller (index.php), exactamente
 * igual a como lo hace Apache con el .htaccess actualizado.
 * Los archivos fisicos reales (assets, imagenes, css, js) se sirven directamente.
 */

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);


// ─── 2. SERVIR ARCHIVOS FISICOS REALES (css, js, img, fuentes, etc.) ─────────
$archivoFisico = __DIR__ . $uri;
if ($uri !== '/' && file_exists($archivoFisico) && is_file($archivoFisico) &&
    !preg_match('/\.php$/', $uri)) {
    return false; // El servidor built-in lo sirve directamente
}

// ─── 3. TODO LO DEMAS → FRONT CONTROLLER MVC ─────────────────────────────────
require __DIR__ . '/index.php';
