<?php
/**
 * Views/respaldo/index.php
 * Vista de Respaldo de Base de Datos.
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Respaldo de Base de Datos</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --azul-institucional: #004085;
            --azul-hover: #0056b3;
            --verde-institucional: #2e7d32;
        }
        body {
            background-image: url('<?= BASE_URL ?>/background.png');
            backdrop-filter: blur(1px);
            background-size: cover;
            background-position: center top;
            background-repeat: no-repeat;
            background-attachment: fixed;
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            padding-bottom: 50px;
            background-color: #FFFFFF;
        }
        .container {
            padding-top: 100px;
        }
        .card-respaldo {
            background: #FFFFFF;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0,64,133,0.15);
            border: none;
            max-width: 800px;
            margin: 0 auto;
        }
        .card-header-custom {
            background: linear-gradient(135deg, #0056b3 0%, #007bff 100%);
            color: white;
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
            padding: 25px;
            position: relative;
        }
        .btn-volver {
            position: absolute;
            top: 50%;
            right: 20px;
            transform: translateY(-50%);
        }
        .info-box {
            background-color: #e8f5e9;
            border-left: 4px solid var(--verde-institucional);
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
        }
        .warning-box {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
        }
        .card.border-success {
            border-width: 2px !important;
            transition: all 0.3s;
        }
        .card.border-success:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(46, 125, 50, 0.3);
        }
        .card.border-primary {
            border-width: 2px !important;
            transition: all 0.3s;
        }
        .card.border-primary:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(25, 118, 210, 0.3);
        }
        .feature-list {
            list-style: none;
            padding-left: 0;
        }
        .feature-list li {
            padding: 10px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        .feature-list li:last-child {
            border-bottom: none;
        }
        .feature-list i {
            color: var(--verde-institucional);
            margin-right: 10px;
        }
        * {
            box-shadow: none !important;
        }
    </style>
</head>
<body>

<div class="container">
    
    <div class="card card-respaldo">
        <div class="card-header-custom">
            <h3 class="mb-0"><i class="bi bi-database-fill-down me-2"></i>RESPALDO COMPLETO DE BASE DE DATOS</h3>
            <a href="<?= BASE_URL ?>/" class="btn btn-light btn-sm btn-volver">
                <i class="bi bi-arrow-left me-2"></i>Volver al Menú
            </a>
        </div>
        <div class="card-body p-4 p-md-5">
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger border-0 border-start border-danger border-4" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <strong>Error:</strong> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <div class="info-box">
                <h5 class="fw-bold mb-3"><i class="bi bi-info-circle-fill me-2"></i>Información del Respaldo</h5>
                <p class="mb-2"><strong>Base de Datos:</strong> <?= htmlspecialchars($dbname) ?></p>
                <p class="mb-2"><strong>Servidor:</strong> <?= htmlspecialchars($host) ?></p>
                <p class="mb-0"><strong>Fecha Actual:</strong> <?= date('d/m/Y H:i:s') ?></p>
            </div>
            
            <div class="warning-box">
                <h5 class="fw-bold mb-3"><i class="bi bi-exclamation-triangle-fill me-2"></i>Advertencia Importante</h5>
                <p class="mb-0">
                    Este proceso generará un archivo SQL con <strong>TODAS las tablas y datos</strong> del sistema, 
                    incluyendo usuarios, expedientes, historial de movimientos y registros de auditoría. 
                    El archivo incluye sentencias <code>DROP TABLE IF EXISTS</code> para facilitar la restauración limpia.
                </p>
            </div>
            
            <h5 class="fw-bold mb-3">¿Qué incluye este respaldo?</h5>
            <ul class="feature-list">
                <li>
                    <i class="bi bi-check-circle-fill"></i>
                    <strong>Estructura completa:</strong> Todas las tablas con sus definiciones (CREATE TABLE)
                </li>
                <li>
                    <i class="bi bi-check-circle-fill"></i>
                    <strong>Datos completos:</strong> Todos los registros de todas las tablas
                </li>
                <li>
                    <i class="bi bi-check-circle-fill"></i>
                    <strong>Usuarios del sistema:</strong> Tabla usuarios_sistema con credenciales
                </li>
                <li>
                    <i class="bi bi-check-circle-fill"></i>
                    <strong>Expedientes:</strong> Tabla maestro con todos los expedientes registrados
                </li>
                <li>
                    <i class="bi bi-check-circle-fill"></i>
                    <strong>Historial de movimientos:</strong> Tabla historial_movimientos completa
                </li>
                <li>
                    <i class="bi bi-check-circle-fill"></i>
                    <strong>Auditoría:</strong> Tabla auditoria_log con todos los registros de seguridad
                </li>
                <li>
                    <i class="bi bi-check-circle-fill"></i>
                    <strong>Tribunales:</strong> Tabla tribunales con catálogo completo
                </li>
            </ul>
            
            <div class="alert alert-success border-0 border-start border-success border-4 mt-4" role="alert">
                <h6 class="fw-bold mb-2"><i class="bi bi-file-earmark-spreadsheet me-2"></i>Dos Formatos Disponibles:</h6>
                <ul class="mb-0">
                    <li><strong>SQL:</strong> Para restauración completa de la base de datos (incluye DROP TABLE IF EXISTS)</li>
                    <li><strong>CSV/Excel:</strong> Para análisis, reportes y consulta de datos en hojas de cálculo (formato CSV compatible con Excel)</li>
                </ul>
            </div>
            
            <div class="alert alert-info border-0 border-start border-info border-4 mt-3" role="alert">
                <i class="bi bi-shield-check me-2"></i>
                <strong>Registro de Auditoría:</strong> Esta acción quedará registrada en el log de auditoría del sistema 
                con tu nombre de usuario y la fecha/hora exacta de generación.
            </div>
            
            <h5 class="fw-bold mb-3 mt-4"><i class="bi bi-download me-2"></i>Selecciona el formato de respaldo:</h5>
            
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="card h-100 border-success">
                        <div class="card-body text-center">
                            <i class="bi bi-database-fill-gear text-success" style="font-size: 3rem;"></i>
                            <h5 class="mt-3 mb-2">Respaldo SQL</h5>
                            <p class="text-muted small mb-3">
                                Archivo .sql con estructura y datos completos. 
                                Ideal para restauración de base de datos.
                            </p>
                            <a href="<?= BASE_URL ?>/respaldo/descargar?generar=sql" class="btn btn-success w-100 btn-descargar-sql">
                                <i class="bi bi-file-earmark-code me-2"></i>Descargar SQL
                            </a>
                            <small class="text-muted d-block mt-2">
                                respaldo_total_<?= date('d_m_Y_His') ?>.sql
                            </small>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card h-100 border-primary">
                        <div class="card-body text-center">
                            <i class="bi bi-file-earmark-spreadsheet text-primary" style="font-size: 3rem;"></i>
                            <h5 class="mt-3 mb-2">Respaldo Excel/CSV</h5>
                            <p class="text-muted small mb-3">
                                Archivo .csv compatible con Excel y hojas de cálculo. 
                                Ideal para análisis y reportes.
                            </p>
                            <a href="<?= BASE_URL ?>/respaldo/descargar?generar=excel" class="btn btn-primary w-100 btn-descargar-excel">
                                <i class="bi bi-file-earmark-excel me-2"></i>Descargar CSV
                            </a>
                            <small class="text-muted d-block mt-2">
                                respaldo_total_<?= date('d_m_Y_His') ?>.csv
                            </small>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="d-grid gap-3 mt-4">
                <a href="<?= BASE_URL ?>/" class="btn btn-secondary btn-lg">
                    <i class="bi bi-x-circle me-2"></i>Cancelar y Volver al Menú
                </a>
            </div>
            
            <div class="mt-4 p-3 bg-light rounded">
                <h6 class="fw-bold mb-2"><i class="bi bi-info-circle me-2"></i>Diferencias entre formatos:</h6>
                <div class="row">
                    <div class="col-md-6">
                        <strong>SQL (.sql)</strong>
                        <ul class="small text-muted">
                            <li>Restauración completa de BD</li>
                            <li>Incluye DROP TABLE</li>
                            <li>Mantiene tipos de datos</li>
                            <li>Ejecutable en MySQL/MariaDB</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <strong>CSV (.csv)</strong>
                        <ul class="small text-muted">
                            <li>Visualización en Excel/Calc</li>
                            <li>Análisis y filtros</li>
                            <li>Formato tabular con comas</li>
                            <li>Compatible con Office y Google Sheets</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/app-alerts.js"></script>

<script>
$(document).ready(function() {
    $('.btn-descargar-sql').on('click', async function(e) {
        e.preventDefault();
        const href = $(this).attr('href');
        const confirmacion = await window.appAlerts.confirm("Se generará un respaldo completo en formato SQL. ¿Deseas continuar?", {
            type: 'success',
            title: 'Generar respaldo SQL',
            okText: 'Generar SQL',
            cancelText: 'Cancelar'
        });
        if (confirmacion) {
            window.location.href = href;
        }
    });

    $('.btn-descargar-excel').on('click', async function(e) {
        e.preventDefault();
        const href = $(this).attr('href');
        const confirmacion = await window.appAlerts.confirm("Se generará un respaldo en formato CSV compatible con Excel. ¿Deseas continuar?", {
            type: 'info',
            title: 'Generar respaldo CSV',
            okText: 'Generar CSV',
            cancelText: 'Cancelar'
        });
        if (confirmacion) {
            window.location.href = href;
        }
    });
});
</script>
</body>
</html>
