<?php
// Archivo para verificar que el usuario este autenticado
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Si no hay sesion activa, redirigir al login
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

// Funcion para cerrar sesion
function logout() {
    session_start();
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit;
}




