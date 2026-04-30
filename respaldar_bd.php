<?php
require_once "conexion.php";
require_once "auth_check.php";
require_once "auditoria_functions.php";

// SEGURIDAD: Solo administradores pueden generar respaldos
if ($_SESSION['usuario_rol'] !== 'admin') {
    header('Location: index.php');
    exit;
}

// Funcion para generar el respaldo completo de la base de datos
function generarRespaldoCompleto($pdo, $dbname) {
    $respaldo = "";
    $respaldo .= "-- =====================================================\n";
    $respaldo .= "-- RESPALDO COMPLETO DE BASE DE DATOS\n";
    $respaldo .= "-- Base de Datos: {$dbname}\n";
    $respaldo .= "-- Fecha de Generacion: " . date('d/m/Y H:i:s') . "\n";
    $respaldo .= "-- =====================================================\n\n";
    $respaldo .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
    $respaldo .= "SET time_zone = \"+00:00\";\n\n";
    
    try {
        // Obtener todas las tablas de la base de datos
        $stmtTablas = $pdo->query("SHOW TABLES");
        $tablas = $stmtTablas->fetchAll(PDO::FETCH_COLUMN);
        
        foreach ($tablas as $tabla) {
            $respaldo .= "\n-- =====================================================\n";
            $respaldo .= "-- Tabla: {$tabla}\n";
            $respaldo .= "-- =====================================================\n\n";
            
            // DROP TABLE IF EXISTS para limpieza automatica
            $respaldo .= "DROP TABLE IF EXISTS `{$tabla}`;\n\n";
            
            // Obtener la estructura de la tabla (CREATE TABLE)
            $stmtCreate = $pdo->query("SHOW CREATE TABLE `{$tabla}`");
            $createTable = $stmtCreate->fetch(PDO::FETCH_ASSOC);
            $respaldo .= $createTable['Create Table'] . ";\n\n";
            
            // Obtener los datos de la tabla
            $stmtDatos = $pdo->query("SELECT * FROM `{$tabla}`");
            $datos = $stmtDatos->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($datos) > 0) {
                $respaldo .= "-- Datos de la tabla `{$tabla}`\n";
                
                foreach ($datos as $fila) {
                    $columnas = array_keys($fila);
                    $valores = array_values($fila);
                    
                    // Escapar valores para SQL
                    $valoresEscapados = array_map(function($valor) use ($pdo) {
                        if ($valor === null) {
                            return 'NULL';
                        }
                        return $pdo->quote($valor);
                    }, $valores);
                    
                    $columnasStr = '`' . implode('`, `', $columnas) . '`';
                    $valoresStr = implode(', ', $valoresEscapados);
                    
                    $respaldo .= "INSERT INTO `{$tabla}` ({$columnasStr}) VALUES ({$valoresStr});\n";
                }
                
                $respaldo .= "\n";
            } else {
                $respaldo .= "-- La tabla `{$tabla}` esta vacia\n\n";
            }
        }
        
        $respaldo .= "\n-- =====================================================\n";
        $respaldo .= "-- FIN DEL RESPALDO\n";
        $respaldo .= "-- =====================================================\n";
        
        return $respaldo;
        
    } catch (PDOException $e) {
        throw new Exception("Error al generar respaldo: " . $e->getMessage());
    }
}

