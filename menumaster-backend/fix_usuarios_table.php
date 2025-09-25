<?php

// fix_usuarios_table.php - Fix usuarios table and create test user

require_once __DIR__ . '/vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

use App\Config\ConexionDb;

try {
    $pdo = ConexionDb::getConnection();
    echo "Fixing usuarios table structure...\n";

    // Check if email column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM usuarios LIKE 'email'");
    $emailExists = $stmt->fetch();
    
    if (!$emailExists) {
        echo "Adding email column...\n";
        $pdo->exec('ALTER TABLE usuarios ADD COLUMN email VARCHAR(255) UNIQUE AFTER nombre');
        echo "✅ Email column added!\n";
    } else {
        echo "Email column already exists.\n";
    }

    // Check if test user exists
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ? OR nombre = ?");
    $stmt->execute(['test@example.com', 'test@example.com']);
    
    if ($stmt->fetch()) {
        echo "Test user already exists!\n";
        exit(0);
    }

    // Create test user with proper structure
    $hashedPassword = password_hash('test123', PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("
        INSERT INTO usuarios (nombre, email, password, rol_id, estado_id) 
        VALUES (?, ?, ?, ?, ?)
    ");
    
    $result = $stmt->execute([
        'Test User',
        'test@example.com',
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