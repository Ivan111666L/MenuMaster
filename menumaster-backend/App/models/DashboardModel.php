<?php
namespace App\Models;

use PDO;
use PDOException;
use App\EstadosMesa;
use App\EstadosPedido;

class DashboardModel {
    private $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function getActiveOrdersCount(): int {
        try {
            // Pedidos activos por IDs de estado (pendiente, en preparación)
            $stmt = $this->db->prepare(
                "SELECT COUNT(p.id) FROM pedidos p WHERE p.estado_id IN (:pendiente, :en_preparacion)"
            );
            $stmt->execute([
                ':pendiente' => EstadosPedido::PENDIENTE,
                ':en_preparacion' => EstadosPedido::EN_PREPARACION
            ]);
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log('Error en getActiveOrdersCount: ' . $e->getMessage());
            return 0;
        }
    }

    public function getTodaysSales(): float {
        try {
            // Ventas del día basadas en pedidos con estados pagado/servido (por ID)
            $stmt = $this->db->prepare("
                SELECT COALESCE(SUM(dp.cantidad * dp.precio_unitario), 0) as total
                FROM pedidos p
                JOIN detalles_pedido dp ON p.id = dp.pedido_id
                WHERE DATE(p.fecha_creacion) = CURDATE() 
                AND p.estado_id IN (:pagado, :servido)
            ");
            $stmt->execute([
                ':pagado' => EstadosPedido::PAGADO,
                ':servido' => EstadosPedido::SERVIDO
            ]);
            return (float)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log('Error en getTodaysSales: ' . $e->getMessage());
            return 0.0;
        }
    }

    public function getOccupiedTablesCount(): int {
        try {
            // Mesas ocupadas por ID de estado
            $stmt = $this->db->prepare("SELECT COUNT(m.id) FROM mesas m WHERE m.estado_id = :ocupada");
            $stmt->execute([':ocupada' => EstadosMesa::OCUPADA]);
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log('Error en getOccupiedTablesCount: ' . $e->getMessage());
            return 0;
        }
    }
    
    public function getTotalTablesCount(): int {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(id) FROM mesas");
            $stmt->execute();
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log('Error en getTotalTablesCount: ' . $e->getMessage());
            return 0;
        }
    }

    public function getLowStockIngredientsCount(): int {
        try {
            // Ingredientes con stock bajo (menos que el stock mínimo)
            $stmt = $this->db->prepare("
                SELECT COUNT(id) 
                FROM ingredientes 
                WHERE stock_actual <= stock_minimo
            ");
            $stmt->execute();
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log('Error en getLowStockIngredientsCount: ' . $e->getMessage());
            return 0;
        }
    }

    public function getWeeklySales(): array {
        try {
            $sql = "SELECT 
                        DAYNAME(p.fecha_creacion) as day_name,
                        DATE(p.fecha_creacion) as fecha,
                        COALESCE(SUM(dp.cantidad * dp.precio_unitario), 0) as sales
                    FROM pedidos p
                    LEFT JOIN detalles_pedido dp ON p.id = dp.pedido_id
                    WHERE p.fecha_creacion >= CURDATE() - INTERVAL 6 DAY
                    AND p.estado_id IN (:pagado, :servido)
                    GROUP BY DATE(p.fecha_creacion), DAYNAME(p.fecha_creacion)
                    ORDER BY p.fecha_creacion ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':pagado' => EstadosPedido::PAGADO,
                ':servido' => EstadosPedido::SERVIDO
            ]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Formatear los resultados para el frontend
            $formattedResults = [];
            foreach ($results as $row) {
                $formattedResults[] = [
                    'day' => substr($row['day_name'], 0, 3), // Primeras 3 letras del día
                    'sales' => (float)$row['sales']
                ];
            }
            
            return $formattedResults;
        } catch (PDOException $e) {
            error_log('Error en getWeeklySales: ' . $e->getMessage());
            return [];
        }
    }

    public function getTopSellingProducts(): array {
        try {
            $sql = "SELECT 
                        p.nombre as name,
                        p.id,
                        COALESCE(SUM(dp.cantidad), 0) as sales
                    FROM productos p
                    LEFT JOIN detalles_pedido dp ON p.id = dp.producto_id
                    LEFT JOIN pedidos ped ON dp.pedido_id = ped.id
                    WHERE ped.estado_id IN (:pagado, :servido) OR dp.id IS NULL
                    GROUP BY p.id, p.nombre
                    HAVING sales > 0
                    ORDER BY sales DESC
                    LIMIT 5";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':pagado' => EstadosPedido::PAGADO,
                ':servido' => EstadosPedido::SERVIDO
            ]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Convertir sales a entero
            foreach ($results as &$result) {
                $result['sales'] = (int)$result['sales'];
            }
            
            return $results;
        } catch (PDOException $e) {
            error_log('Error en getTopSellingProducts: ' . $e->getMessage());
            return [];
        }
    }
}
