<?php
/**
 * Models/Sede.php
 * Modelo para la tabla sedes_deposito.
 */
class Sede {

    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /** Obtiene todas las sedes ordenadas por activo y nombre (filtrando las eliminadas). */
    public function obtenerTodas(): array {
        $stmt = $this->db->query(
            "SELECT * FROM sedes_deposito WHERE eliminado = 0 ORDER BY activo DESC, nombre_sede ASC"
        );
        return $stmt->fetchAll();
    }

    /** Obtiene una sede por ID. */
    public function obtenerPorId(int $id): ?array {
        $stmt = $this->db->prepare(
            "SELECT * FROM sedes_deposito WHERE id_sede = :id"
        );
        $stmt->execute([':id' => $id]);
        $resultado = $stmt->fetch();
        return $resultado ?: null;
    }

    /** Cuenta expedientes agrupados por sede. Devuelve [id_sede => total]. */
    public function contarExpedientesPorSede(): array {
        $stmt = $this->db->query(
            "SELECT s.id_sede, COUNT(m.Id) as total
             FROM sedes_deposito s
             LEFT JOIN maestro m ON s.id_sede = m.id_sede
             GROUP BY s.id_sede"
        );
        $resultado = [];
        foreach ($stmt->fetchAll() as $row) {
            $resultado[$row['id_sede']] = $row['total'];
        }
        return $resultado;
    }

    /** Verifica si ya existe una sede con ese nombre. */
    public function existeNombre(string $nombre): bool {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM sedes_deposito WHERE nombre_sede = :nombre"
        );
        $stmt->execute([':nombre' => $nombre]);
        return (int)$stmt->fetchColumn() > 0;
    }

    /** Crea una nueva sede. */
    public function crear(array $datos): bool {
        $stmt = $this->db->prepare(
            "INSERT INTO sedes_deposito (nombre_sede, direccion, descripcion, activo)
             VALUES (:nombre, :direccion, :descripcion, 1)"
        );
        return $stmt->execute([
            ':nombre'      => $datos['nombre'],
            ':direccion'   => $datos['direccion'],
            ':descripcion' => $datos['descripcion'],
        ]);
    }

    /** Actualiza una sede. */
    public function actualizar(int $id, array $datos): bool {
        $stmt = $this->db->prepare(
            "UPDATE sedes_deposito SET nombre_sede=:nombre, direccion=:direccion,
             descripcion=:descripcion WHERE id_sede=:id"
        );
        return $stmt->execute([
            ':nombre'      => $datos['nombre'],
            ':direccion'   => $datos['direccion'],
            ':descripcion' => $datos['descripcion'],
            ':id'          => $id,
        ]);
    }

    /** Alterna el estado activo/inactivo de la sede. Devuelve el nuevo estado. */
    public function toggleEstado(int $id): ?int {
        $sede = $this->obtenerPorId($id);
        if (!$sede) return null;

        $nuevoEstado = $sede['activo'] == 1 ? 0 : 1;
        $stmt = $this->db->prepare(
            "UPDATE sedes_deposito SET activo = :estado WHERE id_sede = :id"
        );
        $stmt->execute([':estado' => $nuevoEstado, ':id' => $id]);
        return $nuevoEstado;
    }

    /** Borrado logico (eliminado = 1). */
    public function eliminar(int $id): bool {
        $stmt = $this->db->prepare(
            "UPDATE sedes_deposito SET eliminado = 1 WHERE id_sede = :id"
        );
        return $stmt->execute([':id' => $id]);
    }
}
