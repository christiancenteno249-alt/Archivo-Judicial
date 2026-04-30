<?php
/**
 * Funciones de Auditoria del Sistema
 * Registra todas las acciones importantes en la tabla auditoria_log
 */

/**
 * Registra una accion en el log de auditoria
 * 
 * @param string $accion Tipo de accion (LOGIN, LOGOUT, CREAR_EXPEDIENTE, etc.)
 * @param string $recurso Recurso afectado (nombre de expediente, usuario, etc.)
 * @param string $detalles Detalles adicionales en formato JSON o texto
 */
function registrarAccion($accion, $recurso = '', $detalles = '') {
    global $pdo;
    
    // Verificar que la sesion este iniciada
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Capturar ID de usuario de la sesion (si existe)
    $id_usuario = $_SESSION['usuario_id'] ?? null;
    
    // Capturar IP del cliente (mejorado para obtener IP real)
    $ip_address = 'DESCONOCIDA';
    
    // Prioridad 1: IP detras de proxy/load balancer
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip_address = $_SERVER['HTTP_CLIENT_IP'];
    }
    // Prioridad 2: IP forwarded por proxy
    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        // Puede contener multiples IPs separadas por coma, tomar la primera
        $ip_list = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $ip_address = trim($ip_list[0]);
    }
    // Prioridad 3: IP directa
    elseif (!empty($_SERVER['REMOTE_ADDR'])) {
        $ip_address = $_SERVER['REMOTE_ADDR'];
    }
    
    // Si es localhost IPv6, convertir a IPv4
    if ($ip_address === '::1') {
        $ip_address = '127.0.0.1';
    }
    
    try {
        $sql = "INSERT INTO auditoria_log (id_usuario, accion, recurso, detalles, ip_maquina, fecha_hora) 
                VALUES (:id_usuario, :accion, :recurso, :detalles, :ip_maquina, NOW())";
        
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([
            ':id_usuario' => $id_usuario,
            ':accion' => $accion,
            ':recurso' => $recurso,
            ':detalles' => $detalles,
            ':ip_maquina' => $ip_address
        ]);
        
        return $result;
    } catch (PDOException $e) {
        // Log del error para debugging
        error_log("Error en auditoria: " . $e->getMessage());
        // Temporalmente mostrar el error
        if (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'admin') {
            $_SESSION['debug_auditoria'] = $e->getMessage();
        }
        return false;
    }
}

