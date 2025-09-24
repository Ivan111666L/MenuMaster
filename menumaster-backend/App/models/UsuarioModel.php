<?php 
namespace App\Models;

use PDO;
use PDOException;

class UsuarioModel {
    private PDO $db;
    private string $table = 'usuarios';

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /**
     * Crea un nuevo usuario en la base de datos.
     */
    public function create(string $nombre, string $email, string $password_hash, int $rol_id): int|false {
        $sql = "INSERT INTO {$this->table} (nombre, email, password, rol_id, estado_id) 
                VALUES (:nombre, :email, :password, :rol_id, 1)";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $password_hash);
            $stmt->bindParam(':rol_id', $rol_id, PDO::PARAM_INT);

            if (!$stmt->execute()) {
                return false;
            }
            return (int) $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log('Error en UsuarioModel::create: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Busca un usuario por su ID.
     */
    public function find(int $id): array|false {
        $sql = "SELECT 
                    u.id, u.nombre, u.email, u.fecha_creacion,
                    r.nombre AS rol,
                    e.nombre AS estado
                FROM {$this->table} u
                LEFT JOIN roles r ON u.rol_id = r.id
                LEFT JOIN estados_generales e ON u.estado_id = e.id
                WHERE u.id = :id";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error en UsuarioModel::find: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Busca un usuario por email (para login).
     */
    public function findByEmail(string $email): array|false {
        $sql = "SELECT 
                    u.id, u.nombre, u.email, u.password, u.rol_id, u.estado_id,
                    r.nombre AS rol
                FROM {$this->table} u
                LEFT JOIN roles r ON u.rol_id = r.id
                WHERE u.email = :email";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error en UsuarioModel::findByEmail: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene todos los usuarios.
     */
    public function findAll(): array|false {
        $sql = "SELECT 
                    u.id, u.nombre, u.email, u.fecha_creacion,
                    r.nombre AS rol,
                    e.nombre AS estado
                FROM {$this->table} u
                LEFT JOIN roles r ON u.rol_id = r.id
                LEFT JOIN estados_generales e ON u.estado_id = e.id
                ORDER BY u.nombre ASC";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error en UsuarioModel::findAll: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza datos de usuario.
     */
    public function update(int $id, string $nombre, string $email, int $rol_id, int $estado_id): bool {
        $sql = "UPDATE {$this->table} 
                SET nombre = :nombre, email = :email, rol_id = :rol_id, estado_id = :estado_id
                WHERE id = :id";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':rol_id', $rol_id, PDO::PARAM_INT);
            $stmt->bindParam(':estado_id', $estado_id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Error en UsuarioModel::update: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Elimina usuario por ID.
     */
    public function delete(int $id): bool {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Error en UsuarioModel::delete: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Cambia la contraseña.
     */
    public function changePassword(int $id, string $new_password_hash): bool {
        $sql = "UPDATE {$this->table} SET password = :password WHERE id = :id";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':password', $new_password_hash);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Error en UsuarioModel::changePassword: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza solo estado.
     */
    public function updateStatus(int $id, int $estado_id): bool {
        $sql = "UPDATE {$this->table} SET estado_id = :estado_id WHERE id = :id";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':estado_id', $estado_id, PDO::PARAM_INT);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Error en UsuarioModel::updateStatus: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Guarda token de reseteo.
     */
    public function setResetToken(int $id, string $tokenHash, string $expiresAt): bool {
        $sql = "UPDATE {$this->table} 
                SET reset_token = :token, reset_token_expires_at = :expires 
                WHERE id = :id";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':token', $tokenHash);
            $stmt->bindParam(':expires', $expiresAt);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Error en UsuarioModel::setResetToken: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Busca por token de reseteo.
     */
    public function findByResetToken(string $tokenHash): array|false {
        $sql = "SELECT * FROM {$this->table} 
                WHERE reset_token = :token 
                  AND reset_token_expires_at > NOW()";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':token', $tokenHash);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error en UsuarioModel::findByResetToken: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Cambia contraseña y limpia token.
     */
    public function updatePasswordAndClearResetToken(int $id, string $newPasswordHash): bool {
        $sql = "UPDATE {$this->table} 
                SET password = :password, 
                    reset_token = NULL, 
                    reset_token_expires_at = NULL 
                WHERE id = :id";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':password', $newPasswordHash);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Error en UsuarioModel::updatePasswordAndClearResetToken: ' . $e->getMessage());
            return false;
        }
    }
}