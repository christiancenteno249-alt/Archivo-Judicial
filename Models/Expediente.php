<?php
/**
 * Models/Expediente.php
 * Modelo de la tabla maestro + historial_movimientos.
 * Centraliza toda la logica de acceso a datos relacionada con expedientes.
 */
class Expediente {

    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /**
     * Busca expedientes con filtros opcionales y paginacion.
     *
     * @param array $filtros  Campos de busqueda (expediente, demandante, etc.)
     * @param int   $pagina   Pagina actual (1-based)
     * @param int   $porPagina Resultados por pagina
     * @return array ['resultados' => [], 'total' => int, 'totalPaginas' => int]
     */
    public function buscar(array $filtros, int $pagina = 1, int $porPagina = 10): array {
        $base = " FROM maestro m
                  LEFT JOIN tribunales t ON m.id_tribunal = t.id_tribunal
                  LEFT JOIN sedes_deposito s ON m.id_sede = s.id_sede
                  WHERE 1=1";

        $condiciones = [];
        $params = [];

        if (!empty($filtros['expediente'])) {
            $condiciones[] = "m.n_expediente LIKE :expediente";
            $params[':expediente'] = '%' . $filtros['expediente'] . '%';
        }
        if (!empty($filtros['n_legajo'])) {
            $condiciones[] = "m.n_legajo LIKE :n_legajo";
            $params[':n_legajo'] = '%' . $filtros['n_legajo'] . '%';
        }
        if (!empty($filtros['demandante'])) {
            $condiciones[] = "m.demandante LIKE :demandante";
            $params[':demandante'] = '%' . $filtros['demandante'] . '%';
        }
        if (!empty($filtros['ced_dante'])) {
            $tipo = $filtros['tipo_dante'] ?? 'V';
            $condiciones[] = "m.cedula_rif_demandante LIKE :ced_dante";
            $params[':ced_dante'] = '%' . $tipo . $filtros['ced_dante'] . '%';
        }
        if (!empty($filtros['demandado'])) {
            $condiciones[] = "m.demandado LIKE :demandado";
            $params[':demandado'] = '%' . $filtros['demandado'] . '%';
        }
        if (!empty($filtros['ced_dado'])) {
            $tipo = $filtros['tipo_dado'] ?? 'V';
            $condiciones[] = "m.cedula_rif_demandado LIKE :ced_dado";
            $params[':ced_dado'] = '%' . $tipo . $filtros['ced_dado'] . '%';
        }
        if (!empty($filtros['fecha'])) {
            $condiciones[] = "DATE(m.fecha_entrada) = :fecha";
            $params[':fecha'] = $filtros['fecha'];
        }
        if (!empty($filtros['fecha_desde']) && !empty($filtros['fecha_hasta'])) {
            $condiciones[] = "DATE(m.fecha_entrada) BETWEEN :fecha_desde AND :fecha_hasta";
            $params[':fecha_desde'] = $filtros['fecha_desde'];
            $params[':fecha_hasta'] = $filtros['fecha_hasta'];
        } elseif (!empty($filtros['fecha_desde'])) {
            $condiciones[] = "DATE(m.fecha_entrada) >= :fecha_desde";
            $params[':fecha_desde'] = $filtros['fecha_desde'];
        } elseif (!empty($filtros['fecha_hasta'])) {
            $condiciones[] = "DATE(m.fecha_entrada) <= :fecha_hasta";
            $params[':fecha_hasta'] = $filtros['fecha_hasta'];
        }

        $where = count($condiciones) > 0 ? ' AND ' . implode(' AND ', $condiciones) : '';

        // Total de registros
        $stmtCount = $this->db->prepare("SELECT COUNT(DISTINCT m.Id) as total" . $base . $where);
        $stmtCount->execute($params);
        $total = (int)$stmtCount->fetch()['total'];
        $totalPaginas = (int)ceil($total / $porPagina);

        if ($pagina > $totalPaginas && $totalPaginas > 0) {
            $pagina = $totalPaginas;
        }
        $offset = ($pagina - 1) * $porPagina;

        // Resultados paginados
        $sql = "SELECT m.*, ANY_VALUE(t.tribunal) AS tribunal, ANY_VALUE(s.nombre_sede) AS nombre_sede"
             . $base . $where
             . " GROUP BY m.Id ORDER BY m.fecha_entrada DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => &$val) {
            $stmt->bindParam($key, $val);
        }
        $stmt->bindValue(':limit',  $porPagina, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset,    PDO::PARAM_INT);
        $stmt->execute();