// Funcion para generar respaldo en formato Excel (CSV con UTF-8 BOM)
function generarRespaldoExcel($pdo) {
    try {
        // Obtener todas las tablas
        $stmtTablas = $pdo->query("SHOW TABLES");
        $tablas = $stmtTablas->fetchAll(PDO::FETCH_COLUMN);
        
        // Crear contenido Excel (CSV con UTF-8 BOM y separador explicito)
        $contenido = "\xEF\xBB\xBF"; // UTF-8 BOM para Excel
        $contenido .= "sep=," . PHP_EOL; // Indicar a Excel que use coma como separador
        
        foreach ($tablas as $tabla) {
            // Encabezado de la tabla
            $contenido .= PHP_EOL . "TABLA: " . strtoupper($tabla) . PHP_EOL;
            $contenido .= str_repeat("=", 100) . PHP_EOL . PHP_EOL;
            
            // Obtener datos
            $stmtDatos = $pdo->query("SELECT * FROM `{$tabla}`");
            $datos = $stmtDatos->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($datos) > 0) {
                // Encabezados de columnas
                $columnas = array_keys($datos[0]);
                $columnasEscapadas = array_map(function($col) {
                    // Escapar comillas y envolver en comillas si contiene coma
                    $col = str_replace('"', '""', $col);
                    return '"' . $col . '"';
                }, $columnas);
                $contenido .= implode(",", $columnasEscapadas) . PHP_EOL;
                
                // Datos
                foreach ($datos as $fila) {
                    $valores = array_map(function($valor) {
                        if ($valor === null) return '';
                        // Escapar comillas dobles y envolver en comillas
                        $valor = str_replace('"', '""', $valor);
                        // Reemplazar saltos de linea por espacios
                        $valor = str_replace(["\n", "\r"], [" ", ""], $valor);
                        return '"' . $valor . '"';
                    }, array_values($fila));
                    $contenido .= implode(",", $valores) . PHP_EOL;
                }
            } else {
                $contenido .= "(Tabla vacia)" . PHP_EOL;
            }
            
            $contenido .= PHP_EOL . PHP_EOL;
        }
        
        return $contenido;
        
    } catch (PDOException $e) {
        throw new Exception("Error al generar respaldo Excel: " . $e->getMessage());
    }
}

// Procesar la solicitud de respaldo SQL
if (isset($_GET['generar']) && $_GET['generar'] === 'sql') {
    try {
        // Generar el nombre del archivo con formato: respaldo_total_DIA_MES_ANO_HORA.sql
        $nombreArchivo = 'respaldo_total_' . date('d_m_Y_His') . '.sql';
        
        // Generar el contenido del respaldo
        $contenidoRespaldo = generarRespaldoCompleto($pdo, $dbname);
        
        // Registrar en auditoria
        registrarAccion(
            'RESPALDO_BASE_DATOS',
            'Base de Datos Completa (SQL)',
            "El Administrador genero una copia de seguridad total del sistema en formato SQL. Archivo: {$nombreArchivo}"
        );
        
        // Configurar headers para descarga
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
        header('Content-Length: ' . strlen($contenidoRespaldo));
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        
        // Enviar el contenido
        echo $contenidoRespaldo;
        exit;
        
    } catch (Exception $e) {
        $error = "Error al generar el respaldo SQL: " . $e->getMessage();
    }
}

