<?php
namespace app\Models;

use PDO;
use PDOException;

class ProveedorModel 
{
    private $conn;
    private $table_name = "proveedores";

    public function __construct(PDO $db) 
    {
        $this->conn = $db;
    }

    /**
     * Obtiene todos los proveedores.
     * @return array|false Un array de proveedores o false si hay un error.
     */
    public function findAll(): array|false
    {
        $sql = "SELECT id, nombre, contacto, telefono, email FROM {$this->table_name} ORDER BY nombre ASC";
        
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error en ProveedorModel::findAll: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene un proveedor por su ID.
     * @param int $id
     * @return array|false
     */
    public function find(int $id): array|false
    {
        $sql = "SELECT id, nombre, contacto, telefono, email FROM {$this->table_name} WHERE id = :id";
        
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error en ProveedorModel::find: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene un proveedor por su nombre exacto.
     * Esencial para que el IngredienteController pueda convertir un nombre en un ID.
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
            error_log('Error en ProveedorModel::findByName: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Crea un nuevo proveedor a partir de un array de datos.
     * @param array $data ['nombre' => '...', 'telefono' => '...']
     * @return int|false El ID del nuevo proveedor o false si falla.
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
            error_log('Error en ProveedorModel::create: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza un proveedor a partir de un array de datos.
     * @param int $id El ID del proveedor a actualizar.
     * @param array $data ['telefono' => '...', 'email' => '...']
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
            error_log('Error en ProveedorModel::update: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Elimina un proveedor por su ID.
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
            error_log('Error en ProveedorModel::delete: ' . $e->getMessage());
            return false;
        }
    }
}