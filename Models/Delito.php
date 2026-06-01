<?php
/**
 * Models/Delito.php
 * Modelo de la tabla delitos.
 * Centraliza las operaciones de catálogo de delitos para los administradores.
 */
class Delito {

    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /**
     * Obtiene todos los delitos ordenados alfabéticamente.
     */
    public function all(): array {
        $stmt = $this->db->query("SELECT * FROM delitos ORDER BY nombre_delito ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Encuentra un delito por su ID.
     */
    public function find(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM delitos WHERE id_delito = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Crea un nuevo delito en el catálogo.
     */
    public function create(string $nombre): bool {
        $nombre = mb_strtoupper(trim($nombre), 'UTF-8');
        if (empty($nombre)) return false;
        try {
            $stmt = $this->db->prepare("INSERT INTO delitos (nombre_delito) VALUES (:nombre)");
            return $stmt->execute([':nombre' => $nombre]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Actualiza el nombre de un delito.
     */
    public function update(int $id, string $nombre): bool {
        $nombre = mb_strtoupper(trim($nombre), 'UTF-8');
        if (empty($nombre)) return false;
        try {
            $stmt = $this->db->prepare("UPDATE delitos SET nombre_delito = :nombre WHERE id_delito = :id");
            return $stmt->execute([':nombre' => $nombre, ':id' => $id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Elimina un delito del catálogo.
     */
    public function delete(int $id): bool {
        try {
            $stmt = $this->db->prepare("DELETE FROM delitos WHERE id_delito = :id");
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            return false;
        }
    }
}