// Procesar la solicitud de respaldo Excel
if (isset($_GET['generar']) && $_GET['generar'] === 'excel') {
    try {
        // Generar el nombre del archivo con formato: respaldo_total_DIA_MES_ANO_HORA.csv
        $nombreArchivo = 'respaldo_total_' . date('d_m_Y_His') . '.csv';
        
        // Generar el contenido del respaldo
        $contenidoRespaldo = generarRespaldoExcel($pdo);
        
        // Registrar en auditoria
        registrarAccion(
            'RESPALDO_BASE_DATOS',
            'Base de Datos Completa (Excel/CSV)',
            "El Administrador genero una copia de seguridad total del sistema en formato Excel/CSV. Archivo: {$nombreArchivo}"
        );
        
        // Configurar headers para descarga CSV (compatible con Excel)
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
        header('Content-Length: ' . strlen($contenidoRespaldo));
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        
        // Enviar el contenido
        echo $contenidoRespaldo;
        exit;
        
    } catch (Exception $e) {
        $error = "Error al generar el respaldo Excel: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Respaldo de Base de Datos</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    
    <style>
        :root {
            --institucional-blue: #004085;
            --respaldo-blue: #0056b3;
        }
        body {
            background-image: url('BACKGROUND (1).png');
            backdrop-filter: blur(1px);
            background-size: cover;
            background-position: center top;
            background-repeat: no-repeat;
            background-attachment: fixed;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            min-height: 100vh;
            padding-bottom: 50px;
            background-color: #FFFFFF;
        }
        .container {
            padding-top: 120px;
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
            border-left: 4px solid var(--institucional-green);
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
        .btn-respaldo {
            background: linear-gradient(135deg, #0056b3 0%, #007bff 100%);
            border: none;
            color: white;
            font-weight: 700;
            font-size: 1.25rem;
            padding: 18px;
            letter-spacing: 1px;
            transition: all 0.3s;
        }
        .btn-respaldo:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(46, 125, 50, 0.4);
            color: white;
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
            color: var(--institucional-green);
            margin-right: 10px;
        }
    </style>
</head>
<body>

<div class="container">
    
    <div class="card card-respaldo">
        <div class="card-header-custom">
            <h3 class="mb-0"><i class="bi bi-database-fill-down me-2"></i>RESPALDO COMPLETO DE BASE DE DATOS</h3>
            <a href="index.php" class="btn btn-light btn-sm btn-volver">
                <i class="bi bi-arrow-left me-2"></i>Volver al Menu
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
                    Este proceso generara un archivo SQL con <strong>TODAS las tablas y datos</strong> del sistema, 
                    incluyendo usuarios, expedientes, historial de movimientos y registros de auditoría. 
                    El archivo incluye sentencias <code>DROP TABLE IF EXISTS</code> para facilitar la restauración limpia.
                </p>
            </div>
            
            <h5 class="fw-bold mb-3">Que incluye este respaldo?</h5>
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
                    <strong>Tribunales:</strong> Tabla tribunales con catalogo completo
                </li>
            </ul>
            
            <div class="alert alert-success border-0 border-start border-success border-4 mt-4" role="alert">
                <h6 class="fw-bold mb-2"><i class="bi bi-file-earmark-spreadsheet me-2"></i>Dos Formatos Disponibles:</h6>
                <ul class="mb-0">
                    <li><strong>SQL:</strong> Para restauracion completa de la base de datos (incluye DROP TABLE IF EXISTS)</li>
                    <li><strong>CSV/Excel:</strong> Para analisis, reportes y consulta de datos en hojas de calculo (formato CSV compatible con Excel)</li>
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
                                Ideal para restauracion de base de datos.
                            </p>
                            <a href="respaldar_bd.php?generar=sql" class="btn btn-success w-100"
                               data-confirm-message="Se generara un respaldo completo en formato SQL. Deseas continuar?"
                               data-confirm-title="Generar respaldo SQL"
                               data-confirm-ok="Generar SQL"
                               data-confirm-cancel="Cancelar">
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
                                Archivo .csv compatible con Excel y hojas de calculo. 
                                Ideal para analisis y reportes.
                            </p>
                            <a href="respaldar_bd.php?generar=excel" class="btn btn-primary w-100"
                               data-confirm-message="Se generara un respaldo en formato CSV compatible con Excel. Deseas continuar?"
                               data-confirm-title="Generar respaldo CSV"
                               data-confirm-ok="Generar CSV"
                               data-confirm-cancel="Cancelar">
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
                <a href="index.php" class="btn btn-secondary btn-lg">
                    <i class="bi bi-x-circle me-2"></i>Cancelar y Volver al Menu
                </a>
            </div>
            
            <div class="mt-4 p-3 bg-light rounded">
                <h6 class="fw-bold mb-2"><i class="bi bi-info-circle me-2"></i>Diferencias entre formatos:</h6>
                <div class="row">
                    <div class="col-md-6">
                        <strong>SQL (.sql)</strong>
                        <ul class="small text-muted">
                            <li>Restauracion completa de BD</li>
                            <li>Incluye DROP TABLE</li>
                            <li>Mantiene tipos de datos</li>
                            <li>Ejecutable en MySQL/MariaDB</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <strong>CSV (.csv)</strong>
                        <ul class="small text-muted">
                            <li>Visualizacion en Excel/Calc</li>
                            <li>Analisis y filtros</li>
                            <li>Formato tabular con comas</li>
                            <li>Compatible con Office y Google Sheets</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
</div>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/app-alerts.js"></script>
</body>
</html>