        return [
            'resultados'   => $stmt->fetchAll(),
            'total'        => $total,
            'totalPaginas' => $totalPaginas,
            'paginaActual' => $pagina,
        ];
    }

    /**
     * Obtiene un expediente por su ID unico (PK).
     */
    public function obtenerPorId(int $id): ?array {
        $stmt = $this->db->prepare(
            "SELECT m.*, t.tribunal
             FROM maestro m
             INNER JOIN tribunales t ON m.id_tribunal = t.id_tribunal
             WHERE m.Id = :id
             LIMIT 1"
        );
        $stmt->execute([':id' => $id]);
        $resultado = $stmt->fetch();
        return $resultado ?: null;
    }

    /**
     * Obtiene el historial de movimientos de un expediente por su numero.
     */
    public function obtenerHistorial(string $n_expediente): array {
        $stmt = $this->db->prepare(
            "SELECT h.*, t.tribunal, u.nombre_full AS usuario_nombre
             FROM historial_movimientos h
             INNER JOIN tribunales t ON h.id_tribunal = t.id_tribunal
             LEFT JOIN usuarios_sistema u ON h.id_usuario = u.id_usuario
             WHERE h.n_expediente = :n_expediente
             ORDER BY h.fecha_movimiento DESC"
        );
        $stmt->execute([':n_expediente' => $n_expediente]);
        return $stmt->fetchAll();
    }

    /**
     * Guarda o actualiza un expediente y registra en historial.
     * Logica identica a registrar.php legacy.
     *
     * @return array ['esNuevo' => bool, 'registro' => array, 'cambios' => array]
     */
    public function guardar(array $datos, int $idUsuario): array {
        // Verificar si el expediente ya existe
        $stmtCheck = $this->db->prepare("SELECT * FROM maestro WHERE n_expediente = :n_expediente LIMIT 1");
        $stmtCheck->execute([':n_expediente' => $datos['n_expediente']]);
        $existente = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        $this->db->beginTransaction();
        $esNuevo = false;
        $cambios = [];

        try {
            if (!$existente) {
                // CREAR nuevo expediente
                $stmt = $this->db->prepare(
                    "INSERT INTO maestro (n_expediente, fecha_entrada, id_tribunal, demandante,
                     cedula_rif_demandante, demandado, cedula_rif_demandado, motivo_delito, n_legajo, observaciones)
                     VALUES (:n_expediente, :fecha_entrada, :id_tribunal, :demandante,
                     :cedula_rif_demandante, :demandado, :cedula_rif_demandado, :motivo_delito, :n_legajo, :observaciones)"
                );
                $stmt->execute([
                    ':n_expediente'          => $datos['n_expediente'],
                    ':fecha_entrada'         => $datos['fecha_entrada'],
                    ':id_tribunal'           => $datos['id_tribunal'],
                    ':demandante'            => $datos['demandante'],
                    ':cedula_rif_demandante' => $datos['cedula_rif_demandante'],
                    ':demandado'             => $datos['demandado'],
                    ':cedula_rif_demandado'  => $datos['cedula_rif_demandado'],
                    ':motivo_delito'         => $datos['motivo_delito'],
                    ':n_legajo'              => $datos['n_legajo'],
                    ':observaciones'         => $datos['observaciones'],
                ]);
                $esNuevo = true;
            } else {
                // ACTUALIZAR expediente existente — detectar cambios
                $campos = ['id_tribunal','fecha_entrada','demandante','cedula_rif_demandante',
                           'demandado','cedula_rif_demandado','motivo_delito','n_legajo','observaciones'];
                $nombres = [
                    'id_tribunal' => 'Tribunal', 'fecha_entrada' => 'Fecha de Entrada',
                    'demandante'  => 'Demandante', 'cedula_rif_demandante' => 'CI/RIF Demandante',
                    'demandado'   => 'Demandado',  'cedula_rif_demandado'  => 'CI/RIF Demandado',
                    'motivo_delito' => 'Motivo/Delito', 'n_legajo' => 'Nro Legajo',
                    'observaciones' => 'Observaciones',
                ];

                foreach ($campos as $campo) {
                    $viejo = trim((string)($existente[$campo] ?? ''));
                    $nuevo = trim((string)($datos[$campo] ?? ''));
                    $diferente = ($campo === 'id_tribunal')
                        ? ((int)$viejo !== (int)$nuevo)
                        : ($viejo !== $nuevo);
                    if ($diferente) {
                        $cambios[] = "[CAMBIO] {$nombres[$campo]}: '{$viejo}' -> '{$nuevo}'";
                    }
                }

                $stmt = $this->db->prepare(
                    "UPDATE maestro SET id_tribunal=:id_tribunal, fecha_entrada=:fecha_entrada,
                     demandante=:demandante, cedula_rif_demandante=:cedula_rif_demandante,
                     demandado=:demandado, cedula_rif_demandado=:cedula_rif_demandado,
                     motivo_delito=:motivo_delito, n_legajo=:n_legajo, observaciones=:observaciones
                     WHERE n_expediente=:n_expediente"
                );
                $stmt->execute([
                    ':id_tribunal'           => $datos['id_tribunal'],
                    ':fecha_entrada'         => $datos['fecha_entrada'],
                    ':demandante'            => $datos['demandante'],
                    ':cedula_rif_demandante' => $datos['cedula_rif_demandante'],
                    ':demandado'             => $datos['demandado'],
                    ':cedula_rif_demandado'  => $datos['cedula_rif_demandado'],
                    ':motivo_delito'         => $datos['motivo_delito'],
                    ':n_legajo'              => $datos['n_legajo'],
                    ':observaciones'         => $datos['observaciones'],
                    ':n_expediente'          => $datos['n_expediente'],
                ]);
            }

            // SIEMPRE registrar en historial_movimientos
            $stmtH = $this->db->prepare(
                "INSERT INTO historial_movimientos (n_expediente, id_tribunal, fecha_movimiento, observaciones, id_usuario)
                 VALUES (:n_expediente, :id_tribunal, NOW(), :observaciones, :id_usuario)"
            );
            $stmtH->execute([
                ':n_expediente' => $datos['n_expediente'],
                ':id_tribunal'  => $datos['id_tribunal'],
                ':observaciones'=> $datos['observaciones'],
                ':id_usuario'   => $idUsuario,
            ]);

            $this->db->commit();

            // Recargar el registro guardado para confirmacion
            $stmtV = $this->db->prepare(
                "SELECT m.*, t.tribunal AS nombre_tribunal
                 FROM maestro m LEFT JOIN tribunales t ON m.id_tribunal = t.id_tribunal
                 WHERE m.n_expediente = :n_expediente LIMIT 1"
            );
            $stmtV->execute([':n_expediente' => $datos['n_expediente']]);
            $registro = $stmtV->fetch();

            return ['esNuevo' => $esNuevo, 'registro' => $registro, 'cambios' => $cambios];

        } catch (PDOException $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Actualiza un expediente por ID primario (edicion directa desde buscador).
     *
     * @return array ['cambios' => [], 'filasAfectadas' => int]
     */
    public function actualizar(int $id, array $datos, array $registroAnterior): array {
        $campos = ['n_expediente','id_tribunal','fecha_entrada','demandante','cedula_rif_demandante',
                   'demandado','cedula_rif_demandado','motivo_delito','n_legajo','observaciones'];
        $nombres = [
            'n_expediente' => 'Nro Expediente', 'id_tribunal' => 'Tribunal',
            'fecha_entrada'=> 'Fecha de Entrada', 'demandante' => 'Demandante',
            'cedula_rif_demandante' => 'CI/RIF Demandante', 'demandado' => 'Demandado',
            'cedula_rif_demandado'  => 'CI/RIF Demandado',  'motivo_delito' => 'Motivo/Delito',
            'n_legajo' => 'Nro Legajo', 'observaciones' => 'Observaciones',
        ];

        $cambios = [];
        foreach ($campos as $campo) {
            $viejo = trim((string)($registroAnterior[$campo] ?? ''));
            $nuevo = trim((string)($datos[$campo] ?? ''));
            $diferente = ($campo === 'id_tribunal')
                ? ((int)$viejo !== (int)$nuevo)
                : ($viejo !== $nuevo);
            if ($diferente) {
                $cambios[] = "[CAMBIO] {$nombres[$campo]}: '{$viejo}' -> '{$nuevo}'";
            }
        }

        $stmt = $this->db->prepare(
            "UPDATE maestro SET n_expediente=:n_expediente, id_tribunal=:id_tribunal,
             fecha_entrada=:fecha_entrada, demandante=:demandante,
             cedula_rif_demandante=:cedula_rif_demandante, demandado=:demandado,
             cedula_rif_demandado=:cedula_rif_demandado, motivo_delito=:motivo_delito,
             n_legajo=:n_legajo, observaciones=:observaciones
             WHERE Id = :id LIMIT 1"
        );
        $stmt->execute([
            ':n_expediente'          => $datos['n_expediente'],
            ':id_tribunal'           => $datos['id_tribunal'],
            ':fecha_entrada'         => $datos['fecha_entrada'],
            ':demandante'            => $datos['demandante'],
            ':cedula_rif_demandante' => $datos['cedula_rif_demandante'],
            ':demandado'             => $datos['demandado'],
            ':cedula_rif_demandado'  => $datos['cedula_rif_demandado'],
            ':motivo_delito'         => $datos['motivo_delito'],
            ':n_legajo'              => $datos['n_legajo'],
            ':observaciones'         => $datos['observaciones'],
            ':id'                    => $id,
        ]);

        return ['cambios' => $cambios, 'filasAfectadas' => $stmt->rowCount()];
    }

    /**
     * Obtiene la lista de tribunales ordenada alfabeticamente.
     */
    public function obtenerTribunales(): array {
        $stmt = $this->db->query("SELECT id_tribunal, tribunal FROM tribunales ORDER BY tribunal ASC");
        return $stmt->fetchAll();
    }

    /**
     * Obtiene la lista de delitos del catálogo.
     */
    public function obtenerDelitos(): array {
        $stmt = $this->db->query("SELECT id_delito, nombre_delito FROM delitos ORDER BY nombre_delito ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Inserta un delito en la base de datos si no existe en el catálogo.
     */
    public function asegurarDelitoExiste(string $nombreDelito): void {
        $nombreDelito = mb_strtoupper(trim($nombreDelito), 'UTF-8');
        if (empty($nombreDelito)) return;

        $stmt = $this->db->prepare("SELECT COUNT(*) FROM delitos WHERE nombre_delito = :nombre");
        $stmt->execute([':nombre' => $nombreDelito]);
        if ((int)$stmt->fetchColumn() === 0) {
            $stmtInsert = $this->db->prepare("INSERT INTO delitos (nombre_delito) VALUES (:nombre)");
            $stmtInsert->execute([':nombre' => $nombreDelito]);
        }
    }

    /**
     * Verifica si un numero de expediente ya existe en otro registro (para edicion).
     */
    public function existeExpedienteOtroId(string $nExpediente, int $idExcluir): bool {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM maestro WHERE n_expediente = :n_expediente AND Id != :id"
        );
        $stmt->execute([':n_expediente' => $nExpediente, ':id' => $idExcluir]);
        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * Obtiene el nombre de un tribunal dado su ID.
     */
    public function obtenerNombreTribunal(int $idTribunal): string {
        $stmt = $this->db->prepare(
            "SELECT tribunal FROM tribunales WHERE id_tribunal = :id LIMIT 1"
        );
        $stmt->execute([':id' => $idTribunal]);
        $row = $stmt->fetch();
        return $row ? $row['tribunal'] : '';
    }
}
