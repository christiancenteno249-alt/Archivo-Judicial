<?php
/**
 * Models/Usuario.php
 * Modelo para la tabla usuarios_sistema.
 */
class Usuario {

    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /** Obtiene todos los usuarios activos. */
    public function obtenerTodos(): array {
        $stmt = $this->db->query(
            "SELECT * FROM usuarios_sistema WHERE status = 1 ORDER BY fecha_registro DESC"
        );
        return $stmt->fetchAll();
    }

    /** Obtiene un usuario por ID (solo activos). */
    public function obtenerPorId(int $id): ?array {
        $stmt = $this->db->prepare(
            "SELECT * FROM usuarios_sistema WHERE id_usuario = :id AND status = 1"
        );
        $stmt->execute([':id' => $id]);
        $resultado = $stmt->fetch();
        return $resultado ?: null;
    }

    /** Verifica si el nick de usuario ya existe. */
    public function existeNick(string $nick, ?int $excluirId = null): bool {
        if ($excluirId) {
            $stmt = $this->db->prepare(
                "SELECT COUNT(*) FROM usuarios_sistema WHERE usuario_nick = :nick AND id_usuario != :id"
            );
            $stmt->execute([':nick' => $nick, ':id' => $excluirId]);
        } else {
            $stmt = $this->db->prepare(
                "SELECT COUNT(*) FROM usuarios_sistema WHERE usuario_nick = :nick"
            );
            $stmt->execute([':nick' => $nick]);
        }
        return (int)$stmt->fetchColumn() > 0;
    }

    /** Crea un nuevo usuario. */
    public function crear(array $datos): bool {
        $hash = password_hash($datos['password'], PASSWORD_DEFAULT);
        $stmt = $this->db->prepare(
            "INSERT INTO usuarios_sistema (nombre_full, usuario_nick, password_hash, rol)
             VALUES (:nombre, :usuario, :hash, :rol)"
        );
        return $stmt->execute([
            ':nombre'  => $datos['nombre'],
            ':usuario' => $datos['usuario'],
            ':hash'    => $hash,
            ':rol'     => $datos['rol'],
        ]);
    }

    /** Actualiza un usuario. Si no se pasa password, no la cambia. */
    public function actualizar(int $id, array $datos): bool {
        if (!empty($datos['password'])) {
            $hash = password_hash($datos['password'], PASSWORD_DEFAULT);
            $stmt = $this->db->prepare(
                "UPDATE usuarios_sistema SET nombre_full=:nombre, usuario_nick=:usuario,
                 password_hash=:hash, rol=:rol WHERE id_usuario=:id"
            );
            return $stmt->execute([
                ':nombre'  => $datos['nombre'],
                ':usuario' => $datos['usuario'],
                ':hash'    => $hash,
                ':rol'     => $datos['rol'],
                ':id'      => $id,
            ]);
        } else {
            $stmt = $this->db->prepare(
                "UPDATE usuarios_sistema SET nombre_full=:nombre, usuario_nick=:usuario, rol=:rol
                 WHERE id_usuario=:id"
            );
            return $stmt->execute([
                ':nombre'  => $datos['nombre'],
                ':usuario' => $datos['usuario'],
                ':rol'     => $datos['rol'],
                ':id'      => $id,
            ]);
        }
    }

    /** Borrado logico (status = 0). */
    public function desactivar(int $id): bool {
        $stmt = $this->db->prepare(
            "UPDATE usuarios_sistema SET status = 0 WHERE id_usuario = :id"
        );
        return $stmt->execute([':id' => $id]);
    }
}
