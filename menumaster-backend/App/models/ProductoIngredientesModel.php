<?php
namespace App\Models;

use PDO;
use PDOException;

class ProductoIngredientesModel
{
    private $conn;
    private $table = 'productos_ingredientes';

    public function __construct(PDO $db)
    {
        $this->conn = $db;
    }

    /**
     * Crea una nueva relación producto-ingrediente
     */
    public function create(array $data): bool
    {
        try {
            $sql = "INSERT INTO {$this->table} (producto_id, ingrediente_id, cantidad) 
                    VALUES (:producto_id, :ingrediente_id, :cantidad)";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':producto_id', $data['producto_id'], PDO::PARAM_INT);
            $stmt->bindParam(':ingrediente_id', $data['ingrediente_id'], PDO::PARAM_INT);
            $stmt->bindParam(':cantidad', $data['cantidad']);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Error en ProductoIngredientesModel::create: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene todos los ingredientes de un producto
     */
    public function getByProducto(int $productoId): array
    {
        try {
            $sql = "SELECT pi.*, i.nombre as ingrediente_nombre, i.unidad_medida
                    FROM {$this->table} pi
                    JOIN ingredientes i ON pi.ingrediente_id = i.id
                    WHERE pi.producto_id = :producto_id";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':producto_id', $productoId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error en ProductoIngredientesModel::getByProducto: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Elimina todos los ingredientes de un producto
     */
    public function deleteByProducto(int $productoId): bool
    {
        try {
            $sql = "DELETE FROM {$this->table} WHERE producto_id = :producto_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':producto_id', $productoId, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Error en ProductoIngredientesModel::deleteByProducto: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza los ingredientes de un producto (elimina los existentes y crea los nuevos)
     */
    public function updateProductoIngredientes(int $productoId, array $ingredientes): bool
    {
        try {
            $this->conn->beginTransaction();

            // Eliminar ingredientes existentes
            $this->deleteByProducto($productoId);

            // Agregar nuevos ingredientes
            foreach ($ingredientes as $ingrediente) {
                $data = [
                    'producto_id' => $productoId,
                    'ingrediente_id' => $ingrediente['ingrediente_id'],
                    'cantidad' => $ingrediente['cantidad']
                ];
                
                if (!$this->create($data)) {
                    throw new PDOException('Error al crear relación producto-ingrediente');
                }
            }

            $this->conn->commit();
            return true;
        } catch (PDOException $e) {
            $this->conn->rollBack();
            error_log('Error en ProductoIngredientesModel::updateProductoIngredientes: ' . $e->getMessage());
            return false;
        }
    }
}