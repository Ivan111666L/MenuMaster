<?php
// Script para verificar errores de sintaxis en todos los archivos PHP

$backend_dir = __DIR__ . '/App';
$errors = [];

function checkPhpSyntax($file) {
    $output = [];
    $return_var = 0;
    exec("php -l " . escapeshellarg($file) . " 2>&1", $output, $return_var);
    
    if ($return_var !== 0) {
        return implode("\n", $output);
    }
    return null;
}

function scanDirectory($dir, &$errors) {
    $files = glob($dir . '/*.php');
    foreach ($files as $file) {
        $error = checkPhpSyntax($file);
        if ($error) {
            $errors[basename($file)] = $error;
        }
    }
    
    // Escanear subdirectorios
    $subdirs = glob($dir . '/*', GLOB_ONLYDIR);
    foreach ($subdirs as $subdir) {
        scanDirectory($subdir, $errors);
    }
}

echo "=== VERIFICANDO SINTAXIS PHP ===\n";
scanDirectory($backend_dir, $errors);

if (empty($errors)) {
    echo "✅ No se encontraron errores de sintaxis en archivos PHP\n";
} else {
    echo "❌ Se encontraron errores de sintaxis:\n\n";
    foreach ($errors as $file => $error) {
        echo "Archivo: $file\n";
        echo "Error: $error\n";
        echo "---\n";
    }
}

// Verificar también archivos de rutas
echo "\n=== VERIFICANDO RUTAS ===\n";
$routes_dir = __DIR__ . '/routes';
$route_errors = [];
scanDirectory($routes_dir, $route_errors);

if (empty($route_errors)) {
    echo "✅ No se encontraron errores de sintaxis en archivos de rutas\n";
} else {
    echo "❌ Se encontraron errores de sintaxis en rutas:\n\n";
    foreach ($route_errors as $file => $error) {
        echo "Archivo: $file\n";
        echo "Error: $error\n";
        echo "---\n";
    }
}
?>