<?php
namespace App\Models;

use PDO;
use Exception;

class ProductoIngredienteModel {
    private $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /**
     * Obtiene todos los ingredientes de un producto
     */
    public function getIngredientesByProductoId($productoId) {
        $query = "
            SELECT 
                pi.*, 
                i.nombre as ingrediente_nombre, 
                i.unidad_medida, 
                i.stock_actual,
                i.precio_compra AS costo_unitario
            FROM productos_ingredientes pi
            JOIN ingredientes i ON pi.ingrediente_id = i.id
            WHERE pi.producto_id = :producto_id
        ";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':producto_id', $productoId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Agrega un ingrediente a un producto
     */
    public function agregarIngrediente($productoId, $ingredienteId, $cantidad) {
        try {
            $query = "
                INSERT INTO productos_ingredientes (producto_id, ingrediente_id, cantidad)
                VALUES (:producto_id, :ingrediente_id, :cantidad)
                ON DUPLICATE KEY UPDATE cantidad = :cantidad
            ";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':producto_id', $productoId, PDO::PARAM_INT);
            $stmt->bindParam(':ingrediente_id', $ingredienteId, PDO::PARAM_INT);
            $stmt->bindParam(':cantidad', $cantidad, PDO::PARAM_STR);
            
            return $stmt->execute();
        } catch (Exception $e) {
            throw new Exception("Error al agregar ingrediente al producto: " . $e->getMessage());
        }
    }

    /**
     * Elimina un ingrediente de un producto
     */
    public function eliminarIngrediente($productoId, $ingredienteId) {
        try {
            $query = "
                DELETE FROM productos_ingredientes 
                WHERE producto_id = :producto_id AND ingrediente_id = :ingrediente_id
            ";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':producto_id', $productoId, PDO::PARAM_INT);
            $stmt->bindParam(':ingrediente_id', $ingredienteId, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (Exception $e) {
            throw new Exception("Error al eliminar ingrediente del producto: " . $e->getMessage());
        }
    }

    /**
     * Calcula el costo total de un producto basado en sus ingredientes
     */
    public function calcularCostoProducto($productoId) {
        $query = "
            SELECT SUM(pi.cantidad * i.precio_compra) as costo_total
            FROM productos_ingredientes pi
            JOIN ingredientes i ON pi.ingrediente_id = i.id
            WHERE pi.producto_id = :producto_id
        ";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':producto_id', $productoId, PDO::PARAM_INT);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['costo_total'] ?? 0;
    }

    /**
     * Descuenta ingredientes del inventario basado en la cantidad de productos vendidos
     */
    public function descontarInventario($productoId, $cantidad) {
        try {
            // Obtener ingredientes del producto
            $ingredientes = $this->getIngredientesByProductoId($productoId);

            // Descontar cada ingrediente del inventario (sin manejar transacción aquí)
            foreach ($ingredientes as $ingrediente) {
                $cantidadTotal = $ingrediente['cantidad'] * $cantidad;

                // Actualizar stock
                $query = "
                    UPDATE ingredientes
                    SET stock_actual = stock_actual - :cantidad_total
                    WHERE id = :ingrediente_id
                ";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':cantidad_total', $cantidadTotal, PDO::PARAM_STR);
                $stmt->bindParam(':ingrediente_id', $ingrediente['ingrediente_id'], PDO::PARAM_INT);
                $stmt->execute();

                // Verificar si el stock quedó negativo
                $checkQuery = "SELECT stock_actual FROM ingredientes WHERE id = :ingrediente_id";
                $checkStmt = $this->db->prepare($checkQuery);
                $checkStmt->bindParam(':ingrediente_id', $ingrediente['ingrediente_id'], PDO::PARAM_INT);
                $checkStmt->execute();
                $stockActual = $checkStmt->fetchColumn();

                if ($stockActual < 0) {
                    throw new Exception("Stock insuficiente para el ingrediente: " . ($ingrediente['ingrediente_nombre'] ?? $ingrediente['ingrediente_id']));
                }
            }

            return true;
        } catch (Exception $e) {
            throw new Exception("Error al descontar inventario: " . $e->getMessage());
        }
    }
}
?>
