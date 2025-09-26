<?php
// Create admin user for testing

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Dotenv\Dotenv;

// Load environment variables
$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

// Database connection
$host = $_ENV['DB_HOST'] ?? 'localhost';
$dbname = $_ENV['DB_NAME'] ?? 'menumaster';
$username = $_ENV['DB_USER'] ?? 'root';
$password = $_ENV['DB_PASS'] ?? '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // First, check if admin role exists
    $stmt = $pdo->prepare("SELECT id FROM roles WHERE nombre = 'administrador'");
    $stmt->execute();
    $adminRole = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$adminRole) {
        // Create admin role if it doesn't exist
        $stmt = $pdo->prepare("INSERT INTO roles (nombre, descripcion) VALUES ('administrador', 'Administrador del sistema')");
        $stmt->execute();
        $adminRoleId = $pdo->lastInsertId();
        echo "✅ Admin role created with ID: $adminRoleId\n";
    } else {
        $adminRoleId = $adminRole['id'];
        echo "✅ Admin role found with ID: $adminRoleId\n";
    }
    
    // Check if admin user already exists
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = 'admin@menumaster.com'");
    $stmt->execute();
    $existingAdmin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existingAdmin) {
        echo "ℹ️ Admin user already exists with ID: " . $existingAdmin['id'] . "\n";
    } else {
        // Create admin user
        $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("
            INSERT INTO usuarios (nombre, email, password, rol_id, estado_id, fecha_creacion, fecha_actualizacion) 
            VALUES ('Administrator', 'admin@menumaster.com', ?, ?, 1, NOW(), NOW())
        ");
        $stmt->execute([$hashedPassword, $adminRoleId]);
        
        $adminUserId = $pdo->lastInsertId();
        echo "✅ Admin user created successfully!\n";
        echo "   ID: $adminUserId\n";
        echo "   Email: admin@menumaster.com\n";
        echo "   Password: admin123\n";
        echo "   Role ID: $adminRoleId\n";
    }
    
    // Verify the admin user
    $stmt = $pdo->prepare("
        SELECT u.id, u.nombre, u.email, u.rol_id, r.nombre as rol 
        FROM usuarios u 
        JOIN roles r ON u.rol_id = r.id 
        WHERE u.email = 'admin@menumaster.com'
    ");
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin) {
        echo "\n✅ Admin user verification:\n";
        echo "   ID: " . $admin['id'] . "\n";
        echo "   Name: " . $admin['nombre'] . "\n";
        echo "   Email: " . $admin['email'] . "\n";
        echo "   Role: " . $admin['rol'] . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}