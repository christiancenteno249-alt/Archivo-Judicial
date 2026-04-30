<?php
require_once "conexion.php";
require_once "auth_check.php";
require_once "auditoria_functions.php";

$mensaje = '';
$tipo_alerta = '';
$modo = $_GET['modo'] ?? 'individual'; // 'individual' o 'lote'

// Mantener valores de sesion para UX rapida
if (!isset($_SESSION['ultima_sede'])) $_SESSION['ultima_sede'] = '';
if (!isset($_SESSION['ultima_area'])) $_SESSION['ultima_area'] = '';

// Cargar sedes activas
$sedes = [];
try {
    $stmtSedes = $pdo->query("SELECT id_sede, nombre_sede, descripcion, direccion FROM sedes_deposito WHERE activo = 1 ORDER BY nombre_sede ASC");
    $sedes = $stmtSedes->fetchAll();
    
    // DEBUG: Verificar si hay sedes
    if (empty($sedes) && $_SESSION['usuario_rol'] === 'admin') {
        $mensaje = " ADVERTENCIA: No hay sedes registradas en la base de datos. Por favor, ejecuta el script 'verificar_y_crear_sedes.sql' en phpMyAdmin.";
        $tipo_alerta = 'warning';
    }
} catch (PDOException $e) {
    $mensaje = "Error al cargar sedes: " . $e->getMessage() . "<br><small>Verifica que la tabla 'sedes_deposito' exista en la base de datos.</small>";
    $tipo_alerta = 'danger';
}

// ============================================================
// MODO INDIVIDUAL: Buscar expediente y asignar ubicacion
// ============================================================
$expediente_encontrado = null;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['buscar_expediente'])) {
    $n_expediente = trim($_POST['n_expediente_buscar'] ?? '');
    
    if (empty($n_expediente)) {
        $mensaje = 'Ingresa un numero de expediente para buscar.';
        $tipo_alerta = 'warning';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT Id, n_expediente, demandante, demandado, id_sede, ubicacion_area, ubicacion_detalle FROM maestro WHERE n_expediente = :expediente LIMIT 1");
            $stmt->execute([':expediente' => $n_expediente]);
            $expediente_encontrado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$expediente_encontrado) {
                $mensaje = " Expediente '{$n_expediente}' no encontrado en el sistema. Verifica el numero e intenta nuevamente.";
                $tipo_alerta = 'danger';
            }
        } catch (PDOException $e) {
            $mensaje = "Error en la busqueda: " . $e->getMessage();
            $tipo_alerta = 'danger';
        }
    }
}

// ============================================================
// GUARDAR UBICACION INDIVIDUAL
// ============================================================
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['guardar_ubicacion_individual'])) {
    $id_expediente = trim($_POST['id_expediente'] ?? '');
    $n_expediente = trim($_POST['n_expediente'] ?? '');
    $id_sede = trim($_POST['id_sede'] ?? '');
    $ubicacion_area = trim($_POST['ubicacion_area'] ?? '');
    $ubicacion_detalle = trim($_POST['ubicacion_detalle'] ?? '');
    
    if (empty($id_expediente) || empty($id_sede)) {
        $mensaje = 'Debes seleccionar una sede para asignar la ubicacion.';
        $tipo_alerta = 'warning';
    } else {
        try {
            // Obtener nombre de la sede para auditoria
            $stmtSede = $pdo->prepare("SELECT nombre_sede FROM sedes_deposito WHERE id_sede = :id_sede");
            $stmtSede->execute([':id_sede' => $id_sede]);
            $sede = $stmtSede->fetch();
            $nombre_sede = $sede['nombre_sede'] ?? 'Desconocida';
            
            // Actualizar ubicacion
            $sqlUpdate = "UPDATE maestro 
                          SET id_sede = :id_sede, 
                              ubicacion_area = :ubicacion_area, 
                              ubicacion_detalle = :ubicacion_detalle,
                              fecha_ultima_ubicacion = NOW()
                          WHERE Id = :id 
                          LIMIT 1";
            
            $stmtUpdate = $pdo->prepare($sqlUpdate);
            $stmtUpdate->execute([
                ':id_sede' => $id_sede,
                ':ubicacion_area' => $ubicacion_area,
                ':ubicacion_detalle' => $ubicacion_detalle,
                ':id' => $id_expediente
            ]);
            
            // Registrar en auditoria
            $detalle_auditoria = "Cambio de Ubicacion: {$n_expediente} movido a {$nombre_sede}";
            if (!empty($ubicacion_area)) $detalle_auditoria .= " - {$ubicacion_area}";
            if (!empty($ubicacion_detalle)) $detalle_auditoria .= " - {$ubicacion_detalle}";
            
            registrarAccion('CAMBIO_UBICACION', "Exp: {$n_expediente}", $detalle_auditoria);
            
            // Guardar en sesion para UX rapida
            $_SESSION['ultima_sede'] = $id_sede;
            $_SESSION['ultima_area'] = $ubicacion_area;
            
            $mensaje = "Ubicacion asignada correctamente al expediente {$n_expediente}.";
            $tipo_alerta = 'success';
            
            // Limpiar busqueda
            unset($expediente_encontrado);
            
        } catch (PDOException $e) {
            $mensaje = "Error al guardar ubicacion: " . $e->getMessage();
            $tipo_alerta = 'danger';
        }
    }
}

