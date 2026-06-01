<?php
/**
 * Views/ubicacion/index.php
 * Vista de Gestión de Ubicaciones Físicas (Individual y por Lote).
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Ubicaciones - Archivo Judicial</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/select/1.7.0/css/select.bootstrap5.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --azul-institucional: #004085;
            --azul-claro: #0056b3;
        }
        body {
            background-image: url('<?= BASE_URL ?>/background.png');
            background-size: cover;
            background-position: top center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            padding-bottom: 50px;
            background-color: #FFFFFF;
        }
        .container {
            padding-top: 80px;
            position: relative;
            z-index: 1;
        }
        .card-ubicaciones {
            background: #FFFFFF;
            border-radius: 12px;
            border: 1px solid rgba(0,64,133,0.1);
        }
        .card-header-custom {
            background: linear-gradient(135deg, #004085 0%, #0056b3 100%);
            color: white;
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
            padding: 20px;
        }
        .nav-tabs .nav-link {
            color: var(--azul-institucional);
            font-weight: 600;
        }
        .nav-tabs .nav-link.active {
            background-color: var(--azul-claro);
            color: white;
            border-color: var(--azul-claro);
        }
        .expediente-card {
            background-color: #f8f9fa;
            border-left: 4px solid var(--azul-claro);
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .ip-badge {
            font-family: monospace;
            font-size: 0.85rem;
            background-color: #e3f2fd;
            color: #1565c0;
            padding: 3px 8px;
            border-radius: 4px;
        }
        * {
            box-shadow: none !important;
        }
        .modal-content {
            box-shadow: 0 4px 6px rgba(0,0,0,0.1) !important;
        }
    </style>
</head>
<body>

<div class="container">
    
    <!-- Botón de Retorno -->
    <div class="mb-4">
        <a href="<?= BASE_URL ?>/" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-2"></i>Volver al Menú
        </a>
        <?php if ($_SESSION['usuario_rol'] === 'admin'): ?>
        <a href="<?= BASE_URL ?>/sedes" class="btn btn-outline-primary">
            <i class="bi bi-building me-2"></i>Gestionar Sedes
        </a>
        <?php endif; ?>
    </div>
    
    <!-- Alertas -->
    <?php if (!empty($mensaje)): ?>
        <div class="alert alert-<?= $tipoAlerta ?> alert-dismissible fade show" role="alert">
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
                <li class="nav-item">
                    <a class="nav-link <?= $modo === 'individual' ? 'active' : '' ?>" 
                       href="<?= BASE_URL ?>/ubicaciones?modo=individual">
                        <i class="bi bi-search me-2"></i>Asignación Individual
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $modo === 'lote' ? 'active' : '' ?>" 
                       href="<?= BASE_URL ?>/ubicaciones?modo=lote">
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
                    <form method="POST" action="<?= BASE_URL ?>/ubicaciones?modo=individual">
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
            
            <?php if (isset($expedienteEncontrado) && $expedienteEncontrado): ?>
            <hr class="my-4">
            
            <!-- Información del Expediente -->
            <div class="expediente-card mb-4">
                <h6 class="text-primary mb-3"><i class="bi bi-check-circle-fill me-2"></i>Expediente Encontrado</h6>
                <div class="row">
                    <div class="col-md-8">
                        <p class="mb-1"><strong>Nro Expediente:</strong> <?= htmlspecialchars($expedienteEncontrado['n_expediente']) ?></p>
                        <p class="mb-1"><strong>Demandante:</strong> <?= htmlspecialchars($expedienteEncontrado['demandante']) ?></p>
                        <p class="mb-0"><strong>Demandado:</strong> <?= htmlspecialchars($expedienteEncontrado['demandado']) ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Ubicación Actual Destacada -->
            <div class="card mb-4" style="border: 2px solid var(--azul-claro);">
                <div class="card-header" style="background: linear-gradient(135deg, #004085 0%, #0056b3 100%); color: white;">
                    <h5 class="mb-0"><i class="bi bi-geo-alt-fill me-2"></i>Ubicación Actual del Expediente</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($expedienteEncontrado['id_sede']) && isset($ubicacionActual) && $ubicacionActual): ?>
                        <div class="row g-4">
                            <div class="col-md-4">
                                <div class="text-center p-3" style="background-color: #e3f2fd; border-radius: 8px;">
                                    <i class="bi bi-building" style="font-size: 2.5rem; color: var(--azul-claro);"></i>
                                    <h6 class="mt-2 mb-1 text-uppercase text-muted" style="font-size: 0.75rem; letter-spacing: 1px;">SEDE</h6>
                                    <p class="mb-0 fw-bold" style="color: var(--azul-claro);"><?= htmlspecialchars($ubicacionActual['nombre_sede']) ?></p>
                                    <?php if (!empty($ubicacionActual['direccion'])): ?>
                                        <small class="text-muted"><i class="bi bi-pin-map me-1"></i><?= htmlspecialchars($ubicacionActual['direccion']) ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="text-center p-3" style="background-color: #e3f2fd; border-radius: 8px;">
                                    <i class="bi bi-map" style="font-size: 2.5rem; color: #0056b3;"></i>
                                    <h6 class="mt-2 mb-1 text-uppercase text-muted" style="font-size: 0.75rem; letter-spacing: 1px;">ÁREA</h6>
                                    <p class="mb-0 fw-bold" style="color: #0056b3;">
                                        <?= !empty($expedienteEncontrado['ubicacion_area']) ? htmlspecialchars($expedienteEncontrado['ubicacion_area']) : 'No especificada' ?>
                                    </p>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="text-center p-3" style="background-color: #e3f2fd; border-radius: 8px;">
                                    <i class="bi bi-box-seam" style="font-size: 2.5rem; color: #004085;"></i>
                                    <h6 class="mt-2 mb-1 text-uppercase text-muted" style="font-size: 0.75rem; letter-spacing: 1px;">ESTANTE</h6>
                                    <p class="mb-0 fw-bold" style="color: #004085;">
                                        <?= !empty($expedienteEncontrado['ubicacion_detalle']) ? htmlspecialchars($expedienteEncontrado['ubicacion_detalle']) : 'No especificado' ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="bi bi-inbox" style="font-size: 4rem; color: #ccc;"></i>
                            <h5 class="mt-3 text-muted">Sin Ubicación Asignada</h5>
                            <p class="text-muted mb-0">Este expediente aún no tiene una ubicación física registrada en el sistema.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Formulario de Cambio de Ubicación (Solo Administradores) -->
            <?php if ($_SESSION['usuario_rol'] === 'admin'): ?>
            <div class="card">
                <div class="card-header bg-warning bg-opacity-25">
                    <h5 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Cambiar Ubicación <span class="badge bg-danger ms-2">Solo Administradores</span></h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= BASE_URL ?>/ubicaciones?modo=individual">
                        <input type="hidden" name="guardar_ubicacion_individual" value="1">
                        <input type="hidden" name="id_expediente" value="<?= $expedienteEncontrado['Id'] ?>">
                        <input type="hidden" name="n_expediente" value="<?= $expedienteEncontrado['n_expediente'] ?>">
                        
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Nueva Sede <span class="text-danger">*</span></label>
                                <select class="form-select" name="id_sede" id="sede_select_individual" required>
                                    <option value="">Selecciona una sede...</option>
                                    <?php foreach ($sedes as $sd): ?>
                                        <option value="<?= $sd['id_sede'] ?>" 
                                                data-descripcion="<?= htmlspecialchars($sd['descripcion'] ?? '') ?>"
                                                data-direccion="<?= htmlspecialchars($sd['direccion'] ?? '') ?>"
                                                <?= ($expedienteEncontrado['id_sede'] == $sd['id_sede']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($sd['nombre_sede']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div id="descripcion_sede_individual" class="mt-2 p-2 bg-light border-start border-primary border-3 rounded" style="display: none;">
                                    <small class="text-muted">
                                        <i class="bi bi-geo-alt-fill me-1"></i>
                                        <strong>Dirección:</strong> <span id="texto_direccion_individual"></span>
                                    </small>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Nueva Área</label>
                                <input type="text" class="form-control" name="ubicacion_area" 
                                       placeholder="Ej: Piso 3, Seccion A"
                                       value="<?= htmlspecialchars($expedienteEncontrado['ubicacion_area'] ?? '') ?>">
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Nuevo Estante</label>
                                <input type="text" class="form-control" name="ubicacion_detalle" 
                                       placeholder="Ej: Estante B / Caja 4"
                                       value="<?= htmlspecialchars($expedienteEncontrado['ubicacion_detalle'] ?? '') ?>">
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-warning btn-lg">
                                <i class="bi bi-arrow-repeat me-2"></i>Actualizar Ubicación
                            </button>
                            <small class="text-muted ms-3">
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                Esta acción sobrescribirá la ubicación actual del expediente.
                            </small>
                        </div>
                    </form>
                </div>
            </div>
            <?php else: ?>
            <div class="alert alert-info border-start border-info border-4">
                <i class="bi bi-shield-lock-fill me-2"></i>
                <strong>Acceso Restringido:</strong> Solo los administradores pueden modificar las ubicaciones de los expedientes.
            </div>
            <?php endif; ?>
            
            <?php endif; ?>
            
            <?php endif; ?>
            
            <!-- ============================================ -->
            <!-- MODO LOTE -->
            <!-- ============================================ -->
            <?php if ($modo === 'lote'): ?>
            <div class="alert alert-info border-start border-info border-4 mb-4">
                <i class="bi bi-lightbulb-fill me-2"></i>
                <strong>Carga por Lote (Pick-list):</strong> Selecciona múltiples expedientes de la lista y asígnales la misma ubicación de una sola vez.
                <br><small class="text-muted">Mostrando expedientes sin ubicación asignada. Total disponibles: <strong><?= number_format($totalSinUbicacion) ?></strong></small>
            </div>
            
            <form method="POST" action="<?= BASE_URL ?>/ubicaciones?modo=lote" id="formLote">
                <input type="hidden" name="guardar_ubicacion_lote" value="1">
                
                <div class="card mb-4">
                    <div class="card-header bg-primary bg-opacity-25 text-dark">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-geo-alt-fill me-2"></i>Configurar Ubicación para Expedientes Seleccionados</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Sede <span class="text-danger">*</span></label>
                                <select class="form-select" name="id_sede_lote" id="sede_select_lote" required>
                                    <option value="">Selecciona una sede...</option>
                                    <?php foreach ($sedes as $sd): ?>
                                        <option value="<?= $sd['id_sede'] ?>" 
                                                data-descripcion="<?= htmlspecialchars($sd['descripcion'] ?? '') ?>"
                                                data-direccion="<?= htmlspecialchars($sd['direccion'] ?? '') ?>"
                                                <?= ($_SESSION['ultima_sede'] ?? '') == $sd['id_sede'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($sd['nombre_sede']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div id="descripcion_sede_lote" class="mt-2 p-2 bg-light border-start border-primary border-3 rounded" style="display: none;">
                                    <small class="text-muted">
                                        <i class="bi bi-geo-alt-fill me-1"></i>
                                        <strong>Dirección:</strong> <span id="texto_direccion_lote"></span>
                                    </small>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Área</label>
                                <input type="text" class="form-control" name="ubicacion_area_lote" 
                                       placeholder="Ej: Piso 3, Seccion A"
                                       value="<?= htmlspecialchars($_SESSION['ultima_area'] ?? '') ?>">
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Estante</label>
                                <input type="text" class="form-control" name="ubicacion_detalle_lote" 
                                       placeholder="Ej: Estante B / Caja 4">
                            </div>
                        </div>
                        
                        <div class="mt-3 d-flex justify-content-between align-items-center">
                            <div>
                                <span class="badge bg-primary fs-6" id="contadorSeleccionados">0 expedientes seleccionados</span>
                            </div>
                            <button type="submit" class="btn btn-primary px-4 py-2" id="btnProcesarLote" disabled>
                                <i class="bi bi-check2-all me-2"></i>Procesar Seleccionados
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-bold"><i class="bi bi-list-check me-2"></i>Expedientes Sin Ubicación</h6>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-sm btn-outline-primary" id="seleccionarTodos">
                                    <i class="bi bi-check-all me-1"></i>Seleccionar Todos
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="limpiarSeleccion">
                                    <i class="bi bi-x-circle me-1"></i>Limpiar Selección
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <?php if (count($expedientesSinUbicacion) > 0): ?>
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
                                    <?php foreach ($expedientesSinUbicacion as $exp): ?>
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
                            <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                            <h5 class="mt-3 text-success">¡Excelente!</h5>
                            <p class="text-muted mb-0">Todos los expedientes tienen ubicación asignada.</p>
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
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/app-alerts.js"></script>

<script>
// Mostrar dirección de sede en modo individual
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

// Mostrar dirección de sede en modo lote
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

// Disparar el evento change al cargar la página si hay una sede pre-seleccionada
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

$(document).ready(function() {
    // Inicializar DataTable
    if ($('#tablaExpedientes').length > 0) {
        const table = $('#tablaExpedientes').DataTable({
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
            },
            pageLength: 25,
            order: [[1, 'desc']],
            columnDefs: [
                { orderable: false, targets: 0 }
            ],
            dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                 '<"row"<"col-sm-12"tr>>' +
                 '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            drawCallback: function() {
                actualizarContadorSeleccionados();
            }
        });
    }
    
    function actualizarContadorSeleccionados() {
        const checkboxes = $('.checkbox-expediente:checked');
        const cantidad = checkboxes.length;
        
        $('#contadorSeleccionados').text(cantidad + ' expedientes seleccionados');
        $('#btnProcesarLote').prop('disabled', cantidad === 0);
        
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
    
    $('#checkboxMaestro').on('change', function() {
        const isChecked = $(this).is(':checked');
        $('.checkbox-expediente').prop('checked', isChecked);
        actualizarContadorSeleccionados();
    });
    
    $(document).on('change', '.checkbox-expediente', function() {
        actualizarContadorSeleccionados();
    });
    
    $('#seleccionarTodos').on('click', function() {
        $('.checkbox-expediente').prop('checked', true);
        actualizarContadorSeleccionados();
    });
    
    $('#limpiarSeleccion').on('click', function() {
        $('.checkbox-expediente').prop('checked', false);
        actualizarContadorSeleccionados();
    });
    
    // Efecto de desvanecimiento tras el procesamiento
    if (typeof expedientesProcesados !== 'undefined' && expedientesProcesados.length > 0) {
        expedientesProcesados.forEach(function(id) {
            $('tr[data-id="' + id + '"]').fadeOut(1000, function() {
                $(this).remove();
                if ($.fn.DataTable.isDataTable('#tablaExpedientes')) {
                    $('#tablaExpedientes').DataTable().row($(this)).remove().draw();
                }
                actualizarContadorSeleccionados();
            });
        });
        setTimeout(function() {
            expedientesProcesados = [];
        }, 2000);
    }
    
    // Validación y confirmación del procesamiento en lote
    let envioConfirmadoLote = false;
    $('#formLote').on('submit', async function(e) {
        if (envioConfirmadoLote) return true;
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
            await window.appAlerts.alert('Debes seleccionar una sede para asignar la ubicación.', {
                type: 'warning',
                title: 'Falta una sede'
            });
            return false;
        }
        
        const confirmacion = await window.appAlerts.confirm(`¿Estás seguro de procesar ${expedientesSeleccionados} expediente(s)?`, {
            type: 'info',
            title: 'Confirmar procesamiento en lote',
            okText: 'Sí, procesar',
            cancelText: 'Cancelar'
        });

        if (!confirmacion) return false;

        envioConfirmadoLote = true;
        this.submit();
    });
    
    actualizarContadorSeleccionados();
});
</script>

</body>
</html>
