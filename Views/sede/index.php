<?php
/**
 * Views/sede/index.php
 * Vista de gestión de sedes de depósito.
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Sedes - Archivo Judicial</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/bootstrap-icons.css">
    <style>
        :root{--azul:#004085;--azul2:#0056b3;}
        body{background-image:url('/background.png');background-size:cover;background-position:top center;background-repeat:no-repeat;background-attachment:fixed;font-family:'Segoe UI',system-ui,sans-serif;min-height:100vh;padding-bottom:50px;background-color:#FFF;}
        .container{padding-top:100px;}
        .card-sedes{background:#FFF;border-radius:12px;box-shadow:0 8px 30px rgba(0,64,133,.15);border:none;}
        .card-header-custom{background:linear-gradient(135deg,#004085,#0056b3);color:#fff;border-top-left-radius:12px;border-top-right-radius:12px;padding:20px;}
        .table thead{background-color:var(--azul2);color:#fff;}
        .badge-activo{background-color:#0056b3;}
        .badge-inactivo{background-color:#757575;}
        .truncate-text{max-width:250px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;display:inline-block;vertical-align:middle;cursor:help;}
        .truncate-direccion{max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;display:block;}
    </style>
</head>
<body>
<div class="container">
    <div class="mb-4">
        <a href="/ubicaciones" class="btn btn-secondary"><i class="bi bi-arrow-left me-2"></i>Volver a Ubicaciones</a>
        <a href="/" class="btn btn-outline-secondary"><i class="bi bi-house me-2"></i>Menú Principal</a>
    </div>

    <?php if(!empty($mensaje)): ?>
    <div class="alert alert-<?= $tipoAlerta ?> alert-dismissible fade show" role="alert">
        <?= $mensaje ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <?php if($accion==='crear' || $accion==='editar'): ?>
    <div class="card card-sedes mb-4">
        <div class="card-header-custom">
            <h4 class="mb-0"><i class="bi bi-<?= $accion==='crear'?'plus-circle':'pencil-square' ?> me-2"></i>
                <?= $accion==='crear'?'Crear Nueva Sede':'Editar Sede' ?>
            </h4>
        </div>
        <div class="card-body p-4">
            <form method="POST" action="/sedes">
                <?php if($accion==='editar' && $sedeEditar): ?>
                <input type="hidden" name="id_sede" value="<?= $sedeEditar['id_sede'] ?>">
                <?php endif; ?>
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label fw-bold">Nombre de la Sede *</label>
                        <input type="text" class="form-control" name="nombre_sede" required maxlength="255"
                               placeholder="Ej: Galpón Palo Negro - Depósito Central"
                               value="<?= $accion==='editar' && $sedeEditar ? htmlspecialchars($sedeEditar['nombre_sede']) : '' ?>">
                        <small class="text-muted">Máximo 255 caracteres</small>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label fw-bold">Dirección Completa</label>
                        <textarea class="form-control" name="direccion" rows="2" placeholder="Ej: Zona Industrial Palo Negro..."><?= $accion==='editar' && $sedeEditar ? htmlspecialchars($sedeEditar['direccion']) : '' ?></textarea>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label fw-bold">Descripción</label>
                        <textarea class="form-control" name="descripcion" rows="3" placeholder="Descripción detallada de la sede..."><?= $accion==='editar' && $sedeEditar ? htmlspecialchars($sedeEditar['descripcion']) : '' ?></textarea>
                    </div>
                </div>
                <div class="mt-4 d-flex gap-2">
                    <button type="submit" name="<?= $accion==='crear'?'crear_sede':'editar_sede' ?>" class="btn btn-primary">
                        <i class="bi bi-save me-2"></i><?= $accion==='crear'?'Crear Sede':'Guardar Cambios' ?>
                    </button>
                    <a href="/sedes" class="btn btn-secondary"><i class="bi bi-x-circle me-2"></i>Cancelar</a>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <div class="card card-sedes">
        <div class="card-header-custom d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><i class="bi bi-building me-2"></i>Sedes de Depósito</h4>
            <a href="/sedes?accion=crear" class="btn btn-light btn-sm"><i class="bi bi-plus-circle me-1"></i>Nueva Sede</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr>
                        <th class="ps-4">Nombre de la Sede</th><th>Dirección</th><th>Expedientes</th><th>Estado</th><th class="pe-4 text-center">Acciones</th>
                    </tr></thead>
                    <tbody>
                    <?php if(count($sedes)>0): ?>
                        <?php foreach($sedes as $s): ?>
                        <tr>
                            <td class="ps-4" title="<?= htmlspecialchars($s['nombre_sede']) ?>">
                                <strong class="truncate-text"><?= htmlspecialchars($s['nombre_sede']) ?></strong>
                                <?php if(!empty($s['descripcion'])): ?>
                                <br><small class="text-muted truncate-text" title="<?= htmlspecialchars($s['descripcion']) ?>"><?= htmlspecialchars(substr($s['descripcion'],0,80)) ?>...</small>
                                <?php endif; ?>
                            </td>
                            <td title="<?= htmlspecialchars($s['direccion']??'Sin dirección') ?>">
                                <?php if(!empty($s['direccion'])): ?>
                                <small class="truncate-direccion"><?= htmlspecialchars($s['direccion']) ?></small>
                                <?php else: ?><span class="text-muted">Sin dirección</span><?php endif; ?>
                            </td>
                            <td><span class="badge bg-info"><?= $expedientesPorSede[$s['id_sede']] ?? 0 ?> expedientes</span></td>
                            <td><span class="badge badge-<?= $s['activo']==1?'activo':'inactivo' ?>"><?= $s['activo']==1?'Activa':'Inactiva' ?></span></td>
                            <td class="pe-4 text-center">
                                <div class="btn-group" role="group">
                                    <a href="/sedes?accion=editar&id=<?= $s['id_sede'] ?>" class="btn btn-sm btn-warning" title="Editar"><i class="bi bi-pencil"></i></a>
                                    <a href="/sedes?toggle=<?= $s['id_sede'] ?>"
                                       class="btn btn-sm btn-<?= $s['activo']==1?'secondary':'success' ?>"
                                       data-confirm-message="¿Estás seguro de <?= $s['activo']==1?'desactivar':'activar' ?> esta sede?"
                                       data-confirm-title="Confirmar cambio de estado"
                                       data-confirm-ok="Sí, continuar"
                                       data-confirm-cancel="No"
                                       title="<?= $s['activo']==1?'Desactivar':'Activar' ?>">
                                        <i class="bi bi-<?= $s['activo']==1?'toggle-off':'toggle-on' ?>"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                    <tr><td colspan="5" class="text-center py-5 text-muted">
                        <i class="bi bi-inbox fs-1"></i>
                        <p class="mt-3">No hay sedes registradas</p>
                        <a href="/sedes?accion=crear" class="btn btn-primary"><i class="bi bi-plus-circle me-2"></i>Crear Primera Sede</a>
                    </td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="/assets/js/app-alerts.js"></script>
</body></html>
