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
    public function getSummary(): void
    {
        try {
            // La lógica para obtener los datos ya era correcta.
            $summaryData = [
                'pedidosActivos' => $this->dashboardModel->getActiveOrdersCount(),
                'ventasDia' => $this->dashboardModel->getTodaysSales(),
                'mesasOcupadas' => $this->dashboardModel->getOccupiedTablesCount(),
                'mesasTotales' => $this->dashboardModel->getTotalTablesCount(),
                'inventarioBajo' => $this->dashboardModel->getLowStockIngredientsCount(),
                'ventasSemanales' => $this->dashboardModel->getWeeklySales(),
                'topProductos' => $this->dashboardModel->getTopSellingProducts()
            ];

            // CORRECCIÓN: Se usa el método helper 'sendResponse' para consistencia.
            $this->sendResponse(200, $summaryData);

        } catch (Exception $e) {
            // El bloque catch ahora puede lanzar una excepción que será manejada
            // por el enrutador de forma centralizada.
            throw new Exception('Error interno del servidor al obtener los datos del dashboard.', 500);
        }
    }

    /**
     * Envía la respuesta HTTP en formato JSON y termina la ejecución del script.
     * (Este es el método helper que hemos usado en otros controladores).
     */
    private function sendResponse(int $statusCode, $data): void
    {
        http_response_code($statusCode);
        if ($statusCode !== 204) {
            echo json_encode(['success' => true, 'data' => $data]);
        }
        exit;
    }
}