<?php
namespace app\Models;

use PDO;
use PDOException;

class UsuarioModel {
    // CORRECCIÓN: Se estandariza el uso de '$db' para la conexión y '$table' para el nombre de la tabla.
    private $db;
    private $table = 'usuarios';

    /**
     * El constructor recibe la conexión PDO y la almacena.
     * @param PDO $db La conexión a la base de datos.
     */
    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /**
     * Crea un nuevo usuario en la base de datos.
     * @return int|false El ID del nuevo usuario creado o false si falla.
     */
    public function create(string $nombre, string $email, string $password_hash, int $rol_id): int|false
    {
    $sql = "INSERT INTO {$this->table} (nombre, email, password, rol_id, estado_id) 
            VALUES (:nombre, :email, :password, :rol_id, 1)"; // Se asume estado 'activo' (ID 1)

    try {
        $stmt = $this->db->prepare($sql);

        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $password_hash);
        $stmt->bindParam(':rol_id', $rol_id, PDO::PARAM_INT);

        // --- CORRECCIÓN CLAVE ---
        // Verificamos si la ejecución fue exitosa. Si no, devolvemos false.
        if (!$stmt->execute()) {
            return false;
        }
        
        // Si tiene éxito, devolvemos el ID del usuario recién creado.
        return (int)$this->db->lastInsertId();

    } catch (PDOException $e) {
        // Si hay una excepción de la base de datos (ej. email duplicado), la registramos y devolvemos false.
        error_log('Error en UsuarioModel::create: ' . $e->getMessage());
        return false;
    }   
    }   
    
    /**
     * Busca un usuario por su ID y devuelve sus datos junto con el nombre del rol y estado.
     */
    public function find(int $id): array|false {
        $sql = "SELECT 
                    u.id, u.nombre, u.email, u.fecha_creacion,
                    r.nombre AS rol,
                    e.nombre AS estado
                FROM 
                    {$this->table} u
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
     * Guarda un token de reseteo de contraseña y su fecha de expiración para un usuario.
     */
    public function setResetToken(int $id, string $tokenHash, string $expiresAt): bool
    {
        // CORRECCIÓN: Se usa '$this->table' en lugar de '$this->table_name' para consistencia.
        $query = "UPDATE " . $this->table . " SET reset_token = :token, reset_token_expires_at = :expires WHERE id = :id";
        
        try {
            // CORRECCIÓN: Se usa '$this->db' en lugar de '$this->conn' para consistencia.
            $stmt = $this->db->prepare($query);

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
     * Busca un usuario por su email. Esencial para el login.
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
     * Actualiza los datos de un usuario existente.
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
     * Elimina un usuario por su ID.
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
     * Obtiene todos los usuarios con sus roles y estados.
     */
    public function findAll(): array|false
    {
        $sql = "SELECT 
                    u.id, u.nombre, u.email, u.fecha_creacion,
                    r.nombre AS rol,
                    e.nombre AS estado
                FROM 
                    {$this->table} u
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
     * Cambia la contraseña de un usuario.
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
     * Actualiza únicamente el estado de un usuario.
     */
    public function updateStatus(int $id, int $estado_id): bool
    {
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
 * Busca un usuario por su token de reseteo, siempre que no haya expirado.
 * @param string $tokenHash El hash SHA256 del token.
 * @return array|false Los datos del usuario o false si no se encuentra o el token ha expirado.
 */
public function findByResetToken(string $tokenHash): array|false
{
    $sql = "SELECT * FROM {$this->table} 
            WHERE reset_token = :token AND reset_token_expires_at > NOW()";
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
 * Actualiza la contraseña del usuario y limpia los campos del token de reseteo.
 * @param int $id ID del usuario.
 * @param string $newPasswordHash El nuevo hash de la contraseña.
 * @return bool True si tuvo éxito.
 */
public function updatePasswordAndClearResetToken(int $id, string $newPasswordHash): bool
{
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