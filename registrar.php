<?php
require_once "conexion.php";
require_once "auth_check_ajax.php";
require_once "auditoria_functions.php";

function validarDocumentoCiRif($tipo, $numero, $etiquetaCampo) {
    if ($numero === '') {
        return '';
    }

    if (!in_array($tipo, ['V', 'E', 'J'], true)) {
        return "Tipo de documento invalido en {$etiquetaCampo}.";
    }

    $longitudMinima = ($tipo === 'J') ? 10 : 8;
    $longitudMaxima = ($tipo === 'J') ? 10 : 9;
    if (!preg_match('/^\d+$/', $numero)) {
        return "El campo {$etiquetaCampo} solo debe contener digitos.";
    }

    $longitudActual = strlen($numero);
    if ($longitudActual < $longitudMinima || $longitudActual > $longitudMaxima) {
        if ($tipo === 'J') {
            return "El campo {$etiquetaCampo} debe tener exactamente 10 digitos para tipo J.";
        }
        return "El campo {$etiquetaCampo} debe tener 8 o 9 digitos para tipo {$tipo}.";
    }

    return '';
}

function responderRegistro($tipo, $mensaje, $datos = null, $esAjax = false) {
    if ($esAjax && $tipo === 'success') {
        $_SESSION['flash_mensaje'] = $mensaje;
        $_SESSION['flash_tipo'] = $tipo;
        if ($datos !== null) {
            $_SESSION['flash_datos'] = $datos;
        }
    }

    if ($esAjax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'ok' => $tipo === 'success',
            'tipo' => $tipo,
            'mensaje' => $mensaje,
            'redirect_url' => 'registrar.php'
        ], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        exit;
    }

    $_SESSION['flash_mensaje'] = $mensaje;
    $_SESSION['flash_tipo'] = $tipo;
    if ($datos !== null) {
        $_SESSION['flash_datos'] = $datos;
    } else {
        unset($_SESSION['flash_datos']);
    }
    header('Location: registrar.php');
    exit;
}

$mensaje = '';
$tipo_alerta = '';
$datos_impresion = null;

// Recuperar mensaje de sesion (viene de un redirect POST)
if (!empty($_SESSION['flash_mensaje'])) {
    $mensaje = $_SESSION['flash_mensaje'];
    $tipo_alerta = $_SESSION['flash_tipo'];
    $datos_impresion = $_SESSION['flash_datos'] ?? null;
    unset($_SESSION['flash_mensaje'], $_SESSION['flash_tipo'], $_SESSION['flash_datos']);
}

