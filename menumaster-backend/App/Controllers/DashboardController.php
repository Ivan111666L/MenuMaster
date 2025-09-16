<?php
namespace App\Controllers;

use App\Models\DashboardModel;
use PDO;
use Exception;

class DashboardController
{
    private $db;
    private $dashboardModel;

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->dashboardModel = new DashboardModel($this->db);
    }

    /**
     * Obtiene un resumen de todos los datos para el panel de control.
     * Corresponde a: GET /api/dashboard/summary
     */
    public function getSummary()
    {
        try {
            // Llamamos a cada método del modelo para construir la respuesta
            $summaryData = [
                'pedidosActivos' => $this->dashboardModel->getActiveOrdersCount(),
                'ventasDia' => $this->dashboardModel->getTodaysSales(),
                'mesasOcupadas' => $this->dashboardModel->getOccupiedTablesCount(),
                'mesasTotales' => $this->dashboardModel->getTotalTablesCount(),
                'inventarioBajo' => $this->dashboardModel->getLowStockIngredientsCount(),
                'ventasSemanales' => $this->dashboardModel->getWeeklySales(),
                'topProductos' => $this->dashboardModel->getTopSellingProducts()
            ];

            // Enviamos la respuesta en el formato esperado por el frontend
            http_response_code(200);
            echo json_encode(['success' => true, 'data' => $summaryData]);

        } catch (Exception $e) {
            http_response_code(500);

            echo json_encode(['success' => false, 'error' => 'Error interno del servidor al obtener los datos del dashboard.']);
        }
    }
}