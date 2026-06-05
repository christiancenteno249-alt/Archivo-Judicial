<?php
/**
 * Views/expediente/registrar.php
 * Vista del formulario de registro de nuevo expediente.
 * Replica registrar.php con compatibilidad MVC y AJAX.
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Expediente - Archivo Judicial</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.5/dist/sweetalert2.min.css" rel="stylesheet">
    <style>
        :root{--azul:#1a237e;}
        body{background-image:url('/background.png');background-size:cover;background-position:center top;background-repeat:no-repeat;background-attachment:fixed;font-family:'Segoe UI',system-ui,sans-serif;min-height:100vh;}
        .header-bg{background-color:var(--azul);color:#fff;border-top-left-radius:12px;border-top-right-radius:12px;position:relative;}
        .btn-volver-menu{position:absolute;top:50%;right:20px;transform:translateY(-50%);z-index:10;}
        .card-custom{border-radius:12px;box-shadow:0 8px 30px rgba(0,0,0,.12);border:none;background-color:#fff;max-width:95%;margin:130px auto 0;width:100%;}
        .card-body{padding:1.2rem 5rem!important;}
        .btn-success-custom{font-weight:700;font-size:1.25rem;padding:15px;letter-spacing:1px;}
        .form-label{font-weight:600;color:#4a4a4a;}
        @media print{
            @page{size:letter;margin:0;}
            body{background-color:#fff!important;background-image:none!important;font-size:12px;margin:0;padding:0;}
            .d-print-none{display:none!important;}
            .d-print-block{display:block!important;}
            .print-container{max-width:100%!important;width:100%!important;margin:0!important;padding:1cm!important;box-shadow:none!important;border:none!important;box-sizing:border-box;}
            .section-title{background-color:#1a237e!important;color:#fff!important;margin-top:10px;margin-bottom:5px;padding:4px 10px;font-size:1rem;}
            .print-label{color:#1a237e!important;margin-bottom:0px;font-size:0.75rem;}
            .print-value{background-color:#f8f9fa!important;border-left:3px solid #1a237e!important;margin-bottom:6px;padding:4px 8px;min-height:22px;font-size:0.9rem;}
            *{-webkit-print-color-adjust:exact!important;print-color-adjust:exact!important;}
        }
        .print-label{font-weight:700;color:#1a237e;font-size:.85rem;text-transform:uppercase;margin-bottom:5px;display:block;}
        .print-value{font-size:1rem;color:#333;padding:8px 12px;background-color:#f8f9fa;border-left:3px solid #1a237e;margin-bottom:15px;min-height:38px;}
        .section-title{background-color:#1a237e;color:#fff;padding:10px 15px;font-weight:600;font-size:1.1rem;margin-top:25px;margin-bottom:15px;border-radius:4px;}
        /* Forzar mayúsculas en inputs de texto */
        input[type="text"], textarea { text-transform: uppercase; }
    </style>
