<?php
namespace app\Models;

use PDO;
use PDOException;

class ProductoModel
{
    private $conn;
    private $table = 'productos';

    public function __construct(PDO $db)
    {
        $this->conn = $db;
    }

    /**
     * Obtiene todos los productos con el nombre de su categoría y estado.
     * @param bool $todos Si es true, devuelve todos los productos incluyendo inactivos
     * @return array|false
     */
    public function findAll(bool $todos = false): array|false
    {
        $sql = "SELECT 
                    p.id,
                    p.nombre,
                    p.descripcion,
                    p.precio,
                    p.imagen_url,
                    p.tiempo_preparacion_min,
                    p.destacado,
                    c.nombre AS categoria_nombre,
                    ep.nombre AS estado_nombre
                FROM 
                    {$this->table} p
                LEFT JOIN categorias c ON p.categoria_id = c.id
                LEFT JOIN estados_producto ep ON p.estado_id = ep.id";
                
        if (!$todos) {
            // Si no se solicitan todos, filtramos por productos activos/disponibles
            $sql .= " WHERE ep.nombre = 'disponible'";
        }
        
        $sql .= " ORDER BY p.nombre ASC";
        
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error en ProductoModel::findAll: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene un producto por su ID con el nombre de su categoría y estado.
     * @param int $id
     * @return array|false
     */
    public function find(int $id): array|false
    {
        $sql = "SELECT 
                    p.id,
                    p.nombre,
                    p.descripcion,
                    p.precio,
                    p.imagen_url,
                    p.tiempo_preparacion_min,
                    p.destacado,
                    c.nombre AS categoria_nombre,
                    ep.nombre AS estado_nombre,
                    p.categoria_id,
                    p.estado_id
                FROM 
                    {$this->table} p
                LEFT JOIN categorias c ON p.categoria_id = c.id
                LEFT JOIN estados_producto ep ON p.estado_id = ep.id
                WHERE p.id = :id";
        
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error en ProductoModel::find: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Crea un nuevo producto a partir de un array de datos.
     * @param array $data Datos del producto (ej. ['nombre' => 'Pizza', 'precio' => 10.50, 'categoria_id' => 1])
     * @return int|false El ID del nuevo producto o false si falla.
     */
    public function create(array $data): int|false
    {
        // Construye la consulta dinámicamente a partir de las claves del array
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";

        try {
            $stmt = $this->conn->prepare($sql);
            // Vincula los valores del array a los placeholders de la consulta
            foreach ($data as $key => &$value) {
                $stmt->bindParam(':' . $key, $value);
            }
            
            $stmt->execute();
            return (int)$this->conn->lastInsertId();
        } catch (PDOException $e) {
            error_log('Error en ProductoModel::create: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza un producto a partir de un array de datos.
     * Solo actualiza los campos que se proporcionan en el array.
     * @param int $id El ID del producto a actualizar.
     * @param array $data Datos a actualizar (ej. ['precio' => 12.00, 'estado_id' => 2])
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        // Construye la parte SET de la consulta dinámicamente
        $fields = [];
        foreach ($data as $key => $value) {
            $fields[] = "{$key} = :{$key}";
        }
        $fieldString = implode(', ', $fields);

        $sql = "UPDATE {$this->table} SET {$fieldString} WHERE id = :id";

        try {
            $stmt = $this->conn->prepare($sql);
            // Vincula los valores de los datos a actualizar
            foreach ($data as $key => &$value) {
                $stmt->bindParam(':' . $key, $value);
            }
            // Vincula el ID
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Error en ProductoModel::update: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Elimina un producto por su ID.
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Error en ProductoModel::delete: ' . $e->getMessage());
            return false;
        }
    }
}