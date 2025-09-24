<?php
namespace App\Models;

use PDO;
use Exception;

class RolModel {
    private $db;
    private $table = 'roles';

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /**
     * Busca un rol por su nombre.
     * @param string $nombre El nombre del rol (ej. 'administrador')
     * @return array|false
     */
    public function findByName(string $nombre): array|false {
        $sql = "SELECT id, nombre, descripcion, estado_id FROM {$this->table} WHERE nombre = :nombre";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Busca un rol por su ID.
     * @param int $id
     * @return array|false
     */
    public function findById(int $id): array|false {
        $sql = "SELECT id, nombre, descripcion, estado_id, created_at FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener todos los roles activos
     * @return array
     */
    public function getAllActive(): array {
        $sql = "SELECT id, nombre, descripcion, estado_id, created_at FROM {$this->table} WHERE estado_id = 1 ORDER BY nombre";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Crear un nuevo rol
     * @param array $data
     * @return int ID del rol creado
     */
    public function create(array $data): int {
        $sql = "INSERT INTO {$this->table} (nombre, descripcion, estado_id, created_at) VALUES (:nombre, :descripcion, 1, NOW())";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':nombre' => $data['nombre'],
            ':descripcion' => $data['descripcion'] ?? ''
        ]);
        return $this->db->lastInsertId();
    }

    /**
     * Actualizar un rol
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool {
        $sql = "UPDATE {$this->table} SET nombre = :nombre, descripcion = :descripcion WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id' => $id,
            ':nombre' => $data['nombre'],
            ':descripcion' => $data['descripcion'] ?? ''
        ]);
    }

    /**
     * Eliminar un rol (cambiar estado)
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool {
        $sql = "UPDATE {$this->table} SET estado_id = 2 WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Verificar si un rol existe
     * @param int $id
     * @return bool
     */
    public function exists(int $id): bool {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE id = :id AND estado_id = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Verificar si un nombre de rol ya existe
     * @param string $nombre
     * @param int|null $excludeId ID a excluir de la verificación (para updates)
     * @return bool
     */
    public function nameExists(string $nombre, ?int $excludeId = null): bool {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE nombre = :nombre AND estado_id = 1";
        $params = [':nombre' => $nombre];
        
        if ($excludeId) {
            $sql .= " AND id != :exclude_id";
            $params[':exclude_id'] = $excludeId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Obtener permisos de un rol
     * @param int $rolId
     * @return array
     */
    public function getPermisos(int $rolId): array {
        $sql = "
            SELECT p.id, p.nombre, p.descripcion, p.modulo, p.accion
            FROM rol_permisos rp
            INNER JOIN permisos p ON rp.permiso_id = p.id
            WHERE rp.rol_id = :rol_id AND p.estado_id = 1
            ORDER BY p.modulo, p.nombre
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':rol_id' => $rolId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Asignar permisos a un rol
     * @param int $rolId
     * @param array $permisoIds
     * @return bool
     */
    public function assignPermisos(int $rolId, array $permisoIds): bool {
        try {
            $this->db->beginTransaction();

            // Eliminar permisos existentes
            $deleteStmt = $this->db->prepare("DELETE FROM rol_permisos WHERE rol_id = :rol_id");
            $deleteStmt->execute([':rol_id' => $rolId]);

            // Insertar nuevos permisos
            if (!empty($permisoIds)) {
                $insertStmt = $this->db->prepare("INSERT INTO rol_permisos (rol_id, permiso_id, created_at) VALUES (:rol_id, :permiso_id, NOW())");
                
                foreach ($permisoIds as $permisoId) {
                    $insertStmt->execute([
                        ':rol_id' => $rolId,
                        ':permiso_id' => $permisoId
                    ]);
                }
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}