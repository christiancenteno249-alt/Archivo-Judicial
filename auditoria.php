<?php
require_once "conexion.php";
require_once "auth_check.php";

// Solo administradores pueden ver la auditoria
if ($_SESSION['usuario_rol'] !== 'admin') {
    header('Location: index.php');
    exit;
}

// Parametros de filtrado
$filtro_accion = $_GET['accion'] ?? '';
$filtro_usuario = $_GET['usuario'] ?? '';
$filtro_fecha_desde = $_GET['fecha_desde'] ?? '';
$filtro_fecha_hasta = $_GET['fecha_hasta'] ?? '';

// Paginacion
$registros_por_pagina = 50;
$pagina_actual = isset($_GET['pagina']) && is_numeric($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
if ($pagina_actual < 1) $pagina_actual = 1;
$offset = ($pagina_actual - 1) * $registros_por_pagina;

// Construir consulta con filtros
$sqlBase = " FROM auditoria_log a 
             LEFT JOIN usuarios_sistema u ON a.id_usuario = u.id_usuario 
             WHERE 1=1";

$parametros = [];

if (!empty($filtro_accion)) {
    $sqlBase .= " AND a.accion = :accion";
    $parametros[':accion'] = $filtro_accion;
}

if (!empty($filtro_usuario)) {
    $sqlBase .= " AND a.id_usuario = :usuario";
    $parametros[':usuario'] = $filtro_usuario;
}

if (!empty($filtro_fecha_desde)) {
    $sqlBase .= " AND DATE(a.fecha_hora) >= :fecha_desde";
    $parametros[':fecha_desde'] = $filtro_fecha_desde;
}

if (!empty($filtro_fecha_hasta)) {
    $sqlBase .= " AND DATE(a.fecha_hora) <= :fecha_hasta";
    $parametros[':fecha_hasta'] = $filtro_fecha_hasta;
}

// Contar total de registros
$sqlCount = "SELECT COUNT(*) as total" . $sqlBase;
$stmtCount = $pdo->prepare($sqlCount);
$stmtCount->execute($parametros);
$total_registros = $stmtCount->fetch()['total'];
$total_paginas = ceil($total_registros / $registros_por_pagina);

// Obtener registros de auditoria
$sql = "SELECT a.*, u.nombre_full, u.usuario_nick" . $sqlBase . " ORDER BY a.fecha_hora DESC LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);

foreach ($parametros as $key => &$val) {
    $stmt->bindParam($key, $val);
}
$stmt->bindValue(':limit', $registros_por_pagina, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

$stmt->execute();
$logs = $stmt->fetchAll();

// Obtener lista de acciones unicas para el filtro
$acciones = $pdo->query("SELECT DISTINCT accion FROM auditoria_log ORDER BY accion")->fetchAll(PDO::FETCH_COLUMN);

// Obtener lista de usuarios para el filtro
$usuarios = $pdo->query("SELECT id_usuario, nombre_full FROM usuarios_sistema ORDER BY nombre_full")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auditoría del Sistema</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/bootstrap-icons.css">
    
    <style>
        :root {
            --institucional-blue: #1a237e;
        }
        body {
            background-image: url('/background.png');
            background-size: cover;
            background-position: top center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            min-height: 100vh;
            padding-bottom: 50px;
        }
        .container {
            padding-top: 100px;
        }
        .card-auditoria {
            background: white;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.15);
            border: none;
        }
        .card-header-custom {
            background-color: var(--institucional-blue);
            color: white;
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
            padding: 20px;
        }
        .table thead {
            background-color: var(--institucional-blue);
            color: white;
        }
        .table th {
            font-size: 0.85rem;
            font-weight: 600;
        }
        .badge-login { background-color: #2e7d32; }
        .badge-logout { background-color: #757575; }
        .badge-crear { background-color: #1976d2; }
        .badge-actualizar { background-color: #f57c00; }
        .badge-editar { background-color: #9c27b0; }
        .badge-eliminar { background-color: #d32f2f; }
        .badge-intento { background-color: #c62828; }
        .badge-sobrescritura { background-color: #ff6f00; font-weight: bold; }
        .badge-duplicidad { background-color: #ff9800; font-weight: bold; }
        .badge-respaldo { background-color: #2e7d32; font-weight: bold; }
        .ip-badge {
            font-family: monospace;
            font-size: 0.85rem;
            background-color: #e3f2fd;
            color: #1565c0;
            padding: 3px 8px;
            border-radius: 4px;
        }
        
        /* Truncado de texto con tooltip */
        .truncate-text {
            max-width: 200px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            display: inline-block;
            vertical-align: middle;
            cursor: help;
        }
        .truncate-detalles {
            max-width: 250px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            display: block;
        }
    </style>
</head>
<body>

<div class="container">
    
    <!-- Boton de Retorno -->
    <div class="mb-4">
        <a href="index.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-2"></i>Volver al Menu
        </a>
    </div>
    
    <!-- Filtros -->
    <div class="card card-auditoria mb-4">
        <div class="card-header-custom">
            <h5 class="mb-0"><i class="bi bi-funnel me-2"></i>Filtros de Busqueda</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="auditoria.php">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Accion</label>
                        <select class="form-select" name="accion">
                            <option value="">Todas las acciones</option>
                            <?php foreach ($acciones as $accion): ?>
                                <option value="<?= htmlspecialchars($accion) ?>" <?= $filtro_accion === $accion ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($accion) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Usuario</label>
                        <select class="form-select" name="usuario">
                            <option value="">Todos los usuarios</option>
                            <?php foreach ($usuarios as $user): ?>
                                <option value="<?= $user['id_usuario'] ?>" <?= $filtro_usuario == $user['id_usuario'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($user['nombre_full']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label fw-bold">Desde</label>
                        <input type="date" class="form-control" name="fecha_desde" value="<?= htmlspecialchars($filtro_fecha_desde) ?>">
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label fw-bold">Hasta</label>
                        <input type="date" class="form-control" name="fecha_hasta" value="<?= htmlspecialchars($filtro_fecha_hasta) ?>">
                    </div>
                    
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search me-1"></i>Filtrar
                        </button>
                    </div>
                </div>
                
                <?php if (!empty($filtro_accion) || !empty($filtro_usuario) || !empty($filtro_fecha_desde) || !empty($filtro_fecha_hasta)): ?>
                <div class="mt-3">
                    <a href="auditoria.php" class="btn btn-secondary btn-sm">
                        <i class="bi bi-x-circle me-1"></i>Limpiar Filtros
                    </a>
                </div>
                <?php endif; ?>
            </form>
        </div>
    </div>
    
    <!-- Tabla de Auditoria -->
    <div class="card card-auditoria">
        <div class="card-header-custom d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><i class="bi bi-shield-check me-2"></i>Registro de Auditoría</h4>
            <span class="badge bg-light text-dark">Total: <?= number_format($total_registros) ?> registros</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">Fecha/Hora</th>
                            <th>Usuario</th>
                            <th>Accion</th>
                            <th>Recurso</th>
                            <th>IP</th>
                            <th class="pe-4">Detalles</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($logs) > 0): ?>
                            <?php foreach ($logs as $log): ?>
                            <tr>
                                <td class="ps-4">
                                    <small><?= date('d/m/Y H:i:s', strtotime($log['fecha_hora'])) ?></small>
                                </td>
                                <td>
                                    <?php if ($log['nombre_full']): ?>
                                        <strong><?= htmlspecialchars($log['nombre_full']) ?></strong>
                                        <br><small class="text-muted"><?= htmlspecialchars($log['usuario_nick']) ?></small>
                                    <?php else: ?>
                                        <span class="text-muted">Sistema</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $badge_class = 'badge-login';
                                    if (strpos($log['accion'], 'LOGOUT') !== false) $badge_class = 'badge-logout';
                                    elseif (strpos($log['accion'], 'CREAR') !== false) $badge_class = 'badge-crear';
                                    elseif (strpos($log['accion'], 'EDITAR') !== false) $badge_class = 'badge-editar';
                                    elseif (strpos($log['accion'], 'DUPLICIDAD') !== false) $badge_class = 'badge-duplicidad';
                                    elseif (strpos($log['accion'], 'ACTUALIZAR') !== false) $badge_class = 'badge-actualizar';
                                    elseif (strpos($log['accion'], 'ELIMINAR') !== false) $badge_class = 'badge-eliminar';
                                    elseif (strpos($log['accion'], 'INTENTO') !== false) $badge_class = 'badge-intento';
                                    elseif (strpos($log['accion'], 'SOBRESCRITURA') !== false) $badge_class = 'badge-sobrescritura';
                                    elseif (strpos($log['accion'], 'RESPALDO') !== false) $badge_class = 'badge-respaldo';
                                    ?>
                                    <span class="badge <?= $badge_class ?>"><?= htmlspecialchars($log['accion']) ?></span>
                                </td>
                                <td><code><?= htmlspecialchars($log['recurso']) ?></code></td>
                                <td><span class="ip-badge"><?= htmlspecialchars($log['ip_maquina']) ?></span></td>
                                <td class="pe-4" title="<?= htmlspecialchars($log['detalles']) ?>">
                                    <?php if (!empty($log['detalles'])): ?>
                                        <?php if (strlen($log['detalles']) > 100): ?>
                                            <small class="text-muted truncate-detalles"><?= htmlspecialchars(substr($log['detalles'], 0, 100)) ?>...</small>
                                            <button class="btn btn-sm btn-link p-0" type="button" data-bs-toggle="modal" data-bs-target="#detalleModal<?= $log['id_log'] ?>">
                                                Ver mas
                                            </button>
                                            
                                            <!-- Modal para detalles completos -->
                                            <div class="modal fade" id="detalleModal<?= $log['id_log'] ?>" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Detalles Completos</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <pre style="white-space: pre-wrap; font-size: 0.9rem;"><?= htmlspecialchars($log['detalles']) ?></pre>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <small class="text-muted truncate-detalles"><?= htmlspecialchars($log['detalles']) ?></small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <small class="text-muted">Sin detalles</small>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1"></i>
                                    <p class="mt-3">No hay registros de auditoria</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Paginacion -->
        <?php if ($total_paginas > 1): ?>
        <div class="card-footer">
            <nav>
                <ul class="pagination justify-content-center mb-0">
                    <li class="page-item <?= $pagina_actual <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="?pagina=<?= $pagina_actual - 1 ?>&accion=<?= urlencode($filtro_accion) ?>&usuario=<?= urlencode($filtro_usuario) ?>&fecha_desde=<?= urlencode($filtro_fecha_desde) ?>&fecha_hasta=<?= urlencode($filtro_fecha_hasta) ?>">
                            Anterior
                        </a>
                    </li>
                    
                    <?php for ($i = max(1, $pagina_actual - 2); $i <= min($total_paginas, $pagina_actual + 2); $i++): ?>
                        <li class="page-item <?= $i === $pagina_actual ? 'active' : '' ?>">
                            <a class="page-link" href="?pagina=<?= $i ?>&accion=<?= urlencode($filtro_accion) ?>&usuario=<?= urlencode($filtro_usuario) ?>&fecha_desde=<?= urlencode($filtro_fecha_desde) ?>&fecha_hasta=<?= urlencode($filtro_fecha_hasta) ?>">
                                <?= $i ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                    
                    <li class="page-item <?= $pagina_actual >= $total_paginas ? 'disabled' : '' ?>">
                        <a class="page-link" href="?pagina=<?= $pagina_actual + 1 ?>&accion=<?= urlencode($filtro_accion) ?>&usuario=<?= urlencode($filtro_usuario) ?>&fecha_desde=<?= urlencode($filtro_fecha_desde) ?>&fecha_hasta=<?= urlencode($filtro_fecha_hasta) ?>">
                            Siguiente
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
        <?php endif; ?>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>





