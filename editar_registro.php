<?php
require_once "conexion.php";
require_once "auth_check.php";
require_once "auditoria_functions.php";

$mensaje = '';
$tipo_alerta = '';

// Capturar ID desde GET (primera carga) o POST (despues de enviar formulario)
$id = trim($_POST['id'] ?? $_GET['id'] ?? '');

// Validar que el ID sea valido
if (empty($id) || !is_numeric($id)) {
    header('Location: buscador.php');
    exit;
}

// Cargar tribunales para el Select dinamico
// IMPORTANTE: Usamos el ID de la fila (si existe) como identificador unico
// para distinguir tribunales con el mismo id_tribunal pero diferentes nombres
$tribunales = [];
try {
    // Intentar obtener el ID de la fila de tribunales (puede variar segun la estructura)
    // Si no existe, usamos id_tribunal + tribunal como identificador
    $stmtTrib = $pdo->query("SELECT id_tribunal, tribunal FROM tribunales ORDER BY tribunal ASC");
    $tribunales = $stmtTrib->fetchAll();
} catch (PDOException $e) {
    $mensaje = "Error al cargar tribunales: " . $e->getMessage();
    $tipo_alerta = 'danger';
}

// Cargar datos del registro existente
$registro = null;
try {
    $stmtLoad = $pdo->prepare("SELECT * FROM maestro WHERE Id = :id LIMIT 1");
    $stmtLoad->execute([':id' => $id]);
    $registro = $stmtLoad->fetch(PDO::FETCH_ASSOC);
    
    if (!$registro) {
        header('Location: buscador.php');
        exit;
    }
} catch (PDOException $e) {
    $mensaje = "Error al cargar el registro: " . $e->getMessage();
    $tipo_alerta = 'danger';
}

