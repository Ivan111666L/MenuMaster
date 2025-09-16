<?php
namespace App\Models;

use PDO;
use PDOException;

class DetallePedido
{
    private $conn;
    private $table = 'detalles_pedido';

    public function __construct(PDO $db)
    {
        $this->conn = $db;
    }

    /**
     * Crea un nuevo detalle (ítem) para un pedido.
     * Este método es llamado por el PedidoController dentro de una transacción.
     * @param array $data Los datos del detalle del pedido.
     * @return bool True si la creación fue exitosa, false si no.
     */
    public function create(array $data): bool
    {
        // El campo 'subtotal' es generado automáticamente por la base de datos,
        // por lo que no es necesario incluirlo en la inserción.
        $sql = "INSERT INTO {$this->table} (pedido_id, producto_id, cantidad, precio_unitario, notas)
                VALUES (:pedido_id, :producto_id, :cantidad, :precio_unitario, :notas)";

        try {
            $stmt = $this->conn->prepare($sql);

            // Vinculación de parámetros
            $stmt->bindParam(':pedido_id', $data['pedido_id'], PDO::PARAM_INT);
            $stmt->bindParam(':producto_id', $data['producto_id'], PDO::PARAM_INT);
            $stmt->bindParam(':cantidad', $data['cantidad'], PDO::PARAM_INT);
            $stmt->bindParam(':precio_unitario', $data['precio_unitario']);
            // Usamos un valor por defecto si las notas no se proporcionan
            $notas = $data['notas'] ?? null;
            $stmt->bindParam(':notas', $notas);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Error en DetallePedidoModel::create: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene todos los ítems asociados a un ID de pedido.
     * @param int $pedido_id El ID del pedido.
     * @return array|false Un array de ítems o false en caso de error.
     */
    public function findAllByOrderId(int $pedido_id): array|false
    {
        $sql = "SELECT 
                    dp.producto_id,
                    p.nombre AS producto_nombre,
                    dp.cantidad,
                    dp.precio_unitario,
                    dp.subtotal,
                    dp.notas
                FROM 
                    {$this->table} dp
                JOIN 
                    productos p ON dp.producto_id = p.id
                WHERE 
                    dp.pedido_id = :pedido_id";
        
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':pedido_id', $pedido_id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error en DetallePedidoModel::findAllByOrderId: ' . $e->getMessage());
            return false;
        }
    }
}