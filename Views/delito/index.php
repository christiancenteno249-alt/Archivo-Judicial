<?php
/**
 * Views/delito/index.php
 * Vista de administración del catálogo de delitos.
 * Interfaz de dos columnas: Formulario de Registro/Edición + Listado Completo.
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo de Delitos - Archivo Judicial</title>
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
        /* Forzar mayúsculas en inputs de texto */
        input[type="text"] {
            text-transform: uppercase;
        }
        .delito-row:hover {
            background-color: rgba(0, 86, 179, 0.05);
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
                    <h5 class="mb-0" id="form-title"><i class="bi bi-plus-circle me-2"></i>Agregar Delito</h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="/delitos/guardar" id="form-delito" class="needs-validation" novalidate>
                        <input type="hidden" name="id_delito" id="id_delito" value="">
                        
                        <div class="mb-3">
                            <label for="nombre_delito" class="form-label fw-bold">Nombre del Delito / Motivo *</label>
                            <input type="text" class="form-control" name="nombre_delito" id="nombre_delito" required placeholder="Ej: ROBO AGRAVADO" maxlength="255">
                            <div class="invalid-feedback">Ingresa el nombre del delito.</div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-primary-custom" id="btn-submit">
                                <i class="bi bi-save me-2"></i>Guardar Delito
                            </button>
                            <button type="button" class="btn btn-secondary d-none" id="btn-cancel" onclick="resetForm()">
                                <i class="bi bi-x-circle me-2"></i>Cancelar Edición
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Columna 2: Tabla del Catálogo de Delitos -->
        <div class="col-lg-8">
            <div class="card card-custom shadow-sm">
                <div class="card-header-custom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-list-stars me-2"></i>Catálogo de Delitos Registrados</h5>
                    <span class="badge bg-light text-dark fw-bold"><?= count($delitos) ?> items</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 550px; overflow-y: auto;">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="sticky-top table-primary">
                                <tr>
                                    <th class="ps-4" style="width: 10%;">ID</th>
                                    <th style="width: 70%;">Nombre del Delito / Motivo</th>
                                    <th class="pe-4 text-center" style="width: 20%;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(count($delitos) > 0): ?>
                                    <?php foreach($delitos as $d): ?>
                                    <tr class="delito-row">
                                        <td class="ps-4 fw-bold text-muted"><?= htmlspecialchars($d['id_delito']) ?></td>
                                        <td><strong><?= htmlspecialchars($d['nombre_delito']) ?></strong></td>
                                        <td class="pe-4 text-center">
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-outline-warning" 
                                                        onclick="prepareEdit(<?= htmlspecialchars($d['id_delito']) ?>, '<?= htmlspecialchars(addslashes($d['nombre_delito'])) ?>')" 
                                                        title="Editar Delito">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                                        onclick="confirmDelete(<?= htmlspecialchars($d['id_delito']) ?>, '<?= htmlspecialchars(addslashes($d['nombre_delito'])) ?>')" 
                                                        title="Eliminar Delito">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="text-center py-5 text-muted">
                                            <i class="bi bi-shield-slash fs-1"></i>
                                            <p class="mt-3 mb-0">No hay delitos registrados en el catálogo.</p>
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
        // Forzar mayúsculas en el input de nombre
        $('#nombre_delito').on('input', function() {
            this.value = this.value.toUpperCase();
        });

        // Validación de formulario
        const form = document.getElementById('form-delito');
        form.addEventListener('submit', function(e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });

    // Preparar el formulario para edición
    function prepareEdit(id, nombre) {
        $('#form-title').html('<i class="bi bi-pencil-square me-2 text-warning"></i>Editar Delito');
        $('#form-delito').attr('action', '/delitos/editar');
        $('#id_delito').val(id);
        $('#nombre_delito').val(nombre).focus();
        $('#btn-submit').html('<i class="bi bi-check-circle me-2"></i>Actualizar Delito').removeClass('btn-primary-custom').addClass('btn-warning text-white fw-bold');
        $('#btn-cancel').removeClass('d-none');
        
        // Efecto scroll suave al formulario en móviles
        document.getElementById('form-container').scrollIntoView({ behavior: 'smooth' });
    }

    // Resetear el formulario a modo adición
    function resetForm() {
        $('#form-title').html('<i class="bi bi-plus-circle me-2"></i>Agregar Delito');
        $('#form-delito').attr('action', '/delitos/guardar').removeClass('was-validated');
        $('#id_delito').val('');
        $('#nombre_delito').val('');
        $('#btn-submit').html('<i class="bi bi-save me-2"></i>Guardar Delito').removeClass('btn-warning text-white fw-bold').addClass('btn-primary-custom');
        $('#btn-cancel').addClass('d-none');
    }

    // Confirmar eliminación del delito
    function confirmDelete(id, nombre) {
        Swal.fire({
            title: '¿Eliminar Delito?',
            text: `¿Seguro que deseas eliminar '${nombre}' del catálogo de delitos? Esta acción no se puede deshacer si el delito no está siendo utilizado en ningún expediente.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `/delitos/eliminar?id=${id}`;
            }
        });
    }
</script>
</body>
</html>