// ============================================================
// GUARDAR UBICACION POR LOTE (NUEVA LOGICA CON IDs)
// ============================================================
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['guardar_ubicacion_lote'])) {
    $expedientes_ids = $_POST['expedientes_seleccionados'] ?? [];
    $id_sede = trim($_POST['id_sede_lote'] ?? '');
    $ubicacion_area = trim($_POST['ubicacion_area_lote'] ?? '');
    $ubicacion_detalle = trim($_POST['ubicacion_detalle_lote'] ?? '');
    
    // Validación básica
    if (empty($expedientes_ids)) {
        $mensaje = '⚠️ Debes seleccionar al menos un expediente para procesar.';
        $tipo_alerta = 'warning';
    } elseif (empty($id_sede)) {
        $mensaje = '⚠️ Debes seleccionar una sede para asignar la ubicación.';
        $tipo_alerta = 'warning';
    } else {
        try {
            // Obtener nombre de la sede
            $stmtSede = $pdo->prepare("SELECT nombre_sede FROM sedes_deposito WHERE id_sede = :id_sede");
            $stmtSede->execute([':id_sede' => $id_sede]);
            $sede = $stmtSede->fetch();
            
            if (!$sede) {
                throw new Exception("Sede con ID $id_sede no encontrada");
            }
            
            $nombre_sede = $sede['nombre_sede'];
            
            $actualizados = 0;
            $errores = [];
            $expedientes_procesados = [];
            
            // Iniciar transacción
            $pdo->beginTransaction();
            
            // Procesar cada expediente
            foreach ($expedientes_ids as $id_expediente) {
                $id_expediente = (int)$id_expediente;
                
                try {
                    // Verificar que el expediente existe
                    $stmtCheck = $pdo->prepare("SELECT Id, n_expediente FROM maestro WHERE Id = :id LIMIT 1");
                    $stmtCheck->execute([':id' => $id_expediente]);
                    $expediente_data = $stmtCheck->fetch();
                    
                    if (!$expediente_data) {
                        $errores[] = "Expediente ID $id_expediente no encontrado";
                        continue;
                    }
                    
                    $n_expediente = $expediente_data['n_expediente'];
                    
                    // Actualizar ubicación
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
                    
                    if ($resultado && $stmtUpdate->rowCount() > 0) {
                        // Registrar en auditoría
                        $detalle_auditoria = "Cambio de Ubicación (Lote): {$n_expediente} movido a {$nombre_sede}";
                        if (!empty($ubicacion_area)) $detalle_auditoria .= " - {$ubicacion_area}";
                        if (!empty($ubicacion_detalle)) $detalle_auditoria .= " - {$ubicacion_detalle}";
                        
                        registrarAccion('CAMBIO_UBICACION_LOTE', "Exp: {$n_expediente}", $detalle_auditoria);
                        
                        $actualizados++;
                        $expedientes_procesados[] = $id_expediente;
                    }
                    
                } catch (Exception $e) {
                    $errores[] = "Error en expediente ID $id_expediente: " . $e->getMessage();
                }
            }
            
            // Confirmar transacción
            $pdo->commit();
            
            // Guardar en sesión para UX rápida
            $_SESSION['ultima_sede'] = $id_sede;
            $_SESSION['ultima_area'] = $ubicacion_area;
            
            // Mensaje de resultado
            if ($actualizados > 0) {
                $mensaje = "✅ Se actualizaron {$actualizados} expediente(s) correctamente a la ubicación: {$nombre_sede}";
                if (!empty($errores)) {
                    $mensaje .= "<br><small class='text-muted'>Advertencias: " . implode(", ", $errores) . "</small>";
                }
                $tipo_alerta = 'success';
                
                // JavaScript para efecto de desvanecimiento
                echo "<script>var expedientesProcesados = " . json_encode($expedientes_procesados) . ";</script>";
            } else {
                $mensaje = "❌ No se pudo actualizar ningún expediente.";
                if (!empty($errores)) {
                    $mensaje .= "<br><small>Errores: " . implode("<br>", $errores) . "</small>";
                }
                $tipo_alerta = 'danger';
            }
            
        } catch (Exception $e) {
            // Revertir transacción en caso de error
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $mensaje = "❌ Error al procesar el lote: " . $e->getMessage();
            $tipo_alerta = 'danger';
        }
    }
}

