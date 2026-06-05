<?php
/**
 * Views/expediente/editar.php
 * Vista del formulario de edicion de un expediente existente.
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Expediente - Archivo Judicial</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
    <style>
        :root{--azul:#1a237e;}
        body{background-image:url('/background.png');background-size:cover;background-position:center top;background-repeat:no-repeat;background-attachment:fixed;font-family:'Segoe UI',system-ui,sans-serif;min-height:100vh;}
        .header-bg{background-color:var(--azul);color:#fff;border-top-left-radius:12px;border-top-right-radius:12px;position:relative;}
        .btn-volver-menu{position:absolute;top:50%;right:20px;transform:translateY(-50%);z-index:10;}
        .card-custom{border-radius:12px;box-shadow:0 8px 30px rgba(0,0,0,.12);border:none;background-color:#fff;max-width:95%;margin:130px auto 0;width:100%;}
        .card-body{padding:1.2rem 5rem!important;}
        .btn-success-custom{font-weight:700;font-size:1.25rem;padding:15px;letter-spacing:1px;}
        .form-label{font-weight:600;color:#4a4a4a;}
        .alert-warning-edit{background-color:#fff3cd;border-left:4px solid #ffc107;color:#856404;}
        /* Forzar mayúsculas en inputs de texto */
        input[type="text"], textarea { text-transform: uppercase; }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-19 col-xxl-11">
            <?php if(!empty($mensaje)): ?>
            <div class="alert alert-<?= $tipoAlerta ?> alert-dismissible fade show shadow-sm fw-bold border-0 border-start border-<?= $tipoAlerta ?> border-4" role="alert">
                <?php if($tipoAlerta=='success'): ?><i class="bi bi-check-circle-fill me-2 fs-5 text-success"></i>
                <?php elseif($tipoAlerta=='warning'): ?><i class="bi bi-exclamation-triangle-fill me-2 fs-5 text-warning"></i>
                <?php elseif($tipoAlerta=='info'): ?><i class="bi bi-info-circle-fill me-2 fs-5 text-info"></i>
                <?php else: ?><i class="bi bi-x-circle-fill me-2 fs-5 text-danger"></i><?php endif; ?>
                <?= htmlspecialchars($mensaje) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <div class="alert alert-warning-edit shadow-sm mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <strong>Modo de Edición:</strong> Estás modificando un registro existente. Todos los cambios quedarán registrados en el log de auditoría.
            </div>

            <div class="card card-custom">
                <div class="card-header header-bg text-center py-4">
                    <h3 class="mb-0 fw-bold"><i class="bi bi-pencil-square me-2"></i>EDITAR EXPEDIENTE</h3>
                    <a href="<?= BASE_URL ?>/consulta" class="btn btn-secondary btn-sm btn-volver-menu"><i class="bi bi-x-lg me-2"></i>Cancelar</a>
                </div>
                <div class="card-body p-4 p-md-5">
                    <form action="/editar/<?= htmlspecialchars($id) ?>" method="POST" class="needs-validation" novalidate>
                        <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">
                        <!-- Expediente y Fecha -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="n_expediente" class="form-label">Nro Expediente <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="n_expediente" name="n_expediente" value="<?= htmlspecialchars($registro['n_expediente']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="fecha_entrada" class="form-label">Fecha de Ingreso <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="fecha_entrada" name="fecha_entrada" value="<?= htmlspecialchars($registro['fecha_entrada']) ?>" required>
                            </div>
                        </div>
                        <!-- Tribunal -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-12">
                                <label for="busqueda-tribunales-editar" class="form-label">Tribunal <span class="text-danger">*</span></label>
                                <select class="form-select" id="busqueda-tribunales-editar" name="id_tribunal" required>
                                    <option value="" disabled>Escribe el nombre del tribunal...</option>
                                    <?php foreach($tribunales as $t):
                                        $sel = ($registro['id_tribunal']==$t['id_tribunal'] && $tribActualNombre==$t['tribunal']);
                                    ?>
                                    <option value="<?= htmlspecialchars($t['id_tribunal']) ?>" <?= $sel?'selected':'' ?>>
                                        Trib. <?= htmlspecialchars($t['id_tribunal']) ?> - <?= htmlspecialchars($t['tribunal']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if($tribActualNombre): ?>
                                <small class="text-muted d-block mt-1"><i class="bi bi-info-circle me-1"></i>Tribunal actual: <strong><?= htmlspecialchars($tribActualNombre) ?></strong></small>
                                <?php endif; ?>
                            </div>
                        </div>
                        <!-- Demandante -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-8">
                                <label for="demandante" class="form-label">Nombre Demandante <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="demandante" name="demandante" value="<?= htmlspecialchars($registro['demandante']) ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">C.I. / RIF Demandante <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <select class="form-select" style="max-width:80px;" id="tipo_doc_demandante" name="tipo_doc_demandante">
                                        <option value="V" <?= $tipoDante=='V'?'selected':'' ?>>V</option>
                                        <option value="E" <?= $tipoDante=='E'?'selected':'' ?>>E</option>
                                        <option value="J" <?= $tipoDante=='J'?'selected':'' ?>>J</option>
                                    </select>
                                    <input type="text" class="form-control" id="cedula_rif_demandante" name="cedula_rif_demandante" value="<?= htmlspecialchars($cedulaDante) ?>" placeholder="Ej: 12345678" required>
                                    <div class="invalid-feedback">Ingresa la Cédula/RIF.</div>
                                </div>
                            </div>
                        </div>
                        <!-- Apellido Demandante -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-12">
                                <label for="apellidos_demandante" class="form-label">Apellido(s) Demandante <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="apellidos_demandante" name="apellidos_demandante" value="<?= htmlspecialchars($registro['apellidos_demandante'] ?? '') ?>" placeholder="Apellido(s) del demandante" required>
                                <div class="invalid-feedback">Ingresa los apellidos del demandante.</div>
                            </div>
                        </div>
                        <!-- Demandado -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-8">
                                <label for="demandado" class="form-label">Nombre Demandado <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="demandado" name="demandado" value="<?= htmlspecialchars($registro['demandado']) ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">C.I. / RIF Demandado <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <select class="form-select" style="max-width:80px;" id="tipo_doc_demandado" name="tipo_doc_demandado">
                                        <option value="V" <?= $tipoDado=='V'?'selected':'' ?>>V</option>
                                        <option value="E" <?= $tipoDado=='E'?'selected':'' ?>>E</option>
                                        <option value="J" <?= $tipoDado=='J'?'selected':'' ?>>J</option>
                                    </select>
                                    <input type="text" class="form-control" id="cedula_rif_demandado" name="cedula_rif_demandado" value="<?= htmlspecialchars($cedulaDado) ?>" placeholder="Ej: 12345678" required>
                                    <div class="invalid-feedback">Ingresa la Cédula/RIF.</div>
                                </div>
                            </div>
                        </div>
                        <!-- Apellido Demandado -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-12">
                                <label for="apellidos_demandado" class="form-label">Apellido(s) Demandado <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="apellidos_demandado" name="apellidos_demandado" value="<?= htmlspecialchars($registro['apellidos_demandado'] ?? '') ?>" placeholder="Apellido(s) del demandado" required>
                                <div class="invalid-feedback">Ingresa los apellidos del demandado.</div>
                            </div>
                        </div>
                        <!-- Motivo y Legajo -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-8">
                                <label for="motivo_delito" class="form-label">Motivo / Delito <span class="text-danger">*</span></label>
                                <select class="form-select" id="motivo_delito" name="motivo_delito" required>
                                    <option value="" disabled>Escribe o selecciona el motivo o delito...</option>
                                    <?php foreach($delitos as $d):
                                        $selDelito = (trim(strtoupper($registro['motivo_delito'] ?? '')) === trim(strtoupper($d['nombre_delito'] ?? '')));
                                    ?>
                                    <option value="<?= htmlspecialchars($d['nombre_delito']) ?>" <?= $selDelito?'selected':'' ?>><?= htmlspecialchars($d['nombre_delito']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">Selecciona o ingresa la descripción del motivo o delito.</div>
                            </div>
                            <div class="col-md-4">
                                <label for="n_legajo" class="form-label">Nro Legajo <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="n_legajo" name="n_legajo" value="<?= htmlspecialchars($registro['n_legajo']) ?>" required>
                            </div>
                        </div>
                        <!-- Observaciones -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-12">
                                <label for="observaciones" class="form-label">Observaciones</label>
                                <textarea class="form-control" id="observaciones" name="observaciones" rows="4"><?= htmlspecialchars($registro['observaciones']) ?></textarea>
                            </div>
                        </div>
                        <!-- Botones -->
                        <div class="row mt-5">
                            <div class="col-12 d-grid gap-3">
                                <button type="submit" name="guardar_cambios" class="btn btn-success btn-success-custom"><i class="bi bi-save-fill me-2"></i>GUARDAR CAMBIOS</button>
                                <a href="<?= BASE_URL ?>/consulta" class="btn btn-secondary btn-success-custom"><i class="bi bi-arrow-left-circle me-2"></i>VOLVER</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function(){
    $('#busqueda-tribunales-editar').select2({theme:'bootstrap-5',width:'100%',placeholder:'Escribe el nombre del tribunal...',allowClear:true,language:{noResults:()=>'No se encontraron resultados',searching:()=>'Buscando...'}});

    const esAdmin = <?= json_encode($_SESSION['usuario_rol'] === 'admin') ?>;
    $('#motivo_delito').select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: 'Escribe o selecciona el motivo o delito...',
        allowClear: true,
        tags: esAdmin,
        language: {
            noResults: () => esAdmin ? 'Escribe para agregar un nuevo delito...' : 'No se encontraron resultados',
            searching: () => 'Buscando...'
        },
        createTag: function (params) {
            var term = $.trim(params.term);
            if (term === '') {
                return null;
            }
            return {
                id: term.toUpperCase(),
                text: term.toUpperCase() + ' (NUEVO MOTIVO/DELITO)',
                newTag: true
            }
        }
    });

    // Forzar mayúsculas en el valor real de los inputs
    $('input[type="text"], textarea').on('input', function() {
        this.value = this.value.toUpperCase();
    });

    // Validación dinámica de longitud de cédula/RIF
    function actualizarValidacionId(selectId, inputId) {
        const sel = $('#' + selectId);
        const inp = $('#' + inputId);
        const val = sel.val();
        if (val === 'J') {
            inp.attr('minlength', '10').attr('maxlength', '10').attr('pattern', '\\d{10}');
        } else {
            inp.attr('minlength', '5').attr('maxlength', '9').attr('pattern', '\\d{5,9}');
        }
    }

    $('#tipo_doc_demandante').on('change', () => actualizarValidacionId('tipo_doc_demandante', 'cedula_rif_demandante'));
    $('#tipo_doc_demandado').on('change', () => actualizarValidacionId('tipo_doc_demandado', 'cedula_rif_demandado'));
    
    // Ejecutar al cargar
    actualizarValidacionId('tipo_doc_demandante', 'cedula_rif_demandante');
    actualizarValidacionId('tipo_doc_demandado', 'cedula_rif_demandado');
});
(function(){'use strict';var forms=document.querySelectorAll('.needs-validation');Array.prototype.slice.call(forms).forEach(function(form){form.addEventListener('submit',function(e){if(!form.checkValidity()){e.preventDefault();e.stopPropagation();}form.classList.add('was-validated');},false);});})();
</script>
</body></html>
