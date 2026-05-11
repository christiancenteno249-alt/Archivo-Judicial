<?php
require_once "conexion.php";
require_once "auth_check.php";

// ------------------------------------------------------------------------
// CONFIGURACION DE ESTADO DE BUSQUEDA (POST + PRG)
// ------------------------------------------------------------------------
$resultados_por_pagina = 10;
$session_key_filtros = 'buscador_filtros';
$session_key_ejecutado = 'buscador_ejecutado';
$session_key_pagina = 'buscador_pagina';

$filtros_default = [
    'expediente' => '',
    'n_legajo' => '',
    'demandante' => '',
    'tipo_dante' => 'V',
    'ced_dante' => '',
    'demandado' => '',
    'tipo_dado' => 'V',
    'ced_dado' => '',
    'fecha' => '',
    'fecha_desde' => '',
    'fecha_hasta' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['limpiar'])) {
        unset($_SESSION[$session_key_filtros], $_SESSION[$session_key_ejecutado], $_SESSION[$session_key_pagina]);
        header('Location: buscador.php');
        exit;
    }

    if (isset($_POST['ejecutar'])) {
        $filtros_post = [
            'expediente' => trim($_POST['expediente'] ?? ''),
            'n_legajo' => trim($_POST['n_legajo'] ?? ''),
            'demandante' => trim($_POST['demandante'] ?? ''),
            'tipo_dante' => $_POST['tipo_dante'] ?? 'V',
            'ced_dante' => trim($_POST['ced_dante'] ?? ''),
            'demandado' => trim($_POST['demandado'] ?? ''),
            'tipo_dado' => $_POST['tipo_dado'] ?? 'V',
            'ced_dado' => trim($_POST['ced_dado'] ?? ''),
            'fecha' => trim($_POST['fecha'] ?? ''),
            'fecha_desde' => trim($_POST['fecha_desde'] ?? ''),
            'fecha_hasta' => trim($_POST['fecha_hasta'] ?? '')
        ];

        $_SESSION[$session_key_filtros] = $filtros_post;
        $_SESSION[$session_key_ejecutado] = true;
        $_SESSION[$session_key_pagina] = 1;

        header('Location: buscador.php');
        exit;
    }

    if (isset($_POST['pagina'])) {
        $pagina_post = (int)($_POST['pagina'] ?? 1);
        $_SESSION[$session_key_pagina] = $pagina_post > 0 ? $pagina_post : 1;
        header('Location: buscador.php');
        exit;
    }
}

// ------------------------------------------------------------------------
// SOPORTE LEGACY: MIGRAR GET A SESION Y LIMPIAR URL
// ------------------------------------------------------------------------
if (!empty($_GET)) {
    $legacy_filtros = $filtros_default;
    $legacy_filtros['expediente'] = trim($_GET['expediente'] ?? '');
    $legacy_filtros['n_legajo'] = trim($_GET['n_legajo'] ?? '');
    $legacy_filtros['demandante'] = trim($_GET['demandante'] ?? '');
    $legacy_filtros['tipo_dante'] = $_GET['tipo_dante'] ?? 'V';
    $legacy_filtros['ced_dante'] = trim($_GET['ced_dante'] ?? '');
    $legacy_filtros['demandado'] = trim($_GET['demandado'] ?? '');
    $legacy_filtros['tipo_dado'] = $_GET['tipo_dado'] ?? 'V';
    $legacy_filtros['ced_dado'] = trim($_GET['ced_dado'] ?? '');
    $legacy_filtros['fecha'] = trim($_GET['fecha'] ?? '');
    $legacy_filtros['fecha_desde'] = trim($_GET['fecha_desde'] ?? '');
    $legacy_filtros['fecha_hasta'] = trim($_GET['fecha_hasta'] ?? '');

    $busqueda_rapida = trim($_GET['search'] ?? '');
    if ($legacy_filtros['expediente'] === '' && $busqueda_rapida !== '') {
        $legacy_filtros['expediente'] = $busqueda_rapida;
    }

    $hay_filtros_legacy = false;
    foreach ($legacy_filtros as $campo => $valor) {
        if (in_array($campo, ['tipo_dante', 'tipo_dado'], true)) {
            continue;
        }
        if ($valor !== '') {
            $hay_filtros_legacy = true;
            break;
        }
    }

    if ($hay_filtros_legacy || isset($_GET['ejecutar'])) {
        $_SESSION[$session_key_filtros] = $legacy_filtros;
        $_SESSION[$session_key_ejecutado] = true;
    }

    if (isset($_GET['pagina']) && is_numeric($_GET['pagina'])) {
        $pagina_get = (int)$_GET['pagina'];
        $_SESSION[$session_key_pagina] = $pagina_get > 0 ? $pagina_get : 1;
    }

    header('Location: buscador.php');
    exit;
}

$filtros = $filtros_default;
if (isset($_SESSION[$session_key_filtros]) && is_array($_SESSION[$session_key_filtros])) {
    $filtros = array_merge($filtros, $_SESSION[$session_key_filtros]);
}

