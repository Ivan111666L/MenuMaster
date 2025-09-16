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
        // Asume que el estado 'pendiente' o 'en preparación' son los activos.
        // Necesitarás los IDs de estado correspondientes. Aquí usamos 1 y 2 como ejemplo.
        $stmt = $this->db->prepare("SELECT COUNT(id) FROM pedidos WHERE estado_id IN (1, 2)");
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    public function getTodaysSales(): float {
        $stmt = $this->db->prepare("SELECT SUM(total) FROM facturas WHERE DATE(fecha_factura) = CURDATE()");
        $stmt->execute();
        return (float)$stmt->fetchColumn();
    }

    public function getOccupiedTablesCount(): int {
        // Asume que el estado 'ocupada' tiene el ID 2
        $stmt = $this->db->prepare("SELECT COUNT(id) FROM mesas WHERE estado_id = 2");
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }
    
    public function getTotalTablesCount(): int {
        $stmt = $this->db->prepare("SELECT COUNT(id) FROM mesas");
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    public function getLowStockIngredientsCount(): int {
        // Compara el stock actual con un stock mínimo definido por ingrediente
        $stmt = $this->db->prepare("SELECT COUNT(id) FROM ingredientes WHERE cantidad_stock <= stock_minimo");
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    public function getWeeklySales(): array {
        $sql = "SELECT 
                    DATE_FORMAT(fecha_factura, '%a') as day, 
                    SUM(total) as sales
                FROM facturas 
                WHERE fecha_factura >= CURDATE() - INTERVAL 6 DAY
                GROUP BY DATE(fecha_factura)
                ORDER BY fecha_factura ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTopSellingProducts(): array {
        $sql = "SELECT 
                    p.nombre as name,
                    p.id,
                    SUM(pi.cantidad) as sales
                FROM pedido_items pi
                JOIN productos p ON pi.producto_id = p.id
                GROUP BY p.id, p.nombre
                ORDER BY sales DESC
                LIMIT 5";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}