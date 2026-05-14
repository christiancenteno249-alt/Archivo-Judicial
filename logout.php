<?php
session_start();
require_once "conexion.php";
require_once "auditoria_functions.php";

// Registrar logout antes de destruir la sesion
if (isset($_SESSION['usuario_nick'])) {
    registrarAccion('LOGOUT', $_SESSION['usuario_nick'], 'Cierre de sesion');
}

session_unset();
session_destroy();
header('Location: login.php');
exit;




