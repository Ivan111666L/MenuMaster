<?php
// Test script to verify dashboard API endpoints
require_once __DIR__ . '/vendor/autoload.php';

// Load environment variables from .env file
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

require_once __DIR__ . '/App/config/conexionDb.php';
require_once __DIR__ . '/App/models/Model.php';
require_once __DIR__ . '/App/models/DashboardModel.php';

use app\config\ConexionDb;
use app\Models\DashboardModel;

echo "=== TESTING DASHBOARD API ENDPOINTS ===\n\n";

try {
    // Get database connection
    $db = ConexionDb::getConnection();
    $dashboardModel = new DashboardModel($db);
    
    // Test 1: Active Orders Count
    echo "1. Testing Active Orders Count:\n";
    $activeOrders = $dashboardModel->getActiveOrdersCount();
    echo "   Result: " . $activeOrders . " active orders\n\n";
    
    // Test 2: Today's Sales
    echo "2. Testing Today's Sales:\n";
    $todaysSales = $dashboardModel->getTodaysSales();
    echo "   Result: $" . number_format($todaysSales, 2) . "\n\n";
    
    // Test 3: Occupied Tables
    echo "3. Testing Occupied Tables:\n";
    $occupiedTables = $dashboardModel->getOccupiedTablesCount();
    $totalTables = $dashboardModel->getTotalTablesCount();
    echo "   Result: " . $occupiedTables . "/" . $totalTables . " tables occupied\n\n";
    
    // Test 4: Low Inventory
    echo "4. Testing Low Inventory:\n";
    $lowInventory = $dashboardModel->getLowStockIngredientsCount();
    echo "   Result: " . $lowInventory . " items with low inventory\n\n";
    
    // Test 5: Weekly Sales
    echo "5. Testing Weekly Sales:\n";
    $weeklySales = $dashboardModel->getWeeklySales();
    echo "   Result: " . count($weeklySales) . " days of sales data\n";
    foreach ($weeklySales as $day) {
        echo "   - " . $day['date'] . ": $" . number_format($day['sales'], 2) . "\n";
    }
    echo "\n";
    
    // Test 6: Top Selling Products
    echo "6. Testing Top Selling Products:\n";
    $topProducts = $dashboardModel->getTopSellingProducts();
    echo "   Result: " . count($topProducts) . " products found\n";
    foreach ($topProducts as $product) {
        echo "   - " . $product['nombre'] . ": " . $product['total_vendido'] . " sold, $" . number_format($product['ingresos'], 2) . " revenue\n";
    }
    echo "\n";
    
    echo "=== ALL TESTS COMPLETED SUCCESSFULLY ===\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
?>