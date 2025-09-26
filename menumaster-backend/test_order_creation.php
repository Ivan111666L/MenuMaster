<?php
require_once 'vendor/autoload.php';
require_once 'App/config/conexionDb.php';

use App\config\ConexionDb;
use App\Controllers\PedidoController;

echo "Testing automatic kitchen printing after order creation...\n\n";

try {
    // Get database connection
    $db = ConexionDb::getConnection();
    
    // Create PedidoController instance
    $controller = new PedidoController($db);
    
    // Simulate order data
    $orderData = [
        'mesa_id' => 1,
        'items' => [
            [
                'producto_id' => 1,
                'cantidad' => 2,
                'precio' => 15.99
            ],
            [
                'producto_id' => 2,
                'cantidad' => 1,
                'precio' => 8.50
            ]
        ],
        'notas' => 'Pedido de prueba - Sin cebolla en hamburguesa'
    ];
    
    echo "Order data:\n";
    print_r($orderData);
    
    echo "\nCreating order...\n";
    
    // Capture output to see the response
    ob_start();
    $controller->store($orderData);
    $output = ob_get_clean();
    
    echo "Controller response:\n";
    echo $output . "\n";
    
    echo "\nCheck the error log for kitchen printing messages.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}