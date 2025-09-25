<?php

// add_email_column.php - Add email column to usuarios table

require_once __DIR__ . '/vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

use App\Config\ConexionDb;

try {
    $pdo = ConexionDb::getConnection();
    echo "Adding email column to usuarios table...\n";

    $pdo->exec('ALTER TABLE usuarios ADD COLUMN email VARCHAR(255) UNIQUE AFTER nombre');
    echo "✅ Email column added successfully!\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}