<?php
/**
 * Views/expediente/historial.php
 * Vista del historial de movimientos de un expediente.
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial del Expediente <?= htmlspecialchars($expediente['n_expediente']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/bootstrap-icons.css">
    <style>
        :root{--azul:#1a237e;--azul-d:#121858;}
        body{background-image:url('/background.png');background-size:cover;background-position:top center;background-repeat:no-repeat;background-attachment:fixed;font-family:'Segoe UI',system-ui,sans-serif;min-height:100vh;padding-bottom:50px;}
        .container.py-5{padding-top:120px!important;}
        .card-info{background:#fff;border-radius:12px;box-shadow:0 8px 30px rgba(0,0,0,.15);border:none;margin-bottom:30px;}
        .card-header-custom{background-color:var(--azul);color:#fff;border-top-left-radius:12px;border-top-right-radius:12px;padding:20px;position:relative;}
        .info-label{font-weight:700;color:#555;font-size:.85rem;text-transform:uppercase;margin-bottom:5px;}
        .info-value{font-size:1rem;color:#333;margin-bottom:15px;}
        .table-historial{background:#fff;border-radius:12px;box-shadow:0 8px 30px rgba(0,0,0,.15);overflow:hidden;}
        .table thead{background-color:var(--azul);color:#fff;}
        .table th{font-weight:600;font-size:.9rem;text-transform:uppercase;vertical-align:middle;border-bottom:none;}
        .table td{vertical-align:middle;}
        .badge-fecha{background-color:#e3f2fd;color:#1976d2;padding:8px 12px;border-radius:6px;font-weight:600;}
    </style>
</head>
<body>
<div class="container py-5">
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <div>
            <?php if(!empty($searchOriginal)): ?>
            <a href="/consulta?search=<?= urlencode($searchOriginal) ?>" class="btn btn-secondary"><i class="bi bi-arrow-left me-2"></i>Volver al Buscador</a>
            <?php else: ?>
            <a href="/consulta" class="btn btn-secondary"><i class="bi bi-arrow-left me-2"></i>Volver al Buscador</a>
            <?php endif; ?>
        </div>
        <div>
            <a href="/imprimir/<?= urlencode($id) ?>" class="btn btn-success" target="_blank"><i class="bi bi-printer-fill me-2"></i>Imprimir Expediente</a>
        </div>
    </div>

    <!-- Datos principales del expediente -->
    <div class="card card-info">
        <div class="card-header-custom">
            <h4 class="mb-0"><i class="bi bi-folder2-open me-2"></i>Expediente Nro <?= htmlspecialchars($expediente['n_expediente']) ?></h4>
        </div>
        <div class="card-body p-4">
            <div class="row">
                <div class="col-md-4"><div class="info-label">Fecha de Entrada</div><div class="info-value"><?= date('d/m/Y', strtotime($expediente['fecha_entrada'])) ?></div></div>
                <div class="col-md-4"><div class="info-label">Nro Legajo</div><div class="info-value"><?= htmlspecialchars($expediente['n_legajo']) ?></div></div>
                <div class="col-md-4">
                    <div class="info-label">Tribunal Actual</div>
                    <div class="info-value">
                        Trib. <?= htmlspecialchars($expediente['id_tribunal']) ?>
                        <?php if(!empty($expediente['tribunal'])): ?><br><small class="text-muted"><?= htmlspecialchars($expediente['tribunal']) ?></small><?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-6">
                    <div class="info-label">Demandante</div>
                    <div class="info-value"><?= htmlspecialchars(mb_strtoupper($expediente['demandante'],'UTF-8')) ?></div>
                    <?php if(!empty($expediente['cedula_rif_demandante'])): ?><small class="text-muted"><i class="bi bi-person-vcard me-1"></i><?= htmlspecialchars($expediente['cedula_rif_demandante']) ?></small><?php endif; ?>
                </div>
                <div class="col-md-6">
                    <div class="info-label">Demandado</div>
                    <div class="info-value"><?= htmlspecialchars(mb_strtoupper($expediente['demandado'],'UTF-8')) ?></div>
                    <?php if(!empty($expediente['cedula_rif_demandado'])): ?><small class="text-muted"><i class="bi bi-person-vcard me-1"></i><?= htmlspecialchars($expediente['cedula_rif_demandado']) ?></small><?php endif; ?>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-12"><div class="info-label">Motivo / Delito</div><div class="info-value"><?= htmlspecialchars(mb_strtoupper($expediente['motivo_delito'],'UTF-8')) ?></div></div>
            </div>
            <?php if(!empty($expediente['observaciones'])): ?>
            <div class="row mt-3">
                <div class="col-md-12"><div class="info-label">Observaciones</div><div class="info-value"><?= nl2br(htmlspecialchars($expediente['observaciones'])) ?></div></div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Historial de movimientos -->
    <div class="table-historial">
        <div class="card-header-custom">
            <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Historial de Movimientos</h5>
        </div>
        <?php if(count($historial)>0): ?>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr>
                    <th class="ps-4">Fecha de Movimiento</th>
                    <th>Tribunal que Recibió</th>
                    <th>Registrado por</th>
                    <th class="pe-4">Observaciones</th>
                </tr></thead>
                <tbody>
                <?php foreach($historial as $m): ?>
                <tr>
                    <td class="ps-4"><span class="badge-fecha"><i class="bi bi-calendar-event me-1"></i><?= date('d/m/Y H:i', strtotime($m['fecha_movimiento'])) ?></span></td>
                    <td>
                        <strong>Tribunal <?= htmlspecialchars($m['id_tribunal']) ?></strong>
                        <?php if(!empty($m['tribunal'])): ?><br><small class="text-muted"><?= htmlspecialchars($m['tribunal']) ?></small><?php endif; ?>
                    </td>
                    <td>
                        <?php if(!empty($m['usuario_nombre'])): ?><i class="bi bi-person-badge me-1"></i><?= htmlspecialchars($m['usuario_nombre']) ?>
                        <?php else: ?><span class="text-muted">No registrado</span><?php endif; ?>
                    </td>
                    <td class="pe-4"><?= !empty($m['observaciones']) ? nl2br(htmlspecialchars($m['observaciones'])) : '<span class="text-muted">Sin observaciones</span>' ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="card-body text-center py-5">
            <i class="bi bi-inbox fs-1 text-muted"></i>
            <p class="text-muted mt-3">No hay movimientos registrados para este expediente.</p>
        </div>
        <?php endif; ?>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body></html>
