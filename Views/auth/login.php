<?php
/**
 * Views/auth/login.php
 * Vista del formulario de Login rediseñada con una interfaz premium y moderna.
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Sistema Judicial</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --azul-institucional: #004085;
            --azul-hover: #003366;
            --azul-claro: #e3f2fd;
        }
        body {
            background-image: url('<?= BASE_URL ?>/background.png');
            background-size: cover;
            background-position: center center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
        }
        .login-container {
            width: 100%;
            max-width: 440px;
            perspective: 1000px;
        }
        .login-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(16px) saturate(180%);
            -webkit-backdrop-filter: blur(16px) saturate(180%);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 15px 35px rgba(0, 64, 133, 0.15);
            padding: 40px 35px;
            transition: transform 0.5s cubic-bezier(0.165, 0.84, 0.44, 1);
        }
        .login-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 45px rgba(0, 64, 133, 0.25);
        }
        .login-header {
            text-align: center;
            margin-bottom: 35px;
        }
        .icon-wrapper {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, var(--azul-institucional) 0%, #0056b3 100%);
            color: white;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 2.2rem;
            margin-bottom: 15px;
            box-shadow: 0 8px 20px rgba(0, 64, 133, 0.3);
            animation: pulse 2s infinite;
        }
        .login-header h3 {
            font-weight: 700;
            color: var(--azul-institucional);
            font-size: 1.6rem;
            margin-bottom: 5px;
        }
        .login-header p {
            color: #616161;
            font-size: 0.95rem;
            font-weight: 400;
        }
        .form-label {
            font-weight: 600;
            color: var(--azul-institucional);
            font-size: 0.9rem;
            margin-bottom: 8px;
        }
        .input-group {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }
        .input-group-text {
            background-color: white;
            border: 1px solid #ced4da;
            border-right: none;
            color: var(--azul-institucional);
            padding-left: 15px;
            padding-right: 10px;
        }
        .form-control {
            border: 1px solid #ced4da;
            border-left: none;
            padding: 12px 15px;
            font-size: 0.95rem;
            color: #333;
        }
        .form-control:focus {
            box-shadow: none;
            border-color: #ced4da;
        }
        .input-group-focus {
            box-shadow: 0 0 0 3px rgba(0, 64, 133, 0.2) !important;
            border-color: var(--azul-institucional) !important;
        }
        .btn-login {
            background: linear-gradient(135deg, var(--azul-institucional) 0%, #0056b3 100%);
            border: none;
            color: white;
            padding: 14px;
            font-weight: 600;
            font-size: 1.05rem;
            border-radius: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 64, 133, 0.25);
            margin-top: 10px;
        }
        .btn-login:hover {
            background: linear-gradient(135deg, #003366 0%, var(--azul-institucional) 100%);
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(0, 64, 133, 0.35);
        }
        .alert {
            border-radius: 10px;
            font-size: 0.88rem;
            border: none;
            margin-bottom: 25px;
            padding: 12px 15px;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="login-card">
        <div class="login-header">
            <div class="icon-wrapper">
                <i class="bi bi-shield-lock-fill"></i>
            </div>
            <h3>ARCHIVO JUDICIAL</h3>
            <p>Ingresa tus credenciales para continuar</p>
        </div>
        
        <?php if (!empty($mensaje)): ?>
            <div class="alert alert-<?= $tipoAlerta ?> alert-dismissible fade show" role="alert">
                <i class="bi <?php 
                    echo $tipoAlerta === 'success' ? 'bi-check-circle-fill' : 
                         ($tipoAlerta === 'warning' ? 'bi-exclamation-triangle-fill' : 'bi-x-circle-fill'); 
                ?> me-2"></i>
                <?= htmlspecialchars($mensaje) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="<?= BASE_URL ?>/login">
            <div class="mb-4">
                <label for="input_usuario" class="form-label">Usuario</label>
                <div class="input-group" id="group_usuario">
                    <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                    <input type="text" class="form-control" id="input_usuario" name="usuario" 
                           placeholder="Ingresa tu usuario" required autofocus autocomplete="username">
                </div>
            </div>
            
            <div class="mb-4">
                <label for="input_password" class="form-label">Contraseña</label>
                <div class="input-group" id="group_password">
                    <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                    <input type="password" class="form-control" id="input_password" name="password" 
                           placeholder="Ingresa tu contraseña" required autocomplete="current-password">
                </div>
            </div>
            
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-login">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Iniciar Sesión
                </button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Agregar efectos de focus premium a los inputs
    document.querySelectorAll('.form-control').forEach(input => {
        input.addEventListener('focus', () => {
            input.parentElement.classList.add('input-group-focus');
        });
        input.addEventListener('blur', () => {
            input.parentElement.classList.remove('input-group-focus');
        });
    });
</script>
</body>
</html>
