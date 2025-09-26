-- =====================================================
-- ASIGNACIÓN DE PERMISOS POR ROL - MENUMASTER
-- =====================================================
-- Este script asigna permisos específicos a cada rol del sistema
-- Basado en las responsabilidades y nivel de acceso de cada rol

-- Limpiar asignaciones existentes (opcional - descomenta si necesitas reiniciar)
-- DELETE FROM rol_permisos;

-- =====================================================
-- ROL: ADMINISTRADOR (ID: 1)
-- Acceso completo a todo el sistema
-- =====================================================

INSERT IGNORE INTO `rol_permisos` (`rol_id`, `permiso_id`) 
SELECT 1, id FROM permisos;

-- =====================================================
-- ROL: MESERO (ID: 2)
-- Acceso a: Mesas, Pedidos, Productos (consulta), Facturación básica
-- =====================================================

INSERT IGNORE INTO `rol_permisos` (`rol_id`, `permiso_id`) 
SELECT 2, p.id FROM permisos p WHERE p.modulo = 'dashboard' AND p.accion IN ('view', 'waiter_view');

INSERT IGNORE INTO `rol_permisos` (`rol_id`, `permiso_id`) 
SELECT 2, p.id FROM permisos p WHERE p.modulo = 'productos' AND p.accion IN ('view', 'view_prices');

INSERT IGNORE INTO `rol_permisos` (`rol_id`, `permiso_id`) 
SELECT 2, p.id FROM permisos p WHERE p.modulo = 'pedidos' AND p.accion IN ('view', 'create', 'edit', 'change_status');

INSERT IGNORE INTO `rol_permisos` (`rol_id`, `permiso_id`) 
SELECT 2, p.id FROM permisos p WHERE p.modulo = 'mesas' AND p.accion IN ('view', 'change_status', 'view_occupancy');

INSERT IGNORE INTO `rol_permisos` (`rol_id`, `permiso_id`) 
SELECT 2, p.id FROM permisos p WHERE p.modulo = 'facturacion' AND p.accion IN ('view', 'create', 'view_invoices', 'process_payments');

-- =====================================================
-- ROL: COCINERO (ID: 3)
-- Acceso a: Cocina, Inventario, Pedidos (consulta), Productos (consulta)
-- =====================================================

INSERT IGNORE INTO `rol_permisos` (`rol_id`, `permiso_id`) 
SELECT 3, p.id FROM permisos p WHERE p.modulo = 'dashboard' AND p.accion IN ('view', 'cook_view');

INSERT IGNORE INTO `rol_permisos` (`rol_id`, `permiso_id`) 
SELECT 3, p.id FROM permisos p WHERE p.modulo = 'productos' AND p.accion IN ('view', 'view_prices');

INSERT IGNORE INTO `rol_permisos` (`rol_id`, `permiso_id`) 
SELECT 3, p.id FROM permisos p WHERE p.modulo = 'inventario' AND p.accion IN ('view', 'view_alerts', 'view_ingredients', 'view_suppliers');

INSERT IGNORE INTO `rol_permisos` (`rol_id`, `permiso_id`) 
SELECT 3, p.id FROM permisos p WHERE p.modulo = 'pedidos' AND p.accion IN ('view', 'change_status');

INSERT IGNORE INTO `rol_permisos` (`rol_id`, `permiso_id`) 
SELECT 3, p.id FROM permisos p WHERE p.modulo = 'cocina';

-- =====================================================
-- ROL: CAJERO (ID: 4)
-- Acceso a: Facturación, Pedidos (consulta), Reportes básicos
-- =====================================================

INSERT IGNORE INTO `rol_permisos` (`rol_id`, `permiso_id`) 
SELECT 4, p.id FROM permisos p WHERE p.modulo = 'dashboard' AND p.accion IN ('view', 'cashier_view');

INSERT IGNORE INTO `rol_permisos` (`rol_id`, `permiso_id`) 
SELECT 4, p.id FROM permisos p WHERE p.modulo = 'productos' AND p.accion IN ('view', 'view_prices');

INSERT IGNORE INTO `rol_permisos` (`rol_id`, `permiso_id`) 
SELECT 4, p.id FROM permisos p WHERE p.modulo = 'pedidos' AND p.accion IN ('view', 'view_all');

INSERT IGNORE INTO `rol_permisos` (`rol_id`, `permiso_id`) 
SELECT 4, p.id FROM permisos p WHERE p.modulo = 'facturacion';

INSERT IGNORE INTO `rol_permisos` (`rol_id`, `permiso_id`) 
SELECT 4, p.id FROM permisos p WHERE p.modulo = 'reportes' AND p.accion IN ('view', 'view_sales', 'view_financial');

-- =====================================================
-- PERMISOS ADICIONALES ESPECÍFICOS POR ROL
-- =====================================================

-- Meseros: Acceso limitado a reportes de sus propias ventas
INSERT IGNORE INTO `rol_permisos` (`rol_id`, `permiso_id`) 
SELECT 2, p.id FROM permisos p WHERE p.modulo = 'reportes' AND p.accion = 'view_sales';

-- Cocineros: Acceso a reportes de inventario
INSERT IGNORE INTO `rol_permisos` (`rol_id`, `permiso_id`) 
SELECT 3, p.id FROM permisos p WHERE p.modulo = 'reportes' AND p.accion = 'view_inventory';

-- Cocineros: Pueden actualizar stock de ingredientes
INSERT IGNORE INTO `rol_permisos` (`rol_id`, `permiso_id`) 
SELECT 3, p.id FROM permisos p WHERE p.modulo = 'inventario' AND p.accion = 'update_stock';

-- =====================================================
-- VERIFICACIÓN DE ASIGNACIONES
-- =====================================================

-- Mostrar resumen de permisos por rol
SELECT 'Resumen de permisos asignados por rol:' as info;

SELECT 
    r.nombre as rol,
    COUNT(rp.permiso_id) as total_permisos
FROM roles r
LEFT JOIN rol_permisos rp ON r.id = rp.rol_id
GROUP BY r.id, r.nombre
ORDER BY r.id;

-- Mostrar permisos detallados por rol
SELECT 'Detalle de permisos por rol:' as info;

SELECT 
    r.nombre as rol,
    p.modulo,
    p.accion,
    p.nombre as permiso
FROM roles r
JOIN rol_permisos rp ON r.id = rp.rol_id
JOIN permisos p ON rp.permiso_id = p.id
ORDER BY r.nombre, p.modulo, p.accion;

-- Verificar que todos los roles tienen al menos un permiso
SELECT 'Roles sin permisos asignados:' as warning;
SELECT r.nombre 
FROM roles r 
LEFT JOIN rol_permisos rp ON r.id = rp.rol_id 
WHERE rp.rol_id IS NULL;