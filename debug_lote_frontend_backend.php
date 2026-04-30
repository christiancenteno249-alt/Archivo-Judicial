<?php
require_once "conexion.php";
require_once "auth_check.php";

// ============================================================
// BACKEND DEBUG - Procesar datos recibidos
// ============================================================
$debug_info = [
    'timestamp' => date('Y-m-d H:i:s'),
    'method' => $_SERVER['REQUEST_METHOD'],
    'post_data' => $_POST,
    'post_raw' => file_get_contents('php://input'),
    'content_type' => $_SERVER['CONTENT_TYPE'] ?? 'no definido',
    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'no definido'
];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $debug_info['procesamiento'] = [];
    
    // Extraer datos
    $expedientes_ids = $_POST['expedientes_seleccionados'] ?? [];
    $id_sede = trim($_POST['id_sede_lote'] ?? '');
    $ubicacion_area = trim($_POST['ubicacion_area_lote'] ?? '');
    $ubicacion_detalle = trim($_POST['ubicacion_detalle_lote'] ?? '');
    
    $debug_info['datos_extraidos'] = [
        'expedientes_ids' => $expedientes_ids,
        'expedientes_count' => count($expedientes_ids),
        'id_sede' => $id_sede,
        'ubicacion_area' => $ubicacion_area,
        'ubicacion_detalle' => $ubicacion_detalle
    ];
    
    // Validaciones
    $debug_info['validaciones'] = [
        'expedientes_empty' => empty($expedientes_ids),
        'sede_empty' => empty($id_sede),
        'expedientes_is_array' => is_array($expedientes_ids)
    ];
    
    // Si pasa validaciones, intentar procesar
    if (!empty($expedientes_ids) && !empty($id_sede)) {
        try {
            // Verificar sede
            $stmtSede = $pdo->prepare("SELECT id_sede, nombre_sede FROM sedes_deposito WHERE id_sede = :id_sede");
            $stmtSede->execute([':id_sede' => $id_sede]);
            $sede = $stmtSede->fetch();
            
            $debug_info['sede_verificacion'] = [
                'encontrada' => $sede ? true : false,
                'datos' => $sede ?: 'No encontrada'
            ];
            
            if ($sede) {
                $debug_info['expedientes_verificacion'] = [];
                
                foreach ($expedientes_ids as $index => $id_expediente) {
                    $id_expediente = (int)$id_expediente;
                    
                    $stmtCheck = $pdo->prepare("SELECT Id, n_expediente, id_sede as sede_actual FROM maestro WHERE Id = :id LIMIT 1");
                    $stmtCheck->execute([':id' => $id_expediente]);
                    $expediente_data = $stmtCheck->fetch();
                    
                    $exp_debug = [
                        'index' => $index,
                        'id_recibido' => $id_expediente,
                        'encontrado' => $expediente_data ? true : false
                    ];
                    
                    if ($expediente_data) {
                        $exp_debug['datos'] = $expediente_data;
                        
                        // Intentar UPDATE
                        $sqlUpdate = "UPDATE maestro 
                                      SET id_sede = :id_sede, 
                                          ubicacion_area = :ubicacion_area, 
                                          ubicacion_detalle = :ubicacion_detalle,
                                          fecha_ultima_ubicacion = NOW()
                                      WHERE Id = :id";
                        
                        $stmtUpdate = $pdo->prepare($sqlUpdate);
                        $resultado = $stmtUpdate->execute([
                            ':id_sede' => $id_sede,
                            ':ubicacion_area' => $ubicacion_area,
                            ':ubicacion_detalle' => $ubicacion_detalle,
                            ':id' => $id_expediente
                        ]);
                        
                        $exp_debug['update'] = [
                            'ejecutado' => $resultado,
                            'filas_afectadas' => $stmtUpdate->rowCount(),
                            'error_info' => $stmtUpdate->errorInfo()
                        ];
                        
                        // Verificar cambio
                        $stmtVerify = $pdo->prepare("SELECT Id, n_expediente, id_sede, ubicacion_area, ubicacion_detalle FROM maestro WHERE Id = :id");
                        $stmtVerify->execute([':id' => $id_expediente]);
                        $verificacion = $stmtVerify->fetch();
                        
                        $exp_debug['verificacion_post_update'] = $verificacion;
                    }
                    
                    $debug_info['expedientes_verificacion'][] = $exp_debug;
                }
            }
            
        } catch (Exception $e) {
            $debug_info['error_exception'] = [
                'mensaje' => $e->getMessage(),
                'archivo' => $e->getFile(),
                'linea' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug Frontend + Backend - Carga por Lote</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .debug-section {
            background: #f8f9fa;
            border-left: 4px solid #007bff;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .debug-success { border-left-color: #28a745; background: #d4edda; }
        .debug-error { border-left-color: #dc3545; background: #f8d7da; }
        .debug-warning { border-left-color: #ffc107; background: #fff3cd; }
        pre {
            background: #2d2d2d;
            color: #f8f8f2;
            padding: 15px;
            border-radius: 4px;
            overflow-x: auto;
            max-height: 400px;
        }
        .badge-custom {
            font-size: 0.9rem;
            padding: 5px 10px;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <h2 class="mb-4">🔍 Debug Completo: Frontend + Backend</h2>
        
        <?php if ($_SERVER["REQUEST_METHOD"] == "POST"): ?>
            <div class="alert alert-info">
                <strong>📥 Datos recibidos en el servidor</strong>
            </div>
            
            <div class="debug-section">
                <h5>📊 Información General</h5>
                <p><strong>Timestamp:</strong> <?= $debug_info['timestamp'] ?></p>
                <p><strong>Método:</strong> <span class="badge bg-primary"><?= $debug_info['method'] ?></span></p>
                <p><strong>Content-Type:</strong> <?= $debug_info['content_type'] ?></p>
            </div>
            
            <div class="debug-section">
                <h5>📦 Datos POST Recibidos</h5>
                <pre><?= json_encode($debug_info['post_data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?></pre>
            </div>
            
            <div class="debug-section">
                <h5>🔧 Datos Extraídos y Procesados</h5>
                <pre><?= json_encode($debug_info['datos_extraidos'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?></pre>
            </div>
            
            <div class="debug-section <?= $debug_info['validaciones']['expedientes_empty'] || $debug_info['validaciones']['sede_empty'] ? 'debug-error' : 'debug-success' ?>">
                <h5>✅ Validaciones</h5>
                <pre><?= json_encode($debug_info['validaciones'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?></pre>
            </div>
            
            <?php if (isset($debug_info['sede_verificacion'])): ?>
                <div class="debug-section <?= $debug_info['sede_verificacion']['encontrada'] ? 'debug-success' : 'debug-error' ?>">
                    <h5>🏢 Verificación de Sede</h5>
                    <pre><?= json_encode($debug_info['sede_verificacion'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?></pre>
                </div>
            <?php endif; ?>
            
            <?php if (isset($debug_info['expedientes_verificacion'])): ?>
                <div class="debug-section">
                    <h5>📋 Verificación de Expedientes (<?= count($debug_info['expedientes_verificacion']) ?> expedientes)</h5>
                    <?php foreach ($debug_info['expedientes_verificacion'] as $exp_debug): ?>
                        <div class="card mb-3 <?= $exp_debug['encontrado'] ? 'border-success' : 'border-danger' ?>">
                            <div class="card-header">
                                <strong>Expediente #<?= $exp_debug['index'] ?></strong> - ID: <?= $exp_debug['id_recibido'] ?>
                                <?php if ($exp_debug['encontrado']): ?>
                                    <span class="badge bg-success float-end">Encontrado</span>
                                <?php else: ?>
                                    <span class="badge bg-danger float-end">No encontrado</span>
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <pre><?= json_encode($exp_debug, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?></pre>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($debug_info['error_exception'])): ?>
                <div class="debug-section debug-error">
                    <h5>❌ Error de Excepción</h5>
                    <pre><?= json_encode($debug_info['error_exception'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?></pre>
                </div>
            <?php endif; ?>
            
            <div class="debug-section">
                <h5>📄 Debug Completo (JSON)</h5>
                <pre><?= json_encode($debug_info, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?></pre>
            </div>
            
        <?php else: ?>
            <div class="alert alert-warning">
                <strong>⚠️ No se recibieron datos POST</strong>
                <p class="mb-0">Usa el formulario del gestor de ubicaciones para enviar datos.</p>
            </div>
        <?php endif; ?>
        
        <hr>
        <div class="d-flex gap-2">
            <a href="gestionar_ubicaciones.php?modo=lote" class="btn btn-primary">
                ← Volver al Gestor de Ubicaciones
            </a>
            <button onclick="location.reload()" class="btn btn-secondary">
                🔄 Recargar Debug
            </button>
        </div>
    </div>
    
    <script>
        // FRONTEND DEBUG
        console.log('=== DEBUG FRONTEND ===');
        console.log('Página cargada:', new Date().toISOString());
        console.log('User Agent:', navigator.userAgent);
        console.log('======================');
    </script>
</body>
</html>