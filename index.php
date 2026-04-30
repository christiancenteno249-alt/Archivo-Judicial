<?php
require_once "auth_check.php";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema Judicial - Archivo</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Inter:wght@400;600&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --bg-color: #FFFFFF; /* Blanco puro */
            --azul-corporativo: #004085; /* Azul profesional DEM */
            --azul-claro: #0056b3;
            --azul-hover: #003366;
            --text-primary: #004085;
            --text-secondary: #424242;
            --text-tertiary: #616161;
            --rojo-alerta: #DC3545;
            --gris-neutro: #6c757d;
        }

        body {
            background-image: url('BACKGROUND (1).png');
            backdrop-filter: blur(1px);
            background-size: cover;
            background-position: center top;
            background-repeat: no-repeat;
            background-color: #FFFFFF;
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 2rem 0;
        }

        /* Encabezado Institucional */
        .institucional-header {
            text-align: center;
            margin-bottom: 4rem;
            background-color: rgba(255, 255, 255, 0.98);
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,64,133,0.15);
            border-top: 4px solid var(--azul-corporativo);
        }

        .institucional-header h2 {
            font-family: 'Playfair Display', serif;
            color: var(--azul-corporativo);
            font-weight: 700;
            margin-bottom: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .institucional-header h4 {
            font-family: 'Playfair Display', serif;
            color: var(--text-secondary);
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .institucional-header h5 {
            font-family: 'Playfair Display', serif;
            color: var(--text-tertiary);
            font-weight: 600;
        }

        /* Tarjetas (Botones Grandes) */
        .card-menu {
            border: none;
            border-radius: 16px;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
            box-shadow: 0 8px 25px rgba(0,64,133,0.2);
            text-align: center;
            padding: 3rem 2rem;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            position: relative;
            background-color: #FFFFFF;
        }

        /* Efecto Hover */
        .card-menu:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 45px rgba(0,64,133,0.35);
        }

        /* Tarjetas principales - Degradados Azules */
        .card-search-action {
            background: linear-gradient(135deg, #004085 0%, #0056b3 100%);
            border: none;
        }
        
        .card-search-action .card-dropdown-icon {
            color: rgba(255, 255, 255, 0.95);
        }
        
        .card-search-action .card-title-action {
            color: white;
        }

        /* Tarjeta Derecha (Registro) - Azul */
        .card-register-action {
            background: linear-gradient(135deg, #0056b3 0%, #007bff 100%);
            border: none;
        }
        
        .card-register-action .card-dropdown-icon {
            color: rgba(255, 255, 255, 0.95);
        }
        
        .card-register-action .card-title-action {
            color: white;
        }
        .card-dropdown-icon {
            font-size: 4.5rem;
            margin-bottom: 1.5rem;
            transition: transform 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        }

        .card-menu:hover .card-dropdown-icon {
            transform: scale(1.1);
        }

        .card-title-action {
            font-weight: 600;
            font-size: 1.3rem;
            letter-spacing: 0.5px;
            margin: 0;
            z-index: 1;
        }
        .card-admin-usuarios {
            background: linear-gradient(135deg, #004085 0%, #0056b3 100%);
        }
        
        .card-admin-auditoria {
            background: linear-gradient(135deg, #003366 0%, #004085 100%);
        }
        
        .card-admin-respaldo {
            background: linear-gradient(135deg, #0056b3 0%, #007bff 100%);
        }
        
        .card-ubicaciones {
            background: linear-gradient(135deg, #004085 0%, #0056b3 100%);
        }
    </style>
</head>
<body>

<div class="container">
    
    <!-- Barra de usuario -->
    <div class="position-absolute top-0 end-0 p-3">
        <div class="d-flex align-items-center gap-3">
            <span class="text-white bg-dark bg-opacity-75 px-3 py-2 rounded">
                <i class="bi bi-person-circle me-2"></i><?= htmlspecialchars($_SESSION['usuario_nombre']) ?>
            </span>
            <a href="salir" class="btn btn-danger btn-sm">
                <i class="bi bi-box-arrow-right me-1"></i>Salir
            </a>
        </div>
    </div>
    
<!--
    <div class="institucional-header row justify-content-center">
        <div class="col-md-10">
            <h2>Dirección Ejecutiva de la Magistratura</h2>
            <h4>Dirección Administrativa Regional del Estado Aragua</h4>
            <h5>Archivo Judicial de la Circunscripción Judicial del Estado Aragua</h5>
        </div>
    </div> -->
    
    <!-- 2 y 3. Diseno y Las Opciones -->
    <div class="row justify-content-center g-4">
        
        <!-- Tarjeta Izquierda (Buscador) -->
        <div class="col-md-5">
            <div class="card card-menu card-search-action">
                <i class="bi bi-search card-dropdown-icon"></i>
                <h3 class="card-title-action">CONSULTA DE EXPEDIENTES</h3>
                <a href="consulta" class="stretched-link"></a>
            </div>
        </div>

        <!-- Tarjeta Derecha (Registro) -->
        <div class="col-md-5">
            <div class="card card-menu card-register-action">
                <i class="bi bi-folder-plus card-dropdown-icon"></i>
                <h3 class="card-title-action">REGISTRO DE NUEVO EXPEDIENTE</h3>
                <a href="registro" class="stretched-link"></a>
            </div>
        </div>

    </div>
    
    <!-- Tarjeta de Gestion de Usuarios (Solo para Admin) -->
    <?php if ($_SESSION['usuario_rol'] === 'admin'): ?>
    <div class="row justify-content-center g-4 mt-3">
        <div class="col-md-5">
            <div class="card card-menu card-admin-usuarios" style="color: white;">
                <i class="bi bi-people card-dropdown-icon" style="color: rgba(255, 255, 255, 0.95);"></i>
                <h3 class="card-title-action" style="color: white;">GESTIÓN DE USUARIOS</h3>
                <a href="usuarios" class="stretched-link"></a>
            </div>
        </div>
        
        <div class="col-md-5">
            <div class="card card-menu card-admin-auditoria" style="color: white;">
                <i class="bi bi-shield-check card-dropdown-icon" style="color: rgba(255, 255, 255, 0.95);"></i>
                <h3 class="card-title-action" style="color: white;">AUDITORÍA DEL SISTEMA</h3>
                <a href="auditoria" class="stretched-link"></a>
            </div>
        </div>
    </div>
    
    <div class="row justify-content-center g-4 mt-3">
        <div class="col-md-5">
            <div class="card card-menu card-admin-respaldo" style="color: white;">
                <i class="bi bi-database-fill-down card-dropdown-icon" style="color: rgba(255, 255, 255, 0.95);"></i>
                <h3 class="card-title-action" style="color: white;">RESPALDO DE BASE DE DATOS</h3>
                <a href="respaldo" class="stretched-link"></a>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Gestión de Ubicaciones (Operadores y Admin) -->
    <div class="row justify-content-center g-4 mt-3">
        <div class="col-md-10">
            <div class="card card-menu card-ubicaciones" style="color: white;">
                <i class="bi bi-geo-alt-fill card-dropdown-icon" style="color: rgba(255, 255, 255, 0.95);"></i>
                <h3 class="card-title-action" style="color: white;">GESTIÓN DE UBICACIONES FÍSICAS</h3>
                <p class="text-white opacity-75 mb-0 mt-2">Centralización de Expedientes - Palo Negro</p>
                <a href="ubicaciones" class="stretched-link"></a>
            </div>
        </div>
    </div>

</div>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>




