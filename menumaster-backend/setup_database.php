<?php
try {
    // Conectar a MySQL sin seleccionar una base de datos
    $pdo = new PDO("mysql:host=localhost", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Crear la base de datos si no existe
    $pdo->exec("CREATE DATABASE IF NOT EXISTS menu_master CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Base de datos creada o ya existente.\n";

    // Seleccionar la base de datos
    $pdo->exec("USE menu_master");

    // Importar el esquema de la base de datos
    $sql = file_get_contents(__DIR__ . '/database/schema.sql');
    $pdo->exec($sql);
    echo "Esquema de base de datos importado correctamente.\n";

    // Crear usuario y asignar privilegios si es necesario
    $pdo->exec("
        GRANT ALL PRIVILEGES ON menu_master.* TO 'root'@'localhost';
        FLUSH PRIVILEGES;
    ");
    echo "Privilegios configurados correctamente.\n";

} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
