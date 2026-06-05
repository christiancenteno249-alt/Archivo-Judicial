<?php
/**
 * Views/expediente/buscar.php
 * Vista del buscador avanzado de expedientes.
 */
function renderPaginaBtn(int $pag, string $label, bool $dis = false, bool $act = false): void {
    $ca = $act ? ' active' : ''; $cd = $dis ? ' disabled' : '';
    echo "<li class=\"page-item{$ca}{$cd}\">";
    if ($dis) { echo "<span class=\"page-link\">{$label}</span>"; }
    else {
        echo '<form method="POST" action="/consulta" class="d-inline m-0 p-0">';
        echo "<input type=\"hidden\" name=\"pagina\" value=\"{$pag}\">";
        echo "<button type=\"submit\" class=\"page-link border-0 bg-transparent\">{$label}</button></form>";
    }
    echo '</li>';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buscador Avanzado - Archivo Judicial</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/bootstrap-icons.css">
    <style>
        :root { --azul:#004085; --azul2:#0056b3; --azulh:#003366; }
        body { background-image:url('/background.png'); background-size:cover; background-position:center top; background-repeat:no-repeat; background-attachment:fixed; font-family:'Segoe UI',system-ui,sans-serif; padding-bottom:50px; min-height:100vh; background-color:#FFF; }
        .header-title { color:var(--azul); font-weight:700; text-transform:uppercase; letter-spacing:1px; margin-bottom:30px; margin-top:120px; }
        .card-search { background:#FFF; border-radius:12px; box-shadow:0 4px 20px rgba(0,64,133,.15); border:none; border-top:5px solid var(--azul); position:relative; }
        .card-search .card-body { padding-top:3.5rem; }
        .btn-volver-menu { position:absolute; top:15px; right:20px; z-index:10; }
        .form-label { font-size:.8rem; font-weight:700; color:#555; text-transform:uppercase; margin-bottom:.25rem; }
        .form-control:focus,.form-select:focus { border-color:var(--azul); box-shadow:0 0 0 .25rem rgba(26,35,126,.25); }
        .btn-prim { background-color:var(--azul); border-color:var(--azul); color:#fff; font-weight:600; transition:all .3s; }
        .btn-prim:hover { background-color:var(--azulh); border-color:var(--azulh); color:#fff; }
        .table-container { background:#FFF; border-radius:12px; box-shadow:0 4px 20px rgba(0,64,133,.1); overflow:hidden; margin-top:25px; }
        .table thead { background-color:var(--azul); color:#fff; }
        .table th { font-weight:600; font-size:.85rem; text-transform:uppercase; vertical-align:middle; border-bottom:none; }
        .table td { font-size:.9rem; vertical-align:middle; }
        .cedula-badge { font-size:.75rem; background-color:#e9ecef; color:#495057; padding:3px 6px; border-radius:4px; margin-top:5px; display:inline-block; font-weight:600; border:1px solid #ced4da; }
        .info-bar { background-color:#e8eaf6; color:var(--azul); border-left:4px solid var(--azul); padding:12px 20px; border-radius:6px; font-weight:600; margin-bottom:20px; }
        .page-link { color:var(--azul); } .page-item.active .page-link { background-color:var(--azul); border-color:var(--azul); }
        .text-primary { color:var(--azul)!important; }
        .sede-cell { max-width:220px; overflow:hidden; }
        .sede-truncate { white-space:nowrap; overflow:hidden; text-overflow:ellipsis; font-size:.9rem; }
        .tribunal-completo { white-space:normal!important; word-wrap:break-word; line-height:1.3; font-size:.85rem; }
        .table { table-layout:auto!important; }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="text-center mb-4">
        <h2 class="header-title"><i class="bi bi-search me-2"></i>Buscador Avanzado de Expedientes</h2>
        <p class="text-muted">Consulta Segura de Expedientes Judiciales</p>
    </div>

    <div class="card card-search mb-4">
        <a href="/" class="btn btn-secondary btn-sm btn-volver-menu"><i class="bi bi-arrow-left me-2"></i>Volver al Menú</a>
        <div class="card-body p-4">
            <form action="/consulta" method="POST" id="searchForm">
                <!-- Boton oculto para que el Enter ejecute la busqueda en lugar de limpiar -->
                <button type="submit" name="ejecutar" class="d-none" aria-hidden="true"></button>
                <div class="row g-3">
                    <div class="col-md-3"><label class="form-label">Nro Expediente</label>
                        <input type="text" class="form-control" name="expediente" placeholder="Ej: 00001" value="<?= htmlspecialchars($filtros['expediente']) ?>"></div>
                    <div class="col-md-3"><label class="form-label">Nro Legajo</label>
                        <input type="text" class="form-control" name="n_legajo" placeholder="Ej: L-001" value="<?= htmlspecialchars($filtros['n_legajo']) ?>"></div>
                    <div class="col-md-6"><label class="form-label">Demandante</label>
                        <input type="text" class="form-control" name="demandante" placeholder="Nombre(s)" value="<?= htmlspecialchars($filtros['demandante']) ?>"></div>
                    <div class="col-md-3"><label class="form-label">Apellido(s) Demandante</label>
                        <input type="text" class="form-control" name="apellidos_demandante" placeholder="Apellido(s)" value="<?= htmlspecialchars($filtros['apellidos_demandante'] ?? '') ?>"></div>
                    <div class="col-md-4"><label class="form-label">C.I. / RIF Demandante</label>
                        <div class="input-group">
                            <select class="form-select" name="tipo_dante" style="max-width:75px;font-weight:bold;">
                                <?php foreach(['V','J','E'] as $t): ?><option value="<?=$t?>" <?=$filtros['tipo_dante']==$t?'selected':''?>><?=$t?></option><?php endforeach; ?>
                            </select>
                            <input type="text" class="form-control" name="ced_dante" placeholder="Numero..." value="<?= htmlspecialchars($filtros['ced_dante']) ?>">
                        </div></div>
                    <div class="col-md-5"><label class="form-label">Demandado</label>
                        <input type="text" class="form-control" name="demandado" placeholder="Nombre(s)" value="<?= htmlspecialchars($filtros['demandado']) ?>"></div>
                    <div class="col-md-3"><label class="form-label">Apellido(s) Demandado</label>
                        <input type="text" class="form-control" name="apellidos_demandado" placeholder="Apellido(s)" value="<?= htmlspecialchars($filtros['apellidos_demandado'] ?? '') ?>"></div>
                    <div class="col-md-4"><label class="form-label">C.I. / RIF Demandado</label>
                        <div class="input-group">
                            <select class="form-select" name="tipo_dado" style="max-width:75px;font-weight:bold;">
                                <?php foreach(['V','J','E'] as $t): ?><option value="<?=$t?>" <?=$filtros['tipo_dado']==$t?'selected':''?>><?=$t?></option><?php endforeach; ?>
                            </select>
                            <input type="text" class="form-control" name="ced_dado" placeholder="Numero..." value="<?= htmlspecialchars($filtros['ced_dado']) ?>">
                        </div></div>
                    <div class="col-md-3"><label class="form-label">Fecha Entrada</label>
                        <input type="date" class="form-control" name="fecha" value="<?= htmlspecialchars($filtros['fecha']) ?>"></div>
                    <div class="col-md-12"><hr class="my-2"><label class="form-label fw-bold text-primary"><i class="bi bi-calendar-range me-2"></i>Filtros Avanzados de Fecha</label></div>
                    <div class="col-md-4"><label class="form-label">Fecha Desde</label>
                        <input type="date" class="form-control" name="fecha_desde" value="<?= htmlspecialchars($filtros['fecha_desde']) ?>"></div>
                    <div class="col-md-4"><label class="form-label">Fecha Hasta</label>
                        <input type="date" class="form-control" name="fecha_hasta" value="<?= htmlspecialchars($filtros['fecha_hasta']) ?>"></div>
                    <div class="col-md-4"><label class="form-label d-block">&nbsp;</label>
                        <small class="text-muted"><i class="bi bi-info-circle me-1"></i>Si solo llenas "Fecha Desde", se buscará hasta hoy</small></div>
                </div>
                <div class="row mt-4">
                    <div class="col-12 d-flex gap-2 justify-content-end">
                        <button type="submit" name="limpiar" class="btn btn-secondary px-4 fw-bold"><i class="bi bi-eraser-fill me-1"></i>Limpiar Filtros</button>
                        <button type="submit" name="ejecutar" class="btn btn-prim px-4 fw-bold"><i class="bi bi-search me-1"></i>Ejecutar Búsqueda</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php if ($mensajeError): ?>
    <div class="alert alert-warning shadow-sm border-0 border-start border-warning border-4" role="alert">
        <i class="bi bi-info-circle-fill me-2"></i><?= htmlspecialchars($mensajeError) ?>
    </div>
    <?php endif; ?>

    <?php if ($busquedaEjecutada && $hayBusqueda && empty($mensajeError)): ?>
        <?php if ($total > 0): ?>
        <div class="info-bar d-flex justify-content-between align-items-center">
            <span><i class="bi bi-bar-chart-fill me-2"></i>Total: <strong><?= number_format($total) ?></strong> registros coincidentes.</span>
            <span class="badge bg-primary fs-6">Pág <?= $paginaActual ?> de <?= $totalPaginas ?></span>
        </div>
        <div class="table-container table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead><tr>
                    <th class="ps-4">Expediente</th><th>F. Entrada</th><th>Legajo</th>
                    <th>Demandante</th><th>Demandado</th><th>Motivo / Delito</th>
                    <th>Tribunal</th><th style="width:220px;">Ubicación</th>
                    <th class="pe-4 text-center">Acciones</th>
                </tr></thead>
                <tbody class="table-group-divider">
                <?php foreach ($resultados as $f): ?>
                <?php
                    $fL  = date("d/m/Y", strtotime($f['fecha_entrada']));
                    $nd  = !empty($f['demandante'])   ? mb_strtoupper($f['demandante'],   'UTF-8') : '---';
                    $ndo = !empty($f['demandado'])    ? mb_strtoupper($f['demandado'],    'UTF-8') : '---';
                    $mo  = !empty($f['motivo_delito'])? mb_strtoupper($f['motivo_delito'],'UTF-8') : '---';
                ?>
                <tr>
                    <td class="ps-4"><strong class="text-primary"><?= htmlspecialchars($f['n_expediente']) ?></strong></td>
                    <td><?= $fL ?></td>
                    <td><?= htmlspecialchars(preg_replace('/\.0$/','',$f['n_legajo']??'---')) ?></td>
                    <td><?= htmlspecialchars($nd) ?><br><span class="cedula-badge"><i class="bi bi-person-vcard text-muted me-1"></i><?= htmlspecialchars($f['cedula_rif_demandante']) ?></span></td>
                    <td><?= htmlspecialchars($ndo) ?><br><span class="cedula-badge"><i class="bi bi-person-vcard text-muted me-1"></i><?= htmlspecialchars($f['cedula_rif_demandado']) ?></span></td>
                    <td><small class="text-muted fw-bold"><?= htmlspecialchars($mo) ?></small></td>
                    <td title="<?= htmlspecialchars($f['tribunal']??'Tribunal '.$f['id_tribunal']) ?>">
                        <span class="badge border border-secondary text-secondary text-wrap">
                            <strong>Trib. <?= htmlspecialchars($f['id_tribunal']) ?></strong>
                            <?php if(!empty($f['tribunal'])): ?><br><span class="tribunal-completo"><?= htmlspecialchars($f['tribunal']) ?></span><?php endif; ?>
                        </span></td>
                    <td class="sede-cell">
                        <?php if(!empty($f['nombre_sede'])): ?>
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="sede-truncate flex-grow-1" title="<?= htmlspecialchars($f['nombre_sede']) ?>">
                                <i class="bi bi-geo-alt-fill text-success me-1"></i><span><?= htmlspecialchars($f['nombre_sede']) ?></span></div>
                            <button class="btn btn-sm ms-2" style="background-color:#004085;color:white;flex-shrink:0;" onclick="verUbicacion(<?= $f['Id'] ?>)" title="Ver ubicación"><i class="bi bi-eye-fill"></i></button>
                        </div>
                        <?php else: ?><span class="text-muted"><small>Sin ubicación</small></span><?php endif; ?>
                    </td>
                    <td class="pe-4 text-center">
                        <div class="btn-group" role="group">
                            <a href="/expediente/<?= urlencode($f['Id']) ?>?search=<?= urlencode($filtros['expediente']) ?>" class="btn btn-sm btn-primary" title="Ver historial"><i class="bi bi-clock-history me-1"></i>Ver más</a>
                            <a href="/editar/<?= urlencode($f['Id']) ?>" class="btn btn-sm btn-warning text-white" title="Editar"><i class="bi bi-pencil-square"></i></a>
                            <a href="/imprimir/<?= urlencode($f['Id']) ?>" class="btn btn-sm btn-success" title="Imprimir"><i class="bi bi-printer-fill"></i></a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php if($totalPaginas>1): ?>
        <nav class="mt-4 d-flex justify-content-center"><ul class="pagination shadow-sm">
            <?php
            renderPaginaBtn($paginaActual-1,'<i class="bi bi-chevron-left"></i> Ant',$paginaActual<=1);
            $s=max(1,$paginaActual-2); $e=min($totalPaginas,$paginaActual+2);
            if($s>1){renderPaginaBtn(1,'1'); if($s>2) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';}
            for($i=$s;$i<=$e;$i++) renderPaginaBtn($i,(string)$i,false,$i==$paginaActual);
            if($e<$totalPaginas){if($e<$totalPaginas-1) echo '<li class="page-item disabled"><span class="page-link">...</span></li>'; renderPaginaBtn($totalPaginas,(string)$totalPaginas);}
            renderPaginaBtn($paginaActual+1,'Sig <i class="bi bi-chevron-right"></i>',$paginaActual>=$totalPaginas);
            ?>
        </ul></nav>
        <?php endif; ?>
        <?php else: ?>
        <div class="alert alert-danger d-flex align-items-center shadow-sm border-0 border-start border-danger border-4 mt-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
            <div><strong>Sin coincidencias.</strong> No se hallaron expedientes con los filtros suministrados.</div>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- Modal Ubicación -->
<div class="modal fade" id="modalUbicacion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border:2px solid #004085;">
            <div class="modal-header" style="background:linear-gradient(135deg,#004085,#0056b3);color:#fff;">
                <h5 class="modal-title"><i class="bi bi-geo-alt-fill me-2"></i>Ubicación Física del Expediente</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4" id="contenidoUbicacion">
                <div class="text-center py-5"><div class="spinner-border text-success" role="status"></div><p class="mt-3 text-muted">Cargando...</p></div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const BASE_URL = '<?= BASE_URL ?>';

function verUbicacion(id){
    const m=new bootstrap.Modal(document.getElementById('modalUbicacion'));
    document.getElementById('contenidoUbicacion').innerHTML='<div class="text-center py-5"><div class="spinner-border text-success" role="status"></div><p class="mt-3 text-muted">Cargando...</p></div>';
    m.show();
    
    fetch(BASE_URL + '/obtener_ubicacion?id='+id)
        .then(r => r.json())
        .then(data => {
            if (data.error) {
                document.getElementById('contenidoUbicacion').innerHTML = '<div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i>' + data.mensaje + '</div>';
                return;
            }
            const d = data.datos;
            const html = `
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="p-3 border rounded bg-light h-100">
                            <h6 class="text-muted fw-bold text-uppercase mb-1"><i class="bi bi-building me-2"></i>Sede</h6>
                            <div class="fs-5 fw-semibold text-primary mb-2">${d.nombre_sede ? d.nombre_sede : '<span class="text-danger">No asignada</span>'}</div>
                            ${d.sede_direccion ? `<small class="text-muted"><i class="bi bi-map me-1"></i>${d.sede_direccion}</small>` : ''}
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 border rounded bg-light h-100">
                            <h6 class="text-muted fw-bold text-uppercase mb-1"><i class="bi bi-box-seam me-2"></i>Área / Nivel</h6>
                            <div class="fs-5 fw-semibold text-dark">${d.ubicacion_area ? d.ubicacion_area : '<span class="text-muted">N/A</span>'}</div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="p-3 border rounded bg-light">
                            <h6 class="text-muted fw-bold text-uppercase mb-1"><i class="bi bi-card-text me-2"></i>Detalle de Ubicación</h6>
                            <div class="text-dark">${d.ubicacion_detalle ? d.ubicacion_detalle : '<span class="text-muted">Sin detalles adicionales</span>'}</div>
                        </div>
                    </div>
                    <div class="col-12 text-end mt-2">
                        <small class="text-muted"><i class="bi bi-clock-history me-1"></i>Última actualización: <strong>${d.fecha_formateada}</strong></small>
                    </div>
                </div>
            `;
            document.getElementById('contenidoUbicacion').innerHTML = html;
        })
        .catch(err => {
            console.error('Error fetching ubicacion:', err);
            document.getElementById('contenidoUbicacion').innerHTML='<div class="alert alert-danger"><i class="bi bi-wifi-off me-2"></i>Error al conectar con el servidor para cargar la ubicación.</div>';
        });
}
</script>
</body></html>
