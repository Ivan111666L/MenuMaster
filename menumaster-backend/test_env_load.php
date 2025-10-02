<?php
// Test environment variable loading
require_once 'vendor/autoload.php';
require_once 'App/config/conexionDb.php';

use App\Config\ConexionDb;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

echo "=== Environment Variables Test ===\n";
echo "DB_HOST: " . ($_ENV['DB_HOST'] ?? 'NOT SET') . "\n";
echo "DB_NAME: " . ($_ENV['DB_NAME'] ?? 'NOT SET') . "\n";
echo "DB_USER: " . ($_ENV['DB_USER'] ?? 'NOT SET') . "\n";
echo "DB_PASS: " . ($_ENV['DB_PASS'] ?? 'NOT SET') . "\n";
echo "DB_CHARSET: " . ($_ENV['DB_CHARSET'] ?? 'NOT SET') . "\n";

// Test database connection
try {
    $conn = ConexionDb::getConnection();
    echo "\n✅ Database connection successful!\n";
    
    // Test a simple query
    $stmt = $conn->query("SELECT 1 as test");
    $result = $stmt->fetch();
    echo "Test query result: " . $result['test'] . "\n";
    
} catch (Exception $e) {
    echo "\n❌ Database connection failed: " . $e->getMessage() . "\n";
}
?>