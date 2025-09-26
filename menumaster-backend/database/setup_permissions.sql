-- =====================================================
-- CONFIGURACIÓN DE PERMISOS PARA MENUMASTER
-- =====================================================
-- Este script configura todos los permisos necesarios para el sistema
-- basado en los módulos y acciones identificados en la aplicación

-- Limpiar datos existentes (opcional - descomenta si necesitas reiniciar)
-- DELETE FROM rol_permisos;
-- DELETE FROM permisos;

-- =====================================================
-- INSERTAR PERMISOS POR MÓDULO
-- =====================================================

-- MÓDULO: Dashboard
INSERT IGNORE INTO `permisos` (`nombre`, `descripcion`, `modulo`, `accion`) VALUES
('Ver Dashboard', 'Acceso al panel principal de control', 'dashboard', 'view'),
('Ver Dashboard Administrador', 'Acceso al dashboard completo de administrador', 'dashboard', 'admin_view'),
('Ver Dashboard Mesero', 'Acceso al dashboard específico de mesero', 'dashboard', 'waiter_view'),
('Ver Dashboard Cocinero', 'Acceso al dashboard específico de cocinero', 'dashboard', 'cook_view'),
('Ver Dashboard Cajero', 'Acceso al dashboard específico de cajero', 'dashboard', 'cashier_view');

-- MÓDULO: Productos
INSERT IGNORE INTO `permisos` (`nombre`, `descripcion`, `modulo`, `accion`) VALUES
('Ver Productos', 'Visualizar lista de productos', 'productos', 'view'),
('Crear Productos', 'Crear nuevos productos', 'productos', 'create'),
('Editar Productos', 'Modificar productos existentes', 'productos', 'edit'),
('Eliminar Productos', 'Eliminar productos del sistema', 'productos', 'delete'),
('Gestionar Categorías', 'Administrar categorías de productos', 'productos', 'manage_categories'),
('Ver Precios', 'Visualizar precios de productos', 'productos', 'view_prices'),
('Editar Precios', 'Modificar precios de productos', 'productos', 'edit_prices');

-- MÓDULO: Inventario
INSERT IGNORE INTO `permisos` (`nombre`, `descripcion`, `modulo`, `accion`) VALUES
('Ver Inventario', 'Visualizar estado del inventario', 'inventario', 'view'),
('Gestionar Stock', 'Administrar niveles de stock', 'inventario', 'manage_stock'),
('Ver Alertas Stock', 'Visualizar alertas de stock bajo', 'inventario', 'view_alerts'),
('Actualizar Stock', 'Modificar cantidades en inventario', 'inventario', 'update_stock'),
('Ver Ingredientes', 'Visualizar lista de ingredientes', 'inventario', 'view_ingredients'),
('Gestionar Ingredientes', 'Administrar ingredientes', 'inventario', 'manage_ingredients'),
('Ver Proveedores', 'Visualizar información de proveedores', 'inventario', 'view_suppliers'),
('Gestionar Proveedores', 'Administrar proveedores', 'inventario', 'manage_suppliers');

-- MÓDULO: Pedidos
INSERT IGNORE INTO `permisos` (`nombre`, `descripcion`, `modulo`, `accion`) VALUES
('Ver Pedidos', 'Visualizar lista de pedidos', 'pedidos', 'view'),
('Crear Pedidos', 'Crear nuevos pedidos', 'pedidos', 'create'),
('Editar Pedidos', 'Modificar pedidos existentes', 'pedidos', 'edit'),
('Cancelar Pedidos', 'Cancelar pedidos', 'pedidos', 'cancel'),
('Ver Todos los Pedidos', 'Visualizar pedidos de todos los meseros', 'pedidos', 'view_all'),
('Cambiar Estado Pedido', 'Modificar estado de pedidos', 'pedidos', 'change_status'),
('Ver Historial Pedidos', 'Acceso al historial completo de pedidos', 'pedidos', 'view_history');

-- MÓDULO: Mesas
INSERT IGNORE INTO `permisos` (`nombre`, `descripcion`, `modulo`, `accion`) VALUES
('Ver Mesas', 'Visualizar estado de las mesas', 'mesas', 'view'),
('Gestionar Mesas', 'Administrar configuración de mesas', 'mesas', 'manage'),
('Asignar Mesas', 'Asignar mesas a meseros', 'mesas', 'assign'),
('Cambiar Estado Mesa', 'Modificar estado de las mesas', 'mesas', 'change_status'),
('Ver Ocupación', 'Visualizar ocupación de mesas', 'mesas', 'view_occupancy');

