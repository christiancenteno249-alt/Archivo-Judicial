<?php
$_SERVER['REQUEST_URI'] = '/ubicaciones';
session_start();
$_SESSION['usuario_id'] = 1;
$_SESSION['usuario_rol'] = 'admin';
$_SESSION['usuario_nick'] = 'admin';
require 'index.php';