// Procesar el formulario de edicion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['guardar_cambios'])) {
    $n_expediente = trim($_POST['n_expediente'] ?? '');
    $fecha_entrada = trim($_POST['fecha_entrada'] ?? '');
    $id_tribunal = trim($_POST['id_tribunal'] ?? '');
    
    $demandante = trim($_POST['demandante'] ?? '');
    $tipo_doc_demandante = trim($_POST['tipo_doc_demandante'] ?? 'V');
    $cedula_rif_demandante_num = trim($_POST['cedula_rif_demandante'] ?? '');
    $cedula_rif_demandante = !empty($cedula_rif_demandante_num) ? $tipo_doc_demandante . '-' . $cedula_rif_demandante_num : '';

    $demandado = trim($_POST['demandado'] ?? '');
    $tipo_doc_demandado = trim($_POST['tipo_doc_demandado'] ?? 'V');
    $cedula_rif_demandado_num = trim($_POST['cedula_rif_demandado'] ?? '');
    $cedula_rif_demandado = !empty($cedula_rif_demandado_num) ? $tipo_doc_demandado . '-' . $cedula_rif_demandado_num : '';
    
    $motivo_delito = trim($_POST['motivo_delito'] ?? '');
    $n_legajo = trim($_POST['n_legajo'] ?? '');
    $observaciones = trim($_POST['observaciones'] ?? '');
    
    // Validacion de campos obligatorios
    if (empty($n_expediente) || empty($fecha_entrada) || empty($id_tribunal) || empty($demandante) || empty($demandado) || empty($motivo_delito) || empty($n_legajo)) {
        $mensaje = 'Por favor completa todos los campos obligatorios (Expediente, Fecha, Tribunal, Demandante, Demandado, Motivo/Delito, Nro Legajo).';
        $tipo_alerta = 'warning';
    } else {
        try {
            // DEBUG: Verificar que tenemos el ID correcto
            if (empty($id) || !is_numeric($id)) {
                throw new Exception("ID invalido o vacio: '{$id}'. No se puede actualizar sin un ID valido.");
            }
            
            // VALIDACION: Verificar si el numero de expediente ya existe en otro registro
            if ($n_expediente !== $registro['n_expediente']) {
                $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM maestro WHERE n_expediente = :n_expediente AND Id != :id");
                $stmtCheck->execute([
                    ':n_expediente' => $n_expediente,
                    ':id' => $id
                ]);
                
                if ($stmtCheck->fetchColumn() > 0) {
                    $mensaje = "El numero de expediente '{$n_expediente}' ya existe en otro registro. No se puede duplicar.";
                    $tipo_alerta = 'danger';
                    
                    // Recargar datos del registro para mantener el formulario
                    $stmtLoad->execute([':id' => $id]);
                    $registro = $stmtLoad->fetch(PDO::FETCH_ASSOC);
                    
                    // Salir sin actualizar
                    goto fin_procesamiento;
                }
            }
            
            // PASO 1: Capturar datos viejos antes de actualizar
            $datos_viejos = $registro;
            
            // PASO 1.5: Obtener el nombre del tribunal VIEJO y NUEVO para comparacion precisa
            $tribunal_viejo_nombre = '';
            $stmtTribViejo = $pdo->prepare("SELECT tribunal FROM tribunales WHERE id_tribunal = :id LIMIT 1");
            $stmtTribViejo->execute([':id' => $datos_viejos['id_tribunal']]);
            $tribViejo = $stmtTribViejo->fetch();
            if ($tribViejo) {
                $tribunal_viejo_nombre = $tribViejo['tribunal'];
            }
            
            $tribunal_nuevo_nombre = '';
            $stmtTribNuevo = $pdo->prepare("SELECT tribunal FROM tribunales WHERE id_tribunal = :id LIMIT 1");
            $stmtTribNuevo->execute([':id' => $id_tribunal]);
            $tribNuevo = $stmtTribNuevo->fetch();
            if ($tribNuevo) {
                $tribunal_nuevo_nombre = $tribNuevo['tribunal'];
            }
            
            // PASO 2: Preparar datos nuevos para comparacion
            $datos_nuevos = [
                'n_expediente' => $n_expediente,
                'id_tribunal' => $id_tribunal,
                'fecha_entrada' => $fecha_entrada,
                'demandante' => $demandante,
                'cedula_rif_demandante' => $cedula_rif_demandante,
                'demandado' => $demandado,
                'cedula_rif_demandado' => $cedula_rif_demandado,
                'motivo_delito' => $motivo_delito,
                'n_legajo' => $n_legajo,
                'observaciones' => $observaciones
            ];
            
            // PASO 3: Comparar y construir log de cambios
            $cambios = [];
            $campos_nombres = [
                'n_expediente' => 'Nro Expediente',
                'id_tribunal' => 'Tribunal',
                'fecha_entrada' => 'Fecha de Entrada',
                'demandante' => 'Demandante',
                'cedula_rif_demandante' => 'CI/RIF Demandante',
                'demandado' => 'Demandado',
                'cedula_rif_demandado' => 'CI/RIF Demandado',
                'motivo_delito' => 'Motivo/Delito',
                'n_legajo' => 'Nro Legajo',
                'observaciones' => 'Observaciones'
            ];
            
            // Campos numericos que deben compararse como enteros
            $campos_numericos = ['id_tribunal'];
            
            foreach ($datos_nuevos as $campo => $valor_nuevo) {
                $valor_viejo = $datos_viejos[$campo] ?? '';
                
                // Limpiar espacios en blanco
                $valor_viejo = trim((string)$valor_viejo);
                $valor_nuevo = trim((string)$valor_nuevo);
                
                // Comparar segun el tipo de campo
                $son_diferentes = false;
                
                if ($campo === 'id_tribunal') {
                    // CASO ESPECIAL: Para tribunales, comparamos TANTO el ID como el NOMBRE
                    // Esto maneja el caso de IDs duplicados con nombres diferentes
                    $id_cambio = ((int)$valor_viejo !== (int)$valor_nuevo);
                    $nombre_cambio = ($tribunal_viejo_nombre !== $tribunal_nuevo_nombre);
                    
                    if ($id_cambio || $nombre_cambio) {
                        $son_diferentes = true;
                        // Registrar el cambio con nombres completos para claridad
                        $nombre_campo = $campos_nombres[$campo] ?? $campo;
                        $cambios[] = "[CAMBIO] {$nombre_campo}: 'Trib. {$valor_viejo} - {$tribunal_viejo_nombre}' -> 'Trib. {$valor_nuevo} - {$tribunal_nuevo_nombre}'";
                        continue; // Saltar la logica normal de registro
                    }
                } elseif (in_array($campo, $campos_numericos)) {
                    // Comparacion numerica para otros campos numericos
                    $son_diferentes = ((int)$valor_viejo !== (int)$valor_nuevo);
                } else {
                    // Comparacion de texto para otros campos
                    $son_diferentes = ($valor_viejo !== $valor_nuevo);
                }
                
                if ($son_diferentes) {
                    $nombre_campo = $campos_nombres[$campo] ?? $campo;
                    $cambios[] = "[CAMBIO] {$nombre_campo}: '{$valor_viejo}' -> '{$valor_nuevo}'";
                }
            }
            
            // PASO 4: Actualizar en la base de datos usando el ID primario
            // IMPORTANTE: Esto es un UPDATE, NO un INSERT
            // LIMIT 1 garantiza que solo se actualice UNA fila, sin importar duplicados
            $sqlUpdate = "UPDATE maestro 
                          SET n_expediente = :n_expediente,
                              id_tribunal = :id_tribunal, 
                              fecha_entrada = :fecha_entrada,
                              demandante = :demandante,
                              cedula_rif_demandante = :cedula_rif_demandante,
                              demandado = :demandado,
                              cedula_rif_demandado = :cedula_rif_demandado,
                              motivo_delito = :motivo_delito,
                              n_legajo = :n_legajo,
                              observaciones = :observaciones
                          WHERE Id = :id
                          LIMIT 1";
            
            $stmtUpdate = $pdo->prepare($sqlUpdate);
            $resultado = $stmtUpdate->execute([
                ':n_expediente' => $n_expediente,
                ':id_tribunal' => $id_tribunal,
                ':fecha_entrada' => $fecha_entrada,
                ':demandante' => $demandante,
                ':cedula_rif_demandante' => $cedula_rif_demandante,
                ':demandado' => $demandado,
                ':cedula_rif_demandado' => $cedula_rif_demandado,
                ':motivo_delito' => $motivo_delito,
                ':n_legajo' => $n_legajo,
                ':observaciones' => $observaciones,
                ':id' => $id
            ]);
            
            // Verificar filas afectadas
            $filasAfectadas = $stmtUpdate->rowCount();
            
            // Si no se afectaron filas, verificar si el ID existe
            if ($filasAfectadas === 0) {
                // Verificar si el registro existe
                $stmtVerify = $pdo->prepare("SELECT COUNT(*) FROM maestro WHERE Id = :id");
                $stmtVerify->execute([':id' => $id]);
                $existe = $stmtVerify->fetchColumn();
                
                if ($existe == 0) {
                    throw new Exception("Error: El registro con ID {$id} no existe en la base de datos.");
                }
                // Si existe pero no se actualizo, es porque no hubo cambios (esto es normal)
            } elseif ($filasAfectadas > 1) {
                throw new Exception("Error critico: Se actualizaron {$filasAfectadas} filas. Solo deberia ser 1.");
            }
            
            // PASO 5: Registrar en auditoria
            if (!empty($cambios)) {
                $detalles_cambios = implode("\n", $cambios);
                $accion_auditoria = 'EDITAR_EXPEDIENTE';
                $recurso_auditoria = "Exp: {$n_expediente}";
                registrarAccion($accion_auditoria, $recurso_auditoria, $detalles_cambios);
                
                $mensaje = 'Expediente actualizado correctamente. Los cambios han sido registrados en el log de auditoria.';
                $tipo_alerta = 'success';
            } else {
                $mensaje = 'No se detectaron cambios en el expediente.';
                $tipo_alerta = 'info';
            }
            
            // Recargar datos actualizados
            $stmtLoad->execute([':id' => $id]);
            $registro = $stmtLoad->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            $mensaje = 'Error al actualizar el expediente: ' . $e->getMessage();
            $tipo_alerta = 'danger';
        }
        
        // Etiqueta para salir del procesamiento en caso de validacion fallida
        fin_procesamiento:
    }
}

