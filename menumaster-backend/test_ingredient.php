<?php
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
require_once __DIR__ . '/App/config/conexionDb.php';
require_once __DIR__ . '/App/models/Model.php';
require_once __DIR__ . '/App/models/IngredienteModel.php';
use app\config\ConexionDb;
use app\Models\IngredienteModel;

$db = ConexionDb::getConnection();
$ingredienteModel = new IngredienteModel($db);

echo "=== TESTING INGREDIENT CREATION ===\n";
$testData = [
    'nombre' => 'Tomate de Prueba',
    'descripcion' => 'Tomate para testing',
    'unidad_medida' => 'kg',
    'stock_actual' => 25.5,
    'stock_minimo' => 5.0,
    'precio_compra' => 3.50
];

$newId = $ingredienteModel->create($testData);
if ($newId) {
    echo "✓ Ingredient created with ID: $newId\n";
    $ingredient = $ingredienteModel->find($newId);
    echo "✓ Retrieved: " . $ingredient['nombre'] . "\n";
} else {
    echo "✗ Failed to create ingredient\n";
}
?>