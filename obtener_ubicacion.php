<?php
require_once "conexion.php";
require_once "auth_check_ajax.php";

header('Content-Type: application/json; charset=UTF-8');

$id = trim($_GET['id'] ?? '');

if (empty($id)) {
    echo json_encode([
        'error' => true,
        'mensaje' => 'ID de expediente no proporcionado'
    ]);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT 
            m.Id,
            m.n_expediente,
            m.demandante,
            m.demandado,
            m.id_sede,
            s.nombre_sede,
            s.direccion as sede_direccion,
            m.ubicacion_area,
            m.ubicacion_detalle,
            m.fecha_ultima_ubicacion
        FROM maestro m
        LEFT JOIN sedes_deposito s ON m.id_sede = s.id_sede
        WHERE m.Id = :id
        LIMIT 1
    ");
    
    $stmt->execute([':id' => $id]);
    $expediente = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$expediente) {
        echo json_encode([
            'error' => true,
            'mensaje' => 'Expediente no encontrado'
        ]);
        exit;
    }
    
    if (!empty($expediente['fecha_ultima_ubicacion'])) {
        $fecha = new DateTime($expediente['fecha_ultima_ubicacion']);
        $expediente['fecha_formateada'] = $fecha->format('d/m/Y H:i');
    } else {
        $expediente['fecha_formateada'] = 'No registrada';
    }
    
    $expediente['tiene_ubicacion'] = !empty($expediente['nombre_sede']);
    
    echo json_encode([
        'error' => false,
        'datos' => $expediente
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'error' => true,
        'mensaje' => 'Error al consultar la base de datos: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'error' => true,
        'mensaje' => 'Error del sistema: ' . $e->getMessage()
    ]);
}
