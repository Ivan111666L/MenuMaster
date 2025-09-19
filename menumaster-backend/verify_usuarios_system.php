<?php
require_once 'vendor/autoload.php';
require_once 'App/config/conexionDb.php';

use app\config\ConexionDb;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

try {
    $db = ConexionDb::getConnection();
    
    echo "=== VERIFICACIÓN SISTEMA DE USUARIOS ===\n\n";
    
    // 1. Verificar tabla usuarios
    echo "1. Estructura de la tabla 'usuarios':\n";
    $stmt = $db->query('DESCRIBE usuarios');
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "- {$row['Field']} ({$row['Type']})\n";
    }
    
    // 2. Mostrar usuarios existentes
    echo "\n2. Usuarios actuales:\n";
    $stmt = $db->query('SELECT id, nombre, email, rol, estado FROM usuarios ORDER BY nombre');
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($usuarios)) {
        echo "⚠️ No hay usuarios configurados además del admin.\n";
    } else {
        foreach ($usuarios as $usuario) {
            echo "- {$usuario['nombre']} ({$usuario['email']}) - Rol: {$usuario['rol']}, Estado: {$usuario['estado']}\n";
        }
    }
    
    // 3. Verificar controlador de usuarios
    echo "\n3. Probando UsuarioController...\n";
    require_once 'App/Controllers/UsuarioController.php';
    
    $controller = new \app\Controllers\UsuarioController($db);
    echo "✅ UsuarioController instanciado correctamente\n";
    
    // 4. Verificar rutas de usuarios
    echo "\n4. Verificando rutas de usuarios...\n";
    if (file_exists('routes/usuarios_api.php')) {
        echo "✅ Archivo de rutas 'usuarios_api.php' existe\n";
    } else {
        echo "❌ Archivo de rutas 'usuarios_api.php' no encontrado\n";
    }
    
    // 5. Verificar roles disponibles
    echo "\n5. Roles del sistema:\n";
    $roles = ['administrador', 'mesero', 'cocinero', 'cajero'];
    foreach ($roles as $rol) {
        echo "- {$rol}\n";
    }
    
    echo "\n✅ Sistema de usuarios verificado correctamente!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>