// ============================================================
// CARGAR EXPEDIENTES SIN UBICACION PARA MODO LOTE
// ============================================================
$expedientes_sin_ubicacion = [];
$total_sin_ubicacion = 0;

if ($modo === 'lote') {
    try {
        // Contar total de expedientes sin ubicacion
        $stmtCount = $pdo->query("SELECT COUNT(*) as total FROM maestro WHERE id_sede IS NULL OR id_sede = 0 OR id_sede = ''");
        $total_sin_ubicacion = $stmtCount->fetch()['total'];
        
        // Obtener expedientes sin ubicacion (limitado para rendimiento)
        $sqlExpedientes = "SELECT Id, n_expediente, demandante, demandado, fecha_entrada 
                          FROM maestro 
                          WHERE id_sede IS NULL OR id_sede = 0 OR id_sede = ''
                          ORDER BY fecha_entrada DESC 
                          LIMIT 500";
        
        $stmtExpedientes = $pdo->query($sqlExpedientes);
        $expedientes_sin_ubicacion = $stmtExpedientes->fetchAll();
        
    } catch (PDOException $e) {
        $mensaje = "Error al cargar expedientes: " . $e->getMessage();
        $tipo_alerta = 'danger';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Ubicaciones - Archivo Judicial</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/select/1.7.0/css/select.bootstrap5.min.css">
    
    <style>
        :root {
            --institucional-blue: #004085;
            --ubicacion-blue: #0056b3;
        }
        body {
            background-image: url('BACKGROUND (1).png');
            background-size: cover;
            background-position: top center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            min-height: 100vh;
            padding-bottom: 50px;
            background-color: #FFFFFF;
            margin: 0;
            padding-top: 0;
        }
        .container {
            padding-top: 80px;
            position: relative;
            z-index: 1;
            margin-top: 0;
        }
        
        /* Eliminar cualquier elemento flotante en la parte superior */
        .container::before,
        .container::after {
            display: none;
        }
        
        /* Asegurar que no hay elementos absolutos problemáticos */
        .card-ubicaciones {
            background: #FFFFFF;
            border-radius: 12px;
            box-shadow: none;
            border: 1px solid rgba(0,64,133,0.1);
            position: relative;
            z-index: 2;
            margin-top: 20px;
        }
        .card-header-custom {
            background: linear-gradient(135deg, #004085 0%, #0056b3 100%);
            color: white;
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
            padding: 20px;
            position: relative;
            z-index: 3;
            box-shadow: none;
        }
        
        /* Botón de retorno sin sombras problemáticas */
        .btn {
            box-shadow: none !important;
        }
        
        /* Eliminar sombras de todos los elementos en la parte superior */
        .mb-4 {
            box-shadow: none;
            position: relative;
            z-index: 10;
        }
        .nav-tabs {
            position: relative;
            z-index: 4;
        }
        .nav-tabs .nav-link {
            color: var(--institucional-blue);
            font-weight: 600;
            position: relative;
            z-index: 5;
        }
        .nav-tabs .nav-link.active {
            background-color: var(--ubicacion-blue);
            color: white;
            border-color: var(--ubicacion-blue);
            position: relative;
            z-index: 6;
        }
        .expediente-card {
            background-color: #f8f9fa;
            border-left: 4px solid var(--ubicacion-blue);
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            position: relative;
            z-index: 2;
        }
        .badge-ubicacion {
            background-color: var(--ubicacion-blue);
            font-size: 0.9rem;
            padding: 8px 12px;
        }
        
        /* Corregir problemas de superposición */
        .table-responsive {
            position: relative;
            z-index: 1;
        }
        
        .dataTables_wrapper {
            position: relative;
            z-index: 1;
        }
        
        /* Asegurar que los modales estén por encima */
        .modal {
            z-index: 1050;
        }
        
        .modal-backdrop {
            z-index: 1040;
        }
        
        /* Corregir sombras de elementos flotantes */
        .btn-group {
            position: relative;
            z-index: 10;
        }
        
        .dropdown-menu {
            z-index: 1020;
        }
        
        /* Eliminar sombras problemáticas */
        .card {
            box-shadow: none !important;
            border: 1px solid rgba(0,0,0,0.1) !important;
        }
        
        /* Asegurar que los alerts estén visibles */
        .alert {
            position: relative;
            z-index: 15;
            box-shadow: none !important;
        }
        
        /* Eliminar TODAS las sombras del sistema */
        * {
            box-shadow: none !important;
        }
        
        /* Restaurar solo las sombras necesarias para modales */
        .modal-content {
            box-shadow: 0 4px 6px rgba(0,0,0,0.1) !important;
        }
        
        /* Limpiar cualquier pseudo-elemento problemático */
        *::before,
        *::after {
            box-shadow: none !important;
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
        <?php if ($_SESSION['usuario_rol'] === 'admin'): ?>
        <a href="gestionar_sedes.php" class="btn btn-outline-primary">
            <i class="bi bi-building me-2"></i>Gestionar Sedes
        </a>
        <?php endif; ?>
    </div>
    
    <!-- Alertas -->
    <?php if (!empty($mensaje)): ?>
        <div class="alert alert-<?= $tipo_alerta ?> alert-dismissible fade show shadow-sm" role="alert">
            <?= $mensaje ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <!-- Card Principal -->
    <div class="card card-ubicaciones">
        <div class="card-header-custom">
            <h4 class="mb-0"><i class="bi bi-geo-alt-fill me-2"></i>Gestión de Ubicaciones Físicas</h4>
            <p class="mb-0 mt-2 opacity-75">Centralización de Expedientes - Palo Negro</p>
        </div>
        
        <div class="card-body p-4">
            
            <!-- Tabs de Modo -->
            <ul class="nav nav-tabs mb-4" role="tablist">
                <li class="nav-item" role="presentation">
                    <a class="nav-link <?= $modo === 'individual' ? 'active' : '' ?>" 
                       href="?modo=individual" role="tab">
                        <i class="bi bi-search me-2"></i>Asignacion Individual
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link <?= $modo === 'lote' ? 'active' : '' ?>" 
                       href="?modo=lote" role="tab">
                        <i class="bi bi-stack me-2"></i>Carga por Lote
                    </a>
                </li>
            </ul>
            
            <!-- ============================================ -->
            <!-- MODO INDIVIDUAL -->
            <!-- ============================================ -->
            <?php if ($modo === 'individual'): ?>
            
            <div class="row">
                <div class="col-md-6">
                    <h5 class="mb-3"><i class="bi bi-1-circle-fill text-primary me-2"></i>Buscar Expediente</h5>
                    <form method="POST" action="gestionar_ubicaciones.php?modo=individual">
                        <div class="input-group mb-3">
                            <input type="text" class="form-control form-control-lg" 
                                   name="n_expediente_buscar" 
                                   placeholder="Ej: 00001-24" 
                                   required autofocus>
                            <button type="submit" name="buscar_expediente" class="btn btn-primary">
                                <i class="bi bi-search me-1"></i>Buscar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <?php if (isset($expediente_encontrado) && $expediente_encontrado !== null && $expediente_encontrado !== false): ?>
            <hr class="my-4">
            
            <!-- Informacion del Expediente -->
            <div class="expediente-card mb-4">
                <h6 class="text-primary mb-3"><i class="bi bi-check-circle-fill me-2"></i>Expediente Encontrado</h6>
                <div class="row">
                    <div class="col-md-8">
                        <p class="mb-1"><strong>Nro Expediente:</strong> <?= htmlspecialchars($expediente_encontrado['n_expediente']) ?></p>
                        <p class="mb-1"><strong>Demandante:</strong> <?= htmlspecialchars($expediente_encontrado['demandante']) ?></p>
                        <p class="mb-0"><strong>Demandado:</strong> <?= htmlspecialchars($expediente_encontrado['demandado']) ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Ubicacion Actual Destacada -->
            <?php 
            // Obtener informacion completa de la ubicacion actual
            $ubicacion_actual = null;
            if (!empty($expediente_encontrado['id_sede'])) {
                try {
                    $stmtUbicacion = $pdo->prepare("SELECT s.nombre_sede, s.direccion FROM sedes_deposito s WHERE s.id_sede = :id_sede");
                    $stmtUbicacion->execute([':id_sede' => $expediente_encontrado['id_sede']]);
                    $ubicacion_actual = $stmtUbicacion->fetch();
                } catch (PDOException $e) {
                    // Error silencioso
                }
            }
            ?>
            
            <div class="card mb-4" style="border: 2px solid var(--ubicacion-blue);">
                <div class="card-header" style="background: linear-gradient(135deg, #004085 0%, #0056b3 100%); color: white;">
                    <h5 class="mb-0"><i class="bi bi-geo-alt-fill me-2"></i>Ubicación Actual del Expediente</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($expediente_encontrado['id_sede']) && $ubicacion_actual): ?>
                        <!-- Tiene ubicacion asignada -->
                        <div class="row g-4">
                            <div class="col-md-4">
                                <div class="text-center p-3" style="background-color: #e3f2fd; border-radius: 8px;">
                                    <i class="bi bi-building" style="font-size: 2.5rem; color: var(--ubicacion-blue);"></i>
                                    <h6 class="mt-2 mb-1 text-uppercase text-muted" style="font-size: 0.75rem; letter-spacing: 1px;">SEDE</h6>
                                    <p class="mb-0 fw-bold" style="color: var(--ubicacion-blue);"><?= htmlspecialchars($ubicacion_actual['nombre_sede']) ?></p>
                                    <?php if (!empty($ubicacion_actual['direccion'])): ?>
                                        <small class="text-muted"><i class="bi bi-pin-map me-1"></i><?= htmlspecialchars($ubicacion_actual['direccion']) ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="text-center p-3" style="background-color: #e3f2fd; border-radius: 8px;">
                                    <i class="bi bi-map" style="font-size: 2.5rem; color: #0056b3;"></i>
                                    <h6 class="mt-2 mb-1 text-uppercase text-muted" style="font-size: 0.75rem; letter-spacing: 1px;">AREA</h6>
                                    <p class="mb-0 fw-bold" style="color: #0056b3;">
                                        <?= !empty($expediente_encontrado['ubicacion_area']) ? htmlspecialchars($expediente_encontrado['ubicacion_area']) : 'No especificada' ?>
                                    </p>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="text-center p-3" style="background-color: #e3f2fd; border-radius: 8px;">
                                    <i class="bi bi-box-seam" style="font-size: 2.5rem; color: #004085;"></i>
                                    <h6 class="mt-2 mb-1 text-uppercase text-muted" style="font-size: 0.75rem; letter-spacing: 1px;">ESTANTE</h6>
                                    <p class="mb-0 fw-bold" style="color: #004085;">
                                        <?= !empty($expediente_encontrado['ubicacion_detalle']) ? htmlspecialchars($expediente_encontrado['ubicacion_detalle']) : 'No especificado' ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-3 p-2 bg-info bg-opacity-10 border-start border-info border-4">
                            <small class="text-muted"><i class="bi bi-info-circle me-1"></i>Esta es la ubicacion fisica actual del expediente en el archivo.</small>
                        </div>
                        
                    <?php else: ?>
                        <!-- Sin ubicacion asignada -->
                        <div class="text-center py-4">
                            <i class="bi bi-inbox" style="font-size: 4rem; color: #ccc;"></i>
                            <h5 class="mt-3 text-muted">Sin Ubicación Asignada</h5>
                            <p class="text-muted mb-0">Este expediente aún no tiene una ubicación física registrada en el sistema.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Formulario de Cambio de Ubicacion (Solo Administradores) -->
            <?php if ($_SESSION['usuario_rol'] === 'admin'): ?>
            
            <div class="card">
                <div class="card-header bg-warning bg-opacity-25">
                    <h5 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Cambiar Ubicación <span class="badge bg-danger ms-2">Solo Administradores</span></h5>
                </div>
                <div class="card-body">
                    
                    <form method="POST" action="gestionar_ubicaciones.php?modo=individual">
                        <input type="hidden" name="id_expediente" value="<?= $expediente_encontrado['Id'] ?>">
                        <input type="hidden" name="n_expediente" value="<?= $expediente_encontrado['n_expediente'] ?>">
                        
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Nueva Sede <span class="text-danger">*</span></label>
                                <select class="form-select" name="id_sede" id="sede_select_individual" required>
                                    <option value="">Selecciona una sede...</option>
                                    <?php foreach ($sedes as $sede): ?>
                                        <option value="<?= $sede['id_sede'] ?>" 
                                                data-descripcion="<?= htmlspecialchars($sede['descripcion']) ?>"
                                                data-direccion="<?= htmlspecialchars($sede['direccion']) ?>"
                                                <?= ($expediente_encontrado['id_sede'] == $sede['id_sede']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($sede['nombre_sede']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div id="descripcion_sede_individual" class="mt-2 p-2 bg-light border-start border-primary border-3 rounded" style="display: none;">
                                    <small class="text-muted">
                                        <i class="bi bi-geo-alt-fill me-1"></i>
                                        <strong>Direccion:</strong> <span id="texto_direccion_individual"></span>
                                    </small>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Nueva Area</label>
                                <input type="text" class="form-control" name="ubicacion_area" 
                                       placeholder="Ej: Piso 3, Seccion A"
                                       value="<?= htmlspecialchars($expediente_encontrado['ubicacion_area'] ?? '') ?>">
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Nuevo Estante</label>
                                <input type="text" class="form-control" name="ubicacion_detalle" 
                                       placeholder="Ej: Estante B / Caja 4"
                                       value="<?= htmlspecialchars($expediente_encontrado['ubicacion_detalle'] ?? '') ?>">
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" name="guardar_ubicacion_individual" class="btn btn-warning btn-lg">
                                <i class="bi bi-arrow-repeat me-2"></i>Actualizar Ubicacion
                            </button>
                            <small class="text-muted ms-3">
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                Esta accion sobrescribira la ubicacion actual del expediente.
                            </small>
                        </div>
                    </form>
                    
                </div>
            </div>
            
            <?php else: ?>
            
            <!-- Mensaje para usuarios no administradores -->
            <div class="alert alert-info border-start border-info border-4">
                <i class="bi bi-shield-lock-fill me-2"></i>
                <strong>Acceso Restringido:</strong> Solo los administradores pueden modificar las ubicaciones de los expedientes. 
                Si necesitas cambiar la ubicacion de este expediente, contacta a un administrador del sistema.
            </div>
            
            <?php endif; ?>
            
            <?php endif; ?>
            
            <?php endif; ?>
            
            <!-- ============================================ -->
            <!-- MODO LOTE (PICK-LIST) -->
            <!-- ============================================ -->
            <?php if ($modo === 'lote'): ?>
            
            <div class="alert alert-info border-start border-info border-4 mb-4">
                <i class="bi bi-lightbulb-fill me-2"></i>
                <strong>Carga por Lote (Pick-list):</strong> Selecciona multiples expedientes de la lista y asignales la misma ubicacion de una sola vez.
                <br><small class="text-muted">Mostrando expedientes sin ubicacion asignada. Total disponibles: <strong><?= number_format($total_sin_ubicacion) ?></strong></small>
            </div>
            
            <form method="POST" action="gestionar_ubicaciones.php?modo=lote" id="formLote">
                <input type="hidden" name="guardar_ubicacion_lote" value="1">
                
                <!-- Configuracion de Ubicacion -->
                <div class="card mb-4">
                    <div class="card-header bg-primary bg-opacity-25">
                        <h6 class="mb-0"><i class="bi bi-geo-alt-fill me-2"></i>Configurar Ubicación para Expedientes Seleccionados</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Sede <span class="text-danger">*</span></label>
                                <select class="form-select" name="id_sede_lote" id="sede_select_lote" required>
                                    <option value="">Selecciona una sede...</option>
                                    <?php foreach ($sedes as $sede): ?>
                                        <option value="<?= $sede['id_sede'] ?>" 
                                                data-descripcion="<?= htmlspecialchars($sede['descripcion']) ?>"
                                                data-direccion="<?= htmlspecialchars($sede['direccion']) ?>"
                                                <?= ($_SESSION['ultima_sede'] == $sede['id_sede']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($sede['nombre_sede']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div id="descripcion_sede_lote" class="mt-2 p-2 bg-light border-start border-primary border-3 rounded" style="display: none;">
                                    <small class="text-muted">
                                        <i class="bi bi-geo-alt-fill me-1"></i>
                                        <strong>Direccion:</strong> <span id="texto_direccion_lote"></span>
                                    </small>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Area</label>
                                <input type="text" class="form-control" name="ubicacion_area_lote" 
                                       placeholder="Ej: Piso 3, Seccion A"
                                       value="<?= htmlspecialchars($_SESSION['ultima_area']) ?>">
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Estante</label>
                                <input type="text" class="form-control" name="ubicacion_detalle_lote" 
                                       placeholder="Ej: Estante B / Caja 4">
                            </div>
                        </div>
                        
                        <div class="mt-3 d-flex justify-content-between align-items-center">
                            <div>
                                <span class="badge bg-primary" id="contadorSeleccionados">0 expedientes seleccionados</span>
                            </div>
                            <button type="submit" class="btn btn-primary" id="btnProcesarLote" disabled>
                                <i class="bi bi-check2-all me-2"></i>Procesar Expedientes Seleccionados
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Lista de Expedientes -->
                <div class="card">
                    <div class="card-header bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0"><i class="bi bi-list-check me-2"></i>Expedientes Sin Ubicación</h6>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-sm btn-outline-primary" id="seleccionarTodos">
                                    <i class="bi bi-check-all me-1"></i>Seleccionar Todos
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="limpiarSeleccion">
                                    <i class="bi bi-x-circle me-1"></i>Limpiar Seleccion
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        
                        <?php if (count($expedientes_sin_ubicacion) > 0): ?>
                        
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="tablaExpedientes">
                                <thead class="table-light">
                                    <tr>
                                        <th width="50">
                                            <input type="checkbox" class="form-check-input" id="checkboxMaestro">
                                        </th>
                                        <th>Nro Expediente</th>
                                        <th>Fecha Entrada</th>
                                        <th>Demandante</th>
                                        <th>Demandado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($expedientes_sin_ubicacion as $exp): ?>
                                    <tr data-id="<?= $exp['Id'] ?>" class="expediente-row">
                                        <td>
                                            <input type="checkbox" class="form-check-input checkbox-expediente" 
                                                   name="expedientes_seleccionados[]" 
                                                   value="<?= $exp['Id'] ?>">
                                        </td>
                                        <td>
                                            <strong class="text-primary"><?= htmlspecialchars($exp['n_expediente']) ?></strong>
                                        </td>
                                        <td>
                                            <?= date('d/m/Y', strtotime($exp['fecha_entrada'])) ?>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($exp['demandante']) ?>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($exp['demandado']) ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <?php else: ?>
                        
                        <div class="text-center py-5">
                            <i class="bi bi-check-circle" style="font-size: 4rem; color: #0056b3;"></i>
                            <h5 class="mt-3 text-primary">Excelente!</h5>
                            <p class="text-muted mb-0">Todos los expedientes tienen ubicacion asignada.</p>
                        </div>
                        
                        <?php endif; ?>
                        
                    </div>
                </div>
                
            </form>
            
            <?php endif; ?>
            
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/app-alerts.js"></script>

<!-- jQuery (requerido para DataTables) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
// Mostrar direccion de sede en modo individual
document.getElementById('sede_select_individual')?.addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const direccion = selectedOption.getAttribute('data-direccion');
    const descripcionDiv = document.getElementById('descripcion_sede_individual');
    const textoDireccion = document.getElementById('texto_direccion_individual');
    
    if (direccion && direccion !== 'null' && direccion !== '') {
        textoDireccion.textContent = direccion;
        descripcionDiv.style.display = 'block';
    } else {
        descripcionDiv.style.display = 'none';
    }
});

// Mostrar direccion de sede en modo lote
document.getElementById('sede_select_lote')?.addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const direccion = selectedOption.getAttribute('data-direccion');
    const descripcionDiv = document.getElementById('descripcion_sede_lote');
    const textoDireccion = document.getElementById('texto_direccion_lote');
    
    if (direccion && direccion !== 'null' && direccion !== '') {
        textoDireccion.textContent = direccion;
        descripcionDiv.style.display = 'block';
    } else {
        descripcionDiv.style.display = 'none';
    }
});

// Disparar el evento change al cargar la pagina si hay una sede pre-seleccionada
window.addEventListener('DOMContentLoaded', function() {
    const sedeIndividual = document.getElementById('sede_select_individual');
    const sedeLote = document.getElementById('sede_select_lote');
    
    if (sedeIndividual && sedeIndividual.value) {
        sedeIndividual.dispatchEvent(new Event('change'));
    }
    
    if (sedeLote && sedeLote.value) {
        sedeLote.dispatchEvent(new Event('change'));
    }
});

// ============================================================
// FUNCIONALIDAD PARA MODO LOTE (PICK-LIST)
// ============================================================
$(document).ready(function() {
    
    // Inicializar DataTable si existe la tabla
    if ($('#tablaExpedientes').length > 0) {
        const table = $('#tablaExpedientes').DataTable({
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
            },
            pageLength: 25,
            order: [[1, 'desc']], // Ordenar por Nro Expediente descendente
            columnDefs: [
                { orderable: false, targets: 0 } // Deshabilitar ordenamiento en columna de checkbox
            ],
            dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                 '<"row"<"col-sm-12"tr>>' +
                 '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            drawCallback: function() {
                // Reactivar eventos despues de cada redibujado de la tabla
                actualizarContadorSeleccionados();
            }
        });
    }
    
    // Funcion para actualizar contador de seleccionados
    function actualizarContadorSeleccionados() {
        const checkboxes = $('.checkbox-expediente:checked');
        const cantidad = checkboxes.length;
        
        $('#contadorSeleccionados').text(cantidad + ' expedientes seleccionados');
        
        // Habilitar/deshabilitar boton de procesar
        $('#btnProcesarLote').prop('disabled', cantidad === 0);
        
        // Actualizar estado del checkbox maestro
        const totalCheckboxes = $('.checkbox-expediente').length;
        const checkboxMaestro = $('#checkboxMaestro');
        
        if (cantidad === 0) {
            checkboxMaestro.prop('indeterminate', false);
            checkboxMaestro.prop('checked', false);
        } else if (cantidad === totalCheckboxes) {
            checkboxMaestro.prop('indeterminate', false);
            checkboxMaestro.prop('checked', true);
        } else {
            checkboxMaestro.prop('indeterminate', true);
        }
    }
    
    // Checkbox maestro (seleccionar/deseleccionar todos)
    $('#checkboxMaestro').on('change', function() {
        const isChecked = $(this).is(':checked');
        $('.checkbox-expediente').prop('checked', isChecked);
        actualizarContadorSeleccionados();
    });
    
    // Checkboxes individuales
    $(document).on('change', '.checkbox-expediente', function() {
        actualizarContadorSeleccionados();
    });
    
    // Boton "Seleccionar Todos"
    $('#seleccionarTodos').on('click', function() {
        $('.checkbox-expediente').prop('checked', true);
        actualizarContadorSeleccionados();
    });
    
    // Boton "Limpiar Seleccion"
    $('#limpiarSeleccion').on('click', function() {
        $('.checkbox-expediente').prop('checked', false);
        actualizarContadorSeleccionados();
    });
    
    // Efecto de desvanecimiento para expedientes procesados
    if (typeof expedientesProcesados !== 'undefined' && expedientesProcesados.length > 0) {
        expedientesProcesados.forEach(function(id) {
            $('tr[data-id="' + id + '"]').fadeOut(1000, function() {
                $(this).remove();
                // Actualizar DataTable si existe
                if ($.fn.DataTable.isDataTable('#tablaExpedientes')) {
                    $('#tablaExpedientes').DataTable().row($(this)).remove().draw();
                }
                actualizarContadorSeleccionados();
            });
        });
        
        // Limpiar variable para evitar efectos repetidos
        setTimeout(function() {
            expedientesProcesados = [];
        }, 2000);
    }
    
    // Validacion del formulario
    let envioConfirmadoLote = false;
    $('#formLote').on('submit', async function(e) {
        if (envioConfirmadoLote) {
            return true;
        }

        e.preventDefault();
        
        const expedientesSeleccionados = $('.checkbox-expediente:checked').length;
        const sedeSeleccionada = $('#sede_select_lote').val();
        
        if (expedientesSeleccionados === 0) {
            await window.appAlerts.alert('Debes seleccionar al menos un expediente para procesar.', {
                type: 'warning',
                title: 'Faltan expedientes'
            });
            return false;
        }
        
        if (!sedeSeleccionada) {
            await window.appAlerts.alert('Debes seleccionar una sede para asignar la ubicacion.', {
                type: 'warning',
                title: 'Falta una sede'
            });
            return false;
        }
        
        // Confirmacion antes de procesar
        const confirmacion = await window.appAlerts.confirm(`Estas seguro de procesar ${expedientesSeleccionados} expediente(s)?`, {
            type: 'info',
            title: 'Confirmar procesamiento en lote',
            okText: 'Si, procesar',
            cancelText: 'Cancelar'
        });

        if (!confirmacion) {
            return false;
        }

        envioConfirmadoLote = true;
        this.submit();
    });
    
    // Inicializar contador al cargar la pagina
    actualizarContadorSeleccionados();
});
</script>

</body>
</html>