$expediente = $filtros['expediente'];
$n_legajo   = $filtros['n_legajo'];
$demandante = $filtros['demandante'];
$tipo_dante = $filtros['tipo_dante'];
$ced_dante  = $filtros['ced_dante'];
$demandado  = $filtros['demandado'];
$tipo_dado  = $filtros['tipo_dado'];
$ced_dado   = $filtros['ced_dado'];
$fecha      = $filtros['fecha'];

// NUEVOS FILTROS: Rango de fechas
$fecha_desde = $filtros['fecha_desde'];
$fecha_hasta = $filtros['fecha_hasta'];

$pagina_actual = isset($_SESSION[$session_key_pagina]) && is_numeric($_SESSION[$session_key_pagina]) ? (int)$_SESSION[$session_key_pagina] : 1;
if ($pagina_actual < 1) $pagina_actual = 1;
$offset = ($pagina_actual - 1) * $resultados_por_pagina;
$busqueda_ejecutada = !empty($_SESSION[$session_key_ejecutado]);

// Si fecha_desde tiene valor pero fecha_hasta no, asumir fecha_hasta = hoy
if (!empty($fecha_desde) && empty($fecha_hasta)) {
    $fecha_hasta = date('Y-m-d');
}

$hay_busqueda = $expediente !== '' || $n_legajo !== '' || $demandante !== '' || $ced_dante !== '' || 
                $demandado !== '' || $ced_dado !== '' || $fecha !== '' || 
                $fecha_desde !== '' || $fecha_hasta !== '';

$resultados = [];
$total_registros = 0;
$total_paginas = 0;
$mensaje_error = '';