// Cargar tribunales para el Select dinamico (ordenados alfabeticamente)
$tribunales = [];
try {
    $stmtTrib = $pdo->query("SELECT id_tribunal, tribunal FROM tribunales ORDER BY tribunal ASC");
    $tribunales = $stmtTrib->fetchAll();
} catch (PDOException $e) {
    if (empty($mensaje)) {
        $mensaje = "Error al cargar tribunales: " . $e->getMessage();
        $tipo_alerta = 'danger';
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $esAjax = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';

    $n_expediente = trim($_POST['n_expediente'] ?? '');
    $fecha_entrada = trim($_POST['fecha_entrada'] ?? '');
    $id_tribunal = trim($_POST['id_tribunal'] ?? '');
    
    $demandante = trim($_POST['demandante'] ?? '');
    $tipo_doc_demandante = trim($_POST['tipo_doc_demandante'] ?? 'V');
    $cedula_rif_demandante_num = preg_replace('/\D+/', '', trim($_POST['cedula_rif_demandante'] ?? ''));
    $cedula_rif_demandante = !empty($cedula_rif_demandante_num) ? $tipo_doc_demandante . '-' . $cedula_rif_demandante_num : '';

    $demandado = trim($_POST['demandado'] ?? '');
    $tipo_doc_demandado = trim($_POST['tipo_doc_demandado'] ?? 'V');
    $cedula_rif_demandado_num = preg_replace('/\D+/', '', trim($_POST['cedula_rif_demandado'] ?? ''));
    $cedula_rif_demandado = !empty($cedula_rif_demandado_num) ? $tipo_doc_demandado . '-' . $cedula_rif_demandado_num : '';
    
    $motivo_delito = trim($_POST['motivo_delito'] ?? '');
    $n_legajo = trim($_POST['n_legajo'] ?? '');
    $observaciones = trim($_POST['observaciones'] ?? '');
    
    // Bloqueo en Servidor: Verifica con empty() que Expediente, Fecha, Tribunal, Demandante, Demandado, Motivo/Delito y Nro Legajo tengan datos
    if (empty($n_expediente) || empty($fecha_entrada) || empty($id_tribunal) || empty($demandante) || empty($demandado) || empty($motivo_delito) || empty($n_legajo)) {
        responderRegistro('warning', 'Por favor completa todos los campos obligatorios (Expediente, Fecha, Tribunal, Demandante, Demandado, Motivo/Delito, Nro Legajo).', null, $esAjax);
    }

    $errorDocumentoDemandante = validarDocumentoCiRif($tipo_doc_demandante, $cedula_rif_demandante_num, 'C.I./RIF Demandante');
    if ($errorDocumentoDemandante !== '') {
        responderRegistro('warning', $errorDocumentoDemandante, null, $esAjax);
    }

    $errorDocumentoDemandado = validarDocumentoCiRif($tipo_doc_demandado, $cedula_rif_demandado_num, 'C.I./RIF Demandado');
    if ($errorDocumentoDemandado !== '') {
        responderRegistro('warning', $errorDocumentoDemandado, null, $esAjax);
    } else {
        try {
            // PASO 1: Verificar si el expediente ya existe
            $stmtCheck = $pdo->prepare("SELECT * FROM maestro WHERE n_expediente = :n_expediente LIMIT 1");
            $stmtCheck->execute([':n_expediente' => $n_expediente]);
            $expedienteExistente = $stmtCheck->fetch(PDO::FETCH_ASSOC);
            
            // PASO 2: Iniciar transaccion de seguridad
            $pdo->beginTransaction();
            
            $esNuevo = false;
            $cambios = [];
            $detalles_cambios = '';
            
            if (!$expedienteExistente) {
                // CASO A: El expediente NO existe - Crear nuevo registro
                $sqlInsert = "INSERT INTO maestro (n_expediente, fecha_entrada, id_tribunal, demandante, cedula_rif_demandante, demandado, cedula_rif_demandado, motivo_delito, n_legajo, observaciones) 
                              VALUES (:n_expediente, :fecha_entrada, :id_tribunal, :demandante, :cedula_rif_demandante, :demandado, :cedula_rif_demandado, :motivo_delito, :n_legajo, :observaciones)";
                
                $stmtInsert = $pdo->prepare($sqlInsert);
                $stmtInsert->execute([
                    ':n_expediente' => $n_expediente,
                    ':fecha_entrada' => $fecha_entrada,
                    ':id_tribunal' => $id_tribunal,
                    ':demandante' => $demandante,
                    ':cedula_rif_demandante' => $cedula_rif_demandante,
                    ':demandado' => $demandado,
                    ':cedula_rif_demandado' => $cedula_rif_demandado,
                    ':motivo_delito' => $motivo_delito,
                    ':n_legajo' => $n_legajo,
                    ':observaciones' => $observaciones
                ]);
                
                $esNuevo = true;
                
            } else {
                // CASO B: El expediente YA existe - Capturar datos viejos y comparar
                $datos_viejos = $expedienteExistente;
                
                // Preparar datos nuevos para comparacion
                $datos_nuevos = [
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
                
                // Comparar y construir log de cambios
                $cambios = [];
                $campos_nombres = [
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
                    
                    if (in_array($campo, $campos_numericos)) {
                        // Comparacion numerica para campos como id_tribunal
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
                
                // Actualizar en la base de datos
                $sqlUpdate = "UPDATE maestro 
                              SET id_tribunal = :id_tribunal, 
                                  fecha_entrada = :fecha_entrada,
                                  demandante = :demandante,
                                  cedula_rif_demandante = :cedula_rif_demandante,
                                  demandado = :demandado,
                                  cedula_rif_demandado = :cedula_rif_demandado,
                                  motivo_delito = :motivo_delito,
                                  n_legajo = :n_legajo,
                                  observaciones = :observaciones
                              WHERE n_expediente = :n_expediente";
                
                $stmtUpdate = $pdo->prepare($sqlUpdate);
                $stmtUpdate->execute([
                    ':id_tribunal' => $id_tribunal,
                    ':fecha_entrada' => $fecha_entrada,
                    ':demandante' => $demandante,
                    ':cedula_rif_demandante' => $cedula_rif_demandante,
                    ':demandado' => $demandado,
                    ':cedula_rif_demandado' => $cedula_rif_demandado,
                    ':motivo_delito' => $motivo_delito,
                    ':n_legajo' => $n_legajo,
                    ':observaciones' => $observaciones,
                    ':n_expediente' => $n_expediente
                ]);
                
                // Guardar los cambios para auditoria
                $detalles_cambios = implode("\n", $cambios);
            }
            
            // PASO 3: SIEMPRE registrar en historial_movimientos
            $sqlHistorial = "INSERT INTO historial_movimientos (n_expediente, id_tribunal, fecha_movimiento, observaciones, id_usuario) 
                             VALUES (:n_expediente, :id_tribunal, NOW(), :observaciones, :id_usuario)";
            
            $stmtHistorial = $pdo->prepare($sqlHistorial);
            $stmtHistorial->execute([
                ':n_expediente' => $n_expediente,
                ':id_tribunal' => $id_tribunal,
                ':observaciones' => $observaciones,
                ':id_usuario' => $_SESSION['usuario_id']
            ]);
            
            // PASO 4: Confirmar transaccion
            $pdo->commit();
            
            // REGISTRAR EN AUDITORIA con formato mejorado
            $actualizar_confirmado = isset($_POST['actualizar_confirmado']) && $_POST['actualizar_confirmado'] == '1';
            
            if ($esNuevo) {
                // Expediente nuevo
                $accion_auditoria = 'CREAR_EXPEDIENTE';
                $recurso_auditoria = "Exp: {$n_expediente}";
                $detalles_auditoria = "Nuevo expediente creado\n" .
                                     "Tribunal: {$id_tribunal}\n" .
                                     "Demandante: {$demandante}\n" .
                                     "Demandado: {$demandado}\n" .
                                     "Legajo: {$n_legajo}";
            } else {
                // Expediente existente - verificar si hubo cambios
                if (!empty($cambios)) {
                    // Verificar si fue una actualizacion confirmada por el usuario
                    if ($actualizar_confirmado) {
                        $accion_auditoria = 'ACTUALIZACION_POR_DUPLICIDAD';
                        $recurso_auditoria = "Exp: {$n_expediente}";
                        $detalles_auditoria = "Actualizacion confirmada por el usuario (expediente existente)\n" . $detalles_cambios;
                    } else {
                        // Actualizacion normal
                        $accion_auditoria = 'ACTUALIZAR_EXPEDIENTE';
                        $recurso_auditoria = "Exp: {$n_expediente}";
                        $detalles_auditoria = $detalles_cambios;
                    }
                } else {
                    // No hubo cambios, es una sobrescritura/duplicado
                    $accion_auditoria = 'SOBREESCRITURA_POR_DUPLICADO';
                    $recurso_auditoria = "Exp: {$n_expediente}";
                    $detalles_auditoria = "Intento de registrar expediente existente sin cambios. Posible error de carga duplicada.";
                }
            }
            
            $resultado_auditoria = registrarAccion($accion_auditoria, $recurso_auditoria, $detalles_auditoria);
            
            // Debug temporal para admin
            if (!$resultado_auditoria && isset($_SESSION['debug_auditoria'])) {
                error_log("Error auditoria en registro: " . $_SESSION['debug_auditoria']);
            }
            
            // PASO 5: Verificar que el registro quedo guardado
            $stmtVerify = $pdo->prepare(
                "SELECT m.*, t.tribunal AS nombre_tribunal 
                 FROM maestro m 
                 LEFT JOIN tribunales t ON m.id_tribunal = t.id_tribunal 
                 WHERE m.n_expediente = :n_expediente 
                 LIMIT 1"
            );
            $stmtVerify->execute([':n_expediente' => $n_expediente]);
            $registroGuardado = $stmtVerify->fetch();

            if (!$registroGuardado) {
                responderRegistro('danger', 'Error: el expediente no pudo confirmarse en la base de datos.', null, $esAjax);
            } else {
                // PASO 6: Mensaje personalizado segun si era nuevo o existente
                $mensajeFinal = '';
                if ($esNuevo) {
                    $mensajeFinal = 'Expediente creado con exito.';
                } else {
                    $mensajeFinal = 'El expediente ya existia. Se ha actualizado su ubicacion y registrado el movimiento en el historial.';
                }
                
                $datosFlash = [
                    'n_expediente'          => $registroGuardado['n_expediente'],
                    'fecha_entrada'         => date('d/m/Y', strtotime($registroGuardado['fecha_entrada'])),
                    'tribunal'              => 'Trib. ' . $registroGuardado['id_tribunal'] . ' - ' . $registroGuardado['nombre_tribunal'],
                    'demandante'            => $registroGuardado['demandante'],
                    'cedula_rif_demandante' => $registroGuardado['cedula_rif_demandante'],
                    'demandado'             => $registroGuardado['demandado'],
                    'cedula_rif_demandado'  => $registroGuardado['cedula_rif_demandado'],
                    'motivo_delito'         => $registroGuardado['motivo_delito'],
                    'n_legajo'              => $registroGuardado['n_legajo'],
                    'observaciones'         => $registroGuardado['observaciones'],
                ];
                responderRegistro('success', $mensajeFinal, $datosFlash, $esAjax);
            }
        } catch (PDOException $e) {
            // Si hay error, revertir toda la transaccion
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            responderRegistro('danger', 'Error al guardar el expediente: ' . $e->getMessage(), null, $esAjax);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Expediente</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="assets/css/bootstrap-icons.css">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.5/dist/sweetalert2.min.css" rel="stylesheet">
    
    <style>
        :root {
            --institucional-blue: #1a237e;
            --institucional-gray: #f8f9fa;
        }
        body {
            /* Fondo de la pagina con imagen institucional */
            background-image: url('/background.png');
            backdrop-filter: blur(1px);
            background-size: cover;
            background-position: center top;
            background-repeat: no-repeat;
            background-attachment: fixed;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            min-height: 100vh;
        }
        .header-bg {
            /* Encabezado azul oscuro profesional */
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
            /* Card centrada con sombras - Formato rectangular maximo */
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
            /* Boton grande de color verde */
            font-weight: 700;
            font-size: 1.25rem;
            padding: 15px;
            letter-spacing: 1px;
        }
        .form-label {
            font-weight: 600;
            color: #4a4a4a;
        }
        @media print {
            body { background-color: #ffffff !important; font-size: 14px; }
            .d-print-none { display: none !important; }
            .d-print-block { display: block !important; }
            .print-sheet { border: 2px solid #000; padding: 2rem; border-radius: 8px; }
            .print-header { text-align: center; border-bottom: 2px solid #000; margin-bottom: 2rem; padding-bottom: 1rem; }
            .print-header h2 { font-weight: bold; text-transform: uppercase; margin: 0; }
            .print-label { font-weight: bold; text-transform: uppercase; font-size: 0.8rem; color: #555; display: block; margin-bottom: 5px; }
            .print-value { border: 1px solid #ccc; padding: 10px; border-radius: 5px; min-height: 42px; font-weight: 500; font-size: 1rem; margin-bottom: 15px; }
            .print-value-full { min-height: 80px; }
        }
    </style>
</head>
<body>

<div class="container py-5 d-print-none">
    
    
    <div class="row justify-content-center">
        <div class="col-19 col-xxl-11">
            
            <!-- Alertas de mensajes -->
            <?php if (!empty($mensaje)): ?>
                <div class="alert alert-<?= $tipo_alerta ?> alert-dismissible fade show shadow-sm fw-bold border-0 border-start border-<?= $tipo_alerta ?> border-4" role="alert">
                    <?php if ($tipo_alerta == 'success'): ?>
                        <i class="bi bi-check-circle-fill me-2 fs-5 text-success"></i>
                    <?php elseif ($tipo_alerta == 'warning'): ?>
                        <i class="bi bi-exclamation-triangle-fill me-2 fs-5 text-warning"></i>
                    <?php else: ?>
                        <i class="bi bi-x-circle-fill me-2 fs-5 text-danger"></i>
                    <?php endif; ?>
                    <?= htmlspecialchars($mensaje) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="card card-custom">
                <div class="card-header header-bg text-center py-4">
                    <h3 class="mb-0 fw-bold"><i class="bi bi-folder-plus me-2"></i>REGISTRAR EXPEDIENTE</h3>
                    <a href="index.php" class="btn btn-secondary btn-sm btn-volver-menu">
                        <i class="bi bi-arrow-left me-2"></i>Volver al Menu
                    </a>
                </div>
                <div class="card-body p-4 p-md-5">
                    
                    <!-- Formulario con Bloqueo en Navegador (needs-validation) -->
                    <form action="registrar.php" method="POST" class="needs-validation" novalidate id="formRegistrarExpediente">
                        
                        <!-- FILA 1: Nro Expediente y Fecha de Ingreso -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="n_expediente" class="form-label">Nro Expediente <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="n_expediente" name="n_expediente" placeholder="Ej: 000-24" required>
                                <div class="invalid-feedback">Ingresa el Nro de Expediente.</div>
                            </div>
                            <div class="col-md-6">
                                <label for="fecha_entrada" class="form-label">Fecha de Ingreso <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="fecha_entrada" name="fecha_entrada" required>
                                <div class="invalid-feedback">Selecciona la fecha de ingreso.</div>
                            </div>
                        </div>

                        <!-- FILA 2: Tribunal -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-12">
                                <label for="busqueda-tribunales" class="form-label">Tribunal <span class="text-danger">*</span></label>
                                <select class="form-select" id="busqueda-tribunales" name="id_tribunal" required>
                                    <option value="" disabled selected>Escribe el nombre del tribunal...</option>
                                    <?php foreach ($tribunales as $trib): ?>
                                        <option value="<?= htmlspecialchars($trib['id_tribunal']) ?>">
                                            Trib. <?= htmlspecialchars($trib['id_tribunal']) ?> - <?= htmlspecialchars($trib['tribunal']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">Selecciona el tribunal correspondiente.</div>
                            </div>
                        </div>

                        <!-- FILA 3: Nombre Demandante y C.I./RIF Demandante -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-8">
                                <label for="demandante" class="form-label">Nombre Demandante <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="demandante" name="demandante" placeholder="Nombre completo del demandante" required>
                                <div class="invalid-feedback">Ingresa el nombre del demandante.</div>
                            </div>
                            <div class="col-md-4">
                                <label for="cedula_rif_demandante" class="form-label">C.I. / RIF Demandante</label>
                                <div class="input-group">
                                    <select class="form-select" style="max-width: 80px;" id="tipo_doc_demandante" name="tipo_doc_demandante" required>
                                        <option value="V" selected>V</option>
                                        <option value="E">E</option>
                                        <option value="J">J</option>
                                    </select>
                                    <input type="text" class="form-control" id="cedula_rif_demandante" name="cedula_rif_demandante" placeholder="Ej: 12345678">
                                </div>
                            </div>
                        </div>

                        <!-- FILA 4: Nombre Demandado y C.I./RIF Demandado -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-8">
                                <label for="demandado" class="form-label">Nombre Demandado <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="demandado" name="demandado" placeholder="Nombre completo del demandado" required>
                                <div class="invalid-feedback">Ingresa el nombre del demandado.</div>
                            </div>
                            <div class="col-md-4">
                                <label for="cedula_rif_demandado" class="form-label">C.I. / RIF Demandado</label>
                                <div class="input-group">
                                    <select class="form-select" style="max-width: 80px;" id="tipo_doc_demandado" name="tipo_doc_demandado" required>
                                        <option value="V" selected>V</option>
                                        <option value="E">E</option>
                                        <option value="J">J</option>
                                    </select>
                                    <input type="text" class="form-control" id="cedula_rif_demandado" name="cedula_rif_demandado" placeholder="Ej: 12345678">
                                </div>
                            </div>
                        </div>

                        <!-- FILA 5: Motivo/Delito y Nro Legajo -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-8">
                                <label for="motivo_delito" class="form-label">Motivo / Delito <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="motivo_delito" name="motivo_delito" placeholder="Descripcion del motivo o delito" required>
                                <div class="invalid-feedback">Ingresa la descripcion del motivo o delito.</div>
                            </div>
                            <div class="col-md-4">
                                <label for="n_legajo" class="form-label">Nro Legajo <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="n_legajo" name="n_legajo" placeholder="Ej: L-001" required>
                                <div class="invalid-feedback">Ingresa el numero de legajo.</div>
                            </div>
                        </div>

                        <!-- FILA 6: Observaciones -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-12">
                                <label for="observaciones" class="form-label">Observaciones</label>
                                <textarea class="form-control" id="observaciones" name="observaciones" rows="4" placeholder="Detalles o anotaciones adicionales importantes..."></textarea>
                            </div>
                        </div>

                        <!-- FILA 7: Botones de Accion -->
                        <div class="row mt-5">
                            <div class="col-12 d-grid gap-3">
                                <button type="submit" class="btn btn-success btn-success-custom js-submit-btn" id="btnRegistrarExpediente"
                                        data-default-html="<i class='bi bi-save-fill me-2'></i>GUARDAR EXPEDIENTE"
                                        data-loading-html="<span class='spinner-border spinner-border-sm me-2' role='status' aria-hidden='true'></span>Procesando registro...">
                                    <i class="bi bi-save-fill me-2"></i>GUARDAR EXPEDIENTE
                                </button>
                                <?php if (!empty($datos_impresion) && $tipo_alerta == 'success'): ?>
                                    <button type="button" class="btn btn-info btn-success-custom text-white fw-bold" onclick="window.print()">
                                        <i class="bi bi-printer-fill me-2"></i>IMPRIMIR FICHA DE REGISTRO
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="mt-4 text-center">
                            <a href="buscador.php" class="text-decoration-none text-secondary fw-bold">
                                <i class="bi bi-search me-1"></i> Ir al Buscador de Expedientes
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($datos_impresion) && $tipo_alerta == 'success'): ?>
<!-- Ficha de Registro para Impresion -->
<div class="container py-5 d-none d-print-block">
    <div class="print-sheet">
        <div class="print-header">
            <h2>Ficha Oficial de Registro de Expediente</h2>
        </div>
        
        <div class="row mb-3">
            <div class="col-6">
                <span class="print-label">Nro Expediente</span>
                <div class="print-value"><?= htmlspecialchars($datos_impresion['n_expediente']) ?></div>
            </div>
            <div class="col-6">
                <span class="print-label">Fecha de Ingreso</span>
                <div class="print-value"><?= htmlspecialchars($datos_impresion['fecha_entrada']) ?></div>
            </div>
        </div>
        
        <div class="row mb-3">
            <div class="col-12">
                <span class="print-label">Tribunal Asignado</span>
                <div class="print-value"><?= htmlspecialchars($datos_impresion['tribunal']) ?></div>
            </div>
        </div>
        
        <div class="row mb-3">
            <div class="col-8">
                <span class="print-label">Demandante</span>
                <div class="print-value"><?= htmlspecialchars($datos_impresion['demandante']) ?></div>
            </div>
            <div class="col-4">
                <span class="print-label">C.I. / RIF Demandante</span>
                <div class="print-value"><?= htmlspecialchars($datos_impresion['cedula_rif_demandante']) ?: 'N/A' ?></div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-8">
                <span class="print-label">Demandado</span>
                <div class="print-value"><?= htmlspecialchars($datos_impresion['demandado']) ?></div>
            </div>
            <div class="col-4">
                <span class="print-label">C.I. / RIF Demandado</span>
                <div class="print-value"><?= htmlspecialchars($datos_impresion['cedula_rif_demandado']) ?: 'N/A' ?></div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-8">
                <span class="print-label">Motivo / Delito</span>
                <div class="print-value"><?= htmlspecialchars($datos_impresion['motivo_delito']) ?></div>
            </div>
            <div class="col-4">
                <span class="print-label">Nro Legajo</span>
                <div class="print-value"><?= htmlspecialchars($datos_impresion['n_legajo']) ?></div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-12">
                <span class="print-label">Observaciones</span>
                <div class="print-value print-value-full"><?= nl2br(htmlspecialchars($datos_impresion['observaciones'])) ?: 'Sin observaciones.' ?></div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
// Logica de Validacion: Bloqueo en Navegador (Bootstrap needs-validation)
(function () {
  'use strict'
  const form = document.getElementById('formRegistrarExpediente');
  const submitBtn = document.getElementById('btnRegistrarExpediente');
  let envioEnProceso = false;

  function activarEstadoCarga() {
    if (!submitBtn) return;
    if (!submitBtn.dataset.defaultHtml) {
      submitBtn.dataset.defaultHtml = submitBtn.innerHTML;
    }
    submitBtn.disabled = true;
    submitBtn.innerHTML = submitBtn.dataset.loadingHtml || '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Procesando registro...';
  }

  function restaurarEstadoBoton() {
    if (!submitBtn) return;
    submitBtn.disabled = false;
    submitBtn.innerHTML = submitBtn.dataset.defaultHtml || "<i class='bi bi-save-fill me-2'></i>GUARDAR EXPEDIENTE";
  }

  if (!form) return;

  form.addEventListener('submit', async function (e) {
    e.preventDefault();
    e.stopPropagation();

    if (envioEnProceso) return;

    if (!form.checkValidity()) {
      form.classList.add('was-validated');
      return;
    }

    form.classList.add('was-validated');
    envioEnProceso = true;
    activarEstadoCarga();

    try {
      const formData = new FormData(form);
      if (typeof confirmadoActualizar !== 'undefined' && confirmadoActualizar) {
        formData.set('actualizar_confirmado', '1');
      }

      const response = await fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }
      });

      // Debug: Ver qué está devolviendo el servidor
      console.log('Response status:', response.status);
      console.log('Response headers:', response.headers.get('Content-Type'));

      const rawText = await response.text();
      console.log('Raw response:', rawText.substring(0, 500));
      
      let data = null;
      try {
        data = JSON.parse(rawText);
        console.log('Parsed data:', data);
      } catch (parseError) {
        console.error('Parse error:', parseError);
        console.error('Raw text:', rawText);
        
        if (response.redirected && response.url) {
          window.location.replace(response.url);
          return;
        }
        throw new Error('Respuesta no JSON valida: ' + rawText.substring(0, 180));
      }

      if (data.ok) {
        window.location.replace(data.redirect_url || 'registrar.php');
        return;
      }

      await Swal.fire({
        title: 'Validacion del sistema',
        text: data.mensaje || 'No se pudo completar el registro.',
        icon: data.tipo || 'warning',
        confirmButtonText: 'Entendido'
      });
    } catch (error) {
    if response.ok === false {
      await Swal.fire({
        title: 'Error de conexion',
        text: 'No fue posible completar el envio. Intenta nuevamente.',
        icon: 'error',
        confirmButtonText: 'Entendido'
      
      })};
    } finally {
      envioEnProceso = false;
      restaurarEstadoBoton();
    }
  }, false);
})()
</script>

<!-- jQuery (requerido para Select2) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.5/dist/sweetalert2.all.min.js"></script>

<script>
// Variable global para controlar si el usuario confirmo actualizar
let confirmadoActualizar = false;

// Inicializar Select2 en el selector de tribunales
$(document).ready(function() {
    $('#busqueda-tribunales').select2({
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

    function aplicarReglasDocumento(tipoSelector, numeroSelector) {
        const tipo = $(tipoSelector).val();
        const $numero = $(numeroSelector);
        const maxLen = (tipo === 'J') ? 10 : 9;
        const minLen = (tipo === 'J') ? 10 : 8;
        const ejemplo = (tipo === 'J') ? 'Ej: 1234567890' : 'Ej: 12345678 o 123456789';

        $numero.attr('maxlength', maxLen);
        $numero.attr('minlength', minLen);
        $numero.attr('placeholder', ejemplo);
        $numero.val(($numero.val() || '').replace(/\D/g, '').slice(0, maxLen));
    }

    $('#cedula_rif_demandante, #cedula_rif_demandado').on('input', function() {
        this.value = this.value.replace(/\D/g, '');
    });

    $('#tipo_doc_demandante').on('change', function() {
        aplicarReglasDocumento('#tipo_doc_demandante', '#cedula_rif_demandante');
    });

    $('#tipo_doc_demandado').on('change', function() {
        aplicarReglasDocumento('#tipo_doc_demandado', '#cedula_rif_demandado');
    });

    aplicarReglasDocumento('#tipo_doc_demandante', '#cedula_rif_demandante');
    aplicarReglasDocumento('#tipo_doc_demandado', '#cedula_rif_demandado');
    
    // Validacion AJAX cuando el campo n_expediente pierde el foco
    $('#n_expediente').on('blur', function() {
        const n_expediente = $(this).val().trim();
        
        console.log('=== BLUR EVENT DEBUG ===');
        console.log('Campo encontrado:', $(this).length > 0);
        console.log('Valor del campo:', n_expediente);
        console.log('Longitud:', n_expediente.length);
        console.log('========================');
        
        // Si el campo esta vacio, no hacer nada
        if (n_expediente === '') {
            console.log('Campo vacío, no se hace petición AJAX');
            return;
        }
        
        console.log('Enviando AJAX con n_expediente:', n_expediente);
        
        // Hacer peticion AJAX para verificar si existe (usando GET por compatibilidad)
        $.ajax({
            url: '/verificar_expediente',
            type: 'GET',
            data: { n_expediente: n_expediente },
            dataType: 'json',
            cache: false,
            beforeSend: function() {
                console.log('Enviando datos via GET:', { n_expediente: n_expediente });
            },
            success: function(response) {
                console.log('Response recibida:', response);
                
                if (response.error) {
                    // Hay un error pero se recibió respuesta JSON válida
                    Swal.fire({
                        title: 'Error del Sistema',
                        text: response.mensaje,
                        icon: 'error',
                        confirmButtonText: 'Entendido'
                    });
                    return;
                }
                
                if (response.existe) {
                    // El expediente ya existe, mostrar alerta con SweetAlert2
                    Swal.fire({
                        title: ' Atencion',
                        html: `
                            <p class="mb-3"><strong>Este expediente ya existe en el sistema:</strong></p>
                            <div class="text-start bg-light p-3 rounded">
                                <p class="mb-1"><strong>Expediente:</strong> ${response.datos.n_expediente}</p>
                                <p class="mb-1"><strong>Demandante:</strong> ${response.datos.demandante}</p>
                                <p class="mb-0"><strong>Demandado:</strong> ${response.datos.demandado}</p>
                            </div>
                            <p class="mt-3 text-danger"><strong>Deseas actualizar su informacion o prefieres corregir el numero?</strong></p>
                        `,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#28a745',
                        cancelButtonColor: '#dc3545',
                        confirmButtonText: '<i class="bi bi-arrow-repeat me-2"></i>Actualizar Informacion',
                        cancelButtonText: '<i class="bi bi-pencil me-2"></i>Corregir Numero',
                        reverseButtons: true,
                        allowOutsideClick: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Usuario eligio ACTUALIZAR
                            confirmadoActualizar = true;
                            Swal.fire({
                                title: 'Modo Actualizacion',
                                text: 'El sistema actualizara el expediente existente cuando guardes.',
                                icon: 'info',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        } else {
                            // Usuario eligio CORREGIR
                            confirmadoActualizar = false;
                            $('#n_expediente').val('').focus();
                            Swal.fire({
                                title: 'Corregir Numero',
                                text: 'Por favor, ingresa el numero de expediente correcto.',
                                icon: 'info',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        }
                    });
                } else {
                    // El expediente no existe, puede registrarse normalmente
                    confirmadoActualizar = false;
                }
            },
            error: function(xhr, status, error) {
                console.log('=== AJAX ERROR DEBUG ===');
                console.log('Status:', status);
                console.log('Error:', error);
                console.log('Response Text:', xhr.responseText);
                console.log('Response Length:', xhr.responseText ? xhr.responseText.length : 0);
                console.log('Status Code:', xhr.status);
                console.log('Content-Type:', xhr.getResponseHeader('Content-Type'));
                console.log('========================');
                
                let errorMsg = 'Error desconocido';
                let responsePreview = '';
                
                // Intentar obtener más detalles del error
                if (xhr.responseText) {
                    responsePreview = xhr.responseText.substring(0, 500);
                    errorMsg = xhr.responseText.substring(0, 200);
                } else if (error) {
                    errorMsg = error;
                }
                
                Swal.fire({
                    title: 'Error de Verificación',
                    html: `
                        <p>No se pudo verificar si el expediente existe en el sistema.</p>
                        <div class="text-start bg-light p-2 rounded mb-3" style="max-height: 200px; overflow-y: auto; font-size: 0.85rem;">
                            <strong>Detalles técnicos:</strong><br>
                            <strong>Status:</strong> ${status}<br>
                            <strong>Error:</strong> ${error}<br>
                            <strong>Código HTTP:</strong> ${xhr.status}<br>
                            <strong>Content-Type:</strong> ${xhr.getResponseHeader('Content-Type') || 'N/A'}<br>
                            <strong>Response Length:</strong> ${xhr.responseText ? xhr.responseText.length : 0} bytes<br>
                            ${responsePreview ? '<hr><strong>Response Preview:</strong><br><pre style="font-size: 0.75rem; white-space: pre-wrap;">' + responsePreview + '</pre>' : ''}
                        </div>
                        <p><strong>¿Deseas continuar con el registro de todas formas?</strong></p>
                    `,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="bi bi-check-circle me-2"></i>Continuar Registro',
                    cancelButtonText: '<i class="bi bi-arrow-clockwise me-2"></i>Intentar Nuevamente',
                    reverseButtons: true,
                    width: '600px'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Usuario eligió continuar sin verificación
                        confirmadoActualizar = false;
                        Swal.fire({
                            title: 'Continuando...',
                            text: 'Se registrará el expediente sin verificación de duplicados.',
                            icon: 'info',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        // Usuario eligió intentar nuevamente
                        $('#n_expediente').focus();
                    }
                });
            }
        });
    });
    
});
</script>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>





