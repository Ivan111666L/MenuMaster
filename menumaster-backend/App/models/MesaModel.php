<?php
namespace App\Models;

use PDO;
use PDOException;

// CORRECCIÓN: Se renombra la clase a 'MesaModel' para consistencia con los otros modelos.
class MesaModel
{
    // CORRECCIÓN: Se estandarizan las propiedades a '$db' y '$table'.
    private $db;
    private $table = "mesas";

    public function __construct(PDO $db)
    {
        // CORRECCIÓN: Se usa '$this->db' en lugar de '$this->conn'.
        $this->db = $db;
    }

    /**
     * Obtiene todas las mesas con el nombre de su estado.
     */
    public function findAll(): array|false
    {
        $sql = "SELECT 
                    m.id, m.numero, m.capacidad, m.ubicacion,
                    em.nombre AS estado
                FROM 
                    {$this->table} m
                LEFT JOIN 
                    estados_mesa em ON m.estado_id = em.id
                ORDER BY 
                    m.numero ASC";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error en MesaModel::findAll: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene una mesa por su ID.
     */
    public function find(int $id): array|false
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error en MesaModel::find: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Crea una nueva mesa a partir de un array de datos.
     */
    public function create(array $data): int|false
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";

        try {
            $stmt = $this->db->prepare($sql);
            foreach ($data as $key => &$value) {
                // Se determina el tipo de parámetro para mayor seguridad
                $paramType = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
                $stmt->bindParam(':' . $key, $value, $paramType);
            }
            
            $stmt->execute();
            return (int)$this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log('Error en MesaModel::create: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene todas las mesas disponibles.
     */
    public function findDisponibles(): array|false
    {
        $sql = "SELECT 
                    m.id, m.numero, m.capacidad, m.ubicacion,
                    em.nombre AS estado
                FROM 
                    {$this->table} m
                LEFT JOIN 
                    estados_mesa em ON m.estado_id = em.id
                WHERE 
                    m.estado_id = :estado_disponible_id
                ORDER BY 
                    m.numero ASC";
        
        try {
            $stmt = $this->db->prepare($sql);
            // Usar constante centralizada
            $estadoDisponibleId = \App\EstadosMesa::DISPONIBLE;
            $stmt->bindParam(':estado_disponible_id', $estadoDisponibleId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error en MesaModel::findDisponibles: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza una mesa a partir de un array de datos.
     */
    public function update(int $id, array $data): bool
    {
        $fields = [];
        foreach ($data as $key => $value) {
            $fields[] = "{$key} = :{$key}";
        }
        $fieldString = implode(', ', $fields);

        $sql = "UPDATE {$this->table} SET {$fieldString} WHERE id = :id";

        try {
            $stmt = $this->db->prepare($sql);
            foreach ($data as $key => &$value) {
                $paramType = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
                $stmt->bindParam(':' . $key, $value, $paramType);
            }
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Error en MesaModel::update: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Elimina una mesa por su ID.
     */
    public function delete(int $id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Error en MesaModel::delete: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Resetea el estado de TODAS las mesas a 'disponible'.
     */
    public function resetAll(): bool
    {
        $sql = "UPDATE {$this->table} SET estado_id = :estado_disponible_id";

        try {
            $stmt = $this->db->prepare($sql);
            $estadoDisponibleId = \App\EstadosMesa::DISPONIBLE;
            $stmt->bindParam(':estado_disponible_id', $estadoDisponibleId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Error en MesaModel::resetAll: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Cambia el estado de una mesa específica por nombre del estado.
     */
    public function cambiarEstado(int $mesaId, string $nombreEstado): bool
    {
        $sql = "UPDATE {$this->table} 
                SET estado_id = (SELECT id FROM estados_mesa WHERE nombre = :nombre_estado)
                WHERE id = :mesa_id";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':mesa_id', $mesaId, PDO::PARAM_INT);
            $stmt->bindParam(':nombre_estado', $nombreEstado, PDO::PARAM_STR);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Error en MesaModel::cambiarEstado: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Cambia el estado de una mesa específica por ID del estado.
     */
    public function cambiarEstadoPorId(int $mesaId, int $estadoId): bool
    {
        $sql = "UPDATE {$this->table} SET estado_id = :estado_id WHERE id = :mesa_id";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':mesa_id', $mesaId, PDO::PARAM_INT);
            $stmt->bindParam(':estado_id', $estadoId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Error en MesaModel::cambiarEstadoPorId: ' . $e->getMessage());
            return false;
        }
    }

    
}