if ($busqueda_ejecutada) {
    if ($hay_busqueda) {
        
        // ------------------------------------------------------------------------
        // CONSTRUCCION DINAMICA DE LA CONSULTA (PDO)
        // ------------------------------------------------------------------------
        // Base de la consulta
        $sqlBase = " FROM maestro m 
                     LEFT JOIN tribunales t ON m.id_tribunal = t.id_tribunal 
                     LEFT JOIN sedes_deposito s ON m.id_sede = s.id_sede 
                     WHERE 1=1";

        // COUNT: Cuenta expedientes unicos
        $sqlCount = "SELECT COUNT(DISTINCT m.Id) as total" . $sqlBase;
        
        // SELECT: Trae datos de expedientes
        $sql = "SELECT m.*, ANY_VALUE(t.tribunal) AS tribunal, ANY_VALUE(s.nombre_sede) AS nombre_sede" . $sqlBase;
        
        $condiciones = [];
        $parametros = [];
        
        if ($expediente !== '') {
            $condiciones[] = "m.n_expediente LIKE :expediente";
            $parametros[':expediente'] = "%$expediente%";
        }
        
        if ($n_legajo !== '') {
            $condiciones[] = "m.n_legajo LIKE :n_legajo";
            $parametros[':n_legajo'] = "%$n_legajo%";
        }
        
        if ($demandante !== '') {
            $condiciones[] = "m.demandante LIKE :demandante";
            $parametros[':demandante'] = "%$demandante%";
        }
        
        if ($ced_dante !== '') {
            $c_dante_completa = $tipo_dante . $ced_dante;
            $condiciones[] = "m.cedula_rif_demandante LIKE :ced_dante";
            $parametros[':ced_dante'] = "%$c_dante_completa%";
        }

        if ($demandado !== '') {
            $condiciones[] = "m.demandado LIKE :demandado";
            $parametros[':demandado'] = "%$demandado%";
        }
        
        if ($ced_dado !== '') {
            $c_dado_completa = $tipo_dado . $ced_dado;
            $condiciones[] = "m.cedula_rif_demandado LIKE :ced_dado";
            $parametros[':ced_dado'] = "%$c_dado_completa%";
        }
        
        if ($fecha !== '') {
            $condiciones[] = "DATE(m.fecha_entrada) = :fecha";
            $parametros[':fecha'] = $fecha;
        }
        
        // NUEVO: Filtro de rango de fechas
        if (!empty($fecha_desde) && !empty($fecha_hasta)) {
            $condiciones[] = "DATE(m.fecha_entrada) BETWEEN :fecha_desde AND :fecha_hasta";
            $parametros[':fecha_desde'] = $fecha_desde;
            $parametros[':fecha_hasta'] = $fecha_hasta;
        } elseif (!empty($fecha_desde)) {
            // Solo fecha_desde: buscar desde esa fecha en adelante
            $condiciones[] = "DATE(m.fecha_entrada) >= :fecha_desde";
            $parametros[':fecha_desde'] = $fecha_desde;
        } elseif (!empty($fecha_hasta)) {
            // Solo fecha_hasta: buscar hasta esa fecha
            $condiciones[] = "DATE(m.fecha_entrada) <= :fecha_hasta";
            $parametros[':fecha_hasta'] = $fecha_hasta;
        }
        
        if (count($condiciones) > 0) {
            $where_clause = " AND " . implode(" AND ", $condiciones);
            $sqlCount .= $where_clause;
            $sql .= $where_clause;
        }
        
        try {
            $stmtCount = $pdo->prepare($sqlCount);
            $stmtCount->execute($parametros);
            $total_registros = $stmtCount->fetch()['total'];
            $total_paginas = ceil($total_registros / $resultados_por_pagina);
            
            if ($offset >= $total_registros && $total_registros > 0) {
                $pagina_actual = $total_paginas;
                $offset = ($pagina_actual - 1) * $resultados_por_pagina;
            }

            // GROUP BY para evitar duplicados
            $sql .= " GROUP BY m.Id ORDER BY m.fecha_entrada DESC LIMIT :limit OFFSET :offset";
            
            $stmt = $pdo->prepare($sql);
            
            foreach ($parametros as $key => &$val) {
                $stmt->bindParam($key, $val);
            }
            
            $stmt->bindValue(':limit', $resultados_por_pagina, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            
            $stmt->execute();
            $resultados = $stmt->fetchAll();
            
        } catch (PDOException $e) {
            $mensaje_error = "Error DB: " . $e->getMessage();
        }

    } else {
        $mensaje_error = ' Ingresa al menos un dato para iniciar la busqueda.';
    }
}

function renderPaginationPostButton($page, $label, $isDisabled = false, $isActive = false) {
    $activeClass = $isActive ? ' active' : '';
    $disabledClass = $isDisabled ? ' disabled' : '';
    echo '<li class="page-item' . $activeClass . $disabledClass . '">';

    if ($isDisabled) {
        echo '<span class="page-link">' . $label . '</span>';
    } else {
        echo '<form method="POST" action="buscador.php" class="d-inline m-0 p-0">';
        echo '<input type="hidden" name="pagina" value="' . (int)$page . '">';
        echo '<button type="submit" class="page-link border-0 bg-transparent">' . $label . '</button>';
        echo '</form>';
    }

    echo '</li>';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buscador Avanzado - Archivo Judicial</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="assets/css/bootstrap-icons.css">
    
    <style>
        :root {
            --institucional-blue: #004085;
            --azul-claro: #0056b3;
            --azul-hover: #003366;
            --institucional-gray: #f8f9fa;
            --rojo-alerta: #DC3545;
        }
        body {
            background-image: url('/background.png');
            backdrop-filter: blur(1px);
            background-size: cover;
            background-position: center top;
            background-repeat: no-repeat;
            background-attachment: fixed;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            padding-bottom: 50px;
            min-height: 100vh;
            background-color: #FFFFFF;
        }
        .header-title {
            color: var(--institucional-blue);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 30px;
            margin-top: 120px;
        }
        .card-search {
            background: #FFFFFF;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,64,133,0.15);
            border: none;
            border-top: 5px solid var(--institucional-blue);
            position: relative;
        }
        .card-search .card-body {
            padding-top: 3.5rem;
        }
        .btn-volver-menu {
            position: absolute;
            top: 15px;
            right: 20px;
            z-index: 10;
        }
        .form-label {
            font-size: 0.8rem;
            font-weight: 700;
            color: #555;
            text-transform: uppercase;
            margin-bottom: 0.25rem;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--institucional-blue);
            box-shadow: 0 0 0 0.25rem rgba(26, 35, 126, 0.25);
        }
        .btn-primary-custom {
            background-color: var(--institucional-blue);
            border-color: var(--institucional-blue);
            color: white;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-primary-custom:hover {
            background-color: var(--azul-hover);
            border-color: var(--azul-hover);
            color: white;
        }
        .table-container {
            background: #FFFFFF;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,64,133,0.1);
            overflow: hidden;
            margin-top: 25px;
        }
        .table thead {
            background-color: var(--institucional-blue);
            color: white;
        }
        .table th {
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            vertical-align: middle;
            border-bottom: none;
        }
        .table td {
            font-size: 0.9rem;
            vertical-align: middle;
        }
        .cedula-badge {
            font-size: 0.75rem;
            background-color: #e9ecef;
            color: #495057;
            padding: 3px 6px;
            border-radius: 4px;
            margin-top: 5px;
            display: inline-block;
            font-weight: 600;
            border: 1px solid #ced4da;
        }
        .info-bar {
            background-color: #e8eaf6;
            color: var(--institucional-blue);
            border-left: 4px solid var(--institucional-blue);
            padding: 12px 20px;
            border-radius: 6px;
            font-weight: 600;
            margin-bottom: 20px;
        }
        .pagination { margin-bottom: 0; }
        .page-link { color: var(--institucional-blue); }
        .page-item.active .page-link {
            background-color: var(--institucional-blue);
            border-color: var(--institucional-blue);
        }
        .form-check-input:checked {
            background-color: var(--institucional-blue);
            border-color: var(--institucional-blue);
        }
        .text-primary {
            color: var(--institucional-blue) !important;
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
        .truncate-ubicacion {
            max-width: 180px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            display: block;
        }
        /* Estilos para celda de sede */
        .sede-cell {
            max-width: 220px;
            overflow: hidden;
        }
        .sede-truncate {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            font-size: 0.9rem;
        }
        .sede-truncate span {
            display: inline-block;
            max-width: 140px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            vertical-align: middle;
        }
        .btn-link {
            color: var(--institucional-blue);
            text-decoration: none;
            font-size: 0.85rem;
        }
        .btn-link:hover {
            text-decoration: underline;
        }
        /* Estilos para nombres completos de tribunales */
        .tribunal-completo {
            white-space: normal !important;
            word-wrap: break-word;
            line-height: 1.3;
            font-size: 0.85rem;
        }
        /* Tabla flexible para adaptarse al contenido */
        .table {
            table-layout: auto !important;
        }
    </style>
</head>
<body>

<div class="container py-5">
    
    <div class="text-center mb-4">
        <h2 class="header-title"><i class="bi bi-search me-2"></i>Buscador Avanzado de Expedientes</h2>
        <p class="text-muted">Consulta Segura de Expedientes Judiciales</p>
        
    </div>
    <?php
    
    
    
    ?>
    
    <div class="card card-search mb-4">
        <a href="index.php" class="btn btn-secondary btn-sm btn-volver-menu">
            <i class="bi bi-arrow-left me-2"></i>Volver al Menu
        </a>
        <div class="card-body p-4">
            <form action="buscador.php" method="POST" id="searchForm">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Nro Expediente</label>
                        <input type="text" class="form-control" name="expediente" placeholder="Ej: 00001" value="<?= htmlspecialchars($expediente) ?>">
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label">Nro Legajo</label>
                        <input type="text" class="form-control" name="n_legajo" placeholder="Ej: L-001" value="<?= htmlspecialchars($n_legajo) ?>">
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">Demandante</label>
                        <input type="text" class="form-control" name="demandante" placeholder="Nombre completo" value="<?= htmlspecialchars($demandante) ?>">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">C.I. / RIF Demandante</label>
                        <div class="input-group">
                            <select class="form-select" name="tipo_dante" style="max-width: 75px; font-weight: bold;">
                                <option value="V" <?= $tipo_dante == 'V' ? 'selected' : '' ?>>V</option>
                                <option value="J" <?= $tipo_dante == 'J' ? 'selected' : '' ?>>J</option>
                                <option value="E" <?= $tipo_dante == 'E' ? 'selected' : '' ?>>E</option>
                            </select>
                            <input type="text" class="form-control" name="ced_dante" placeholder="Numero..." value="<?= htmlspecialchars($ced_dante) ?>">
                        </div>
                    </div>

                    <div class="col-md-5">
                        <label class="form-label">Demandado</label>
                        <input type="text" class="form-control" name="demandado" placeholder="Nombre completo" value="<?= htmlspecialchars($demandado) ?>">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">C.I. / RIF Demandado</label>
                        <div class="input-group">
                            <select class="form-select" name="tipo_dado" style="max-width: 75px; font-weight: bold;">
                                <option value="V" <?= $tipo_dado == 'V' ? 'selected' : '' ?>>V</option>
                                <option value="J" <?= $tipo_dado == 'J' ? 'selected' : '' ?>>J</option>
                                <option value="E" <?= $tipo_dado == 'E' ? 'selected' : '' ?>>E</option>
                            </select>
                            <input type="text" class="form-control" name="ced_dado" placeholder="Numero..." value="<?= htmlspecialchars($ced_dado) ?>">
                        </div>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Fecha Entrada</label>
                        <input type="date" class="form-control" name="fecha" value="<?= htmlspecialchars($fecha) ?>">
                    </div>
                    
                    <!-- NUEVOS FILTROS: Rango de Fechas -->
                    <div class="col-md-12">
                        <hr class="my-2">
                        <label class="form-label fw-bold text-primary"><i class="bi bi-calendar-range me-2"></i>Filtros Avanzados de Auditoria</label>
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label">Fecha Desde</label>
                        <input type="date" class="form-control" name="fecha_desde" value="<?= htmlspecialchars($fecha_desde) ?>" placeholder="Inicio del rango">
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label">Fecha Hasta</label>
                        <input type="date" class="form-control" name="fecha_hasta" value="<?= htmlspecialchars($fecha_hasta) ?>" placeholder="Fin del rango">
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label d-block">&nbsp;</label>
                        <small class="text-muted"><i class="bi bi-info-circle me-1"></i>Si solo llenas "Fecha Desde", se buscara hasta hoy</small>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-12 d-flex gap-2 justify-content-end">
                        <button type="submit" name="limpiar" class="btn btn-secondary px-4 fw-bold">
                            <i class="bi bi-eraser-fill me-1"></i> Limpiar Filtros
                        </button>
                        <button type="submit" name="ejecutar" class="btn btn-primary-custom px-4 fw-bold">
                            <i class="bi bi-search me-1"></i> Ejecutar Busqueda
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Gestion de Alertas y Errores -->
    <?php if ($mensaje_error): ?>
        <div class="alert alert-warning shadow-sm border-0 border-start border-warning border-4" role="alert">
            <i class="bi bi-info-circle-fill me-2"></i> <?= htmlspecialchars($mensaje_error) ?>
        </div>
    <?php endif; ?>

    <!-- Visualizacion de Resultados -->
    <?php if ($busqueda_ejecutada && $hay_busqueda && empty($mensaje_error)): ?>
        
        <?php if ($total_registros > 0): ?>
            
            <div class="info-bar d-flex justify-content-between align-items-center">
                <span><i class="bi bi-bar-chart-fill me-2"></i> Mostrando expedientes. Total listado: <strong><?= number_format($total_registros) ?></strong> registros coincidentes.</span>
                <span class="badge bg-primary fs-6">Pagina <?= $pagina_actual ?> de <?= $total_paginas ?></span>
            </div>

            <div class="table-container table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead>
                        <tr>
                            <th scope="col" class="ps-4">Expediente</th>
                            <th scope="col">F. Entrada</th>
                            <th scope="col">LEGAJO</th>
                            <th scope="col">Demandante</th>
                            <th scope="col">Demandado</th>
                            <th scope="col">Motivo / Delito</th>
                            <th scope="col">Tribunal</th>
                            <th scope="col" style="width: 220px;">Ubicacion</th>
                            <th scope="col" class="pe-4 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="table-group-divider">
                        <?php foreach ($resultados as $fila): ?>
                            <?php 
                                $fecha_limpia = date("d/m/Y", strtotime($fila['fecha_entrada']));
                                $nom_dante = !empty($fila['demandante']) ? mb_strtoupper($fila['demandante'], 'UTF-8') : "---";
                                $nom_dado = !empty($fila['demandado']) ? mb_strtoupper($fila['demandado'], 'UTF-8') : "---";
                                $motivo = !empty($fila['motivo_delito']) ? mb_strtoupper($fila['motivo_delito'], 'UTF-8') : "---";
                            ?>
                            <tr>
                                <td class="ps-4"><strong class="text-primary"><?= htmlspecialchars($fila['n_expediente']) ?></strong></td>
                                <td><?= $fecha_limpia ?></td>
                                <td><?= htmlspecialchars(preg_replace('/\.0$/', '', $fila['n_legajo'] ?? '---')) ?></td>
                                <td>
                                    <?= htmlspecialchars($nom_dante) ?><br>
                                    <span class="cedula-badge"><i class="bi bi-person-vcard text-muted me-1"></i><?= htmlspecialchars($fila['cedula_rif_demandante']) ?></span>
                                </td>
                                <td>
                                    <?= htmlspecialchars($nom_dado) ?><br>
                                    <span class="cedula-badge"><i class="bi bi-person-vcard text-muted me-1"></i><?= htmlspecialchars($fila['cedula_rif_demandado']) ?></span>
                                </td>
                                <td><small class="text-muted fw-bold"><?= htmlspecialchars($motivo) ?></small></td>
                                <td title="<?= htmlspecialchars($fila['tribunal'] ?? 'Tribunal ' . $fila['id_tribunal']) ?>">
                                    <span class="badge border border-secondary text-secondary text-wrap">
                                        <strong>Trib. <?= htmlspecialchars($fila['id_tribunal']) ?></strong>
                                        <?php if (!empty($fila['tribunal'])): ?>
                                            <br>
                                            <span class="tribunal-completo"><?= htmlspecialchars($fila['tribunal']) ?></span>
                                        <?php endif; ?>
                                    </span>
                                </td>
                                <td class="sede-cell" style="max-width: 220px;">
                                    <?php if (!empty($fila['nombre_sede'])): ?>
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div class="sede-truncate flex-grow-1" title="<?= htmlspecialchars($fila['nombre_sede']) ?>">
                                                <i class="bi bi-geo-alt-fill text-success me-1"></i>
                                                <span><?= htmlspecialchars($fila['nombre_sede']) ?></span>
                                            </div>
                                            <button class="btn btn-sm ms-2" 
                                                    style="background-color: #004085; color: white; flex-shrink: 0;"
                                                    onclick="verUbicacion(<?= $fila['Id'] ?>)"
                                                    title="Ver ubicación completa">
                                                <i class="bi bi-eye-fill"></i>
                                            </button>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted"><small>Sin ubicacion</small></span>
                                    <?php endif; ?>
                                </td>
                                <td class="pe-4 text-center">
                                    <div class="btn-group" role="group">
                                        <a href="ver_historial.php?id=<?= urlencode($fila['Id']) ?>&search=<?= urlencode($expediente) ?>" class="btn btn-sm btn-primary" title="Ver historial">
                                            <i class="bi bi-clock-history me-1"></i>Ver mas
                                        </a>
                                        <a href="editar_registro.php?id=<?= urlencode($fila['Id']) ?>" class="btn btn-sm btn-warning text-white" title="Editar registro">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <a href="imprimir_expediente.php?id=<?= urlencode($fila['Id']) ?>" class="btn btn-sm btn-success" title="Imprimir expediente" target="_blank">
                                            <i class="bi bi-printer-fill"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Navegacion de Paginacion -->
            <?php if ($total_paginas > 1): ?>
                <nav aria-label="Navegacion de paginas" class="mt-4 d-flex justify-content-center">
                    <ul class="pagination shadow-sm">
                        <!-- Primer pagina y Anterior -->
                        <?php renderPaginationPostButton($pagina_actual - 1, '<i class="bi bi-chevron-left"></i> Ant', $pagina_actual <= 1); ?>
                        
                        <?php
                        $start_page = max(1, $pagina_actual - 2);
                        $end_page = min($total_paginas, $pagina_actual + 2);

                        if ($start_page > 1) {
                            renderPaginationPostButton(1, '1');
                            if ($start_page > 2) {
                                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                            }
                        }

                        for ($i = $start_page; $i <= $end_page; $i++) {
                            $active = ($i == $pagina_actual);
                            renderPaginationPostButton($i, (string)$i, false, $active);
                        }

                        if ($end_page < $total_paginas) {
                            if ($end_page < $total_paginas - 1) {
                                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                            }
                            renderPaginationPostButton($total_paginas, (string)$total_paginas);
                        }
                        ?>

                        <?php renderPaginationPostButton($pagina_actual + 1, 'Sig <i class="bi bi-chevron-right"></i>', $pagina_actual >= $total_paginas); ?>
                    </ul>
                </nav>
            <?php endif; ?>

        <?php else: ?>
            <div class="alert alert-danger d-flex align-items-center shadow-sm border-0 border-start border-danger border-4 mt-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
                <div>
                    <strong>Sin coincidencias destacables.</strong> No se hallaron expedientes que cumplan con los filtros y criterios suministrados.
                </div>
            </div>
        <?php endif; ?>
        
    <?php endif; ?>
</div>

<!-- Modal de Ubicacion Fisica -->
<div class="modal fade" id="modalUbicacion" tabindex="-1" aria-labelledby="modalUbicacionLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #004085 0%, #0056b3 100%); color: white;">
                <h5 class="modal-title" id="modalUbicacionLabel">
                    <i class="bi bi-geo-alt-fill me-2"></i>Ubicación Física del Expediente
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="contenidoUbicacion">
                <div class="text-center py-5">
                    <div class="spinner-border text-success" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-3 text-muted">Cargando ubicacion...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Estilos para el modal de ubicacion */
#modalUbicacion .modal-body {
    font-size: 0.95rem;
}
#modalUbicacion .sede-nombre {
    white-space: normal !important;
    word-wrap: break-word !important;
    word-break: break-word !important;
    font-size: 0.9rem;
    line-height: 1.4;
}
#modalUbicacion .card-ubicacion {
    border: none;
    border-radius: 8px;
    transition: transform 0.2s;
}
#modalUbicacion .card-ubicacion:hover {
    transform: translateY(-2px);
}

