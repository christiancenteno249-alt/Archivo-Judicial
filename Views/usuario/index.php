<?php
/**
 * Views/usuario/index.php
 * Vista de gestión de usuarios del sistema.
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - Archivo Judicial</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/bootstrap-icons.css">
    <style>
        :root{--azul:#1a237e;}
        body{background-image:url('/background.png');background-size:cover;background-position:top center;background-repeat:no-repeat;background-attachment:fixed;font-family:'Segoe UI',system-ui,sans-serif;min-height:100vh;padding-bottom:50px;}
        .container{padding-top:100px;}
        .card-usuarios{background:#fff;border-radius:12px;box-shadow:0 8px 30px rgba(0,0,0,.15);border:none;}
        .card-header-custom{background-color:var(--azul);color:#fff;border-top-left-radius:12px;border-top-right-radius:12px;padding:20px;}
        .table thead{background-color:var(--azul);color:#fff;}
        .badge-admin{background-color:#d32f2f;}
        .badge-operador{background-color:#1976d2;}
    </style>
</head>
<body>
<div class="container">
    <div class="mb-4">
        <a href="/" class="btn btn-secondary"><i class="bi bi-arrow-left me-2"></i>Volver al Menú</a>
    </div>

    <?php if(!empty($mensaje)): ?>
    <div class="alert alert-<?= $tipoAlerta ?> alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($mensaje) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <?php if($accion==='crear' || $accion==='editar'): ?>
    <div class="card card-usuarios mb-4">
        <div class="card-header-custom">
            <h4 class="mb-0"><i class="bi bi-person-<?= $accion==='crear'?'plus':'gear' ?> me-2"></i>
                <?= $accion==='crear'?'Crear Nuevo Usuario':'Editar Usuario' ?>
            </h4>
        </div>
        <div class="card-body p-4">
            <form method="POST" action="/usuarios">
                <?php if($accion==='editar' && $usuarioEditar): ?>
                <input type="hidden" name="id_usuario" value="<?= $usuarioEditar['id_usuario'] ?>">
                <?php endif; ?>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Nombre Completo *</label>
                        <input type="text" class="form-control" name="nombre" required
                               value="<?= $accion==='editar' && $usuarioEditar ? htmlspecialchars($usuarioEditar['nombre_full']) : '' ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Usuario (Nick) *</label>
                        <input type="text" class="form-control" name="usuario" required
                               value="<?= $accion==='editar' && $usuarioEditar ? htmlspecialchars($usuarioEditar['usuario_nick']) : '' ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Contraseña <?= $accion==='crear'?'*':'(dejar vacío para no cambiar)' ?></label>
                        <input type="password" class="form-control" name="password" id="campo_password"
                               <?= $accion==='crear'?'required':'' ?>
                               placeholder="<?= $accion==='crear'?'Ingrese la contraseña':'Dejar vacío para no cambiar' ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Confirmar Contraseña <?= $accion==='crear'?'*':'(requerido si cambia la contraseña)' ?></label>
                        <input type="password" class="form-control" name="confirm_password" id="campo_confirm"
                               <?= $accion==='crear'?'required':'' ?>
                               placeholder="Repita la contraseña">
                        <div class="invalid-feedback">Las contraseñas no coinciden.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Rol *</label>
                        <select class="form-select" name="rol" required>
                            <option value="operador" <?= ($accion==='editar' && $usuarioEditar && $usuarioEditar['rol']==='operador')?'selected':'' ?>>Operador</option>
                            <option value="admin"    <?= ($accion==='editar' && $usuarioEditar && $usuarioEditar['rol']==='admin')?'selected':'' ?>>Administrador</option>
                        </select>
                    </div>
                </div>
                <div class="mt-4 d-flex gap-2">
                    <button type="submit" name="<?= $accion==='crear'?'crear_usuario':'editar_usuario' ?>" class="btn btn-primary">
                        <i class="bi bi-save me-2"></i><?= $accion==='crear'?'Crear Usuario':'Guardar Cambios' ?>
                    </button>
                    <a href="/usuarios" class="btn btn-secondary"><i class="bi bi-x-circle me-2"></i>Cancelar</a>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <div class="card card-usuarios">
        <div class="card-header-custom d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><i class="bi bi-people me-2"></i>Usuarios del Sistema</h4>
            <a href="/usuarios?accion=crear" class="btn btn-light btn-sm"><i class="bi bi-person-plus me-1"></i>Nuevo Usuario</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr>
                        <th class="ps-4">ID</th><th>Nombre Completo</th><th>Usuario</th><th>Rol</th><th>Fecha Registro</th><th class="pe-4 text-center">Acciones</th>
                    </tr></thead>
                    <tbody>
                    <?php foreach($usuarios as $u): ?>
                    <tr>
                        <td class="ps-4"><?= $u['id_usuario'] ?></td>
                        <td><?= htmlspecialchars($u['nombre_full']) ?></td>
                        <td><code><?= htmlspecialchars($u['usuario_nick']) ?></code></td>
                        <td><span class="badge badge-<?= $u['rol'] ?>"><?= $u['rol']==='admin'?'Administrador':'Operador' ?></span></td>
                        <td><?= date('d/m/Y H:i', strtotime($u['fecha_registro'])) ?></td>
                        <td class="pe-4 text-center">
                            <a href="/usuarios?accion=editar&id=<?= $u['id_usuario'] ?>" class="btn btn-sm btn-warning" title="Editar"><i class="bi bi-pencil"></i></a>
                            <?php if($u['id_usuario'] != $_SESSION['usuario_id']): ?>
                            <a href="/usuarios?eliminar=<?= $u['id_usuario'] ?>"
                               class="btn btn-sm btn-danger"
                               data-confirm-message="¿Estás seguro de eliminar este usuario?"
                               data-confirm-title="Eliminar usuario"
                               data-confirm-ok="Sí, eliminar"
                               data-confirm-cancel="Cancelar"
                               title="Eliminar"><i class="bi bi-trash"></i></a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="/assets/js/app-alerts.js"></script>
<script>
(function(){
    const pass    = document.getElementById('campo_password');
    const confirm = document.getElementById('campo_confirm');
    if (!pass || !confirm) return;

    function validar() {
        if (confirm.value === '') {
            confirm.classList.remove('is-invalid','is-valid');
            return;
        }
        if (pass.value === confirm.value) {
            confirm.classList.remove('is-invalid'); confirm.classList.add('is-valid');
        } else {
            confirm.classList.remove('is-valid'); confirm.classList.add('is-invalid');
        }
    }

    pass.addEventListener('input', validar);
    confirm.addEventListener('input', validar);

    const form = pass.closest('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            if (pass.value !== '' && pass.value !== confirm.value) {
                e.preventDefault();
                confirm.classList.add('is-invalid');
                confirm.focus();
            }
        });
    }
})();
</script>
</body></html>
