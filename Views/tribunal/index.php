<?php
/**
 * Views/tribunal/index.php
 * Vista de administración del catálogo de tribunales.
 * Interfaz de dos columnas: Formulario de Registro/Edición + Listado Completo.
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo de Tribunales - Archivo Judicial</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.5/dist/sweetalert2.min.css" rel="stylesheet">
    <style>
        :root {
            --azul: #004085;
            --azul2: #0056b3;
            --primary-hover: #003366;
        }
        body {
            background-image: url('/background.png');
            background-size: cover;
            background-position: top center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            font-family: 'Segoe UI', system-ui, sans-serif;
            min-height: 100vh;
            padding-bottom: 50px;
            background-color: #FFF;
        }
        .container {
            padding-top: 100px;
        }
        .card-custom {
            background: #FFF;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0, 64, 133, 0.15);
            border: none;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        .card-header-custom {
            background: linear-gradient(135deg, #004085, #0056b3);
            color: #fff;
            padding: 20px;
            font-weight: 600;
        }
        .table thead {
            background-color: var(--azul2);
            color: #fff;
        }
        .btn-primary-custom {
            background-color: var(--azul);
            border-color: var(--azul);
            font-weight: 600;
        }
        .btn-primary-custom:hover {
            background-color: var(--primary-hover);
            border-color: var(--primary-hover);
        }
        input[type="text"] {
            text-transform: uppercase;
        }
        .tribunal-row:hover {
            background-color: rgba(0, 86, 179, 0.05);
        }
        .id-badge {
            background: linear-gradient(135deg, #004085, #0056b3);
            color: white;
            border-radius: 6px;
            padding: 2px 8px;
            font-size: 0.8rem;
            font-weight: 700;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <div>
            <a href="/" class="btn btn-secondary shadow-sm"><i class="bi bi-house me-2"></i>Menú Principal</a>
            <a href="/registro" class="btn btn-outline-secondary shadow-sm"><i class="bi bi-folder-plus me-2"></i>Registrar Expediente</a>
        </div>
        <span class="badge bg-secondary p-2 fs-6 shadow-sm"><i class="bi bi-shield-lock me-2"></i>Modo Administrador</span>
    </div>

    <?php if(!empty($mensaje)): ?>
    <div class="alert alert-<?= $tipoAlerta ?> alert-dismissible fade show shadow-sm fw-bold border-0 border-start border-<?= $tipoAlerta ?> border-4 mb-4" role="alert">
        <?php if($tipoAlerta=='success'): ?><i class="bi bi-check-circle-fill me-2 fs-5 text-success"></i>
        <?php elseif($tipoAlerta=='warning'): ?><i class="bi bi-exclamation-triangle-fill me-2 fs-5 text-warning"></i>
        <?php else: ?><i class="bi bi-x-circle-fill me-2 fs-5 text-danger"></i><?php endif; ?>
        <?= htmlspecialchars($mensaje) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Columna 1: Formulario para Agregar/Editar -->
        <div class="col-lg-4">
            <div class="card card-custom shadow-sm mb-4" id="form-container">
                <div class="card-header-custom">
                    <h5 class="mb-0" id="form-title"><i class="bi bi-plus-circle me-2"></i>Agregar Tribunal</h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="/tribunales/guardar" id="form-tribunal" class="needs-validation" novalidate>
                        <input type="hidden" name="id_tribunal" id="id_tribunal" value="">

                        <div class="mb-3">
                            <label for="nombre_tribunal" class="form-label fw-bold">Nombre del Tribunal *</label>
                            <textarea class="form-control" name="nombre_tribunal" id="nombre_tribunal" required
                                placeholder="Ej: JUZGADO PRIMERO DE PRIMERA INSTANCIA CIVIL..."
                                rows="3" maxlength="255" style="text-transform: uppercase;"></textarea>
                            <small class="text-muted">Máximo 255 caracteres. Se guardará en MAYÚSCULAS.</small>
                            <div class="invalid-feedback">Ingresa el nombre del tribunal.</div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-primary-custom" id="btn-submit">
                                <i class="bi bi-save me-2"></i>Guardar Tribunal
                            </button>
                            <button type="button" class="btn btn-secondary d-none" id="btn-cancel" onclick="resetForm()">
                                <i class="bi bi-x-circle me-2"></i>Cancelar Edición
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tarjeta informativa -->
            <div class="card card-custom shadow-sm">
                <div class="card-body p-3">
                    <h6 class="fw-bold text-primary"><i class="bi bi-info-circle me-2"></i>Información</h6>
                    <ul class="small text-muted mb-0 ps-3">
                        <li>Al agregar un tribunal, queda disponible inmediatamente en el selector de expedientes.</li>
                        <li>Al eliminar un tribunal, <strong>los expedientes existentes</strong> que lo tienen asignado <strong>no se ven afectados</strong>.</li>
                        <li>Solo los administradores pueden gestionar este catálogo.</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Columna 2: Tabla del Catálogo de Tribunales -->
        <div class="col-lg-8">
            <div class="card card-custom shadow-sm">
                <div class="card-header-custom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-bank me-2"></i>Tribunales Registrados</h5>
                    <span class="badge bg-light text-dark fw-bold"><?= count($tribunales) ?> tribunales</span>
                </div>
                <div class="card-body p-0">
                    <!-- Buscador local -->
                    <div class="p-3 border-bottom">
                        <input type="text" class="form-control" id="buscadorLocal"
                               placeholder="&#128269; Filtrar tribunales por nombre..."
                               oninput="filtrarTabla(this.value)">
                    </div>
                    <div class="table-responsive" style="max-height: 550px; overflow-y: auto;">
                        <table class="table table-hover align-middle mb-0" id="tablaTribunales">
                            <thead class="sticky-top table-primary">
                                <tr>
                                    <th class="ps-3" style="width: 8%;">ID</th>
                                    <th style="width: 72%;">Nombre del Tribunal</th>
                                    <th class="pe-3 text-center" style="width: 20%;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyTribunales">
                                <?php if(count($tribunales) > 0): ?>
                                    <?php foreach($tribunales as $t): ?>
                                    <tr class="tribunal-row">
                                        <td class="ps-3"><span class="id-badge"><?= htmlspecialchars($t['Id_tribunal']) ?></span></td>
                                        <td><span class="tribunal-nombre"><?= htmlspecialchars($t['tribunal']) ?></span></td>
                                        <td class="pe-3 text-center">
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-outline-warning"
                                                        onclick="prepareEdit(<?= htmlspecialchars($t['Id_tribunal']) ?>, '<?= htmlspecialchars(addslashes($t['tribunal'])) ?>')"
                                                        title="Editar Tribunal">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger"
                                                        onclick="confirmDelete(<?= htmlspecialchars($t['Id_tribunal']) ?>, '<?= htmlspecialchars(addslashes($t['tribunal'])) ?>')"
                                                        title="Eliminar Tribunal">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="text-center py-5 text-muted">
                                            <i class="bi bi-bank fs-1"></i>
                                            <p class="mt-3 mb-0">No hay tribunales registrados en el catálogo.</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.5/dist/sweetalert2.all.min.js"></script>
<script>
    $(document).ready(function() {
        // Forzar mayúsculas en el textarea
        $('#nombre_tribunal').on('input', function() {
            this.value = this.value.toUpperCase();
        });

        // Validación de formulario Bootstrap
        const form = document.getElementById('form-tribunal');
        form.addEventListener('submit', function(e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });

    // Filtrado local de la tabla sin recargar la página
    function filtrarTabla(query) {
        const filas = document.querySelectorAll('#tbodyTribunales tr.tribunal-row');
        const q = query.toUpperCase().trim();
        filas.forEach(function(fila) {
            const nombre = fila.querySelector('.tribunal-nombre')?.textContent.toUpperCase() || '';
            fila.style.display = nombre.includes(q) ? '' : 'none';
        });
    }

    // Preparar el formulario para edición
    function prepareEdit(id, nombre) {
        $('#form-title').html('<i class="bi bi-pencil-square me-2 text-warning"></i>Editar Tribunal');
        $('#form-tribunal').attr('action', '/tribunales/editar');
        $('#id_tribunal').val(id);
        $('#nombre_tribunal').val(nombre).focus();
        $('#btn-submit').html('<i class="bi bi-check-circle me-2"></i>Actualizar Tribunal')
                        .removeClass('btn-primary-custom').addClass('btn-warning text-white fw-bold');
        $('#btn-cancel').removeClass('d-none');
        document.getElementById('form-container').scrollIntoView({ behavior: 'smooth' });
    }

    // Resetear el formulario a modo adición
    function resetForm() {
        $('#form-title').html('<i class="bi bi-plus-circle me-2"></i>Agregar Tribunal');
        $('#form-tribunal').attr('action', '/tribunales/guardar').removeClass('was-validated');
        $('#id_tribunal').val('');
        $('#nombre_tribunal').val('');
        $('#btn-submit').html('<i class="bi bi-save me-2"></i>Guardar Tribunal')
                        .removeClass('btn-warning text-white fw-bold').addClass('btn-primary-custom');
        $('#btn-cancel').addClass('d-none');
    }

    // Confirmar eliminación del tribunal
    function confirmDelete(id, nombre) {
        Swal.fire({
            title: '¿Eliminar Tribunal?',
            html: `¿Seguro que deseas eliminar:<br><strong>${nombre}</strong>?<br><br><small class="text-muted">Los expedientes existentes que lo tienen asignado <strong>no se verán afectados</strong>.</small>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `/tribunales/eliminar?id=${id}`;
            }
        });
    }
</script>
</body>
</html>