/* ELIMINAR COMPLETAMENTE LA SOMBRA DEL MODAL */
#modalUbicacion .modal-content {
    box-shadow: none !important;
    -webkit-box-shadow: none !important;
    -moz-box-shadow: none !important;
    border: 2px solid #004085;
    filter: none !important;
}

#modalUbicacion .modal-dialog {
    box-shadow: none !important;
    -webkit-box-shadow: none !important;
    -moz-box-shadow: none !important;
    filter: none !important;
    margin: 0;
    width: 100%;
    max-width: none;
}

#modalUbicacion .modal-body {
    padding: 20px;
    overflow: visible;
}

#modalUbicacion {
    box-shadow: none !important;
    -webkit-box-shadow: none !important;
    -moz-box-shadow: none !important;
    filter: none !important;
    overflow-y: auto;
}

/* Eliminar COMPLETAMENTE el fondo del modal */
.modal-backdrop {
    display: none !important;
}

/* Eliminar el fondo del modal cuando está activo */
#modalUbicacion.show {
    background: none !important;
    background-color: transparent !important;
}

/* Asegurar que no hay fondo en ningún estado del modal */
.modal {
    background: none !important;
    background-color: transparent !important;
}

/* Permitir scroll de la página cuando el modal esté abierto */
body.modal-open {
    overflow: auto !important;
    padding-right: 0 !important;
}

