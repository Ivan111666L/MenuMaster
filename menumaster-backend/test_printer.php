<?php
require_once 'vendor/autoload.php';

use App\Utils\PrinterManager;

echo "Testing PrinterManager...\n";

try {
    $printerManager = new PrinterManager();
    
    echo "Printer configuration:\n";
    $config = $printerManager->getConfig();
    print_r($config);
    
    echo "\nTesting connection...\n";
    $connectionTest = $printerManager->testConnection();
    echo "Connection test result: " . ($connectionTest ? "SUCCESS" : "FAILED") . "\n";
    
    echo "\nTesting print functionality with sample order...\n";
    $sampleOrder = [
        'id' => 999,
        'mesa_nombre' => 'Mesa 1',
        'usuario_nombre' => 'Test User',
        'fecha_creacion' => date('Y-m-d H:i:s'),
        'items' => [
            [
                'cantidad' => 2,
                'nombre' => 'Hamburguesa Clásica',
                'es_combo' => false,
                'notas' => 'Sin cebolla'
            ],
            [
                'cantidad' => 1,
                'nombre' => 'Papas Fritas',
                'es_combo' => false,
                'notas' => ''
            ]
        ],
        'notas' => 'Pedido de prueba para cocina'
    ];
    
    $printResult = $printerManager->printOrder($sampleOrder);
    echo "Print test result: " . ($printResult ? "SUCCESS" : "FAILED") . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}