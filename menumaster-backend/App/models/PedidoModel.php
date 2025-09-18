<?php
namespace app\Models;

use PDO;
use PDOException;
use Exception;

class PedidoModel
{
    // CORRECCIÓN: Se estandariza el uso de '$db' para la conexión.
    private $db; 
    private $table = 'pedidos';

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Busca todos los pedidos, con un filtro opcional por uno o varios estados.
     * @param string|null $estados Nombres de los estados para filtrar, separados por coma (ej. 'pendiente,en preparacion').
     * @return array|false Un array de pedidos o false si hay error.
     */
    public function findAll(string $estados = null): array|false
    {
        $sql = "SELECT 
                    p.id, m.numero AS mesa_numero, u.nombre AS mesero_nombre,
                    ep.nombre AS estado, p.fecha_creacion
                FROM {$this->table} p
                LEFT JOIN mesas m ON p.mesa_id = m.id
                LEFT JOIN usuarios u ON p.usuario_id = u.id
                LEFT JOIN estados_pedido ep ON p.estado_id = ep.id";

        // CORRECCIÓN: Lógica mejorada para filtrar por múltiples estados.
        if ($estados) {
            $estadosArray = explode(',', $estados);
            $placeholders = implode(',', array_fill(0, count($estadosArray), '?'));
            $sql .= " WHERE ep.nombre IN ({$placeholders})";
        }

        $sql .= " ORDER BY p.fecha_creacion DESC";

        try {
            $stmt = $this->db->prepare($sql);
            if ($estados) {
                // Se vinculan los valores del array de estados.
                foreach ($estadosArray as $k => $estado) {
                    $stmt->bindValue(($k + 1), trim($estado));
                }
            }
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error en PedidoModel::findAll: ' . $e->getMessage());
            return false;
        }
    }
    
    
    /**
     * Busca un pedido y todos sus ítems asociados.
     */
    public function getPedidoWithDetails(int $id): array|false
    {
        // 1. Obtenemos los datos básicos del pedido.
        $sql = "SELECT p.id, p.notas, p.fecha_creacion, m.numero AS mesa_numero, 
                       u.nombre AS mesero_nombre, ep.nombre AS estado
                FROM {$this->table} p
                LEFT JOIN mesas m ON p.mesa_id = m.id
                LEFT JOIN usuarios u ON p.usuario_id = u.id
                LEFT JOIN estados_pedido ep ON p.estado_id = ep.id
                WHERE p.id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $pedido = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$pedido) {
            return false; // El pedido no existe.
        }

        // 2. Obtenemos los ítems del pedido.
        $sqlItems = "SELECT pi.cantidad, pi.precio_unitario, pr.nombre AS nombre_producto
                     FROM pedido_items pi
                     JOIN productos pr ON pi.producto_id = pr.id
                     WHERE pi.pedido_id = :id";
        
        $stmtItems = $this->db->prepare($sqlItems);
        $stmtItems->bindParam(':id', $id, PDO::PARAM_INT);
        $stmtItems->execute();
        $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

        // 3. Añadimos los ítems al array del pedido.
        $pedido['items'] = $items;
        
        return $pedido;
    }

    /**
     * Crea un pedido y sus detalles en una transacción.
     */
    public function createPedido(int $mesa_id, int $usuario_id, array $items, ?string $notas): int|false
{
    try {
        $this->db->beginTransaction();

        $stmtPedido = $this->db->prepare(
            // Se usa el $usuario_id dinámico en lugar de un '1' fijo
            "INSERT INTO pedidos (mesa_id, usuario_id, estado_id, notas) 
             VALUES (:mesa_id, :usuario_id, (SELECT id FROM estados_pedido WHERE nombre = 'pendiente'), :notas)"
        );
        $stmtPedido->bindParam(':mesa_id', $mesa_id, PDO::PARAM_INT);
        $stmtPedido->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT); // <-- Se usa el ID real
        $stmtPedido->bindParam(':notas', $notas);
        $stmtPedido->execute();
        $pedidoId = (int)$this->db->lastInsertId();

        // ... (el resto del método para insertar los items permanece igual) ...

        $this->db->commit();
        return $pedidoId;

    } catch (Exception $e) {
        $this->db->rollBack();
        error_log('Error en PedidoModel::createPedido: ' . $e->getMessage());
        return false;
    }
}
    
    public function getTomaPedidoData(): void
    {
        // Aquí iría la lógica para buscar en la BD los productos, mesas, etc.
        $data = [
            'productos' => [['id' => 1, 'nombre' => 'Producto 1']],
            'mesas' => [['id' => 1, 'numero' => 'Mesa 1']],
            'meseros' => [['id' => 1, 'nombre' => 'Mesero 1']]
        ];

        http_response_code(200);
        echo json_encode($data);
    }

    /**
     * Actualiza el estado de un pedido por su ID y el nombre del nuevo estado.
     */
    public function actualizarEstadoPedido(int $id, string $nuevoEstado): bool
    {
        $sql = "UPDATE {$this->table} SET estado_id = (SELECT id FROM estados_pedido WHERE nombre = :nuevo_estado) WHERE id = :id";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':nuevo_estado', $nuevoEstado);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Error en PedidoModel::actualizarEstadoPedido: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Cambia el estado de un pedido a 'facturado'.
     */
    public function facturarPedido(int $id): bool
    {
        return $this->actualizarEstadoPedido($id, 'facturado');
    }
    
    /**
     * Elimina un pedido y sus detalles asociados (requiere configuración de FK en cascada en la DB).
     */
    public function eliminarPedido(int $id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Error en PedidoModel::eliminarPedido: ' . $e->getMessage());
            return false;
        }
    }
}