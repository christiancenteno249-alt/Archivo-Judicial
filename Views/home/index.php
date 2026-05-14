<?php
/**
 * Views/home/index.php
 * Vista del menú principal (dashboard) del sistema.
 * Replica exactamente el HTML original del index.php legacy.
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema Judicial - Archivo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-color: #f4f6f9;
            --azul-corporativo: #004085;
            --azul-claro: #0056b3;
            --azul-hover: #003366;
            --text-primary: #004085;
            --text-secondary: #424242;
            --text-tertiary: #616161;
        }
        body {
            background-image: url('/background.png');
            background-size: cover;
            background-position: center top;
            background-repeat: no-repeat;
            background-color: #e9ecef; /* Evitar el parpadeo blanco brillante */
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 1rem 0; /* Menos padding vertical */
        }
        .card-menu {
            border-radius: 12px;
            cursor: pointer;
            transition: transform 0.3s cubic-bezier(0.165, 0.84, 0.44, 1), box-shadow 0.3s ease;
            box-shadow: 0 4px 15px rgba(0,64,133,0.15);
            text-align: center;
            padding: 1.5rem 1rem; /* Reducido para que quepa todo */
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            position: relative;
            background-color: #FFFFFF;
        }
        .card-menu:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 30px rgba(0,64,133,0.25);
        }
        .card-search-action   { background: linear-gradient(135deg, #004085 0%, #0056b3 100%); border: none; }
        .card-register-action { background: linear-gradient(135deg, #0056b3 0%, #007bff 100%); border: none; }
        .card-admin-usuarios  { background: linear-gradient(135deg, #004085 0%, #0056b3 100%); border: none; }
        .card-admin-auditoria { background: linear-gradient(135deg, #003366 0%, #004085 100%); border: none; }
        .card-admin-respaldo  { background: linear-gradient(135deg, #0056b3 0%, #007bff 100%); border: none; }
        .card-ubicaciones     { background: linear-gradient(135deg, #004085 0%, #0056b3 100%); border: none; }
        .card-dropdown-icon {
            font-size: 3rem; /* Reducido */
            margin-bottom: 0.8rem;
            transition: transform 0.3s cubic-bezier(0.165, 0.84, 0.44, 1);
            color: rgba(255, 255, 255, 0.95);
        }
        .card-menu:hover .card-dropdown-icon { transform: scale(1.1); }
        .card-title-action {
            font-weight: 600;
            font-size: 1.1rem; /* Reducido */
            letter-spacing: 0.5px;
            margin: 0;
            z-index: 1;
            color: white;
        }
        /* Nueva barra de usuario premium */
        .user-bar-container {
            position: absolute;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }
        .user-bar {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 50px;
            padding: 6px 6px 6px 18px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            gap: 15px;
            border: 1px solid rgba(255, 255, 255, 0.5);
            transition: all 0.3s ease;
        }
        .user-bar:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
            background: rgba(255, 255, 255, 1);
        }
        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--azul-corporativo);
            font-weight: 700;
            font-size: 0.95rem;
        }
        .btn-logout {
            background: linear-gradient(135deg, #ff4b2b 0%, #dc3545 100%);
            color: white;
            border-radius: 50px;
            padding: 8px 20px;
            font-size: 0.85rem;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            border: none;
            box-shadow: 0 4px 10px rgba(220, 53, 69, 0.2);
        }
        .btn-logout:hover {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            box-shadow: 0 6px 15px rgba(220, 53, 69, 0.4);
            transform: translateX(3px);
            color: white;
        }
        .user-avatar {
            width: 32px;
            height: 32px;
            background: var(--azul-corporativo);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
        }
    </style>
</head>
<body>
<div class="container">

    <!-- Barra de usuario rediseñada -->
    <div class="user-bar-container">
        <div class="user-bar">
            <div class="user-info">
                <div class="user-avatar">
                    <i class="bi bi-person-fill"></i>
                </div>
                <span><?= htmlspecialchars($_SESSION['usuario_nombre'] ?? $_SESSION['nombre'] ?? 'Usuario') ?></span>
            </div>
            <a href="<?= BASE_URL ?>/salir" class="btn-logout">
                <span>SALIR</span>
                <i class="bi bi-box-arrow-right"></i>
            </a>
        </div>
    </div>

    <!-- Opciones principales -->
    <div class="row justify-content-center g-4">
        <div class="col-md-5">
            <div class="card card-menu card-search-action">
                <i class="bi bi-search card-dropdown-icon"></i>
                <h3 class="card-title-action">CONSULTA DE EXPEDIENTES</h3>
                <a href="<?= BASE_URL ?>/consulta" class="stretched-link"></a>
            </div>
        </div>
        <div class="col-md-5">
            <div class="card card-menu card-register-action">
                <i class="bi bi-folder-plus card-dropdown-icon"></i>
                <h3 class="card-title-action">REGISTRO DE NUEVO EXPEDIENTE</h3>
                <a href="<?= BASE_URL ?>/registro" class="stretched-link"></a>
            </div>
        </div>
    </div>

    <!-- Opciones de administrador -->
    <?php if (($_SESSION['usuario_rol'] ?? '') === 'admin'): ?>
    <div class="row justify-content-center g-4 mt-3">
        <div class="col-md-5">
            <div class="card card-menu card-admin-usuarios">
                <i class="bi bi-people card-dropdown-icon"></i>
                <h3 class="card-title-action">GESTIÓN DE USUARIOS</h3>
                <a href="<?= BASE_URL ?>/usuarios" class="stretched-link"></a>
            </div>
        </div>
        <div class="col-md-5">
            <div class="card card-menu card-admin-auditoria">
                <i class="bi bi-shield-check card-dropdown-icon"></i>
                <h3 class="card-title-action">AUDITORÍA DEL SISTEMA</h3>
                <a href="<?= BASE_URL ?>/auditoria" class="stretched-link"></a>
            </div>
        </div>
    </div>
    <div class="row justify-content-center g-4 mt-3">
        <div class="col-md-5">
            <div class="card card-menu card-admin-respaldo">
                <i class="bi bi-database-fill-down card-dropdown-icon"></i>
                <h3 class="card-title-action">RESPALDO DE BASE DE DATOS</h3>
                <a href="<?= BASE_URL ?>/respaldo" class="stretched-link"></a>
            </div>
        </div>
        <div class="col-md-5">
            <div class="card card-menu" style="background:linear-gradient(135deg,#004085,#0056b3);">
                <i class="bi bi-building card-dropdown-icon"></i>
                <h3 class="card-title-action">GESTIÓN DE SEDES</h3>
                <a href="<?= BASE_URL ?>/sedes" class="stretched-link"></a>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Gestión de Ubicaciones (todos) -->
    <div class="row justify-content-center g-4 mt-3">
        <div class="col-md-10">
            <div class="card card-menu card-ubicaciones">
                <i class="bi bi-geo-alt-fill card-dropdown-icon"></i>
                <h3 class="card-title-action">GESTIÓN DE UBICACIONES FÍSICAS</h3>
                <p class="text-white opacity-75 mb-0 mt-2">Centralización de Expedientes - Palo Negro</p>
                <a href="<?= BASE_URL ?>/ubicaciones" class="stretched-link"></a>
            </div>
        </div>
    </div>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
