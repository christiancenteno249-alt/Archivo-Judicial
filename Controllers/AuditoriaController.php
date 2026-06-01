<?php
/**
 * Controllers/AuditoriaController.php
 * Controlador para la visualización del registro de auditoría del sistema (Solo Admin).
 */
class AuditoriaController extends Controller {

    /**
     * Muestra la tabla del log de auditoría con filtros y paginación.
     */
    public function index(): void {
        $this->requireAdmin();

        // Parámetros de filtrado
        $filtroAccion = $_GET['accion'] ?? '';
        $filtroUsuario = $_GET['usuario'] ?? '';
        $filtroFechaDesde = $_GET['fecha_desde'] ?? '';
        $filtroFechaHasta = $_GET['fecha_hasta'] ?? '';

        // Paginación
        $registrosPorPagina = 50;
        $paginaActual = isset($_GET['pagina']) && is_numeric($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
        if ($paginaActual < 1) $paginaActual = 1;
        $offset = ($paginaActual - 1) * $registrosPorPagina;

        // Construir consulta con filtros
        $sqlBase = " FROM auditoria_log a 
                     LEFT JOIN usuarios_sistema u ON a.id_usuario = u.id_usuario 
                     WHERE 1=1";
        
        $parametros = [];

        if (!empty($filtroAccion)) {
            $sqlBase .= " AND a.accion = :accion";
            $parametros[':accion'] = $filtroAccion;
        }

        if (!empty($filtroUsuario)) {
            $sqlBase .= " AND a.id_usuario = :usuario";
            $parametros[':usuario'] = $filtroUsuario;
        }

        if (!empty($filtroFechaDesde)) {
            $sqlBase .= " AND DATE(a.fecha_hora) >= :fecha_desde";
            $parametros[':fecha_desde'] = $filtroFechaDesde;
        }

        if (!empty($filtroFechaHasta)) {
            $sqlBase .= " AND DATE(a.fecha_hora) <= :fecha_hasta";
            $parametros[':fecha_hasta'] = $filtroFechaHasta;
        }

        // Contar total de registros
        try {
            $stmtCount = $this->db->prepare("SELECT COUNT(*) as total" . $sqlBase);
            $stmtCount->execute($parametros);
            $totalRegistros = (int)$stmtCount->fetch(PDO::FETCH_ASSOC)['total'];
            $totalPaginas = (int)ceil($totalRegistros / $registrosPorPagina);

            // Obtener registros de auditoría
            $sql = "SELECT a.*, u.nombre_full, u.usuario_nick" . $sqlBase . " ORDER BY a.fecha_hora DESC LIMIT :limit OFFSET :offset";
            $stmt = $this->db->prepare($sql);

            foreach ($parametros as $key => &$val) {
                $stmt->bindParam($key, $val);
            }
            $stmt->bindValue(':limit', $registrosPorPagina, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Obtener lista de acciones únicas para el filtro
            $acciones = $this->db->query("SELECT DISTINCT accion FROM auditoria_log ORDER BY accion")->fetchAll(PDO::FETCH_COLUMN);

            // Obtener lista de usuarios para el filtro
            $usuarios = $this->db->query("
                SELECT id_usuario, nombre_full 
                FROM usuarios_sistema 
                WHERE status = 1 
                   OR id_usuario IN (SELECT DISTINCT id_usuario FROM auditoria_log)
                ORDER BY nombre_full
            ")->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            die("Error en base de datos al cargar auditoría: " . $e->getMessage());
        }

        $this->render('auditoria/index', compact(
            'logs', 'acciones', 'usuarios', 'totalRegistros', 'totalPaginas', 'paginaActual',
            'filtroAccion', 'filtroUsuario', 'filtroFechaDesde', 'filtroFechaHasta'
        ));
    }
}
