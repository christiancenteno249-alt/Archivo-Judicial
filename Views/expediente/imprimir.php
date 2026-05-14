<?php
/**
 * Views/expediente/imprimir.php
 * Vista de impresion del expediente. Replica imprimir_expediente.php
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Imprimir Expediente - <?= htmlspecialchars($expediente['n_expediente']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            @page{size:letter;margin:0;}
            body{background-color:#fff!important;background-image:none!important;font-size:14px;margin:0;padding:0;}
            .no-print, .d-print-none{display:none!important;}
            .print-container{max-width:100%!important;width:100%!important;margin:0!important;padding:1.5cm!important;box-shadow:none!important;border:none!important;box-sizing:border-box;}
            .section-title{background-color:#1a237e!important;color:#fff!important;margin-top:15px;margin-bottom:10px;padding:8px 15px;}
            .print-label{color:#1a237e!important;margin-bottom:2px;}
            .print-value{background-color:#f8f9fa!important;border-left:3px solid #1a237e!important;margin-bottom:10px;padding:6px 10px;min-height:30px;}
            *{-webkit-print-color-adjust:exact!important;print-color-adjust:exact!important;}
        }
        body{background-color:#f8f9fa;padding:20px;}
        .print-container{background:#fff;max-width:900px;margin:0 auto;padding:40px;box-shadow:0 0 20px rgba(0,0,0,.1);border-radius:8px;}
        .print-label{font-weight:700;color:#1a237e;font-size:.85rem;text-transform:uppercase;margin-bottom:5px;display:block;}
        .print-value{font-size:1rem;color:#333;padding:8px 12px;background-color:#f8f9fa;border-left:3px solid #1a237e;margin-bottom:15px;min-height:38px;}
        .section-title{background-color:#1a237e;color:#fff;padding:10px 15px;font-weight:600;font-size:1.1rem;margin-top:25px;margin-bottom:15px;border-radius:4px;}
        .btn-print{position:fixed;bottom:30px;right:30px;z-index:1000;box-shadow:0 4px 12px rgba(0,0,0,.3);}
        .btn-back{position:fixed;bottom:30px;left:30px;z-index:1000;box-shadow:0 4px 12px rgba(0,0,0,.3);}
    </style>
</head>
<body>
<div class="print-container">
    <!-- Logos -->
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:15px;">
        <img src="/assets/img/dar_logo.jpeg" alt="DAR" style="height:80px;">
        <img src="/assets/img/tsj_logo.jpeg" alt="TSJ" style="height:80px;">
        <img src="/assets/img/dem_logo.jpeg" alt="DEM" style="height:80px;">
    </div>
    <div class="text-center mb-4"><h4 class="fw-bold text-uppercase">Comprobante de Expediente</h4></div>

    <div class="section-title">Datos del Expediente</div>
    <div class="row">
        <div class="col-6"><span class="print-label">Nro Expediente</span><div class="print-value"><?= htmlspecialchars($expediente['n_expediente']) ?></div></div>
        <div class="col-6"><span class="print-label">Fecha de Ingreso</span><div class="print-value"><?= date('d/m/Y', strtotime($expediente['fecha_entrada'])) ?></div></div>
    </div>
    <div class="row">
        <div class="col-12"><span class="print-label">Tribunal Asignado</span>
            <div class="print-value">Trib. <?= htmlspecialchars($expediente['id_tribunal']) ?> - <?= htmlspecialchars($expediente['tribunal'] ?? 'No especificado') ?></div></div>
    </div>
    <div class="row">
        <div class="col-12"><span class="print-label">Nro Legajo</span><div class="print-value"><?= htmlspecialchars($expediente['n_legajo']) ?></div></div>
    </div>

    <div class="section-title">Partes Involucradas</div>
    <div class="row">
        <div class="col-8"><span class="print-label">Demandante</span><div class="print-value"><?= htmlspecialchars(mb_strtoupper($expediente['demandante'],'UTF-8')) ?></div></div>
        <div class="col-4"><span class="print-label">C.I. / RIF</span><div class="print-value"><?= htmlspecialchars($expediente['cedula_rif_demandante']) ?></div></div>
    </div>
    <div class="row">
        <div class="col-8"><span class="print-label">Demandado</span><div class="print-value"><?= htmlspecialchars(mb_strtoupper($expediente['demandado'],'UTF-8')) ?></div></div>
        <div class="col-4"><span class="print-label">C.I. / RIF</span><div class="print-value"><?= htmlspecialchars($expediente['cedula_rif_demandado']) ?></div></div>
    </div>

    <div class="section-title">Detalles del Caso</div>
    <div class="row">
        <div class="col-12"><span class="print-label">Motivo / Delito</span><div class="print-value"><?= htmlspecialchars(mb_strtoupper($expediente['motivo_delito'],'UTF-8')) ?></div></div>
    </div>
    <?php if(!empty($expediente['observaciones'])): ?>
    <div class="row">
        <div class="col-12"><span class="print-label">Observaciones</span><div class="print-value"><?= nl2br(htmlspecialchars($expediente['observaciones'])) ?></div></div>
    </div>
    <?php endif; ?>
</div>

<button onclick="window.print()" class="btn btn-primary btn-lg no-print btn-print"><i class="bi bi-printer-fill me-2"></i>Imprimir</button>
<a href="/consulta" class="btn btn-secondary btn-lg no-print btn-back"><i class="bi bi-arrow-left me-2"></i>Volver</a>
<link rel="stylesheet" href="/assets/css/bootstrap-icons.css">
</body></html>
