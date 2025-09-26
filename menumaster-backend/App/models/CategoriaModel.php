<?php
namespace App\Models;

use PDO;
use PDOException;

class CategoriaModel
{
    private $conn;
    private $table_name = "categorias";

    public function __construct(PDO $db)
    {
        $this->conn = $db;
    }

    /**
     * Obtiene todas las categorías con el nombre de su estado.
     * @return array|false
     */
    public function findAll(): array|false
    {
        $sql = "SELECT 
                    c.id, 
                    c.nombre, 
                    c.descripcion, 
                    e.nombre AS estado_nombre
                FROM 
                    {$this->table_name} c
                LEFT JOIN 
                    estados_generales e ON c.estado_id = e.id
                ORDER BY 
                    c.nombre ASC";
        
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error en CategoriaModel::findAll: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene una categoría por su ID.
     * @param int $id
     * @return array|false
     */
    public function find(int $id): array|false
    {
        $sql = "SELECT 
                    c.id, 
                    c.nombre, 
                    c.descripcion,
                    c.estado_id, 
                    e.nombre AS estado_nombre
                FROM 
                    {$this->table_name} c
                LEFT JOIN 
                    estados_generales e ON c.estado_id = e.id
                WHERE c.id = :id";
        
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error en CategoriaModel::find: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene una categoría por su nombre exacto (útil para validaciones).
     * @param string $name
     * @return array|false
     */
    public function findByName(string $name): array|false
    {
        $sql = "SELECT id, nombre FROM {$this->table_name} WHERE nombre = :nombre";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':nombre', $name);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error en CategoriaModel::findByName: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Crea una nueva categoría a partir de un array de datos.
     * @param array $data ['nombre' => '...', 'descripcion' => '...', 'estado_id' => 1]
     * @return int|false El ID de la nueva categoría o false si falla.
     */
    public function create(array $data): int|false
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO {$this->table_name} ({$columns}) VALUES ({$placeholders})";

        try {
            $stmt = $this->conn->prepare($sql);
            foreach ($data as $key => &$value) {
                $stmt->bindParam(':' . $key, $value);
            }
            
            $stmt->execute();
            return (int)$this->conn->lastInsertId();
        } catch (PDOException $e) {
            error_log('Error en CategoriaModel::create: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza una categoría a partir de un array de datos.
     * @param int $id El ID de la categoría a actualizar.
     * @param array $data ['nombre' => '...', 'estado_id' => 2]
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $fields = [];
        foreach ($data as $key => $value) {
            $fields[] = "{$key} = :{$key}";
        }
        $fieldString = implode(', ', $fields);

        $sql = "UPDATE {$this->table_name} SET {$fieldString} WHERE id = :id";

        try {
            $stmt = $this->conn->prepare($sql);
            foreach ($data as $key => &$value) {
                $stmt->bindParam(':' . $key, $value);
            }
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Error en CategoriaModel::update: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Elimina una categoría por su ID.
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $sql = "DELETE FROM {$this->table_name} WHERE id = :id";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Error en CategoriaModel::delete: ' . $e->getMessage());
            return false;
        }
    }

    
}
