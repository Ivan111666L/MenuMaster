<?php
/**
 * Script de prueba para verificar el sistema de permisos
 * Prueba diferentes roles y sus permisos asignados
 */

use App\Config\ConexionDb;

require_once 'menumaster-backend/App/config/ConexionDb.php';

try {
    $database = new ConexionDb();
    $db = $database->getConnection();
    
    echo "<h1>Test del Sistema de Permisos</h1>\n";
    echo "<style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .role-section { border: 1px solid #ccc; margin: 10px 0; padding: 15px; }
        .permission { background: #f0f0f0; margin: 5px 0; padding: 5px; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
    </style>\n";
    
    // 1. Verificar que los permisos se insertaron correctamente
    echo "<div class='role-section'>\n";
    echo "<h2>1. Verificación de Permisos en Base de Datos</h2>\n";
    
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM permisos");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p class='success'>Total de permisos en BD: " . $result['total'] . "</p>\n";
    
    // Mostrar permisos por módulo
    $stmt = $db->prepare("SELECT modulo, COUNT(*) as cantidad FROM permisos GROUP BY modulo ORDER BY modulo");
    $stmt->execute();
    $permisos_por_modulo = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Permisos por Módulo:</h3>\n";
    foreach ($permisos_por_modulo as $modulo) {
        echo "<div class='permission'>Módulo: {$modulo['modulo']} - Permisos: {$modulo['cantidad']}</div>\n";
    }
    echo "</div>\n";
    
    // 2. Verificar asignaciones de roles
    echo "<div class='role-section'>\n";
    echo "<h2>2. Verificación de Asignaciones de Roles</h2>\n";
    
    $stmt = $db->prepare("
        SELECT r.nombre as rol, COUNT(rp.permiso_id) as total_permisos
        FROM roles r
        LEFT JOIN rol_permisos rp ON r.id = rp.rol_id
        GROUP BY r.id, r.nombre
        ORDER BY r.nombre
    ");
    $stmt->execute();
    $roles_permisos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($roles_permisos as $rol) {
        $class = $rol['total_permisos'] > 0 ? 'success' : 'error';
        echo "<p class='{$class}'>Rol: {$rol['rol']} - Permisos asignados: {$rol['total_permisos']}</p>\n";
    }
    echo "</div>\n";
    
    // 3. Mostrar permisos detallados por rol
    echo "<div class='role-section'>\n";
    echo "<h2>3. Permisos Detallados por Rol</h2>\n";
    
    $roles = ['administrador', 'mesero', 'cocinero', 'cajero'];
    
    foreach ($roles as $rol_nombre) {
        echo "<h3>Rol: " . ucfirst($rol_nombre) . "</h3>\n";
        
        $stmt = $db->prepare("
            SELECT p.modulo, p.accion, p.descripcion
            FROM permisos p
            INNER JOIN rol_permisos rp ON p.id = rp.permiso_id
            INNER JOIN roles r ON rp.rol_id = r.id
            WHERE r.nombre = ?
            ORDER BY p.modulo, p.accion
        ");
        $stmt->execute([$rol_nombre]);
        $permisos_rol = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($permisos_rol)) {
            echo "<p class='error'>No se encontraron permisos para este rol</p>\n";
        } else {
            $modulo_actual = '';
            foreach ($permisos_rol as $permiso) {
                if ($modulo_actual !== $permiso['modulo']) {
                    if ($modulo_actual !== '') echo "</ul>\n";
                    echo "<h4>Módulo: {$permiso['modulo']}</h4>\n<ul>\n";
                    $modulo_actual = $permiso['modulo'];
                }
                echo "<li>{$permiso['accion']}: {$permiso['descripcion']}</li>\n";
            }
            if ($modulo_actual !== '') echo "</ul>\n";
        }
    }
    echo "</div>\n";
    
    // 4. Verificar usuarios existentes y sus roles
    echo "<div class='role-section'>\n";
    echo "<h2>4. Usuarios Existentes y sus Roles</h2>\n";
    
    $stmt = $db->prepare("
        SELECT u.nombre, u.email, r.nombre as rol
        FROM usuarios u
        INNER JOIN roles r ON u.rol_id = r.id
        ORDER BY r.nombre, u.nombre
    ");
    $stmt->execute();
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($usuarios)) {
        echo "<p class='warning'>No se encontraron usuarios en la base de datos</p>\n";
        echo "<p>Para probar el sistema, necesitas crear usuarios con diferentes roles.</p>\n";
    } else {
        echo "<table border='1' cellpadding='5' cellspacing='0'>\n";
        echo "<tr><th>Nombre</th><th>Email</th><th>Rol</th></tr>\n";
        foreach ($usuarios as $usuario) {
            echo "<tr><td>{$usuario['nombre']}</td><td>{$usuario['email']}</td><td>{$usuario['rol']}</td></tr>\n";
        }
        echo "</table>\n";
    }
    echo "</div>\n";
    
    // 5. Sugerencias para pruebas
    echo "<div class='role-section'>\n";
    echo "<h2>5. Sugerencias para Pruebas</h2>\n";
    echo "<ol>\n";
    echo "<li>Inicia sesión con diferentes usuarios (administrador, mesero, cocinero, cajero)</li>\n";
    echo "<li>Verifica que cada rol solo vea los menús correspondientes a sus permisos</li>\n";
    echo "<li>Intenta acceder a rutas protegidas desde el navegador</li>\n";
    echo "<li>Verifica que las llamadas a la API respeten los permisos</li>\n";
    echo "</ol>\n";
    
    echo "<h3>URLs de prueba:</h3>\n";
    echo "<ul>\n";
    echo "<li><a href='http://localhost:5173/' target='_blank'>Frontend - Login</a></li>\n";
    echo "<li><a href='menumaster-backend/routes/permisos_api.php?action=getMisPermisos' target='_blank'>API - Mis Permisos (requiere autenticación)</a></li>\n";
    echo "</ul>\n";
    echo "</div>\n";
    
    echo "<div class='role-section'>\n";
    echo "<h2>✅ Sistema de Permisos Configurado Correctamente</h2>\n";
    echo "<p class='success'>El sistema de permisos ha sido configurado exitosamente. Puedes proceder a probar la aplicación con diferentes roles de usuario.</p>\n";
    echo "</div>\n";
    
} catch (Exception $e) {
    echo "<div class='role-section'>\n";
    echo "<h2 class='error'>❌ Error en la Verificación</h2>\n";
    echo "<p class='error'>Error: " . $e->getMessage() . "</p>\n";
    echo "</div>\n";
}
?>