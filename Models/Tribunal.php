<?php
/**
 * Models/Tribunal.php
 * Modelo de la tabla tribunales.
 * Centraliza las operaciones CRUD del catálogo de tribunales para administradores.
 */
class Tribunal {

    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /**
     * Obtiene todos los tribunales ordenados por ID.
     */
    public function all(): array {
        $stmt = $this->db->query("SELECT Id_tribunal, tribunal FROM tribunales ORDER BY Id_tribunal ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Encuentra un tribunal por su ID.
     */
    public function find(int $id): ?array {
        $stmt = $this->db->prepare("SELECT Id_tribunal, tribunal FROM tribunales WHERE Id_tribunal = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Obtiene el siguiente ID disponible (ya que Id_tribunal no es AUTO_INCREMENT).
     */
    private function siguienteId(): int {
        $stmt = $this->db->query("SELECT COALESCE(MAX(Id_tribunal), 0) + 1 AS siguiente FROM tribunales");
        return (int)$stmt->fetchColumn();
    }

    /**
     * Crea un nuevo tribunal en el catálogo.
     */
    public function create(string $nombre): bool {
        $nombre = mb_strtoupper(trim($nombre), 'UTF-8');
        if (empty($nombre)) return false;
        try {
            $nuevoId = $this->siguienteId();
            $stmt = $this->db->prepare("INSERT INTO tribunales (tribunal, Id_tribunal) VALUES (:tribunal, :id)");
            return $stmt->execute([':tribunal' => $nombre, ':id' => $nuevoId]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Actualiza el nombre de un tribunal.
     */
    public function update(int $id, string $nombre): bool {
        $nombre = mb_strtoupper(trim($nombre), 'UTF-8');
        if (empty($nombre)) return false;
        try {
            $stmt = $this->db->prepare("UPDATE tribunales SET tribunal = :tribunal WHERE Id_tribunal = :id");
            return $stmt->execute([':tribunal' => $nombre, ':id' => $id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Elimina un tribunal del catálogo.
     */
    public function delete(int $id): bool {
        try {
            $stmt = $this->db->prepare("DELETE FROM tribunales WHERE Id_tribunal = :id");
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Verifica si un tribunal con ese nombre ya existe (para evitar duplicados al crear).
     */
    public function existePorNombre(string $nombre, int $excluirId = 0): bool {
        $nombre = mb_strtoupper(trim($nombre), 'UTF-8');
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM tribunales WHERE UPPER(TRIM(tribunal)) = :nombre AND Id_tribunal != :id"
        );
        $stmt->execute([':nombre' => $nombre, ':id' => $excluirId]);
        return (int)$stmt->fetchColumn() > 0;
    }
}
