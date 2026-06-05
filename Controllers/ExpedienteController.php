<?php
/**
 * Controllers/ExpedienteController.php
 * Controlador que maneja todo el flujo de expedientes:
 * busqueda, registro, edicion, historial e impresion.
 */
class ExpedienteController extends Controller {

    private Expediente $model;

    public function __construct() {
        parent::__construct();
        require_once __DIR__ . '/../Models/Expediente.php';
        $this->model = new Expediente($this->db);
    }

    // ─── VERIFICAR DUPLICADO (AJAX) ──────────────────────────────────────────
    public function verificar(): void {
        $this->requireAuth();
        $n_expediente = trim($_GET['expediente'] ?? '');
        $id_ignorar = (int)($_GET['ignorar_id'] ?? 0);
        
        header('Content-Type: application/json; charset=utf-8');
        if (empty($n_expediente)) {
            echo json_encode(['existe' => false]);
            return;
        }

        try {
            $id_encontrado = null;
            if ($id_ignorar > 0) {
                $existe = $this->model->existeExpedienteOtroId($n_expediente, $id_ignorar);
            } else {
                $stmt = $this->db->prepare("SELECT Id FROM maestro WHERE n_expediente = :n_expediente LIMIT 1");
                $stmt->execute([':n_expediente' => $n_expediente]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $existe = (bool) $row;
                if ($existe) {
                    $id_encontrado = $row['Id'];
                }
            }
            echo json_encode(['existe' => $existe, 'id' => $id_encontrado]);
        } catch (Exception $e) {
            echo json_encode(['existe' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    // ─── BUSCAR ───────────────────────────────────────────────────────────────

    public function buscar(): void {
        $this->requireAuth();

        $porPagina   = 10;
        $keyFiltros  = 'buscador_filtros';
        $keyEjecutado = 'buscador_ejecutado';
        $keyPagina   = 'buscador_pagina';

        $filtrosDefault = [
            'expediente' => '', 'n_legajo' => '', 'demandante' => '',
            'apellidos_demandante' => '',
            'tipo_dante' => 'V', 'ced_dante' => '', 'demandado' => '',
            'apellidos_demandado' => '',
            'tipo_dado'  => 'V', 'ced_dado'  => '', 'fecha' => '',
            'fecha_desde'=> '', 'fecha_hasta'=> '',
        ];

        // Procesar POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['limpiar'])) {
                unset($_SESSION[$keyFiltros], $_SESSION[$keyEjecutado], $_SESSION[$keyPagina]);
                $this->redirect('/consulta');
            }
            if (isset($_POST['ejecutar'])) {
                $_SESSION[$keyFiltros] = array_map('trim', array_intersect_key($_POST, $filtrosDefault));
                $_SESSION[$keyEjecutado] = true;
                $_SESSION[$keyPagina] = 1;
                $this->redirect('/consulta');
            }
            if (isset($_POST['pagina'])) {
                $p = (int)$_POST['pagina'];
                $_SESSION[$keyPagina] = $p > 0 ? $p : 1;
                $this->redirect('/consulta');
            }
        }

        // Recuperar filtros de sesion
        $filtros = array_merge($filtrosDefault, (array)($_SESSION[$keyFiltros] ?? []));

        // Auto-completar fecha_hasta si falta
        if (!empty($filtros['fecha_desde']) && empty($filtros['fecha_hasta'])) {
            $filtros['fecha_hasta'] = date('Y-m-d');
        }

        $paginaActual    = max(1, (int)($_SESSION[$keyPagina] ?? 1));
        $busquedaEjecutada = !empty($_SESSION[$keyEjecutado]);
        $hayBusqueda     = !empty(array_filter(array_diff_key($filtros, array_flip(['tipo_dante','tipo_dado']))));

        $resultados    = [];
        $total         = 0;
        $totalPaginas  = 0;
        $mensajeError  = '';

        if ($busquedaEjecutada) {
            if ($hayBusqueda) {
                try {
                    $datos = $this->model->buscar($filtros, $paginaActual, $porPagina);
                    $resultados   = $datos['resultados'];
                    $total        = $datos['total'];
                    $totalPaginas = $datos['totalPaginas'];
                    $paginaActual = $datos['paginaActual'];
                } catch (PDOException $e) {
                    $mensajeError = "Error DB: " . $e->getMessage();
                }
            } else {
                $mensajeError = 'Ingresa al menos un dato para iniciar la búsqueda.';
            }
        }

        $this->render('expediente/buscar', compact(
            'filtros','resultados','total','totalPaginas',
            'paginaActual','busquedaEjecutada','hayBusqueda','mensajeError','porPagina'
        ));
    }

    // ─── REGISTRAR ───────────────────────────────────────────────────────────

    public function registrar(): void {
        $this->requireAuth();

        $tribunales    = $this->model->obtenerTribunales();
        $delitos       = $this->model->obtenerDelitos();
        $mensaje       = '';
        $tipoAlerta    = '';
        $datosImpresion = null;

        // Recuperar flash
        if (!empty($_SESSION['flash_mensaje'])) {
            $mensaje       = $_SESSION['flash_mensaje'];
            $tipoAlerta    = $_SESSION['flash_tipo'];
            $datosImpresion = $_SESSION['flash_datos'] ?? null;
            unset($_SESSION['flash_mensaje'], $_SESSION['flash_tipo'], $_SESSION['flash_datos']);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $esAjax = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';
            $datos  = $this->parsearDatosPost();

            $error = $this->validarCampos($datos);
            if ($error) {
                $this->responderRegistro('warning', $error, null, $esAjax);
                return;
            }

            try {
                // Registrar nuevo delito en el catálogo si es administrador
                if (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'admin') {
                    $this->model->asegurarDelitoExiste($datos['motivo_delito']);
                }

                $resultado = $this->model->guardar($datos, $_SESSION['usuario_id']);
                $reg = $resultado['registro'];

                // Auditoria
                if ($resultado['esNuevo']) {
                    $this->auditoria('CREAR_EXPEDIENTE', "Exp: {$datos['n_expediente']}",
                        "Nuevo expediente.\nTribunal: {$datos['id_tribunal']}\nDemandante: {$datos['demandante']}\nDemandado: {$datos['demandado']}\nLegajo: {$datos['n_legajo']}");
                } else {
                    $accion = !empty($resultado['cambios']) ? 'ACTUALIZAR_EXPEDIENTE' : 'SOBREESCRITURA_POR_DUPLICADO';
                    $this->auditoria($accion, "Exp: {$datos['n_expediente']}", implode("\n", $resultado['cambios']));
                }

                $mensajeFinal = $resultado['esNuevo']
                    ? 'Expediente creado con éxito.'
                    : 'El expediente ya existía. Se actualizó y registró el movimiento en el historial.';

                $datosFlash = [
                    'n_expediente'          => $reg['n_expediente'],
                    'fecha_entrada'         => (strtotime($reg['fecha_entrada']) !== false && strpos($reg['fecha_entrada'], '-') !== false) ? date('d/m/Y', strtotime($reg['fecha_entrada'])) : $reg['fecha_entrada'],
                    'tribunal'              => 'Trib. ' . $reg['id_tribunal'] . ' - ' . $reg['nombre_tribunal'],
                    'demandante'            => $reg['demandante'],
                    'apellidos_demandante'  => $reg['apellidos_demandante'] ?? '',
                    'cedula_rif_demandante' => $reg['cedula_rif_demandante'],
                    'demandado'             => $reg['demandado'],
                    'apellidos_demandado'   => $reg['apellidos_demandado'] ?? '',
                    'cedula_rif_demandado'  => $reg['cedula_rif_demandado'],
                    'motivo_delito'         => $reg['motivo_delito'],
                    'n_legajo'              => $reg['n_legajo'],
                    'observaciones'         => $reg['observaciones'],
                ];
                $this->responderRegistro('success', $mensajeFinal, $datosFlash, $esAjax);

            } catch (PDOException $e) {
                $this->responderRegistro('danger', 'Error al guardar: ' . $e->getMessage(), null, $esAjax);
            }
        }

        $this->render('expediente/registrar', compact('tribunales','delitos','mensaje','tipoAlerta','datosImpresion'));
    }

    // ─── EDITAR ──────────────────────────────────────────────────────────────

    public function editar(): void {
        $this->requireAuth();

        $id = (int)trim($_POST['id'] ?? $_GET['id'] ?? 0);
        if ($id <= 0) {
            $this->redirect('/consulta');
        }

        $registro = $this->model->obtenerPorId($id);
        if (!$registro) {
            $this->redirect('/consulta');
        }

        $tribunales = $this->model->obtenerTribunales();
        $delitos    = $this->model->obtenerDelitos();
        $mensaje    = '';
        $tipoAlerta = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_cambios'])) {
            $datos = $this->parsearDatosPost();

            if (empty($datos['n_expediente']) || empty($datos['fecha_entrada']) ||
                empty($datos['id_tribunal'])  || empty($datos['demandante'])    ||
                empty($datos['apellidos_demandante']) ||
                empty($datos['demandado'])    || empty($datos['apellidos_demandado']) ||
                empty($datos['motivo_delito']) ||
                empty($datos['n_legajo'])) {
                $mensaje    = 'Por favor completa todos los campos obligatorios.';
                $tipoAlerta = 'warning';
            } elseif ($datos['n_expediente'] !== $registro['n_expediente'] &&
                      $this->model->existeExpedienteOtroId($datos['n_expediente'], $id)) {
                $mensaje    = "El número de expediente '{$datos['n_expediente']}' ya existe in otro registro.";
                $tipoAlerta = 'danger';
            } else {
                try {
                    // Registrar nuevo delito en el catálogo si es administrador
                    if (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'admin') {
                        $this->model->asegurarDelitoExiste($datos['motivo_delito']);
                    }

                    $resultado = $this->model->actualizar($id, $datos, $registro);
                    if (!empty($resultado['cambios'])) {
                        $this->auditoria('EDITAR_EXPEDIENTE', "Exp: {$datos['n_expediente']}",
                            implode("\n", $resultado['cambios']));
                        $mensaje    = 'Expediente actualizado. Los cambios fueron registrados en auditoría.';
                        $tipoAlerta = 'success';
                    } else {
                        $mensaje    = 'No se detectaron cambios en el expediente.';
                        $tipoAlerta = 'info';
                    }
                    // Recargar datos actualizados
                    $registro = $this->model->obtenerPorId($id);
                    $delitos = $this->model->obtenerDelitos(); // Recargar delitos
                } catch (PDOException $e) {
                    $mensaje    = 'Error al actualizar: ' . $e->getMessage();
                    $tipoAlerta = 'danger';
                }
            }
        }

        // Separar tipo y numero de cedula para los campos del form
        $tipoDante = 'V'; $cedulaDante = '';
        if (!empty($registro['cedula_rif_demandante'])) {
            $partes = explode('-', $registro['cedula_rif_demandante'], 2);
            if (count($partes) === 2) { $tipoDante = $partes[0]; $cedulaDante = $partes[1]; }
        }
        $tipoDado = 'V'; $cedulaDado = '';
        if (!empty($registro['cedula_rif_demandado'])) {
            $partes = explode('-', $registro['cedula_rif_demandado'], 2);
            if (count($partes) === 2) { $tipoDado = $partes[0]; $cedulaDado = $partes[1]; }
        }

        // Nombre del tribunal actual (para mostrarlo en la vista sin consultar desde la vista)
        $tribActualNombre = $this->model->obtenerNombreTribunal((int)$registro['id_tribunal']);

        $this->render('expediente/editar', compact(
            'registro','tribunales','delitos','mensaje','tipoAlerta',
            'tipoDante','cedulaDante','tipoDado','cedulaDado','id','tribActualNombre'
        ));
    }

    // ─── HISTORIAL ───────────────────────────────────────────────────────────

    public function historial(): void {
        $this->requireAuth();

        $id = (int)trim($_GET['id'] ?? 0);
        if ($id <= 0) {
            $this->redirect('/consulta');
        }

        $expediente = $this->model->obtenerPorId($id);
        if (!$expediente) {
            $this->redirect('/consulta');
        }

        $historial      = $this->model->obtenerHistorial($expediente['n_expediente']);
        $searchOriginal = trim($_GET['search'] ?? '');

        $this->render('expediente/historial', compact('expediente','historial','searchOriginal','id'));
    }

    // ─── IMPRIMIR ────────────────────────────────────────────────────────────

    public function imprimir(): void {
        $this->requireAuth();

        $id = (int)trim($_GET['id'] ?? 0);
        if ($id <= 0) {
            $this->redirect('/consulta');
        }

        $expediente = $this->model->obtenerPorId($id);
        if (!$expediente) {
            $this->redirect('/consulta');
        }

        $this->render('expediente/imprimir', compact('expediente'));
    }

    // ─── HELPERS ─────────────────────────────────────────────────────────────

    /** Extrae y limpia los datos POST del formulario de expediente. */
    private function parsearDatosPost(): array {
        $tipoDante = $_POST['tipo_doc_demandante'] ?? 'V';
        $numDante  = preg_replace('/\D+/', '', $_POST['cedula_rif_demandante'] ?? '');
        $tipoDado  = $_POST['tipo_doc_demandado'] ?? 'V';
        $numDado   = preg_replace('/\D+/', '', $_POST['cedula_rif_demandado'] ?? '');

        return [
            'n_expediente'          => mb_strtoupper(trim($_POST['n_expediente'] ?? ''), 'UTF-8'),
            'fecha_entrada'         => trim($_POST['fecha_entrada'] ?? ''),
            'id_tribunal'           => trim($_POST['id_tribunal'] ?? ''),
            'demandante'            => mb_strtoupper(trim($_POST['demandante'] ?? ''), 'UTF-8'),
            'apellidos_demandante'  => mb_strtoupper(trim($_POST['apellidos_demandante'] ?? ''), 'UTF-8'),
            'tipo_doc_demandante'   => $tipoDante,
            'cedula_rif_demandante' => $numDante ? $tipoDante . '-' . $numDante : '',
            'demandado'             => mb_strtoupper(trim($_POST['demandado'] ?? ''), 'UTF-8'),
            'apellidos_demandado'   => mb_strtoupper(trim($_POST['apellidos_demandado'] ?? ''), 'UTF-8'),
            'tipo_doc_demandado'    => $tipoDado,
            'cedula_rif_demandado'  => $numDado  ? $tipoDado  . '-' . $numDado  : '',
            'motivo_delito'         => mb_strtoupper(trim($_POST['motivo_delito'] ?? ''), 'UTF-8'),
            'n_legajo'              => mb_strtoupper(trim($_POST['n_legajo'] ?? ''), 'UTF-8'),
            'observaciones'         => mb_strtoupper(trim($_POST['observaciones'] ?? ''), 'UTF-8'),
        ];
    }

    /** Valida campos obligatorios y formatos de cedula. Devuelve string de error o vacío. */
    private function validarCampos(array $datos): string {
        if (empty($datos['n_expediente']) || empty($datos['fecha_entrada']) ||
            empty($datos['id_tribunal'])  || empty($datos['demandante'])    ||
            empty($datos['apellidos_demandante']) ||
            empty($datos['demandado'])    || empty($datos['apellidos_demandado']) ||
            empty($datos['motivo_delito']) ||
            empty($datos['n_legajo'])     || empty($datos['cedula_rif_demandante']) ||
            empty($datos['cedula_rif_demandado'])) {
            return 'Por favor completa todos los campos obligatorios (incluyendo apellidos y Cédulas/RIF).';
        }

        foreach ([
            [$datos['tipo_doc_demandante'], preg_replace('/\D+/', '', $datos['cedula_rif_demandante']), 'CI/RIF Demandante'],
            [$datos['tipo_doc_demandado'],  preg_replace('/\D+/', '', $datos['cedula_rif_demandado']),  'CI/RIF Demandado'],
        ] as [$tipo, $num, $etiqueta]) {
            if (!in_array($tipo, ['V','E','J'], true)) return "Tipo de documento inválido en {$etiqueta}.";
            
            if ($tipo === 'J') {
                $min = 10; $max = 10;
                $msg = "exactamente 10";
            } else {
                $min = 5; $max = 9;
                $msg = "entre 5 y 9";
            }

            if (!preg_match('/^\d+$/', $num)) return "El campo {$etiqueta} solo debe contener dígitos.";
            if (strlen($num) < $min || strlen($num) > $max) {
                return "El campo {$etiqueta} debe tener {$msg} dígitos.";
            }
        }

        return '';
    }

    /** Responde al cliente (AJAX o redirect con flash). */
    private function responderRegistro(string $tipo, string $mensaje, ?array $datos, bool $esAjax): void {
        if ($esAjax) {
            if ($tipo === 'success' && $datos !== null) {
                $_SESSION['flash_mensaje'] = $mensaje;
                $_SESSION['flash_tipo']    = $tipo;
                $_SESSION['flash_datos']   = $datos;
            }
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'ok'           => $tipo === 'success',
                'tipo'         => $tipo,
                'mensaje'      => $mensaje,
                'redirect_url' => '/registro',
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $_SESSION['flash_mensaje'] = $mensaje;
        $_SESSION['flash_tipo']    = $tipo;
        if ($datos !== null) $_SESSION['flash_datos'] = $datos;
        else unset($_SESSION['flash_datos']);
        $this->redirect('/registro');
    }
}