html.modal-open {
    overflow: auto !important;
}

/* Posicionar el modal de forma que no interfiera con el scroll de la página */
#modalUbicacion {
    position: fixed !important;
    top: 50px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 1050;
    width: 90%;
    max-width: 800px;
}

/* Eliminar cualquier sombra de Bootstrap por defecto */
.modal.show .modal-dialog {
    box-shadow: none !important;
    -webkit-box-shadow: none !important;
    -moz-box-shadow: none !important;
}

/* Eliminar sombras de las cards internas del modal */
#modalUbicacion .card,
#modalUbicacion .card-ubicacion {
    box-shadow: none !important;
    -webkit-box-shadow: none !important;
    -moz-box-shadow: none !important;
    filter: none !important;
}

/* Eliminar cualquier efecto de elevación */
#modalUbicacion * {
    box-shadow: none !important;
    -webkit-box-shadow: none !important;
    -moz-box-shadow: none !important;
    filter: none !important;
}
</style>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
function verUbicacion(idExpediente) {
    // Abrir modal SIN backdrop y permitir scroll de la página
    const modal = new bootstrap.Modal(document.getElementById('modalUbicacion'), {
        backdrop: false,
        keyboard: true
    });
    
    // Permitir scroll en la página cuando el modal esté abierto
    document.body.style.overflow = 'auto';
    document.documentElement.style.overflow = 'auto';
    
    modal.show();
    
    // Asegurar que el scroll de la página siga funcionando después de abrir el modal
    document.getElementById('modalUbicacion').addEventListener('shown.bs.modal', function () {
        document.body.style.overflow = 'auto';
        document.documentElement.style.overflow = 'auto';
        document.body.classList.remove('modal-open');
    });
    
    // Restaurar scroll normal cuando se cierre el modal
    document.getElementById('modalUbicacion').addEventListener('hidden.bs.modal', function () {
        document.body.style.overflow = 'auto';
        document.documentElement.style.overflow = 'auto';
        document.body.classList.remove('modal-open');
    });
    
    // Mostrar loading
    document.getElementById('contenidoUbicacion').innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-success" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <p class="mt-3 text-muted">Cargando ubicacion...</p>
        </div>
    `;
    
    // Hacer peticion AJAX
    console.log('Solicitando ubicación para expediente ID:', idExpediente);
    
    fetch('obtener_ubicacion.php?id=' + idExpediente)
        .then(response => {
            console.log('Response status:', response.status);
            console.log('Response headers:', response.headers.get('Content-Type'));
            
            // Clonar la respuesta para poder leerla dos veces
            return response.clone().text().then(text => {
                console.log('Response text:', text);
                
                // Intentar parsear como JSON
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error('Error parseando JSON:', e);
                    console.error('Texto recibido:', text.substring(0, 500));
                    throw new Error('Respuesta no es JSON válido. Revisa la consola para más detalles.');
                }
            });
        })
        .then(data => {
            console.log('Datos recibidos:', data);
            
            // Verificar si requiere login
            if (data.requiere_login) {
                document.getElementById('contenidoUbicacion').innerHTML = `
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        ${data.mensaje}
                    </div>
                `;
                setTimeout(() => {
                    window.location.href = 'login.php';
                }, 2000);
                return;
            }
            
            if (data.error) {
                document.getElementById('contenidoUbicacion').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        ${data.mensaje}
                    </div>
                `;
                return;
            }
            
            const exp = data.datos;
            
            if (!exp.tiene_ubicacion) {
                document.getElementById('contenidoUbicacion').innerHTML = `
                    <div class="text-center py-5">
                        <i class="bi bi-inbox" style="font-size: 4rem; color: #ccc;"></i>
                        <h5 class="mt-3 text-muted">Sin Ubicación Asignada</h5>
                        <p class="text-muted">Este expediente aún no tiene una ubicación física registrada.</p>
                        <p class="mb-0"><strong>Expediente:</strong> ${exp.n_expediente}</p>
                        <a href="gestionar_ubicaciones.php" class="btn btn-success mt-3">
                            <i class="bi bi-geo-alt-fill me-2"></i>Asignar Ubicacion
                        </a>
                    </div>
                `;
                return;
            }
            
            // Mostrar ubicacion con diseno de ficha profesional
            document.getElementById('contenidoUbicacion').innerHTML = `
                <!-- Encabezado del Expediente -->
                <div class="card mb-3" style="background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%); border: none;">
                    <div class="card-body">
                        <h6 class="text-uppercase text-muted mb-2" style="font-size: 0.75rem; letter-spacing: 1px;">EXPEDIENTE</h6>
                        <h4 class="mb-1"><strong>${exp.n_expediente}</strong></h4>
                        <p class="mb-0 text-muted"><small>${exp.demandante} vs ${exp.demandado}</small></p>
                    </div>
                </div>
                
                <!-- Ficha de Ubicación -->
                <div class="card card-ubicacion mb-3" style="background-color: #f8f9fa;">
                    <div class="card-body">
                        <div class="d-flex align-items-start">
                            <div class="me-3">
                                <i class="bi bi-building" style="font-size: 3rem; color: #004085;"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="text-uppercase text-muted mb-2" style="font-size: 0.75rem; letter-spacing: 1px;">
                                    <i class="bi bi-geo-alt-fill me-1"></i>SEDE
                                </h6>
                                <h5 class="mb-2 sede-nombre" style="color: #004085;">
                                    <strong>${exp.nombre_sede}</strong>
                                </h5>
                                ${exp.sede_direccion ? `<p class="mb-0 text-muted"><small><i class="bi bi-pin-map me-1"></i>${exp.sede_direccion}</small></p>` : ''}
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Area y Detalle -->
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <div class="card card-ubicacion h-100" style="background-color: #e3f2fd;">
                            <div class="card-body">
                                <div class="d-flex align-items-start">
                                    <div class="me-3">
                                        <i class="bi bi-map" style="font-size: 2rem; color: #0056b3;"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="text-uppercase text-muted mb-2" style="font-size: 0.75rem; letter-spacing: 1px;">AREA</h6>
                                        <h6 class="mb-0 sede-nombre" style="color: #0056b3;">
                                            <strong>${exp.ubicacion_area || 'No especificada'}</strong>
                                        </h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card card-ubicacion h-100" style="background-color: #e3f2fd;">
                            <div class="card-body">
                                <div class="d-flex align-items-start">
                                    <div class="me-3">
                                        <i class="bi bi-box-seam" style="font-size: 2rem; color: #004085;"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="text-uppercase text-muted mb-2" style="font-size: 0.75rem; letter-spacing: 1px;">ESTANTE</h6>
                                        <h6 class="mb-0 sede-nombre" style="color: #004085;">
                                            <strong>${exp.ubicacion_detalle || 'No especificado'}</strong>
                                        </h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Ultima Actualizacion -->
                <div class="card card-ubicacion" style="background-color: #e3f2fd;">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="bi bi-clock-history" style="font-size: 2rem; color: #1976d2;"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="text-uppercase text-muted mb-1" style="font-size: 0.75rem; letter-spacing: 1px;">ULTIMA ACTUALIZACION</h6>
                                <p class="mb-0" style="color: #1976d2;"><strong>${exp.fecha_formateada}</strong></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Boton de Accion -->
                <div class="mt-4 text-center">
                    <a href="gestionar_ubicaciones.php?modo=individual" class="btn btn-outline-success">
                        <i class="bi bi-pencil me-2"></i>Actualizar Ubicacion
                    </a>
                </div>
            `;
        })
        .catch(error => {
            console.error('=== ERROR DETALLADO ===');
            console.error('Error completo:', error);
            console.error('Tipo de error:', error.name);
            console.error('Mensaje:', error.message);
            console.error('=======================');
            
            document.getElementById('contenidoUbicacion').innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Error al cargar la ubicación.</strong>
                    <p class="mb-0 mt-2">Por favor, intenta nuevamente o contacta al administrador.</p>
                    <hr>
                    <small class="text-muted">
                        <strong>Detalles técnicos:</strong><br>
                        ${error.message || 'Error desconocido'}
                    </small>
                </div>
            `;
        });
}

// Manejar Enter en cualquier campo del formulario de búsqueda
document.addEventListener('DOMContentLoaded', function() {
    const searchForm = document.getElementById('searchForm');
    
    if (searchForm) {
        // Agregar evento keypress a todos los inputs del formulario
        const inputs = searchForm.querySelectorAll('input[type="text"], input[type="date"]');
        
        inputs.forEach(function(input) {
            input.addEventListener('keypress', function(e) {
                // Si presiona Enter (código 13)
                if (e.key === 'Enter' || e.keyCode === 13) {
                    e.preventDefault(); // Prevenir el comportamiento por defecto
                    
                    // Agregar el campo 'ejecutar' al formulario si no existe
                    let ejecutarInput = searchForm.querySelector('input[name="ejecutar"]');
                    if (!ejecutarInput) {
                        ejecutarInput = document.createElement('input');
                        ejecutarInput.type = 'hidden';
                        ejecutarInput.name = 'ejecutar';
                        ejecutarInput.value = '1';
                        searchForm.appendChild(ejecutarInput);
                    }
                    
                    // Enviar el formulario
                    searchForm.submit();
                }
            });
        });
        
        // También manejar Enter en los selects (tipo de documento)
        const selects = searchForm.querySelectorAll('select');
        selects.forEach(function(select) {
            select.addEventListener('keypress', function(e) {
                if (e.key === 'Enter' || e.keyCode === 13) {
                    e.preventDefault();
                    
                    // Agregar el campo 'ejecutar' al formulario si no existe
                    let ejecutarInput = searchForm.querySelector('input[name="ejecutar"]');
                    if (!ejecutarInput) {
                        ejecutarInput = document.createElement('input');
                        ejecutarInput.type = 'hidden';
                        ejecutarInput.name = 'ejecutar';
                        ejecutarInput.value = '1';
                        searchForm.appendChild(ejecutarInput);
                    }
                    
                    searchForm.submit();
                }
            });
        });
    }
});
</script>
</body>
</html>





