<?php
// Intenta diferentes configuraciones comunes de XAMPP
$configurations = [
    ['host' => 'localhost', 'user' => 'root', 'pass' => ''],
    ['host' => '127.0.0.1', 'user' => 'root', 'pass' => ''],
    ['host' => 'localhost', 'user' => 'root', 'pass' => 'root'],
];

$success = false;

foreach ($configurations as $config) {
    try {
        $pdo = new PDO("mysql:host={$config['host']}", $config['user'], $config['pass']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "Conexión exitosa con la configuración:\n";
        echo "Host: {$config['host']}\n";
        echo "Usuario: {$config['user']}\n";
        echo "Contraseña: " . ($config['pass'] ? "[SET]" : "[EMPTY]") . "\n";
        $success = true;
        
        // Actualizar el archivo .env con la configuración correcta
        $envContent = file_get_contents('.env');
        $envContent = preg_replace(
            '/DB_HOST=.*\n/',
            "DB_HOST={$config['host']}\n",
            $envContent
        );
        $envContent = preg_replace(
            '/DB_USER=.*\n/',
            "DB_USER={$config['user']}\n",
            $envContent
        );
        $envContent = preg_replace(
            '/DB_PASS=.*\n/',
            "DB_PASS={$config['pass']}\n",
            $envContent
        );
        file_put_contents('.env', $envContent);
        
        break;
    } catch (PDOException $e) {
        echo "Intento fallido con configuración:\n";
        echo "Host: {$config['host']}\n";
        echo "Usuario: {$config['user']}\n";
        echo "Contraseña: " . ($config['pass'] ? "[SET]" : "[EMPTY]") . "\n";
        echo "Error: " . $e->getMessage() . "\n\n";
    }
}

if (!$success) {
    echo "\nNo se pudo conectar con ninguna configuración.\n";
    echo "Por favor, sigue estos pasos:\n";
    echo "1. Abre XAMPP Control Panel\n";
    echo "2. Detén MySQL si está corriendo\n";
    echo "3. Haz clic en el botón 'Shell' en XAMPP Control Panel\n";
    echo "4. Ejecuta estos comandos:\n";
    echo "   cd mysql\\bin\n";
    echo "   mysqld --init-file=C:\\reset_root.txt\n";
    echo "\nAntes de ejecutar los comandos, crea el archivo C:\\reset_root.txt con este contenido:\n";
    echo "ALTER USER 'root'@'localhost' IDENTIFIED BY '';\n";
}
