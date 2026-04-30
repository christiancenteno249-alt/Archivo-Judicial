<?php
header('Content-Type: application/json; charset=UTF-8');

echo json_encode([
    'test' => 'ok',
    'mensaje' => 'El archivo funciona correctamente',
    'id_recibido' => $_GET['id'] ?? 'no proporcionado'
]);
