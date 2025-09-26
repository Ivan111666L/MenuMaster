<?php
// Script para corregir URLs de API en el frontend

$frontendDir = 'c:\xampp\htdocs\MenuMaster\menumaster-frontend\src';

function fixApiUrls($directory) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory)
    );
    
    $filesFixed = 0;
    $totalReplacements = 0;
    
    foreach ($iterator as $file) {
        if ($file->isFile() && 
            (pathinfo($file, PATHINFO_EXTENSION) === 'js' || 
             pathinfo($file, PATHINFO_EXTENSION) === 'jsx')) {
            
            $content = file_get_contents($file);
            $originalContent = $content;
            
            // Reemplazar '/api/' con '/' al inicio de las URLs
            $content = preg_replace("/api\.get\('\/api\//", "api.get('/", $content);
            $content = preg_replace("/api\.post\('\/api\//", "api.post('/", $content);
            $content = preg_replace("/api\.put\('\/api\//", "api.put('/", $content);
            $content = preg_replace("/api\.delete\('\/api\//", "api.delete('/", $content);
            $content = preg_replace("/api\.patch\('\/api\//", "api.patch('/", $content);
            
            // También corregir URLs hardcodeadas
            $content = preg_replace("/const API_URL = '\/api\//", "const API_URL = '/", $content);
            $content = preg_replace("/API_URL = '\/api\//", "API_URL = '/", $content);
            
            if ($content !== $originalContent) {
                file_put_contents($file, $content);
                $filesFixed++;
                $replacements = substr_count($originalContent, '/api/') - substr_count($content, '/api/');
                $totalReplacements += $replacements;
                echo "✅ Corregido: " . $file . " ($replacements reemplazos)\n";
            }
        }
    }
    
    echo "\n📊 Resumen:\n";
    echo "- Archivos corregidos: $filesFixed\n";
    echo "- Total de reemplazos: $totalReplacements\n";
}

echo "🔧 Corrigiendo URLs de API en el frontend...\n\n";
fixApiUrls($frontendDir);
echo "\n✅ Corrección completada!\n";
?>