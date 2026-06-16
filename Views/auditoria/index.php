<?php
/**
 * Views/auditoria/index.php
 * Vista de Auditoría del Sistema.
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auditoría del Sistema</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --azul-institucional: #004085;
            --azul-hover: #003366;
            --bg-color: #f4f6f9;
        }
        body {
            background-image: url('<?= BASE_URL ?>/background.png');
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
        .card-auditoria {
            background: white;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0, 64, 133, 0.15);
            border: none;
        }
        .card-header-custom {
            background: linear-gradient(135deg, var(--azul-institucional) 0%, #0056b3 100%);
            color: white;
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
            padding: 20px;
        }
        .table thead {
            background-color: var(--azul-institucional);
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
        .truncate-detalles {
            max-width: 220px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            display: block;
        }
        .btn-ver-detalle {
            background: none;
            border: none;
            padding: 4px 6px;
            border-radius: 6px;
            cursor: pointer;
            transition: background 0.2s, transform 0.15s;
            color: #1565c0;
            font-size: 1.1rem;
            line-height: 1;
        }
        .btn-ver-detalle:hover {
            background-color: #e3f2fd;
            transform: scale(1.15);
        }
        .btn-ver-detalle.disabled {
            color: #b0bec5;
            cursor: not-allowed;
            pointer-events: none;
        }
        th.col-ojo, td.col-ojo {
            width: 50px;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="container">
    
    <!-- Botón de Retorno -->
    <div class="mb-4">
        <a href="<?= BASE_URL ?>/" class="btn btn-secondary shadow-sm">
            <i class="bi bi-arrow-left me-2"></i>Volver al Menú
        </a>
    </div>
    
    <!-- Filtros -->
    <div class="card card-auditoria mb-4">
        <div class="card-header-custom">
            <h5 class="mb-0"><i class="bi bi-funnel me-2"></i>Filtros de Búsqueda</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="<?= BASE_URL ?>/auditoria">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Acción</label>
                        <select class="form-select" name="accion">
                            <option value="">Todas las acciones</option>
                            <?php foreach ($acciones as $acc): ?>
                                <option value="<?= htmlspecialchars($acc) ?>" <?= $filtroAccion === $acc ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($acc) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Usuario</label>
                        <select class="form-select" name="usuario">
                            <option value="">Todos los usuarios</option>
                            <?php foreach ($usuarios as $usr): ?>
                                <option value="<?= $usr['id_usuario'] ?>" <?= $filtroUsuario == $usr['id_usuario'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($usr['nombre_full']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label fw-bold">Desde</label>
                        <input type="date" class="form-control" name="fecha_desde" value="<?= htmlspecialchars($filtroFechaDesde) ?>">
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label fw-bold">Hasta</label>
                        <input type="date" class="form-control" name="fecha_hasta" value="<?= htmlspecialchars($filtroFechaHasta) ?>">
                    </div>
                    
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search me-1"></i>Filtrar
                        </button>
                    </div>
                </div>
                
                <?php if (!empty($filtroAccion) || !empty($filtroUsuario) || !empty($filtroFechaDesde) || !empty($filtroFechaHasta)): ?>
                <div class="mt-3">
                    <a href="<?= BASE_URL ?>/auditoria" class="btn btn-secondary btn-sm">
                        <i class="bi bi-x-circle me-1"></i>Limpiar Filtros
                    </a>
                </div>
                <?php endif; ?>
            </form>
        </div>
    </div>
    
    <!-- Tabla de Auditoría -->
    <div class="card card-auditoria">
        <div class="card-header-custom d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><i class="bi bi-shield-check me-2"></i>Registro de Auditoría</h4>
            <span class="badge bg-light text-dark">Total: <?= number_format($totalRegistros) ?> registros</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Fecha/Hora</th>
                            <th>Usuario</th>
                            <th>Acción</th>
                            <th>Recurso</th>
                            <th>IP</th>
                            <th>Detalles</th>
                            <th class="col-ojo pe-3"><i class="bi bi-eye text-white opacity-75"></i></th>
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
                                <td><code><?= htmlspecialchars($log['recurso'] ?? '') ?></code></td>
                                <td><span class="ip-badge"><?= htmlspecialchars($log['ip_maquina'] ?? '') ?></span></td>
                                <td>
                                    <?php if (!empty($log['detalles'])): ?>
                                        <small class="text-muted truncate-detalles"><?= htmlspecialchars(substr($log['detalles'], 0, 80)) ?><?= strlen($log['detalles']) > 80 ? '...' : '' ?></small>
                                    <?php else: ?>
                                        <small class="text-muted fst-italic">Sin detalles</small>
                                    <?php endif; ?>
                                </td>
                                <td class="col-ojo pe-3">
                                    <?php if (!empty($log['detalles'])): ?>
                                        <button
                                            class="btn-ver-detalle"
                                            title="Ver detalles completos"
                                            data-detalles="<?= htmlspecialchars($log['detalles'], ENT_QUOTES) ?>"
                                            data-fecha="<?= htmlspecialchars(date('d/m/Y H:i:s', strtotime($log['fecha_hora']))) ?>"
                                            data-accion="<?= htmlspecialchars($log['accion'], ENT_QUOTES) ?>"
                                            onclick="abrirDetalleModal(this)"
                                        >
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    <?php else: ?>
                                        <button class="btn-ver-detalle disabled" title="Sin detalles disponibles" disabled>
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1"></i>
                                    <p class="mt-3">No hay registros de auditoría que coincidan con los filtros</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Paginación -->
        <?php if ($totalPaginas > 1): ?>
        <div class="card-footer bg-white border-0 py-3">
            <nav>
                <ul class="pagination justify-content-center mb-0">
                    <li class="page-item <?= $paginaActual <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="?pagina=<?= $paginaActual - 1 ?>&accion=<?= urlencode($filtroAccion) ?>&usuario=<?= urlencode($filtroUsuario) ?>&fecha_desde=<?= urlencode($filtroFechaDesde) ?>&fecha_hasta=<?= urlencode($filtroFechaHasta) ?>">
                            Anterior
                        </a>
                    </li>
                    
                    <?php for ($i = max(1, $paginaActual - 2); $i <= min($totalPaginas, $paginaActual + 2); $i++): ?>
                        <li class="page-item <?= $i === $paginaActual ? 'active' : '' ?>">
                            <a class="page-link" href="?pagina=<?= $i ?>&accion=<?= urlencode($filtroAccion) ?>&usuario=<?= urlencode($filtroUsuario) ?>&fecha_desde=<?= urlencode($filtroFechaDesde) ?>&fecha_hasta=<?= urlencode($filtroFechaHasta) ?>">
                                <?= $i ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                    
                    <li class="page-item <?= $paginaActual >= $totalPaginas ? 'disabled' : '' ?>">
                        <a class="page-link" href="?pagina=<?= $paginaActual + 1 ?>&accion=<?= urlencode($filtroAccion) ?>&usuario=<?= urlencode($filtroUsuario) ?>&fecha_desde=<?= urlencode($filtroFechaDesde) ?>&fecha_hasta=<?= urlencode($filtroFechaHasta) ?>">
                            Siguiente
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
        <?php endif; ?>
    </div>

</div>

<!-- Modal único reutilizable para detalles de auditoría -->
<div class="modal fade" id="detalleModalGlobal" tabindex="-1" aria-labelledby="detalleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content text-start">
            <div class="modal-header" style="background: linear-gradient(135deg, #004085, #0056b3);">
                <div>
                    <h5 class="modal-title text-white mb-0" id="detalleModalLabel">
                        <i class="bi bi-eye-fill me-2"></i>Detalles del Registro
                    </h5>
                    <small class="text-white opacity-75" id="modalSubtitulo"></small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body bg-light p-3">
                <pre id="modalContenido" style="white-space: pre-wrap; font-size: 0.88rem; font-family: monospace; margin: 0;" class="p-3 bg-white border rounded"></pre>
            </div>
            <div class="modal-footer border-0 bg-white">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i>Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function abrirDetalleModal(btn) {
    const detalles = btn.getAttribute('data-detalles');
    const fecha    = btn.getAttribute('data-fecha');
    const accion   = btn.getAttribute('data-accion');

    document.getElementById('modalContenido').textContent  = detalles;
    document.getElementById('modalSubtitulo').textContent  = accion + '  —  ' + fecha;

    const modal = new bootstrap.Modal(document.getElementById('detalleModalGlobal'));
    modal.show();
}
</script>
</body>
</html>
