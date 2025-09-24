<?php
namespace App\Models;

use PDO;
use PDOException;

class DashboardModel {
    private $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function getActiveOrdersCount(): int {
        try {
            // Pedidos en estados 'pendiente' y 'en preparacion'
            $stmt = $this->db->prepare("
                SELECT COUNT(p.id) 
                FROM pedidos p 
                LEFT JOIN estados_pedido ep ON p.estado_id = ep.id 
                WHERE ep.nombre IN ('pendiente', 'en preparacion')
            ");
            $stmt->execute();
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log('Error en getActiveOrdersCount: ' . $e->getMessage());
            return 0;
        }
    }

    public function getTodaysSales(): float {
        try {
            // Calculamos las ventas del día basándonos en los pedidos pagados
            $stmt = $this->db->prepare("
                SELECT COALESCE(SUM(dp.cantidad * dp.precio_unitario), 0) as total
                FROM pedidos p
                JOIN detalles_pedido dp ON p.id = dp.pedido_id
                LEFT JOIN estados_pedido ep ON p.estado_id = ep.id
                WHERE DATE(p.fecha_creacion) = CURDATE() 
                AND ep.nombre IN ('pagado', 'servido')
            ");
            $stmt->execute();
            return (float)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log('Error en getTodaysSales: ' . $e->getMessage());
            return 0.0;
        }
    }

    public function getOccupiedTablesCount(): int {
        try {
            // Mesas con estado 'ocupada'
            $stmt = $this->db->prepare("
                SELECT COUNT(m.id) 
                FROM mesas m 
                LEFT JOIN estados_mesa em ON m.estado_id = em.id 
                WHERE em.nombre = 'ocupada'
            ");
            $stmt->execute();
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
                    LEFT JOIN estados_pedido ep ON p.estado_id = ep.id
                    WHERE p.fecha_creacion >= CURDATE() - INTERVAL 6 DAY
                    AND ep.nombre IN ('pagado', 'servido')
                    GROUP BY DATE(p.fecha_creacion), DAYNAME(p.fecha_creacion)
                    ORDER BY p.fecha_creacion ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
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
                    LEFT JOIN estados_pedido ep ON ped.estado_id = ep.id
                    WHERE ep.nombre IN ('pagado', 'servido') OR dp.id IS NULL
                    GROUP BY p.id, p.nombre
                    HAVING sales > 0
                    ORDER BY sales DESC
                    LIMIT 5";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
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