</head>
<body>
<div class="container py-5 d-print-none">
    <div class="row justify-content-center">
        <div class="col-19 col-xxl-11">
            <?php if(!empty($mensaje)): ?>
            <div class="alert alert-<?= $tipoAlerta ?> alert-dismissible fade show shadow-sm fw-bold border-0 border-start border-<?= $tipoAlerta ?> border-4" role="alert">
                <?php if($tipoAlerta=='success'): ?><i class="bi bi-check-circle-fill me-2 fs-5 text-success"></i>
                <?php elseif($tipoAlerta=='warning'): ?><i class="bi bi-exclamation-triangle-fill me-2 fs-5 text-warning"></i>
                <?php else: ?><i class="bi bi-x-circle-fill me-2 fs-5 text-danger"></i><?php endif; ?>
                <?= htmlspecialchars($mensaje) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <div class="card card-custom">
                <div class="card-header header-bg text-center py-4">
                    <h3 class="mb-0 fw-bold"><i class="bi bi-folder-plus me-2"></i>REGISTRAR EXPEDIENTE</h3>
                    <a href="/" class="btn btn-secondary btn-sm btn-volver-menu"><i class="bi bi-arrow-left me-2"></i>Volver al Menú</a>
                </div>
                <div class="card-body p-4 p-md-5">
                    <form action="/registro" method="POST" class="needs-validation" novalidate id="formRegistrar">
                        <!-- Expediente y Fecha -->
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
                        <!-- Tribunal -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-12">
                                <label for="busqueda-tribunales" class="form-label">Tribunal <span class="text-danger">*</span></label>
                                <select class="form-select" id="busqueda-tribunales" name="id_tribunal" required>
                                    <option value="" disabled selected>Escribe el nombre del tribunal...</option>
                                    <?php foreach($tribunales as $t): ?>
                                    <option value="<?= htmlspecialchars($t['id_tribunal']) ?>">Trib. <?= htmlspecialchars($t['id_tribunal']) ?> - <?= htmlspecialchars($t['tribunal']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">Selecciona el tribunal correspondiente.</div>
                            </div>
                        </div>
                        <!-- Demandante -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-8">
                                <label for="demandante" class="form-label">Nombre Demandante <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="demandante" name="demandante" placeholder="Nombre(s) del demandante" required>
                                <div class="invalid-feedback">Ingresa el nombre del demandante.</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">C.I. / RIF Demandante <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <select class="form-select" style="max-width:80px;" id="tipo_doc_demandante" name="tipo_doc_demandante">
                                        <option value="V" selected>V</option><option value="E">E</option><option value="J">J</option>
                                    </select>
                                    <input type="text" class="form-control" id="cedula_rif_demandante" name="cedula_rif_demandante" placeholder="Ej: 12345678" required>
                                    <div class="invalid-feedback">Ingresa la Cédula/RIF.</div>
                                </div>
                            </div>
                        </div>
                        <!-- Apellido Demandante -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-12">
                                <label for="apellidos_demandante" class="form-label">Apellido(s) Demandante <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="apellidos_demandante" name="apellidos_demandante" placeholder="Apellido(s) del demandante" required>
                                <div class="invalid-feedback">Ingresa los apellidos del demandante.</div>
                            </div>
                        </div>
                        <!-- Demandado -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-8">
                                <label for="demandado" class="form-label">Nombre Demandado <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="demandado" name="demandado" placeholder="Nombre(s) del demandado" required>
                                <div class="invalid-feedback">Ingresa el nombre del demandado.</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">C.I. / RIF Demandado <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <select class="form-select" style="max-width:80px;" id="tipo_doc_demandado" name="tipo_doc_demandado">
                                        <option value="V" selected>V</option><option value="E">E</option><option value="J">J</option>
                                    </select>
                                    <input type="text" class="form-control" id="cedula_rif_demandado" name="cedula_rif_demandado" placeholder="Ej: 12345678" required>
                                    <div class="invalid-feedback">Ingresa la Cédula/RIF.</div>
                                </div>
                            </div>
                        </div>
                        <!-- Apellido Demandado -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-12">
                                <label for="apellidos_demandado" class="form-label">Apellido(s) Demandado <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="apellidos_demandado" name="apellidos_demandado" placeholder="Apellido(s) del demandado" required>
                                <div class="invalid-feedback">Ingresa los apellidos del demandado.</div>
                            </div>
                        </div>
                        <!-- Motivo y Legajo -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-8">
                                <label for="motivo_delito" class="form-label">Motivo / Delito <span class="text-danger">*</span></label>
                                <select class="form-select" id="motivo_delito" name="motivo_delito" required>
                                    <option value="" disabled selected>Escribe o selecciona el motivo o delito...</option>
                                    <?php foreach($delitos as $d): ?>
                                    <option value="<?= htmlspecialchars($d['nombre_delito']) ?>"><?= htmlspecialchars($d['nombre_delito']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">Selecciona o ingresa la descripción del motivo o delito.</div>
                            </div>
                            <div class="col-md-4">
                                <label for="n_legajo" class="form-label">Nro Legajo <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="n_legajo" name="n_legajo" placeholder="Ej: L-001" required>
                                <div class="invalid-feedback">Ingresa el número de legajo.</div>
                            </div>
                        </div>
                        <!-- Observaciones -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-12">
                                <label for="observaciones" class="form-label">Observaciones</label>
                                <textarea class="form-control" id="observaciones" name="observaciones" rows="4" placeholder="Detalles adicionales..."></textarea>
                            </div>
                        </div>
                        <!-- Botones -->
                        <div class="row mt-5">
                            <div class="col-12 d-grid gap-3">
                                <button type="submit" class="btn btn-success btn-success-custom" id="btnRegistrar"
                                    data-default-html="<i class='bi bi-save-fill me-2'></i>GUARDAR EXPEDIENTE"
                                    data-loading-html="<span class='spinner-border spinner-border-sm me-2'></span>Procesando...">
                                    <i class="bi bi-save-fill me-2"></i>GUARDAR EXPEDIENTE
                                </button>
                                <?php if(!empty($datosImpresion) && $tipoAlerta=='success'): ?>
                                <button type="button" class="btn btn-info btn-success-custom text-white fw-bold" onclick="window.print()">
                                    <i class="bi bi-printer-fill me-2"></i>IMPRIMIR FICHA DE REGISTRO
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="mt-4 text-center">
                            <a href="/consulta" class="text-decoration-none text-secondary fw-bold"><i class="bi bi-search me-1"></i>Ir al Buscador de Expedientes</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if(!empty($datosImpresion) && $tipoAlerta=='success'): ?>
<div class="d-none d-print-block">
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
            <div class="col-6"><span class="print-label">Nro Expediente</span><div class="print-value"><?= htmlspecialchars($datosImpresion['n_expediente']) ?></div></div>
            <div class="col-6"><span class="print-label">Fecha de Ingreso</span><div class="print-value"><?= htmlspecialchars($datosImpresion['fecha_entrada']) ?></div></div>
        </div>
        <div class="row">
            <div class="col-12"><span class="print-label">Tribunal Asignado</span>
                <div class="print-value"><?= htmlspecialchars($datosImpresion['tribunal']) ?></div></div>
        </div>
        <div class="row">
            <div class="col-12"><span class="print-label">Nro Legajo</span><div class="print-value"><?= htmlspecialchars($datosImpresion['n_legajo']) ?></div></div>
        </div>

        <div class="section-title">Partes Involucradas</div>
        <div class="row">
            <div class="<?= !empty($datosImpresion['apellidos_demandante']) ? 'col-5' : 'col-8' ?>"><span class="print-label">Demandante</span><div class="print-value"><?= htmlspecialchars(mb_strtoupper($datosImpresion['demandante'],'UTF-8')) ?></div></div>
            <?php if(!empty($datosImpresion['apellidos_demandante'])): ?>
            <div class="col-4"><span class="print-label">Apellido(s)</span><div class="print-value"><?= htmlspecialchars(mb_strtoupper($datosImpresion['apellidos_demandante'],'UTF-8')) ?></div></div>
            <?php endif; ?>
            <div class="<?= !empty($datosImpresion['apellidos_demandante']) ? 'col-3' : 'col-4' ?>"><span class="print-label">C.I. / RIF</span><div class="print-value"><?= htmlspecialchars($datosImpresion['cedula_rif_demandante']) ?: 'N/A' ?></div></div>
        </div>
        <div class="row">
            <div class="<?= !empty($datosImpresion['apellidos_demandado']) ? 'col-5' : 'col-8' ?>"><span class="print-label">Demandado</span><div class="print-value"><?= htmlspecialchars(mb_strtoupper($datosImpresion['demandado'],'UTF-8')) ?></div></div>
            <?php if(!empty($datosImpresion['apellidos_demandado'])): ?>
            <div class="col-4"><span class="print-label">Apellido(s)</span><div class="print-value"><?= htmlspecialchars(mb_strtoupper($datosImpresion['apellidos_demandado'],'UTF-8')) ?></div></div>
            <?php endif; ?>
            <div class="<?= !empty($datosImpresion['apellidos_demandado']) ? 'col-3' : 'col-4' ?>"><span class="print-label">C.I. / RIF</span><div class="print-value"><?= htmlspecialchars($datosImpresion['cedula_rif_demandado']) ?: 'N/A' ?></div></div>
        </div>

        <div class="section-title">Detalles del Caso</div>
        <div class="row">
            <div class="col-12"><span class="print-label">Motivo / Delito</span><div class="print-value"><?= htmlspecialchars(mb_strtoupper($datosImpresion['motivo_delito'],'UTF-8')) ?></div></div>
        </div>
        <?php if(!empty($datosImpresion['observaciones'])): ?>
        <div class="row">
            <div class="col-12"><span class="print-label">Observaciones</span><div class="print-value"><?= nl2br(htmlspecialchars($datosImpresion['observaciones'])) ?></div></div>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.5/dist/sweetalert2.all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
let confirmadoActualizar = false;
$(document).ready(function() {
    $('#busqueda-tribunales').select2({theme:'bootstrap-5',width:'100%',placeholder:'Escribe el nombre del tribunal...',allowClear:true,language:{noResults:()=>'No se encontraron resultados',searching:()=>'Buscando...'}});

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
    
    // Ejecutar al cargar por si hay valores previos
    actualizarValidacionId('tipo_doc_demandante', 'cedula_rif_demandante');
    actualizarValidacionId('tipo_doc_demandado', 'cedula_rif_demandado');
});
(function(){
    'use strict';
    const form=document.getElementById('formRegistrar');
    const btn=document.getElementById('btnRegistrar');
    let enviando=false;
    if(!form) return;
    form.addEventListener('submit', async function(e){
        e.preventDefault(); e.stopPropagation();
        if(enviando) return;
        if(!form.checkValidity()){form.classList.add('was-validated');return;}
        form.classList.add('was-validated');
        enviando=true; btn.disabled=true;
        btn.innerHTML=btn.dataset.loadingHtml||'<span class="spinner-border spinner-border-sm me-2"></span>Procesando...';
        try{
            const fd=new FormData(form);
            const resp=await fetch('/registro',{method:'POST',body:fd,headers:{'X-Requested-With':'XMLHttpRequest'}});
            const raw=await resp.text();
            let data;
            try{data=JSON.parse(raw);}catch(pe){window.location.replace('/registro');return;}
            if(data.ok){window.location.replace(data.redirect_url||'/registro');return;}
            await Swal.fire({title:'Validación del sistema',text:data.mensaje||'No se pudo completar el registro.',icon:data.tipo||'warning',confirmButtonText:'Entendido'});
        }catch(err){
            await Swal.fire({title:'Error de conexión',text:'No fue posible completar el envío. Intenta nuevamente.',icon:'error',confirmButtonText:'Entendido'});
        }finally{enviando=false;btn.disabled=false;btn.innerHTML=btn.dataset.defaultHtml||"<i class='bi bi-save-fill me-2'></i>GUARDAR EXPEDIENTE";}
    },false);

    // Verificar si el expediente ya existe
    const inputExpediente = document.getElementById('n_expediente');
    if (inputExpediente) {
        inputExpediente.addEventListener('blur', async function() {
            const exp = this.value.trim();
            if(!exp) return;
            try {
                // Fetch to MVC endpoint
                const r = await fetch(BASE_URL + '/verificar_expediente?expediente=' + encodeURIComponent(exp));
                const res = await r.json();
                if(res.existe) {
                    const accion = await Swal.fire({
                        title: '¡Expediente Duplicado!',
                        text: `El expediente ${exp} ya se encuentra registrado. ¿Deseas actualizar sus datos?`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, actualizar datos',
                        cancelButtonText: 'No, corregir número',
                        reverseButtons: true
                    });
                    if(accion.isConfirmed) {
                        if (res.id) {
                            window.location.href = BASE_URL + '/editar/' + res.id;
                        }
                    } else {
                        inputExpediente.value = '';
                        setTimeout(() => inputExpediente.focus(), 100);
                    }
                }
            } catch(e) { console.error('Error verificando expediente:', e); }
        });
    }
})();
const BASE_URL = '<?= BASE_URL ?>';
</script>
</body></html>
