<?php

// create_test_user.php - Script to create a test user for API testing

require_once __DIR__ . '/vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

use App\Config\ConexionDb;

try {
    $pdo = ConexionDb::getConnection();
    echo "Database connection successful!\n";

    // Check if test user already exists
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE nombre = ?");
    $stmt->execute(['test@example.com']);
    
    if ($stmt->fetch()) {
        echo "Test user already exists!\n";
        exit(0);
    }

    // Create test user
    $hashedPassword = password_hash('test123', PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("
        INSERT INTO usuarios (nombre, password, rol_id, estado_id, created_at) 
        VALUES (?, ?, ?, ?, NOW())
    ");
    
    $result = $stmt->execute([
        'test@example.com', // Using nombre field for email since there's no email column
        $hashedPassword,
        1, // Assuming role ID 1 exists
        1  // Active status
    ]);
    
    if ($result) {
        echo "✅ Test user created successfully!\n";
        echo "Email: test@example.com\n";
        echo "Password: test123\n";
    } else {
        echo "❌ Failed to create test user\n";
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}