// Separar tipo de documento y numero para los campos de cedula
$tipo_doc_demandante_actual = 'V';
$cedula_demandante_num = '';
if (!empty($registro['cedula_rif_demandante'])) {
    $partes = explode('-', $registro['cedula_rif_demandante'], 2);
    if (count($partes) == 2) {
        $tipo_doc_demandante_actual = $partes[0];
        $cedula_demandante_num = $partes[1];
    }
}

$tipo_doc_demandado_actual = 'V';
$cedula_demandado_num = '';
if (!empty($registro['cedula_rif_demandado'])) {
    $partes = explode('-', $registro['cedula_rif_demandado'], 2);
    if (count($partes) == 2) {
        $tipo_doc_demandado_actual = $partes[0];
        $cedula_demandado_num = $partes[1];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Expediente</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    
    <style>
        :root {
            --institucional-blue: #1a237e;
            --institucional-gray: #f8f9fa;
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
        }
        .header-bg {
            background-color: var(--institucional-blue);
            color: #ffffff;
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
            position: relative;
        }
        .btn-volver-menu {
            position: absolute;
            top: 50%;
            right: 20px;
            transform: translateY(-50%);
            z-index: 10;
        }
        .card-custom {
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.12);
            border: none;
            background-color: #ffffff;
            max-width: 95%;
            margin: 130px auto 0;
            width: 100%;
        }
        .card-body {
            padding: 1.2rem 5rem !important;
        }
        .row.g-3 {
            row-gap: 0.6rem !important;
        }
        .mb-4 {
            margin-bottom: 0.8rem !important;
        }
        .container {
            max-width: 100% !important;
            padding-left: 1rem;
            padding-right: 1rem;
        }
        .btn-success-custom {
            font-weight: 700;
            font-size: 1.25rem;
            padding: 15px;
            letter-spacing: 1px;
        }
        .form-label {
            font-weight: 600;
            color: #4a4a4a;
        }
        .alert-warning-edit {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            color: #856404;
        }
    </style>
</head>
<body>

<div class="container py-5">
    
    <div class="row justify-content-center">
        <div class="col-19 col-xxl-11">
            
            <!-- Alertas de mensajes -->
            <?php if (!empty($mensaje)): ?>
                <div class="alert alert-<?= $tipo_alerta ?> alert-dismissible fade show shadow-sm fw-bold border-0 border-start border-<?= $tipo_alerta ?> border-4" role="alert">
                    <?php if ($tipo_alerta == 'success'): ?>
                        <i class="bi bi-check-circle-fill me-2 fs-5 text-success"></i>
                    <?php elseif ($tipo_alerta == 'warning'): ?>
                        <i class="bi bi-exclamation-triangle-fill me-2 fs-5 text-warning"></i>
                    <?php elseif ($tipo_alerta == 'info'): ?>
                        <i class="bi bi-info-circle-fill me-2 fs-5 text-info"></i>
                    <?php else: ?>
                        <i class="bi bi-x-circle-fill me-2 fs-5 text-danger"></i>
                    <?php endif; ?>
                    <?= htmlspecialchars($mensaje) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Alerta de advertencia sobre edicion -->
            <div class="alert alert-warning-edit shadow-sm mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <strong>Modo de Edicion:</strong> Estas modificando un registro existente. Todos los cambios quedaran registrados en el log de auditoria del sistema.
            </div>

            <div class="card card-custom">
                <div class="card-header header-bg text-center py-4">
                    <h3 class="mb-0 fw-bold"><i class="bi bi-pencil-square me-2"></i>EDITAR EXPEDIENTE</h3>
                    <a href="buscador.php" class="btn btn-secondary btn-sm btn-volver-menu">
                        <i class="bi bi-arrow-left me-2"></i>Cancelar
                    </a>
                </div>
                <div class="card-body p-4 p-md-5">
                    
                    <form action="editar_registro.php?id=<?= htmlspecialchars($id) ?>" method="POST" class="needs-validation" novalidate>
                        
                        <!-- Campo oculto con el ID del registro -->
                        <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">
                        
                        <!-- FILA 1: Nro Expediente y Fecha de Ingreso -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="n_expediente" class="form-label">Nro Expediente <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="n_expediente" name="n_expediente" placeholder="Ej: 000-24" value="<?= htmlspecialchars($registro['n_expediente']) ?>" required>
                                <div class="invalid-feedback">Ingresa el Nro de Expediente.</div>
                            </div>
                            <div class="col-md-6">
                                <label for="fecha_entrada" class="form-label">Fecha de Ingreso <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="fecha_entrada" name="fecha_entrada" value="<?= htmlspecialchars($registro['fecha_entrada']) ?>" required>
                                <div class="invalid-feedback">Selecciona la fecha de ingreso.</div>
                            </div>
                        </div>

                        <!-- FILA 2: Tribunal -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-12">
                                <label for="busqueda-tribunales-editar" class="form-label">Tribunal <span class="text-danger">*</span></label>
                                <select class="form-select" id="busqueda-tribunales-editar" name="id_tribunal" required>
                                    <option value="" disabled>Escribe el nombre del tribunal...</option>
                                    <?php 
                                    // Obtener el nombre actual del tribunal para comparacion exacta
                                    $tribunal_actual_nombre = '';
                                    $stmtTribActual = $pdo->prepare("SELECT tribunal FROM tribunales WHERE id_tribunal = :id LIMIT 1");
                                    $stmtTribActual->execute([':id' => $registro['id_tribunal']]);
                                    $tribActual = $stmtTribActual->fetch();
                                    if ($tribActual) {
                                        $tribunal_actual_nombre = $tribActual['tribunal'];
                                    }
                                    
                                    foreach ($tribunales as $trib): 
                                        // Marcar como selected solo si coinciden TANTO el ID como el nombre
                                        $is_selected = ($registro['id_tribunal'] == $trib['id_tribunal'] && 
                                                       $tribunal_actual_nombre == $trib['tribunal']);
                                    ?>
                                        <option value="<?= htmlspecialchars($trib['id_tribunal']) ?>" 
                                                data-nombre="<?= htmlspecialchars($trib['tribunal']) ?>"
                                                <?= $is_selected ? 'selected' : '' ?>>
                                            Trib. <?= htmlspecialchars($trib['id_tribunal']) ?> - <?= htmlspecialchars($trib['tribunal']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">Selecciona el tribunal correspondiente.</div>
                                <?php if (!empty($tribunal_actual_nombre)): ?>
                                    <small class="text-muted d-block mt-1">
                                        <i class="bi bi-info-circle me-1"></i>Tribunal actual: <strong><?= htmlspecialchars($tribunal_actual_nombre) ?></strong>
                                    </small>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- FILA 3: Nombre Demandante y C.I./RIF Demandante -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-8">
                                <label for="demandante" class="form-label">Nombre Demandante <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="demandante" name="demandante" placeholder="Nombre completo del demandante" value="<?= htmlspecialchars($registro['demandante']) ?>" required>
                                <div class="invalid-feedback">Ingresa el nombre del demandante.</div>
                            </div>
                            <div class="col-md-4">
                                <label for="cedula_rif_demandante" class="form-label">C.I. / RIF Demandante</label>
                                <div class="input-group">
                                    <select class="form-select" style="max-width: 80px;" id="tipo_doc_demandante" name="tipo_doc_demandante" required>
                                        <option value="V" <?= $tipo_doc_demandante_actual == 'V' ? 'selected' : '' ?>>V</option>
                                        <option value="E" <?= $tipo_doc_demandante_actual == 'E' ? 'selected' : '' ?>>E</option>
                                        <option value="J" <?= $tipo_doc_demandante_actual == 'J' ? 'selected' : '' ?>>J</option>
                                    </select>
                                    <input type="text" class="form-control" id="cedula_rif_demandante" name="cedula_rif_demandante" placeholder="Ej: 12345678" value="<?= htmlspecialchars($cedula_demandante_num) ?>">
                                </div>
                            </div>
                        </div>

                        <!-- FILA 4: Nombre Demandado y C.I./RIF Demandado -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-8">
                                <label for="demandado" class="form-label">Nombre Demandado <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="demandado" name="demandado" placeholder="Nombre completo del demandado" value="<?= htmlspecialchars($registro['demandado']) ?>" required>
                                <div class="invalid-feedback">Ingresa el nombre del demandado.</div>
                            </div>
                            <div class="col-md-4">
                                <label for="cedula_rif_demandado" class="form-label">C.I. / RIF Demandado</label>
                                <div class="input-group">
                                    <select class="form-select" style="max-width: 80px;" id="tipo_doc_demandado" name="tipo_doc_demandado" required>
                                        <option value="V" <?= $tipo_doc_demandado_actual == 'V' ? 'selected' : '' ?>>V</option>
                                        <option value="E" <?= $tipo_doc_demandado_actual == 'E' ? 'selected' : '' ?>>E</option>
                                        <option value="J" <?= $tipo_doc_demandado_actual == 'J' ? 'selected' : '' ?>>J</option>
                                    </select>
                                    <input type="text" class="form-control" id="cedula_rif_demandado" name="cedula_rif_demandado" placeholder="Ej: 12345678" value="<?= htmlspecialchars($cedula_demandado_num) ?>">
                                </div>
                            </div>
                        </div>

                        <!-- FILA 5: Motivo/Delito y Nro Legajo -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-8">
                                <label for="motivo_delito" class="form-label">Motivo / Delito <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="motivo_delito" name="motivo_delito" placeholder="Descripcion del motivo o delito" value="<?= htmlspecialchars($registro['motivo_delito']) ?>" required>
                                <div class="invalid-feedback">Ingresa la descripcion del motivo o delito.</div>
                            </div>
                            <div class="col-md-4">
                                <label for="n_legajo" class="form-label">Nro Legajo <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="n_legajo" name="n_legajo" placeholder="Ej: L-001" value="<?= htmlspecialchars($registro['n_legajo']) ?>" required>
                                <div class="invalid-feedback">Ingresa el numero de legajo.</div>
                            </div>
                        </div>

                        <!-- FILA 6: Observaciones -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-12">
                                <label for="observaciones" class="form-label">Observaciones</label>
                                <textarea class="form-control" id="observaciones" name="observaciones" rows="4" placeholder="Detalles o anotaciones adicionales importantes..."><?= htmlspecialchars($registro['observaciones']) ?></textarea>
                            </div>
                        </div>

                        <!-- FILA 7: Botones de Accion -->
                        <div class="row mt-5">
                            <div class="col-12 d-grid gap-3">
                                <button type="submit" name="guardar_cambios" class="btn btn-success btn-success-custom">
                                    <i class="bi bi-save-fill me-2"></i>GUARDAR CAMBIOS
                                </button>
                                <a href="buscador.php" class="btn btn-secondary btn-success-custom">
                                    <i class="bi bi-x-circle me-2"></i>CANCELAR Y VOLVER
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Logica de Validacion: Bloqueo en Navegador (Bootstrap needs-validation)
(function () {
  'use strict'
  var forms = document.querySelectorAll('.needs-validation')
  Array.prototype.slice.call(forms)
    .forEach(function (form) {
      form.addEventListener('submit', function (event) {
        if (!form.checkValidity()) {
          event.preventDefault()
          event.stopPropagation()
        }
        form.classList.add('was-validated')
      }, false)
    })
})()
</script>

<!-- jQuery (requerido para Select2) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
// Inicializar Select2 en el selector de tribunales
$(document).ready(function() {
    $('#busqueda-tribunales-editar').select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: 'Escribe el nombre del tribunal...',
        allowClear: true,
        language: {
            noResults: function() {
                return "No se encontraron resultados";
            },
            searching: function() {
                return "Buscando...";
            }
        }
    });
});
</script>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>