-- MÓDULO: Cocina
INSERT IGNORE INTO `permisos` (`nombre`, `descripcion`, `modulo`, `accion`) VALUES
('Ver Cocina', 'Acceso al módulo de cocina', 'cocina', 'view'),
('Ver Cola Pedidos', 'Visualizar cola de pedidos pendientes', 'cocina', 'view_queue'),
('Marcar Listo', 'Marcar pedidos como listos', 'cocina', 'mark_ready'),
('Ver Menú del Día', 'Visualizar y gestionar menú del día', 'cocina', 'view_daily_menu'),
('Gestionar Menú del Día', 'Administrar menú del día', 'cocina', 'manage_daily_menu'),
('Ver Tiempos Preparación', 'Visualizar tiempos de preparación', 'cocina', 'view_prep_times');

-- MÓDULO: Facturación
INSERT IGNORE INTO `permisos` (`nombre`, `descripcion`, `modulo`, `accion`) VALUES
('Ver Facturación', 'Acceso al módulo de facturación', 'facturacion', 'view'),
('Crear Facturas', 'Generar nuevas facturas', 'facturacion', 'create'),
('Ver Facturas', 'Visualizar facturas existentes', 'facturacion', 'view_invoices'),
('Anular Facturas', 'Anular facturas existentes', 'facturacion', 'void'),
('Procesar Pagos', 'Procesar diferentes métodos de pago', 'facturacion', 'process_payments'),
('Ver Reportes Ventas', 'Visualizar reportes de ventas', 'facturacion', 'view_sales_reports'),
('Gestionar Métodos Pago', 'Administrar métodos de pago', 'facturacion', 'manage_payment_methods');

-- MÓDULO: Análisis
INSERT IGNORE INTO `permisos` (`nombre`, `descripcion`, `modulo`, `accion`) VALUES
('Ver Análisis', 'Acceso a módulo de análisis avanzado', 'analisis', 'view'),
('Ver Reportes Avanzados', 'Visualizar reportes detallados', 'analisis', 'view_advanced_reports'),
('Exportar Reportes', 'Exportar reportes en diferentes formatos', 'analisis', 'export_reports'),
('Ver Métricas Rendimiento', 'Visualizar métricas de rendimiento', 'analisis', 'view_performance'),
('Ver Análisis Financiero', 'Acceso a análisis financiero detallado', 'analisis', 'view_financial');

-- MÓDULO: Configuración
INSERT IGNORE INTO `permisos` (`nombre`, `descripcion`, `modulo`, `accion`) VALUES
('Ver Configuración', 'Acceso al módulo de configuración', 'configuracion', 'view'),
('Gestionar Usuarios', 'Administrar usuarios del sistema', 'configuracion', 'manage_users'),
('Gestionar Roles', 'Administrar roles y permisos', 'configuracion', 'manage_roles'),
('Configurar Sistema', 'Modificar configuraciones del sistema', 'configuracion', 'system_settings'),
('Ver Logs Sistema', 'Visualizar logs del sistema', 'configuracion', 'view_logs'),
('Gestionar Backup', 'Administrar copias de seguridad', 'configuracion', 'manage_backup'),
('Configurar Impresoras', 'Configurar impresoras del sistema', 'configuracion', 'manage_printers');

-- MÓDULO: Usuarios
INSERT IGNORE INTO `permisos` (`nombre`, `descripcion`, `modulo`, `accion`) VALUES
('Ver Usuarios', 'Visualizar lista de usuarios', 'usuarios', 'view'),
('Crear Usuarios', 'Crear nuevos usuarios', 'usuarios', 'create'),
('Editar Usuarios', 'Modificar usuarios existentes', 'usuarios', 'edit'),
('Eliminar Usuarios', 'Eliminar usuarios del sistema', 'usuarios', 'delete'),
('Cambiar Contraseñas', 'Modificar contraseñas de usuarios', 'usuarios', 'change_password'),
('Ver Actividad Usuarios', 'Visualizar actividad de usuarios', 'usuarios', 'view_activity');

-- MÓDULO: Reportes
INSERT IGNORE INTO `permisos` (`nombre`, `descripcion`, `modulo`, `accion`) VALUES
('Ver Reportes', 'Acceso a módulo de reportes', 'reportes', 'view'),
('Generar Reportes', 'Generar reportes personalizados', 'reportes', 'generate'),
('Ver Reportes Ventas', 'Visualizar reportes de ventas', 'reportes', 'view_sales'),
('Ver Reportes Inventario', 'Visualizar reportes de inventario', 'reportes', 'view_inventory'),
('Ver Reportes Financieros', 'Visualizar reportes financieros', 'reportes', 'view_financial');

-- =====================================================
-- VERIFICAR PERMISOS INSERTADOS
-- =====================================================
SELECT 'Permisos insertados correctamente' as status;
SELECT COUNT(*) as total_permisos FROM permisos;
SELECT modulo, COUNT(*) as cantidad_permisos 
FROM permisos 
GROUP BY modulo 
ORDER BY modulo;