<?php
namespace App\Models;

use PDO;
use PDOException;

class Ingrediente
{
    private $conn;
    private $table_name = "ingredientes";

    public function __construct(PDO $db)
    {
        $this->conn = $db;
    }

    /**
     * Obtiene todos los ingredientes con el nombre de su proveedor y estado.
     * @return array|false
     */
    public function findAll(): array|false
    {
        $sql = "SELECT 
                    i.id, i.nombre, i.descripcion, i.unidad_medida, 
                    i.stock_actual, i.stock_minimo, i.precio_compra,
                    p.nombre AS proveedor_nombre,
                    e.nombre AS estado_nombre
                FROM 
                    {$this->table_name} i
                LEFT JOIN proveedores p ON i.proveedor_id = p.id
                LEFT JOIN estados_generales e ON i.estado_id = e.id
                ORDER BY 
                    i.nombre ASC";
        
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error en IngredienteModel::findAll: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene un único ingrediente por su ID.
     * @param int $id
     * @return array|false
     */
    public function find(int $id): array|false
    {
        $sql = "SELECT 
                    i.*, -- Seleccionamos todo de ingredientes para tener los IDs
                    p.nombre AS proveedor_nombre,
                    e.nombre AS estado_nombre
                FROM 
                    {$this->table_name} i
                LEFT JOIN proveedores p ON i.proveedor_id = p.id
                LEFT JOIN estados_generales e ON i.estado_id = e.id
                WHERE i.id = :id";
        
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error en IngredienteModel::find: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Crea un nuevo ingrediente a partir de un array de datos.
     * @param array $data Datos del ingrediente.
     * @return int|false El ID del nuevo ingrediente o false si falla.
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
            error_log('Error en IngredienteModel::create: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza un ingrediente a partir de un array de datos.
     * @param int $id El ID del ingrediente a actualizar.
     * @param array $data Datos a actualizar.
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
            error_log('Error en IngredienteModel::update: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza el stock de un ingrediente. Crítico para el control de inventario.
     * @param int $id El ID del ingrediente.
     * @param float $cantidad La cantidad a añadir (positivo) o restar (negativo).
     * @return bool
     */
    public function actualizarStock(int $id, float $cantidad): bool
    {
        // Esta consulta previene que el stock se vuelva negativo directamente en la BD
        $sql = "UPDATE {$this->table_name} 
                SET stock_actual = stock_actual + :cantidad 
                WHERE id = :id AND stock_actual + :cantidad >= 0";
    
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':cantidad', $cantidad);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            // execute() devuelve true, pero rowCount() nos dice si realmente se hizo el cambio.
            // Si el stock se iba a volver negativo, rowCount() será 0.
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log('Error en IngredienteModel::actualizarStock: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Elimina un ingrediente por su ID.
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
            error_log('Error en IngredienteModel::delete: ' . $e->getMessage());
            return false;
        }
    }
}