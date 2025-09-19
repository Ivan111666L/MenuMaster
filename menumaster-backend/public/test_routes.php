<?php
// test_routes.php - Archivo para probar las rutas manualmente

header('Content-Type: application/json');

echo json_encode([
    'status' => 'success',
    'message' => 'Backend funcionando correctamente',
    'timestamp' => date('Y-m-d H:i:s'),
    'routes_available' => [
        'GET /api/mesas',
        'GET /api/dashboard/summary'
    ]
]);
?>