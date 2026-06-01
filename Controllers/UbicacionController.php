<?php
/**
 * Controllers/UbicacionController.php
 * Controlador de Gestión de Ubicaciones Físicas de Expedientes.
 */
class UbicacionController extends Controller {

    /**
     * Muestra la interfaz de gestión de ubicaciones y procesa las peticiones POST.
     */
    public function index(): void {
        $this->requireAuth();

        $mensaje = '';
        $tipoAlerta = '';
        $modo = $_GET['modo'] ?? 'individual';

        // Mantener valores de sesión para UX rápida
        if (!isset($_SESSION['ultima_sede'])) $_SESSION['ultima_sede'] = '';
        if (!isset($_SESSION['ultima_area'])) $_SESSION['ultima_area'] = '';

        // Cargar sedes activas
        $sedes = [];
        try {
            $stmtSedes = $this->db->query(
                "SELECT id_sede, nombre_sede, descripcion, direccion 
                 FROM sedes_deposito 
                 WHERE activo = 1 
                 ORDER BY nombre_sede ASC"
            );
            $sedes = $stmtSedes->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $mensaje = "Error al cargar sedes: " . $e->getMessage();
            $tipoAlerta = 'danger';
        }

        $expedienteEncontrado = null;
        $ubicacionActual = null;

        // 1. BUSCAR EXPEDIENTE (INDIVIDUAL)
        if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['buscar_expediente'])) {
            $n_expediente = trim($_POST['n_expediente_buscar'] ?? '');
            
            if (empty($n_expediente)) {
                $mensaje = 'Ingresa un número de expediente para buscar.';
                $tipoAlerta = 'warning';
            } else {
                try {
                    $stmt = $this->db->prepare(
                        "SELECT Id, n_expediente, demandante, demandado, id_sede, ubicacion_area, ubicacion_detalle 
                         FROM maestro 
                         WHERE n_expediente = :expediente LIMIT 1"
                    );
                    $stmt->execute([':expediente' => $n_expediente]);
                    $expedienteEncontrado = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if (!$expedienteEncontrado) {
                        $mensaje = "Expediente '{$n_expediente}' no encontrado en el sistema.";
                        $tipoAlerta = 'danger';
                    } else {
                        // Cargar ubicación actual
                        if (!empty($expedienteEncontrado['id_sede'])) {
                            $stmtUbicacion = $this->db->prepare(
                                "SELECT nombre_sede, direccion FROM sedes_deposito WHERE id_sede = :id_sede"
                            );
                            $stmtUbicacion->execute([':id_sede' => $expedienteEncontrado['id_sede']]);
                            $ubicacionActual = $stmtUbicacion->fetch(PDO::FETCH_ASSOC);
                        }
                    }
                } catch (PDOException $e) {
                    $mensaje = "Error en la búsqueda: " . $e->getMessage();
                    $tipoAlerta = 'danger';
                }
            }
        }

        // 2. GUARDAR UBICACION INDIVIDUAL
        if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['guardar_ubicacion_individual'])) {
            $this->requireAdmin();

            $id_expediente = trim($_POST['id_expediente'] ?? '');
            $n_expediente = trim($_POST['n_expediente'] ?? '');
            $id_sede = trim($_POST['id_sede'] ?? '');
            $ubicacion_area = trim($_POST['ubicacion_area'] ?? '');
            $ubicacion_detalle = trim($_POST['ubicacion_detalle'] ?? '');
            
            if (empty($id_expediente) || empty($id_sede)) {
                $mensaje = 'Debes seleccionar una sede para asignar la ubicación.';
                $tipoAlerta = 'warning';
            } else {
                try {
                    // Obtener nombre de la sede para auditoría
                    $stmtSede = $this->db->prepare("SELECT nombre_sede FROM sedes_deposito WHERE id_sede = :id_sede");
                    $stmtSede->execute([':id_sede' => $id_sede]);
                    $sede = $stmtSede->fetch(PDO::FETCH_ASSOC);
                    $nombre_sede = $sede['nombre_sede'] ?? 'Desconocida';
                    
                    // Actualizar ubicación
                    $stmtUpdate = $this->db->prepare(
                        "UPDATE maestro 
                         SET id_sede = :id_sede, 
                             ubicacion_area = :ubicacion_area, 
                             ubicacion_detalle = :ubicacion_detalle,
                             fecha_ultima_ubicacion = NOW()
                         WHERE Id = :id LIMIT 1"
                    );
                    $stmtUpdate->execute([
                        ':id_sede' => $id_sede,
                        ':ubicacion_area' => $ubicacion_area,
                        ':ubicacion_detalle' => $ubicacion_detalle,
                        ':id' => $id_expediente
                    ]);
                    
                    // Registrar auditoría
                    $detalle_auditoria = "Cambio de Ubicación: {$n_expediente} movido a {$nombre_sede}";
                    if (!empty($ubicacion_area)) $detalle_auditoria .= " - {$ubicacion_area}";
                    if (!empty($ubicacion_detalle)) $detalle_auditoria .= " - {$ubicacion_detalle}";
                    
                    $this->auditoria('CAMBIO_UBICACION', "Exp: {$n_expediente}", $detalle_auditoria);
                    
                    $_SESSION['ultima_sede'] = $id_sede;
                    $_SESSION['ultima_area'] = $ubicacion_area;
                    
                    $mensaje = "Ubicación asignada correctamente al expediente {$n_expediente}.";
                    $tipoAlerta = 'success';
                } catch (PDOException $e) {
                    $mensaje = "Error al guardar ubicación: " . $e->getMessage();
                    $tipoAlerta = 'danger';
                }
            }
        }

        // 3. GUARDAR UBICACION LOTE
        if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['guardar_ubicacion_lote'])) {
            $this->requireAdmin();

            $expedientes_ids = $_POST['expedientes_seleccionados'] ?? [];
            $id_sede = trim($_POST['id_sede_lote'] ?? '');
            $ubicacion_area = trim($_POST['ubicacion_area_lote'] ?? '');
            $ubicacion_detalle = trim($_POST['ubicacion_detalle_lote'] ?? '');
            
            if (empty($expedientes_ids)) {
                $mensaje = 'Debes seleccionar al menos un expediente para procesar.';
                $tipoAlerta = 'warning';
            } elseif (empty($id_sede)) {
                $mensaje = 'Debes seleccionar una sede para asignar la ubicación.';
                $tipoAlerta = 'warning';
            } else {
                try {
                    // Obtener nombre de la sede
                    $stmtSede = $this->db->prepare("SELECT nombre_sede FROM sedes_deposito WHERE id_sede = :id_sede");
                    $stmtSede->execute([':id_sede' => $id_sede]);
                    $sede = $stmtSede->fetch(PDO::FETCH_ASSOC);
                    
                    if (!$sede) {
                        throw new Exception("Sede no encontrada");
                    }
                    $nombre_sede = $sede['nombre_sede'];
                    
                    $actualizados = 0;
                    $expedientes_procesados = [];
                    
                    $this->db->beginTransaction();
                    
                    foreach ($expedientes_ids as $id_expediente) {
                        $id_expediente = (int)$id_expediente;
                        
                        // Verificar que existe
                        $stmtCheck = $this->db->prepare("SELECT n_expediente FROM maestro WHERE Id = :id LIMIT 1");
                        $stmtCheck->execute([':id' => $id_expediente]);
                        $exp_data = $stmtCheck->fetch(PDO::FETCH_ASSOC);
                        
                        if ($exp_data) {
                            $n_expediente = $exp_data['n_expediente'];
                            
                            $stmtUpdate = $this->db->prepare(
                                "UPDATE maestro 
                                 SET id_sede = :id_sede, 
                                     ubicacion_area = :ubicacion_area, 
                                     ubicacion_detalle = :ubicacion_detalle,
                                     fecha_ultima_ubicacion = NOW()
                                 WHERE Id = :id LIMIT 1"
                            );
                            $stmtUpdate->execute([
                                ':id_sede' => $id_sede,
                                ':ubicacion_area' => $ubicacion_area,
                                ':ubicacion_detalle' => $ubicacion_detalle,
                                ':id' => $id_expediente
                            ]);
                            
                            // Auditoría
                            $detalle_auditoria = "Cambio de Ubicación (Lote): {$n_expediente} movido a {$nombre_sede}";
                            if (!empty($ubicacion_area)) $detalle_auditoria .= " - {$ubicacion_area}";
                            if (!empty($ubicacion_detalle)) $detalle_auditoria .= " - {$ubicacion_detalle}";
                            
                            $this->auditoria('CAMBIO_UBICACION_LOTE', "Exp: {$n_expediente}", $detalle_auditoria);
                            
                            $actualizados++;
                            $expedientes_procesados[] = $id_expediente;
                        }
                    }
                    
                    $this->db->commit();
                    
                    $_SESSION['ultima_sede'] = $id_sede;
                    $_SESSION['ultima_area'] = $ubicacion_area;
                    
                    if ($actualizados > 0) {
                        $mensaje = "Se actualizaron {$actualizados} expediente(s) correctamente a la ubicación: {$nombre_sede}";
                        $tipoAlerta = 'success';
                        
                        // Emitir script para fadeOut interactivo
                        echo "<script>var expedientesProcesados = " . json_encode($expedientes_procesados) . ";</script>";
                    } else {
                        $mensaje = "No se pudo actualizar ningún expediente.";
                        $tipoAlerta = 'danger';
                    }
                } catch (Exception $e) {
                    if ($this->db->inTransaction()) {
                        $this->db->rollBack();
                    }
                    $mensaje = "Error al procesar el lote: " . $e->getMessage();
                    $tipoAlerta = 'danger';
                }
            }
        }

        // Cargar datos para lote
        $expedientesSinUbicacion = [];
        $totalSinUbicacion = 0;
        
        if ($modo === 'lote') {
            try {
                $stmtCount = $this->db->query("SELECT COUNT(*) as total FROM maestro WHERE id_sede IS NULL OR id_sede = 0 OR id_sede = ''");
                $totalSinUbicacion = (int)$stmtCount->fetch(PDO::FETCH_ASSOC)['total'];
                
                $stmtExp = $this->db->query(
                    "SELECT Id, n_expediente, demandante, demandado, fecha_entrada 
                     FROM maestro 
                     WHERE id_sede IS NULL OR id_sede = 0 OR id_sede = ''
                     ORDER BY fecha_entrada DESC LIMIT 500"
                );
                $expedientesSinUbicacion = $stmtExp->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                $mensaje = "Error al cargar lote: " . $e->getMessage();
                $tipoAlerta = 'danger';
            }
        }

        $this->render('ubicacion/index', compact(
            'mensaje', 'tipoAlerta', 'modo', 'sedes', 
            'expedienteEncontrado', 'ubicacionActual', 
            'expedientesSinUbicacion', 'totalSinUbicacion'
        ));
    }

    /**
     * Endpoint AJAX que devuelve la ubicación detallada de un expediente en JSON.
     */
    public function obtener(): void {
        $this->requireAuth();
        
        header('Content-Type: application/json; charset=UTF-8');
        
        $id = trim($_GET['id'] ?? '');
        if (empty($id)) {
            echo json_encode(['error' => true, 'mensaje' => 'ID no provisto']);
            exit;
        }

        try {
            $stmt = $this->db->prepare(
                "SELECT m.Id, m.n_expediente, m.demandante, m.demandado, m.id_sede,
                        s.nombre_sede, s.direccion as sede_direccion,
                        m.ubicacion_area, m.ubicacion_detalle, m.fecha_ultima_ubicacion
                 FROM maestro m
                 LEFT JOIN sedes_deposito s ON m.id_sede = s.id_sede
                 WHERE m.Id = :id LIMIT 1"
            );
            $stmt->execute([':id' => $id]);
            $exp = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$exp) {
                echo json_encode(['error' => true, 'mensaje' => 'Expediente no encontrado']);
                exit;
            }

            if (!empty($exp['fecha_ultima_ubicacion'])) {
                $fecha = new DateTime($exp['fecha_ultima_ubicacion']);
                $exp['fecha_formateada'] = $fecha->format('d/m/Y H:i');
            } else {
                $exp['fecha_formateada'] = 'No registrada';
            }

            $exp['tiene_ubicacion'] = !empty($exp['nombre_sede']);

            echo json_encode(['error' => false, 'datos' => $exp]);
        } catch (Exception $e) {
            echo json_encode(['error' => true, 'mensaje' => 'Error: ' . $e->getMessage()]);
        }
        exit;
    }
}
