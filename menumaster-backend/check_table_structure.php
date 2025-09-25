<?php

// check_table_structure.php - Check full table structure

require_once __DIR__ . '/vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

use App\Config\ConexionDb;

try {
    $pdo = ConexionDb::getConnection();
    echo "Full usuarios table structure:\n\n";

    $stmt = $pdo->query('SHOW FULL COLUMNS FROM usuarios');
    while($row = $stmt->fetch()) {
        echo $row['Field'] . ' | ' . $row['Type'] . ' | Null: ' . $row['Null'] . ' | Default: ' . ($row['Default'] ?? 'NULL') . "\n";
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}