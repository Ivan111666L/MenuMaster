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
        $sqlItems = "SELECT dp.cantidad, dp.precio_unitario, pr.nombre AS nombre_producto
                     FROM detalles_pedido dp
                     JOIN productos pr ON dp.producto_id = pr.id
                     WHERE dp.pedido_id = :id";
        
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

            // 1. Insertar el pedido principal
            $stmtPedido = $this->db->prepare(
                "INSERT INTO pedidos (mesa_id, usuario_id, estado_id, notas) 
                 VALUES (:mesa_id, :usuario_id, (SELECT id FROM estados_pedido WHERE nombre = 'pendiente'), :notas)"
            );
            $stmtPedido->bindParam(':mesa_id', $mesa_id, PDO::PARAM_INT);
            $stmtPedido->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
            $stmtPedido->bindParam(':notas', $notas);
            $stmtPedido->execute();
            $pedidoId = (int)$this->db->lastInsertId();

            // 2. Preparar statement para insertar items del pedido
            $stmtItems = $this->db->prepare(
                "INSERT INTO detalles_pedido (pedido_id, producto_id, combo_id, es_combo, cantidad, precio_unitario, notas) 
                 VALUES (:pedido_id, :producto_id, :combo_id, :es_combo, :cantidad, :precio_unitario, :notas)"
            );
            
            // 3. Preparar statement para insertar elementos de combos
            $stmtComboElementos = $this->db->prepare(
                "INSERT INTO detalles_pedido_combo_elementos (detalle_pedido_id, producto_id, cantidad, precio_unitario, subtotal)
                VALUES (:detalle_pedido_id, :producto_id, :cantidad, :precio_unitario, :subtotal)"
            );

            // 4. Insertar cada item del pedido
            foreach ($items as $item) {
                // Obtener el precio actual del producto
                $stmtPrecio = $this->db->prepare("SELECT precio FROM productos WHERE id = :producto_id");
                $stmtPrecio->bindParam(':producto_id', $item['producto_id'], PDO::PARAM_INT);
                $stmtPrecio->execute();
                $producto = $stmtPrecio->fetch(PDO::FETCH_ASSOC);
                
                if (!$producto) {
                    throw new Exception("Producto con ID {$item['producto_id']} no encontrado.");
                }

                $esCombo = isset($item['es_combo']) && $item['es_combo'] ? 1 : 0;
                $comboId = isset($item['combo_id']) ? $item['combo_id'] : null;
                $itemNotas = isset($item['notas']) ? $item['notas'] : null;
                
                // Insertar el item con el precio actual
                $stmtItems->bindParam(':pedido_id', $pedidoId, PDO::PARAM_INT);
                $stmtItems->bindParam(':producto_id', $item['producto_id'], PDO::PARAM_INT);
                $stmtItems->bindParam(':combo_id', $comboId, $comboId ? PDO::PARAM_INT : PDO::PARAM_NULL);
                $stmtItems->bindParam(':es_combo', $esCombo, PDO::PARAM_INT);
                $stmtItems->bindParam(':cantidad', $item['cantidad'], PDO::PARAM_INT);
                $stmtItems->bindParam(':precio_unitario', $producto['precio']);
                $stmtItems->bindParam(':notas', $itemNotas);
                $stmtItems->execute();
                
                $detalleId = (int)$this->db->lastInsertId();
                
                // Si es un combo, insertar sus elementos
                if ($esCombo && !empty($item['elementos'])) {
                    foreach ($item['elementos'] as $elemento) {
                        $subtotal = $elemento['precio_unitario'] * $elemento['cantidad'];
                        
                        $stmtComboElementos->bindParam(':detalle_pedido_id', $detalleId, PDO::PARAM_INT);
                        $stmtComboElementos->bindParam(':producto_id', $elemento['producto_id'], PDO::PARAM_INT);
                        $stmtComboElementos->bindParam(':cantidad', $elemento['cantidad'], PDO::PARAM_INT);
                        $stmtComboElementos->bindParam(':precio_unitario', $elemento['precio_unitario']);
                        $stmtComboElementos->bindParam(':subtotal', $subtotal);
                        $stmtComboElementos->execute();
                    }
                }
                
                // Si el producto está en el menú del día con stock limitado, actualizar stock
                if (!$esCombo) {
                    $this->actualizarStockMenuDelDia($item['producto_id'], $item['cantidad']);
                }
            }

            $this->db->commit();
            return $pedidoId;

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log('Error en PedidoModel::createPedido: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Actualiza el stock de un producto en el menú del día
     */
    private function actualizarStockMenuDelDia(int $productoId, int $cantidad): bool
    {
        try {
            // Verificar si el producto está en el menú del día y tiene stock limitado
            $query = "SELECT stock_actual, stock_limite FROM menu_del_dia 
                     WHERE producto_id = :producto_id AND stock_limite IS NOT NULL";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':producto_id', $productoId, PDO::PARAM_INT);
            $stmt->execute();
            
            $menuItem = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($menuItem) {
                // Actualizar el stock actual
                $nuevoStock = max(0, $menuItem['stock_actual'] - $cantidad);
                $updateQuery = "UPDATE menu_del_dia SET stock_actual = :stock_actual 
                               WHERE producto_id = :producto_id";
                $updateStmt = $this->db->prepare($updateQuery);
                $updateStmt->bindParam(':stock_actual', $nuevoStock, PDO::PARAM_INT);
                $updateStmt->bindParam(':producto_id', $productoId, PDO::PARAM_INT);
                return $updateStmt->execute();
            }
            return true;
        } catch (PDOException $e) {
            error_log('Error en PedidoModel::actualizarStockMenuDelDia: ' . $e->getMessage());
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
     * Cambia el estado de un pedido a 'facturado' y descuenta ingredientes del inventario
     */
    public function facturarPedido(int $id): bool
    {
        try {
            // Iniciar transacción
            $this->db->beginTransaction();
            
            // Obtener detalles del pedido
            $query = "SELECT * FROM detalles_pedido WHERE pedido_id = :pedido_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':pedido_id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Instanciar modelo de producto-ingrediente
            require_once BASE_PATH . '/app/Models/ProductoIngredienteModel.php';
            $productoIngredienteModel = new \app\Models\ProductoIngredienteModel($this->db);
            
            // Descontar ingredientes del inventario para cada producto
            foreach ($detalles as $detalle) {
                try {
                    $productoIngredienteModel->descontarInventario($detalle['producto_id'], $detalle['cantidad']);
                    
                    // Calcular y guardar el costo del producto
                    $costo = $productoIngredienteModel->calcularCostoProducto($detalle['producto_id']) * $detalle['cantidad'];
                    
                    // Guardar el costo en el detalle del pedido para uso posterior
                    $updateCostoQuery = "UPDATE detalles_pedido SET costo_total = :costo WHERE id = :detalle_id";
                    $updateCostoStmt = $this->db->prepare($updateCostoQuery);
                    $updateCostoStmt->bindParam(':costo', $costo, PDO::PARAM_STR);
                    $updateCostoStmt->bindParam(':detalle_id', $detalle['id'], PDO::PARAM_INT);
                    $updateCostoStmt->execute();
                } catch (Exception $e) {
                    // Si hay error en el descuento, revertir transacción
                    $this->db->rollBack();
                    error_log('Error al descontar inventario: ' . $e->getMessage());
                    return false;
                }
            }
            
            // Cambiar estado del pedido a 'facturado'
            $result = $this->actualizarEstadoPedido($id, 'facturado');
            
            // Confirmar transacción
            $this->db->commit();
            return $result;
        } catch (Exception $e) {
            // Revertir transacción en caso de error
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log('Error en PedidoModel::facturarPedido: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Guarda un pedido en el historial antes de eliminarlo
     */
    public function guardarEnHistorial(int $id): bool
    {
        try {
            $this->db->beginTransaction();
            
            // 1. Obtener datos del pedido
            $sqlPedido = "SELECT 
                p.id, p.mesa_id, m.numero as mesa_numero, p.usuario_id, 
                u.nombre as usuario_nombre, ep.nombre as estado, p.total, p.fecha_creacion
                FROM pedidos p
                LEFT JOIN mesas m ON p.mesa_id = m.id
                LEFT JOIN usuarios u ON p.usuario_id = u.id
                LEFT JOIN estados_pedido ep ON p.estado_id = ep.id
                WHERE p.id = :id";
            
            $stmtPedido = $this->db->prepare($sqlPedido);
            $stmtPedido->bindParam(':id', $id, PDO::PARAM_INT);
            $stmtPedido->execute();
            $pedido = $stmtPedido->fetch(PDO::FETCH_ASSOC);
            
            if (!$pedido) {
                throw new Exception("Pedido no encontrado");
            }
            
            // 2. Insertar en historial_pedidos
            $sqlHistorial = "INSERT INTO historial_pedidos 
                (pedido_id, mesa_id, mesa_numero, usuario_id, usuario_nombre, estado_final, total, fecha_creacion, fecha_finalizacion)
                VALUES (:pedido_id, :mesa_id, :mesa_numero, :usuario_id, :usuario_nombre, :estado_final, :total, :fecha_creacion, NOW())";
            
            $stmtHistorial = $this->db->prepare($sqlHistorial);
            $stmtHistorial->bindParam(':pedido_id', $pedido['id'], PDO::PARAM_INT);
            $stmtHistorial->bindParam(':mesa_id', $pedido['mesa_id'], PDO::PARAM_INT);
            $stmtHistorial->bindParam(':mesa_numero', $pedido['mesa_numero']);
            $stmtHistorial->bindParam(':usuario_id', $pedido['usuario_id'], PDO::PARAM_INT);
            $stmtHistorial->bindParam(':usuario_nombre', $pedido['usuario_nombre']);
            $stmtHistorial->bindParam(':estado_final', $pedido['estado']);
            $stmtHistorial->bindParam(':total', $pedido['total']);
            $stmtHistorial->bindParam(':fecha_creacion', $pedido['fecha_creacion']);
            $stmtHistorial->execute();
            
            $historialId = $this->db->lastInsertId();
            
            // 3. Obtener detalles del pedido
            $sqlDetalles = "SELECT 
                dp.*, p.nombre as producto_nombre
                FROM detalles_pedido dp
                LEFT JOIN productos p ON dp.producto_id = p.id
                WHERE dp.pedido_id = :pedido_id";
            
            $stmtDetalles = $this->db->prepare($sqlDetalles);
            $stmtDetalles->bindParam(':pedido_id', $id, PDO::PARAM_INT);
            $stmtDetalles->execute();
            $detalles = $stmtDetalles->fetchAll(PDO::FETCH_ASSOC);
            
            // Instanciar modelo de producto-ingrediente para calcular costos
            require_once BASE_PATH . '/app/Models/ProductoIngredienteModel.php';
            $productoIngredienteModel = new \app\Models\ProductoIngredienteModel($this->db);
            
            // 4. Insertar detalles en historial_detalles_pedido
            $sqlHistorialDetalles = "INSERT INTO historial_detalles_pedido
                (historial_pedido_id, producto_id, producto_nombre, cantidad, precio_unitario, subtotal, costo_total)
                VALUES (:historial_pedido_id, :producto_id, :producto_nombre, :cantidad, :precio_unitario, :subtotal, :costo_total)";
            
            $stmtHistorialDetalles = $this->db->prepare($sqlHistorialDetalles);
            $totalCostos = 0;
            
            foreach ($detalles as $detalle) {
                // Calcular costo total del producto
                $costoTotal = isset($detalle['costo_total']) && $detalle['costo_total'] > 0 
                    ? $detalle['costo_total'] 
                    : $productoIngredienteModel->calcularCostoProducto($detalle['producto_id']) * $detalle['cantidad'];
                
                $totalCostos += $costoTotal;
                
                $stmtHistorialDetalles->bindParam(':historial_pedido_id', $historialId, PDO::PARAM_INT);
                $stmtHistorialDetalles->bindParam(':producto_id', $detalle['producto_id'], PDO::PARAM_INT);
                $stmtHistorialDetalles->bindParam(':producto_nombre', $detalle['producto_nombre']);
                $stmtHistorialDetalles->bindParam(':cantidad', $detalle['cantidad'], PDO::PARAM_INT);
                $stmtHistorialDetalles->bindParam(':precio_unitario', $detalle['precio_unitario']);
                $stmtHistorialDetalles->bindParam(':subtotal', $detalle['subtotal']);
                $stmtHistorialDetalles->bindParam(':costo_total', $costoTotal, PDO::PARAM_STR);
                $stmtHistorialDetalles->execute();
            }
            
            // Actualizar el registro de historial con los costos totales y la rentabilidad
            $rentabilidad = $pedido['total'] - $totalCostos;
            $sqlUpdateHistorial = "UPDATE historial_pedidos 
                SET costo_total = :costo_total, rentabilidad = :rentabilidad 
                WHERE id = :historial_id";
            
            $stmtUpdateHistorial = $this->db->prepare($sqlUpdateHistorial);
            $stmtUpdateHistorial->bindParam(':costo_total', $totalCostos, PDO::PARAM_STR);
            $stmtUpdateHistorial->bindParam(':rentabilidad', $rentabilidad, PDO::PARAM_STR);
            $stmtUpdateHistorial->bindParam(':historial_id', $historialId, PDO::PARAM_INT);
            $stmtUpdateHistorial->execute();
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log('Error en PedidoModel::guardarEnHistorial: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Elimina un pedido y sus detalles asociados después de guardarlos en el historial
     */
    public function eliminarPedido(int $id): bool
    {
        try {
            // Primero guardamos en el historial
            if (!$this->guardarEnHistorial($id)) {
                throw new Exception("No se pudo guardar el pedido en el historial");
            }
            
            // Luego eliminamos el pedido
            $sql = "DELETE FROM {$this->table} WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Error en PedidoModel::eliminarPedido: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtiene estadísticas de ventas para análisis
     */
    public function getEstadisticasVentas(string $fechaInicio = null, string $fechaFin = null): array
    {
        try {
            // Si no se especifican fechas, usar el último mes
            if (!$fechaInicio) {
                $fechaInicio = date('Y-m-d', strtotime('-30 days'));
            }
            if (!$fechaFin) {
                $fechaFin = date('Y-m-d');
            }
            
            // Estadísticas de meseros
            $sqlMeseros = "SELECT 
                usuario_nombre, 
                COUNT(*) as total_pedidos,
                SUM(total) as total_ventas
                FROM historial_pedidos
                WHERE fecha_finalizacion BETWEEN :fecha_inicio AND :fecha_fin
                GROUP BY usuario_id, usuario_nombre
                ORDER BY total_ventas DESC";
            
            $stmtMeseros = $this->db->prepare($sqlMeseros);
            $stmtMeseros->bindParam(':fecha_inicio', $fechaInicio);
            $stmtMeseros->bindParam(':fecha_fin', $fechaFin);
            $stmtMeseros->execute();
            $meseros = $stmtMeseros->fetchAll(PDO::FETCH_ASSOC);
            
            // Estadísticas de productos
            $sqlProductos = "SELECT 
                hdp.producto_nombre,
                SUM(hdp.cantidad) as total_vendido,
                SUM(hdp.subtotal) as total_ingresos
                FROM historial_detalles_pedido hdp
                JOIN historial_pedidos hp ON hdp.historial_pedido_id = hp.id
                WHERE hp.fecha_finalizacion BETWEEN :fecha_inicio AND :fecha_fin
                GROUP BY hdp.producto_id, hdp.producto_nombre
                ORDER BY total_vendido DESC";
            
            $stmtProductos = $this->db->prepare($sqlProductos);
            $stmtProductos->bindParam(':fecha_inicio', $fechaInicio);
            $stmtProductos->bindParam(':fecha_fin', $fechaFin);
            $stmtProductos->execute();
            $productos = $stmtProductos->fetchAll(PDO::FETCH_ASSOC);
            
            // Ventas por día
            $sqlVentasDia = "SELECT 
                DATE(fecha_finalizacion) as fecha,
                COUNT(*) as total_pedidos,
                SUM(total) as total_ventas
                FROM historial_pedidos
                WHERE fecha_finalizacion BETWEEN :fecha_inicio AND :fecha_fin
                GROUP BY DATE(fecha_finalizacion)
                ORDER BY fecha";
            
            $stmtVentasDia = $this->db->prepare($sqlVentasDia);
            $stmtVentasDia->bindParam(':fecha_inicio', $fechaInicio);
            $stmtVentasDia->bindParam(':fecha_fin', $fechaFin);
            $stmtVentasDia->execute();
            $ventasDia = $stmtVentasDia->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'meseros' => $meseros,
                'productos' => $productos,
                'ventas_diarias' => $ventasDia
            ];
            
        } catch (Exception $e) {
            error_log('Error en PedidoModel::getEstadisticasVentas: ' . $e->getMessage());
            return [
                'meseros' => [],
                'productos' => [],
                'ventas_diarias' => []
            ];
        }
    }
}