<?php
// Direct test of dashboard controller without authentication
require_once __DIR__ . '/vendor/autoload.php';

// Load environment variables from .env file
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

require_once __DIR__ . '/App/config/conexionDb.php';
require_once __DIR__ . '/App/models/Model.php';
require_once __DIR__ . '/App/models/DashboardModel.php';

use app\config\ConexionDb;
use app\Models\DashboardModel;

echo "=== TESTING DASHBOARD CONTROLLER ===\n\n";

try {
    // Get database connection
    $db = ConexionDb::getConnection();
    $dashboardModel = new DashboardModel($db);
    
    // Since getSummary() calls exit, we need to test the model directly
    // and build the same data structure as the controller
    $summaryData = [
        'pedidosActivos' => $dashboardModel->getActiveOrdersCount(),
        'ventasDia' => $dashboardModel->getTodaysSales(),
        'mesasOcupadas' => $dashboardModel->getOccupiedTablesCount(),
        'mesasTotales' => $dashboardModel->getTotalTablesCount(),
        'inventarioBajo' => $dashboardModel->getLowStockIngredientsCount(),
        'ventasSemanales' => $dashboardModel->getWeeklySales(),
        'topProductos' => $dashboardModel->getTopSellingProducts()
    ];
    
    $response = ['success' => true, 'data' => $summaryData];
    
    echo "Dashboard API Response (formatted):\n";
    echo json_encode($response, JSON_PRETTY_PRINT) . "\n";
    
    echo "\n=== RESPONSE ANALYSIS ===\n";
    echo "Success: true\n";
    echo "Active Orders: " . $summaryData['pedidosActivos'] . "\n";
    echo "Today's Sales: $" . number_format($summaryData['ventasDia'], 2) . "\n";
    echo "Occupied Tables: " . $summaryData['mesasOcupadas'] . "/" . $summaryData['mesasTotales'] . "\n";
    echo "Low Inventory: " . $summaryData['inventarioBajo'] . " items\n";
    echo "Weekly Sales: " . count($summaryData['ventasSemanales']) . " days\n";
    echo "Top Products: " . count($summaryData['topProductos']) . " items\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
?>