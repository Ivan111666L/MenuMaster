<?php
// Script para actualizar la contraseña del usuario con el hash correcto

require_once __DIR__ . '/vendor/autoload.php';

// Cargar variables de entorno
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Configuración de la base de datos
$host = $_ENV['DB_HOST'] ?? 'localhost';
$dbname = $_ENV['DB_NAME'] ?? 'menu_master';
$username = $_ENV['DB_USER'] ?? 'root';
$password = $_ENV['DB_PASS'] ?? '';

try {
    // Conectar a la base de datos
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Hashear la contraseña
    $plainPassword = '112233';
    $hashedPassword = password_hash($plainPassword, PASSWORD_BCRYPT);
    
    // Actualizar la contraseña del usuario
    $stmt = $pdo->prepare("UPDATE usuarios SET password = :password WHERE email = :email");
    $result = $stmt->execute([
        ':password' => $hashedPassword,
        ':email' => 'michaelripoll9@gmail.com'
    ]);
    
    if ($result) {
        echo "✅ Contraseña actualizada correctamente para michaelripoll9@gmail.com\n";
        echo "📝 Hash generado: $hashedPassword\n";
        
        // Verificar que la actualización fue exitosa
        $stmt = $pdo->prepare("SELECT id, nombre, email FROM usuarios WHERE email = :email");
        $stmt->execute([':email' => 'michaelripoll9@gmail.com']);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            echo "👤 Usuario encontrado: {$user['nombre']} (ID: {$user['id']})\n";
        }
    } else {
        echo "❌ Error al actualizar la contraseña\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Error de conexión: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}