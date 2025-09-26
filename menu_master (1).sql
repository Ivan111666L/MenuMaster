-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 27-09-2025 a las 01:52:53
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `menu_master`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `estado_id` int(11) NOT NULL DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id`, `nombre`, `descripcion`, `estado_id`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, 'Entradas', 'Platos para comenzar la comida', 1, '2025-09-26 18:34:32', '2025-09-26 18:34:32'),
(2, 'Platos Principales', 'Platos fuertes y principales', 1, '2025-09-26 18:34:32', '2025-09-26 18:34:32'),
(3, 'Postres', 'Dulces y postres', 1, '2025-09-26 18:34:32', '2025-09-26 18:34:32'),
(4, 'Bebidas', 'Bebidas y refrescos', 1, '2025-09-26 18:34:32', '2025-09-26 18:34:32');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `compras_proveedor`
--

CREATE TABLE `compras_proveedor` (
  `id` int(11) NOT NULL,
  `proveedor_id` int(11) NOT NULL,
  `fecha_compra` date NOT NULL,
  `numero_factura` varchar(50) DEFAULT NULL,
  `total` decimal(10,2) NOT NULL,
  `estado` varchar(20) DEFAULT 'pendiente',
  `notas` text DEFAULT NULL,
  `creado_por` int(11) DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cuadre_diario`
--

CREATE TABLE `cuadre_diario` (
  `id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `total_ventas` decimal(10,2) DEFAULT 0.00,
  `total_costos` decimal(10,2) DEFAULT 0.00,
  `rentabilidad` decimal(10,2) GENERATED ALWAYS AS (`total_ventas` - `total_costos`) STORED,
  `total_compras_proveedores` decimal(10,2) DEFAULT 0.00,
  `estado` varchar(20) DEFAULT 'pendiente',
  `notas` text DEFAULT NULL,
  `creado_por` int(11) DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalles_pedido`
--

CREATE TABLE `detalles_pedido` (
  `id` int(11) NOT NULL,
  `pedido_id` int(11) NOT NULL,
  `producto_id` int(11) DEFAULT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `notas` text DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_compra_proveedor`
--

CREATE TABLE `detalle_compra_proveedor` (
  `id` int(11) NOT NULL,
  `compra_id` int(11) NOT NULL,
  `ingrediente_id` int(11) NOT NULL,
  `cantidad` decimal(10,2) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estados_generales`
--

CREATE TABLE `estados_generales` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `estados_generales`
--

INSERT INTO `estados_generales` (`id`, `nombre`, `fecha_creacion`) VALUES
(1, 'activo', '2025-09-26 18:34:32'),
(2, 'inactivo', '2025-09-26 18:34:32');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estados_mesa`
--

CREATE TABLE `estados_mesa` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `estados_mesa`
--

INSERT INTO `estados_mesa` (`id`, `nombre`, `fecha_creacion`) VALUES
(1, 'disponible', '2025-09-26 18:34:32'),
(2, 'ocupada', '2025-09-26 18:34:32'),
(3, 'reservada', '2025-09-26 18:34:32');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estados_pedido`
--

CREATE TABLE `estados_pedido` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `estados_pedido`
--

INSERT INTO `estados_pedido` (`id`, `nombre`, `fecha_creacion`) VALUES
(1, 'pendiente', '2025-09-26 18:34:32'),
(2, 'en preparacion', '2025-09-26 18:34:32'),
(3, 'servido', '2025-09-26 18:34:32'),
(4, 'pagado', '2025-09-26 18:34:32'),
(5, 'cancelado', '2025-09-26 18:34:32');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estados_producto`
--

CREATE TABLE `estados_producto` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `estados_producto`
--

INSERT INTO `estados_producto` (`id`, `nombre`, `fecha_creacion`) VALUES
(1, 'disponible', '2025-09-26 18:34:32'),
(2, 'no disponible', '2025-09-26 18:34:32');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial_detalles_pedido`
--

CREATE TABLE `historial_detalles_pedido` (
  `id` int(11) NOT NULL,
  `historial_pedido_id` int(11) NOT NULL,
  `producto_id` int(11) DEFAULT NULL,
  `producto_nombre` varchar(100) DEFAULT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `costo_total` decimal(10,2) DEFAULT 0.00,
  `rentabilidad` decimal(10,2) GENERATED ALWAYS AS (`subtotal` - `costo_total`) STORED
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial_pedidos`
--

CREATE TABLE `historial_pedidos` (
  `id` int(11) NOT NULL,
  `pedido_id` int(11) NOT NULL COMMENT 'ID del pedido original',
  `mesa_id` int(11) DEFAULT NULL,
  `mesa_numero` varchar(50) DEFAULT NULL,
  `usuario_id` int(11) NOT NULL COMMENT 'Mesero que tomó el pedido',
  `usuario_nombre` varchar(100) DEFAULT NULL,
  `estado_final` varchar(50) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `fecha_finalizacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ingredientes`
--

CREATE TABLE `ingredientes` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `unidad_medida` varchar(20) NOT NULL,
  `stock_actual` decimal(10,2) NOT NULL DEFAULT 0.00,
  `stock_minimo` decimal(10,2) NOT NULL DEFAULT 0.00,
  `precio_compra` decimal(10,2) NOT NULL DEFAULT 0.00,
  `proveedor_id` int(11) DEFAULT NULL,
  `estado_id` int(11) NOT NULL DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ingredientes`
--

INSERT INTO `ingredientes` (`id`, `nombre`, `descripcion`, `unidad_medida`, `stock_actual`, `stock_minimo`, `precio_compra`, `proveedor_id`, `estado_id`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, 'Tomate', 'Tomate fresco', 'kg', 10.00, 2.00, 1.50, 1, 1, '2025-09-26 18:34:32', '2025-09-26 18:34:32'),
(2, 'Queso', 'Queso mozzarella', 'kg', 5.00, 1.00, 5.00, 1, 1, '2025-09-26 18:34:32', '2025-09-26 18:34:32'),
(3, 'Harina', 'Harina de trigo', 'kg', 20.00, 5.00, 0.80, 1, 1, '2025-09-26 18:34:32', '2025-09-26 18:34:32');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `failed_attempts` int(11) DEFAULT 1,
  `last_failed_attempt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `menu_del_dia`
--

CREATE TABLE `menu_del_dia` (
  `id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `precio_especial` decimal(10,2) DEFAULT NULL,
  `disponible` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mesas`
--

CREATE TABLE `mesas` (
  `id` int(11) NOT NULL,
  `numero` varchar(10) NOT NULL,
  `capacidad` int(11) NOT NULL DEFAULT 4,
  `estado_id` int(11) NOT NULL DEFAULT 1,
  `ubicacion` varchar(100) DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `mesas`
--

INSERT INTO `mesas` (`id`, `numero`, `capacidad`, `estado_id`, `ubicacion`, `fecha_creacion`) VALUES
(1, '1', 4, 1, 'Área principal', '2025-09-26 18:34:32'),
(2, '2', 4, 1, 'Área principal', '2025-09-26 18:34:32'),
(3, '3', 6, 1, 'Área principal', '2025-09-26 18:34:32'),
(4, '4', 2, 1, 'Área VIP', '2025-09-26 18:34:32'),
(5, '5', 8, 1, 'Área familiar', '2025-09-26 18:34:32');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `metodos_pago`
--

CREATE TABLE `metodos_pago` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `metodos_pago`
--

INSERT INTO `metodos_pago` (`id`, `nombre`, `descripcion`, `activo`, `fecha_creacion`) VALUES
(1, 'Efectivo', 'Pago en efectivo', 1, '2025-09-26 18:34:32'),
(2, 'Tarjeta de Crédito', 'Pago con tarjeta de crédito', 1, '2025-09-26 18:34:32'),
(3, 'Tarjeta de Débito', 'Pago con tarjeta de débito', 1, '2025-09-26 18:34:32'),
(4, 'Transferencia', 'Transferencia bancaria', 1, '2025-09-26 18:34:32');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `movimientos_inventario`
--

CREATE TABLE `movimientos_inventario` (
  `id` int(11) NOT NULL,
  `ingrediente_id` int(11) NOT NULL,
  `tipo_movimiento` enum('entrada','salida') NOT NULL,
  `cantidad` decimal(10,2) NOT NULL,
  `motivo` varchar(255) DEFAULT NULL,
  `usuario_id` int(11) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos`
--

CREATE TABLE `pagos` (
  `id` int(11) NOT NULL,
  `pedido_id` int(11) NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `metodo_pago_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `fecha_pago` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `used` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidos`
--

CREATE TABLE `pedidos` (
  `id` int(11) NOT NULL,
  `mesa_id` int(11) DEFAULT NULL,
  `usuario_id` int(11) NOT NULL,
  `estado_id` int(11) NOT NULL DEFAULT 1,
  `total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `notas` text DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `permisos`
--

CREATE TABLE `permisos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `modulo` varchar(50) NOT NULL,
  `accion` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `permisos`
--

INSERT INTO `permisos` (`id`, `nombre`, `descripcion`, `modulo`, `accion`, `created_at`) VALUES
(1, 'Ver Dashboard', 'Acceso al panel principal de control', 'dashboard', 'view', '2025-09-26 20:10:07'),
(2, 'Ver Dashboard Administrador', 'Acceso al dashboard completo de administrador', 'dashboard', 'admin_view', '2025-09-26 20:10:07'),
(3, 'Ver Dashboard Mesero', 'Acceso al dashboard espec??fico de mesero', 'dashboard', 'waiter_view', '2025-09-26 20:10:07'),
(4, 'Ver Dashboard Cocinero', 'Acceso al dashboard espec??fico de cocinero', 'dashboard', 'cook_view', '2025-09-26 20:10:07'),
(5, 'Ver Dashboard Cajero', 'Acceso al dashboard espec??fico de cajero', 'dashboard', 'cashier_view', '2025-09-26 20:10:07'),
(6, 'Ver Productos', 'Visualizar lista de productos', 'productos', 'view', '2025-09-26 20:10:07'),
(7, 'Crear Productos', 'Crear nuevos productos', 'productos', 'create', '2025-09-26 20:10:07'),
(8, 'Editar Productos', 'Modificar productos existentes', 'productos', 'edit', '2025-09-26 20:10:07'),
(9, 'Eliminar Productos', 'Eliminar productos del sistema', 'productos', 'delete', '2025-09-26 20:10:07'),
(10, 'Gestionar Categor??as', 'Administrar categor??as de productos', 'productos', 'manage_categories', '2025-09-26 20:10:07'),
(11, 'Ver Precios', 'Visualizar precios de productos', 'productos', 'view_prices', '2025-09-26 20:10:07'),
(12, 'Editar Precios', 'Modificar precios de productos', 'productos', 'edit_prices', '2025-09-26 20:10:07'),
(13, 'Ver Inventario', 'Visualizar estado del inventario', 'inventario', 'view', '2025-09-26 20:10:07'),
(14, 'Gestionar Stock', 'Administrar niveles de stock', 'inventario', 'manage_stock', '2025-09-26 20:10:07'),
(15, 'Ver Alertas Stock', 'Visualizar alertas de stock bajo', 'inventario', 'view_alerts', '2025-09-26 20:10:07'),
(16, 'Actualizar Stock', 'Modificar cantidades en inventario', 'inventario', 'update_stock', '2025-09-26 20:10:07'),
(17, 'Ver Ingredientes', 'Visualizar lista de ingredientes', 'inventario', 'view_ingredients', '2025-09-26 20:10:07'),
(18, 'Gestionar Ingredientes', 'Administrar ingredientes', 'inventario', 'manage_ingredients', '2025-09-26 20:10:07'),
(19, 'Ver Proveedores', 'Visualizar informaci??n de proveedores', 'inventario', 'view_suppliers', '2025-09-26 20:10:07'),
(20, 'Gestionar Proveedores', 'Administrar proveedores', 'inventario', 'manage_suppliers', '2025-09-26 20:10:07'),
(21, 'Ver Pedidos', 'Visualizar lista de pedidos', 'pedidos', 'view', '2025-09-26 20:10:07'),
(22, 'Crear Pedidos', 'Crear nuevos pedidos', 'pedidos', 'create', '2025-09-26 20:10:07'),
(23, 'Editar Pedidos', 'Modificar pedidos existentes', 'pedidos', 'edit', '2025-09-26 20:10:07'),
(24, 'Cancelar Pedidos', 'Cancelar pedidos', 'pedidos', 'cancel', '2025-09-26 20:10:07'),
(25, 'Ver Todos los Pedidos', 'Visualizar pedidos de todos los meseros', 'pedidos', 'view_all', '2025-09-26 20:10:07'),
(26, 'Cambiar Estado Pedido', 'Modificar estado de pedidos', 'pedidos', 'change_status', '2025-09-26 20:10:07'),
(27, 'Ver Historial Pedidos', 'Acceso al historial completo de pedidos', 'pedidos', 'view_history', '2025-09-26 20:10:07'),
(28, 'Ver Mesas', 'Visualizar estado de las mesas', 'mesas', 'view', '2025-09-26 20:10:07'),
(29, 'Gestionar Mesas', 'Administrar configuraci??n de mesas', 'mesas', 'manage', '2025-09-26 20:10:07'),
(30, 'Asignar Mesas', 'Asignar mesas a meseros', 'mesas', 'assign', '2025-09-26 20:10:07'),
(31, 'Cambiar Estado Mesa', 'Modificar estado de las mesas', 'mesas', 'change_status', '2025-09-26 20:10:07'),
(32, 'Ver Ocupaci??n', 'Visualizar ocupaci??n de mesas', 'mesas', 'view_occupancy', '2025-09-26 20:10:07'),
(33, 'Ver Cocina', 'Acceso al m??dulo de cocina', 'cocina', 'view', '2025-09-26 20:10:07'),
(34, 'Ver Cola Pedidos', 'Visualizar cola de pedidos pendientes', 'cocina', 'view_queue', '2025-09-26 20:10:07'),
(35, 'Marcar Listo', 'Marcar pedidos como listos', 'cocina', 'mark_ready', '2025-09-26 20:10:07'),
(36, 'Ver Men?? del D??a', 'Visualizar y gestionar men?? del d??a', 'cocina', 'view_daily_menu', '2025-09-26 20:10:07'),
(37, 'Gestionar Men?? del D??a', 'Administrar men?? del d??a', 'cocina', 'manage_daily_menu', '2025-09-26 20:10:07'),
(38, 'Ver Tiempos Preparaci??n', 'Visualizar tiempos de preparaci??n', 'cocina', 'view_prep_times', '2025-09-26 20:10:07'),
(39, 'Ver Facturaci??n', 'Acceso al m??dulo de facturaci??n', 'facturacion', 'view', '2025-09-26 20:10:07'),
(40, 'Crear Facturas', 'Generar nuevas facturas', 'facturacion', 'create', '2025-09-26 20:10:07'),
(41, 'Ver Facturas', 'Visualizar facturas existentes', 'facturacion', 'view_invoices', '2025-09-26 20:10:07'),
(42, 'Anular Facturas', 'Anular facturas existentes', 'facturacion', 'void', '2025-09-26 20:10:07'),
(43, 'Procesar Pagos', 'Procesar diferentes m??todos de pago', 'facturacion', 'process_payments', '2025-09-26 20:10:07'),
(44, 'Ver Reportes Ventas', 'Visualizar reportes de ventas', 'facturacion', 'view_sales_reports', '2025-09-26 20:10:07'),
(45, 'Gestionar M??todos Pago', 'Administrar m??todos de pago', 'facturacion', 'manage_payment_methods', '2025-09-26 20:10:07'),
(46, 'Ver An??lisis', 'Acceso a m??dulo de an??lisis avanzado', 'analisis', 'view', '2025-09-26 20:10:07'),
(47, 'Ver Reportes Avanzados', 'Visualizar reportes detallados', 'analisis', 'view_advanced_reports', '2025-09-26 20:10:07'),
(48, 'Exportar Reportes', 'Exportar reportes en diferentes formatos', 'analisis', 'export_reports', '2025-09-26 20:10:07'),
(49, 'Ver M??tricas Rendimiento', 'Visualizar m??tricas de rendimiento', 'analisis', 'view_performance', '2025-09-26 20:10:07'),
(50, 'Ver An??lisis Financiero', 'Acceso a an??lisis financiero detallado', 'analisis', 'view_financial', '2025-09-26 20:10:07'),
(51, 'Ver Configuraci??n', 'Acceso al m??dulo de configuraci??n', 'configuracion', 'view', '2025-09-26 20:10:07'),
(52, 'Gestionar Usuarios', 'Administrar usuarios del sistema', 'configuracion', 'manage_users', '2025-09-26 20:10:07'),
(53, 'Gestionar Roles', 'Administrar roles y permisos', 'configuracion', 'manage_roles', '2025-09-26 20:10:07'),
(54, 'Configurar Sistema', 'Modificar configuraciones del sistema', 'configuracion', 'system_settings', '2025-09-26 20:10:07'),
(55, 'Ver Logs Sistema', 'Visualizar logs del sistema', 'configuracion', 'view_logs', '2025-09-26 20:10:07'),
(56, 'Gestionar Backup', 'Administrar copias de seguridad', 'configuracion', 'manage_backup', '2025-09-26 20:10:07'),
(57, 'Configurar Impresoras', 'Configurar impresoras del sistema', 'configuracion', 'manage_printers', '2025-09-26 20:10:07'),
(58, 'Ver Usuarios', 'Visualizar lista de usuarios', 'usuarios', 'view', '2025-09-26 20:10:07'),
(59, 'Crear Usuarios', 'Crear nuevos usuarios', 'usuarios', 'create', '2025-09-26 20:10:07'),
(60, 'Editar Usuarios', 'Modificar usuarios existentes', 'usuarios', 'edit', '2025-09-26 20:10:07'),
(61, 'Eliminar Usuarios', 'Eliminar usuarios del sistema', 'usuarios', 'delete', '2025-09-26 20:10:07'),
(62, 'Cambiar Contrase??as', 'Modificar contrase??as de usuarios', 'usuarios', 'change_password', '2025-09-26 20:10:07'),
(63, 'Ver Actividad Usuarios', 'Visualizar actividad de usuarios', 'usuarios', 'view_activity', '2025-09-26 20:10:07'),
(64, 'Ver Reportes', 'Acceso a m??dulo de reportes', 'reportes', 'view', '2025-09-26 20:10:07'),
(65, 'Generar Reportes', 'Generar reportes personalizados', 'reportes', 'generate', '2025-09-26 20:10:07'),
(66, 'Ver Reportes Ventas', 'Visualizar reportes de ventas', 'reportes', 'view_sales', '2025-09-26 20:10:07'),
(67, 'Ver Reportes Inventario', 'Visualizar reportes de inventario', 'reportes', 'view_inventory', '2025-09-26 20:10:07'),
(68, 'Ver Reportes Financieros', 'Visualizar reportes financieros', 'reportes', 'view_financial', '2025-09-26 20:10:07');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio` decimal(10,2) NOT NULL,
  `imagen_url` varchar(255) DEFAULT NULL,
  `categoria_id` int(11) DEFAULT NULL,
  `tiempo_preparacion_min` int(11) DEFAULT 0,
  `estado_id` int(11) NOT NULL DEFAULT 1,
  `destacado` tinyint(1) NOT NULL DEFAULT 0,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `nombre`, `descripcion`, `precio`, `imagen_url`, `categoria_id`, `tiempo_preparacion_min`, `estado_id`, `destacado`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, 'Pizza Margarita', 'Pizza con tomate y queso', 8.50, 'pizza.jpg', 2, 15, 1, 1, '2025-09-26 18:34:32', '2025-09-26 18:34:32'),
(2, 'Ensalada Mixta', 'Ensalada de tomate y lechuga', 5.00, 'ensalada.jpg', 1, 10, 1, 0, '2025-09-26 18:34:32', '2025-09-26 18:34:32');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos_ingredientes`
--

CREATE TABLE `productos_ingredientes` (
  `id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `ingrediente_id` int(11) NOT NULL,
  `cantidad` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `productos_ingredientes`
--

INSERT INTO `productos_ingredientes` (`id`, `producto_id`, `ingrediente_id`, `cantidad`) VALUES
(1, 1, 1, 0.20),
(2, 1, 2, 0.30),
(3, 1, 3, 0.25),
(4, 2, 1, 0.10);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto_ingrediente`
--

CREATE TABLE `producto_ingrediente` (
  `id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `ingrediente_id` int(11) NOT NULL,
  `cantidad` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedores`
--

CREATE TABLE `proveedores` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `contacto` varchar(100) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `proveedores`
--

INSERT INTO `proveedores` (`id`, `nombre`, `contacto`, `telefono`, `email`, `direccion`, `fecha_creacion`) VALUES
(1, 'Proveedor General', 'Juan Pérez', '123456789', 'proveedor1@email.com', NULL, '2025-09-26 18:34:32'),
(2, 'Distribuidora Central', 'María López', '987654321', 'proveedor2@email.com', NULL, '2025-09-26 18:34:32');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedor_ingrediente`
--

CREATE TABLE `proveedor_ingrediente` (
  `id` int(11) NOT NULL,
  `proveedor_id` int(11) NOT NULL,
  `ingrediente_id` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `unidad_medida` varchar(20) NOT NULL,
  `tiempo_entrega` int(11) DEFAULT 1 COMMENT 'Tiempo de entrega en días',
  `es_preferido` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `permisos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`permisos`)),
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id`, `nombre`, `descripcion`, `permisos`, `fecha_creacion`) VALUES
(1, 'Administrador', 'Acceso completo al sistema', '[\"all\"]', '2025-09-26 18:34:32'),
(2, 'Mesero', 'Gestión de pedidos y mesas', '[\"pedidos\", \"mesas\", \"productos\"]', '2025-09-26 18:34:32'),
(3, 'Cocinero', 'Gestión de cocina y preparación', '[\"pedidos\", \"productos\", \"inventario\"]', '2025-09-26 18:34:32'),
(4, 'Cajero', 'Gestión de pagos y facturación', '[\"pedidos\", \"pagos\", \"reportes\"]', '2025-09-26 18:34:32');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol_permisos`
--

CREATE TABLE `rol_permisos` (
  `id` int(11) NOT NULL,
  `rol_id` int(11) NOT NULL,
  `permiso_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `rol_permisos`
--

INSERT INTO `rol_permisos` (`id`, `rol_id`, `permiso_id`, `created_at`) VALUES
(1, 1, 46, '2025-09-26 20:10:27'),
(2, 1, 47, '2025-09-26 20:10:27'),
(3, 1, 48, '2025-09-26 20:10:27'),
(4, 1, 49, '2025-09-26 20:10:27'),
(5, 1, 50, '2025-09-26 20:10:27'),
(6, 1, 33, '2025-09-26 20:10:27'),
(7, 1, 34, '2025-09-26 20:10:27'),
(8, 1, 35, '2025-09-26 20:10:27'),
(9, 1, 36, '2025-09-26 20:10:27'),
(10, 1, 37, '2025-09-26 20:10:27'),
(11, 1, 38, '2025-09-26 20:10:27'),
(12, 1, 51, '2025-09-26 20:10:27'),
(13, 1, 52, '2025-09-26 20:10:27'),
(14, 1, 53, '2025-09-26 20:10:27'),
(15, 1, 54, '2025-09-26 20:10:27'),
(16, 1, 55, '2025-09-26 20:10:27'),
(17, 1, 56, '2025-09-26 20:10:27'),
(18, 1, 57, '2025-09-26 20:10:27'),
(19, 1, 1, '2025-09-26 20:10:27'),
(20, 1, 2, '2025-09-26 20:10:27'),
(21, 1, 3, '2025-09-26 20:10:27'),
(22, 1, 4, '2025-09-26 20:10:27'),
(23, 1, 5, '2025-09-26 20:10:27'),
(24, 1, 39, '2025-09-26 20:10:27'),
(25, 1, 40, '2025-09-26 20:10:27'),
(26, 1, 41, '2025-09-26 20:10:27'),
(27, 1, 42, '2025-09-26 20:10:27'),
(28, 1, 43, '2025-09-26 20:10:27'),
(29, 1, 44, '2025-09-26 20:10:27'),
(30, 1, 45, '2025-09-26 20:10:27'),
(31, 1, 13, '2025-09-26 20:10:27'),
(32, 1, 14, '2025-09-26 20:10:27'),
(33, 1, 15, '2025-09-26 20:10:27'),
(34, 1, 16, '2025-09-26 20:10:27'),
(35, 1, 17, '2025-09-26 20:10:27'),
(36, 1, 18, '2025-09-26 20:10:27'),
(37, 1, 19, '2025-09-26 20:10:27'),
(38, 1, 20, '2025-09-26 20:10:27'),
(39, 1, 28, '2025-09-26 20:10:27'),
(40, 1, 29, '2025-09-26 20:10:27'),
(41, 1, 30, '2025-09-26 20:10:27'),
(42, 1, 31, '2025-09-26 20:10:27'),
(43, 1, 32, '2025-09-26 20:10:27'),
(44, 1, 21, '2025-09-26 20:10:27'),
(45, 1, 22, '2025-09-26 20:10:27'),
(46, 1, 23, '2025-09-26 20:10:27'),
(47, 1, 24, '2025-09-26 20:10:27'),
(48, 1, 25, '2025-09-26 20:10:27'),
(49, 1, 26, '2025-09-26 20:10:27'),
(50, 1, 27, '2025-09-26 20:10:27'),
(51, 1, 6, '2025-09-26 20:10:27'),
(52, 1, 7, '2025-09-26 20:10:27'),
(53, 1, 8, '2025-09-26 20:10:27'),
(54, 1, 9, '2025-09-26 20:10:27'),
(55, 1, 10, '2025-09-26 20:10:27'),
(56, 1, 11, '2025-09-26 20:10:27'),
(57, 1, 12, '2025-09-26 20:10:27'),
(58, 1, 64, '2025-09-26 20:10:27'),
(59, 1, 65, '2025-09-26 20:10:27'),
(60, 1, 66, '2025-09-26 20:10:27'),
(61, 1, 67, '2025-09-26 20:10:27'),
(62, 1, 68, '2025-09-26 20:10:27'),
(63, 1, 58, '2025-09-26 20:10:27'),
(64, 1, 59, '2025-09-26 20:10:27'),
(65, 1, 60, '2025-09-26 20:10:27'),
(66, 1, 61, '2025-09-26 20:10:27'),
(67, 1, 62, '2025-09-26 20:10:27'),
(68, 1, 63, '2025-09-26 20:10:27'),
(128, 2, 1, '2025-09-26 20:10:27'),
(129, 2, 3, '2025-09-26 20:10:27'),
(131, 2, 6, '2025-09-26 20:10:27'),
(132, 2, 11, '2025-09-26 20:10:27'),
(134, 2, 26, '2025-09-26 20:10:27'),
(135, 2, 22, '2025-09-26 20:10:27'),
(136, 2, 23, '2025-09-26 20:10:27'),
(137, 2, 21, '2025-09-26 20:10:27'),
(141, 2, 31, '2025-09-26 20:10:27'),
(142, 2, 28, '2025-09-26 20:10:27'),
(143, 2, 32, '2025-09-26 20:10:27'),
(144, 2, 40, '2025-09-26 20:10:27'),
(145, 2, 43, '2025-09-26 20:10:27'),
(146, 2, 39, '2025-09-26 20:10:27'),
(147, 2, 41, '2025-09-26 20:10:27'),
(151, 3, 4, '2025-09-26 20:10:27'),
(152, 3, 1, '2025-09-26 20:10:27'),
(154, 3, 6, '2025-09-26 20:10:27'),
(155, 3, 11, '2025-09-26 20:10:27'),
(157, 3, 13, '2025-09-26 20:10:27'),
(158, 3, 15, '2025-09-26 20:10:27'),
(159, 3, 17, '2025-09-26 20:10:27'),
(160, 3, 19, '2025-09-26 20:10:27'),
(164, 3, 26, '2025-09-26 20:10:27'),
(165, 3, 21, '2025-09-26 20:10:27'),
(167, 3, 33, '2025-09-26 20:10:27'),
(168, 3, 34, '2025-09-26 20:10:27'),
(169, 3, 35, '2025-09-26 20:10:27'),
(170, 3, 36, '2025-09-26 20:10:27'),
(171, 3, 37, '2025-09-26 20:10:27'),
(172, 3, 38, '2025-09-26 20:10:27'),
(174, 4, 5, '2025-09-26 20:10:27'),
(175, 4, 1, '2025-09-26 20:10:27'),
(177, 4, 6, '2025-09-26 20:10:27'),
(178, 4, 11, '2025-09-26 20:10:27'),
(180, 4, 21, '2025-09-26 20:10:27'),
(181, 4, 25, '2025-09-26 20:10:27'),
(183, 4, 39, '2025-09-26 20:10:27'),
(184, 4, 40, '2025-09-26 20:10:27'),
(185, 4, 41, '2025-09-26 20:10:27'),
(186, 4, 42, '2025-09-26 20:10:27'),
(187, 4, 43, '2025-09-26 20:10:27'),
(188, 4, 44, '2025-09-26 20:10:27'),
(189, 4, 45, '2025-09-26 20:10:27'),
(190, 4, 64, '2025-09-26 20:10:27'),
(191, 4, 68, '2025-09-26 20:10:27'),
(192, 4, 66, '2025-09-26 20:10:27'),
(193, 2, 66, '2025-09-26 20:10:27'),
(194, 3, 67, '2025-09-26 20:10:27'),
(195, 3, 16, '2025-09-26 20:10:27');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user_activity_log`
--

CREATE TABLE `user_activity_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(50) NOT NULL,
  `status` varchar(20) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `user_activity_log`
--

INSERT INTO `user_activity_log` (`id`, `user_id`, `action`, `status`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 2, 'register', 'success', '::1', 'unknown', '2025-09-26 19:06:43'),
(2, 3, 'register', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', '2025-09-26 19:08:33'),
(3, 3, 'login', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', '2025-09-26 19:08:38'),
(4, 3, 'login', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', '2025-09-26 19:10:10'),
(5, 3, 'login', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', '2025-09-26 19:13:47'),
(6, 3, 'login', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', '2025-09-26 19:14:24'),
(7, 4, 'login', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', '2025-09-26 19:14:50'),
(8, 3, 'login', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', '2025-09-26 19:18:10'),
(9, 3, 'login', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-26 19:19:16'),
(10, 3, 'login', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-26 19:25:13'),
(11, 3, 'login', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-26 19:28:15'),
(12, 3, 'login', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-26 19:30:49'),
(13, 3, 'login', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', '2025-09-26 19:31:26'),
(14, 3, 'login', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', '2025-09-26 19:40:48'),
(15, 3, 'login', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', '2025-09-26 19:55:58'),
(16, 3, 'login', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Trae/1.100.3 Chrome/132.0.6834.210 Electron/34.5.1 Safari/537.36', '2025-09-26 20:11:29'),
(17, 3, 'login', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', '2025-09-26 20:13:20'),
(18, 1, 'login', 'failed', '::1', 'unknown', '2025-09-26 20:36:01'),
(19, 1, 'login', 'failed', '::1', 'unknown', '2025-09-26 20:36:31'),
(20, 1, 'login', 'failed', '::1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; es-CO) WindowsPowerShell/5.1.19041.6328', '2025-09-26 20:37:08'),
(21, 1, 'login', 'success', '::1', 'unknown', '2025-09-26 20:39:38'),
(22, 1, 'login', 'success', '::1', 'unknown', '2025-09-26 20:40:13'),
(23, 1, 'login', 'success', '::1', 'unknown', '2025-09-26 20:51:13'),
(24, 1, 'login', 'success', '::1', 'unknown', '2025-09-26 20:53:31'),
(25, 1, 'login', 'success', '::1', 'unknown', '2025-09-26 20:54:46'),
(26, 1, 'login', 'success', 'unknown', 'unknown', '2025-09-26 21:00:13'),
(27, 1, 'login', 'success', '::1', 'unknown', '2025-09-26 21:00:38'),
(28, 1, 'login', 'success', '::1', 'unknown', '2025-09-26 21:08:59'),
(29, 3, 'login', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', '2025-09-26 23:41:17'),
(30, 3, 'login', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', '2025-09-26 23:50:30');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol_id` int(11) NOT NULL DEFAULT 2,
  `estado_id` int(11) NOT NULL DEFAULT 1,
  `ultimo_acceso` timestamp NULL DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `email`, `password`, `rol_id`, `estado_id`, `ultimo_acceso`, `fecha_creacion`, `fecha_actualizacion`, `last_login`) VALUES
(1, 'Administrador', 'admin@menumaster.com', '$2y$10$Kr2bEShfNzxaTGBoTE.F4.8cM7W1uuvwnq5471o6bADuIKlGKi5jy', 1, 1, NULL, '2025-09-26 18:34:32', '2025-09-26 21:08:59', '2025-09-26 21:08:59'),
(2, 'Usuario Test', 'test@example.com', '$2y$10$jezzmVYi3DWU/xFbXi9FEuL1KVQT0/FxFjyZEqjsKFE0p/xxy1BTm', 2, 1, NULL, '2025-09-26 19:06:43', '2025-09-26 19:06:43', NULL),
(3, 'tomate', 'tomat@gmail.com', '$2y$10$xflUzVOKGwCLqas2gQQaLO3mNR2EhSIjbLorcvHfULA4A0tkY9klW', 1, 1, NULL, '2025-09-26 19:08:33', '2025-09-26 23:50:30', '2025-09-26 23:50:30'),
(4, 'Admin Test', 'admin@test.com', '$2y$10$qrwB8vLsySkyJ4zRQsWNjOs/sh77RCM6OtZc00drxZDcaaJ09etJG', 1, 1, NULL, '2025-09-26 19:13:54', '2025-09-26 19:14:50', '2025-09-26 19:14:50'),
(5, 'Mesero Test', 'mesero@test.com', '$2y$10$b0r2QiZnioL7dVMb/wh0feW9W5poFGuyTlZshfH8YzZ1QyPhvMaaK', 2, 1, NULL, '2025-09-26 19:13:54', '2025-09-26 19:13:54', NULL),
(6, 'Cocinero Test', 'cocinero@test.com', '$2y$10$kFuSwuxzPRurS5PNvXamJumYPRmOODU4Q9g7vRocJP.CLAtg93BL.', 3, 1, NULL, '2025-09-26 19:13:54', '2025-09-26 19:13:54', NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`),
  ADD KEY `estado_id` (`estado_id`);

--
-- Indices de la tabla `compras_proveedor`
--
ALTER TABLE `compras_proveedor`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_proveedor_id` (`proveedor_id`),
  ADD KEY `idx_fecha_compra` (`fecha_compra`),
  ADD KEY `idx_creado_por` (`creado_por`);

--
-- Indices de la tabla `cuadre_diario`
--
ALTER TABLE `cuadre_diario`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_fecha` (`fecha`),
  ADD KEY `idx_fecha` (`fecha`),
  ADD KEY `idx_creado_por` (`creado_por`);

--
-- Indices de la tabla `detalles_pedido`
--
ALTER TABLE `detalles_pedido`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pedido_id` (`pedido_id`),
  ADD KEY `producto_id` (`producto_id`);

--
-- Indices de la tabla `detalle_compra_proveedor`
--
ALTER TABLE `detalle_compra_proveedor`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_compra_id` (`compra_id`),
  ADD KEY `idx_ingrediente_id` (`ingrediente_id`);

--
-- Indices de la tabla `estados_generales`
--
ALTER TABLE `estados_generales`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `estados_mesa`
--
ALTER TABLE `estados_mesa`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `estados_pedido`
--
ALTER TABLE `estados_pedido`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `estados_producto`
--
ALTER TABLE `estados_producto`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `historial_detalles_pedido`
--
ALTER TABLE `historial_detalles_pedido`
  ADD PRIMARY KEY (`id`),
  ADD KEY `historial_pedido_id` (`historial_pedido_id`),
  ADD KEY `producto_id` (`producto_id`);

--
-- Indices de la tabla `historial_pedidos`
--
ALTER TABLE `historial_pedidos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pedido_id` (`pedido_id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `mesa_id` (`mesa_id`);

--
-- Indices de la tabla `ingredientes`
--
ALTER TABLE `ingredientes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `proveedor_id` (`proveedor_id`),
  ADD KEY `estado_id` (`estado_id`);

--
-- Indices de la tabla `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_last_attempt` (`last_failed_attempt`);

--
-- Indices de la tabla `menu_del_dia`
--
ALTER TABLE `menu_del_dia`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `producto_fecha` (`producto_id`,`fecha`);

--
-- Indices de la tabla `mesas`
--
ALTER TABLE `mesas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `numero` (`numero`),
  ADD KEY `estado_id` (`estado_id`);

--
-- Indices de la tabla `metodos_pago`
--
ALTER TABLE `metodos_pago`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `movimientos_inventario`
--
ALTER TABLE `movimientos_inventario`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ingrediente_id` (`ingrediente_id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pedido_id` (`pedido_id`),
  ADD KEY `metodo_pago_id` (`metodo_pago_id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_token` (`token`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_expires_at` (`expires_at`);

--
-- Indices de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mesa_id` (`mesa_id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `estado_id` (`estado_id`);

--
-- Indices de la tabla `permisos`
--
ALTER TABLE `permisos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_permiso` (`modulo`,`accion`),
  ADD KEY `idx_modulo` (`modulo`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `categoria_id` (`categoria_id`),
  ADD KEY `estado_id` (`estado_id`);

--
-- Indices de la tabla `productos_ingredientes`
--
ALTER TABLE `productos_ingredientes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `producto_ingrediente` (`producto_id`,`ingrediente_id`),
  ADD KEY `ingrediente_id` (`ingrediente_id`);

--
-- Indices de la tabla `producto_ingrediente`
--
ALTER TABLE `producto_ingrediente`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `producto_ingrediente_unique` (`producto_id`,`ingrediente_id`),
  ADD KEY `idx_producto_id` (`producto_id`),
  ADD KEY `idx_ingrediente_id` (`ingrediente_id`);

--
-- Indices de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `proveedor_ingrediente`
--
ALTER TABLE `proveedor_ingrediente`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `proveedor_ingrediente_unique` (`proveedor_id`,`ingrediente_id`),
  ADD KEY `idx_proveedor_id` (`proveedor_id`),
  ADD KEY `idx_ingrediente_id` (`ingrediente_id`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `rol_permisos`
--
ALTER TABLE `rol_permisos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_rol_permiso` (`rol_id`,`permiso_id`),
  ADD KEY `idx_rol_id` (`rol_id`),
  ADD KEY `idx_permiso_id` (`permiso_id`);

--
-- Indices de la tabla `user_activity_log`
--
ALTER TABLE `user_activity_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `rol_id` (`rol_id`),
  ADD KEY `estado_id` (`estado_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `compras_proveedor`
--
ALTER TABLE `compras_proveedor`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `cuadre_diario`
--
ALTER TABLE `cuadre_diario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `detalles_pedido`
--
ALTER TABLE `detalles_pedido`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `detalle_compra_proveedor`
--
ALTER TABLE `detalle_compra_proveedor`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `estados_generales`
--
ALTER TABLE `estados_generales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `estados_mesa`
--
ALTER TABLE `estados_mesa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `estados_pedido`
--
ALTER TABLE `estados_pedido`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `estados_producto`
--
ALTER TABLE `estados_producto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `historial_detalles_pedido`
--
ALTER TABLE `historial_detalles_pedido`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `historial_pedidos`
--
ALTER TABLE `historial_pedidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `ingredientes`
--
ALTER TABLE `ingredientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `menu_del_dia`
--
ALTER TABLE `menu_del_dia`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `mesas`
--
ALTER TABLE `mesas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `metodos_pago`
--
ALTER TABLE `metodos_pago`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `movimientos_inventario`
--
ALTER TABLE `movimientos_inventario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pagos`
--
ALTER TABLE `pagos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `permisos`
--
ALTER TABLE `permisos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `productos_ingredientes`
--
ALTER TABLE `productos_ingredientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `producto_ingrediente`
--
ALTER TABLE `producto_ingrediente`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `proveedor_ingrediente`
--
ALTER TABLE `proveedor_ingrediente`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `rol_permisos`
--
ALTER TABLE `rol_permisos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=196;

--
-- AUTO_INCREMENT de la tabla `user_activity_log`
--
ALTER TABLE `user_activity_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD CONSTRAINT `categorias_ibfk_1` FOREIGN KEY (`estado_id`) REFERENCES `estados_generales` (`id`);

--
-- Filtros para la tabla `compras_proveedor`
--
ALTER TABLE `compras_proveedor`
  ADD CONSTRAINT `compras_proveedor_ibfk_1` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `compras_proveedor_ibfk_2` FOREIGN KEY (`creado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `cuadre_diario`
--
ALTER TABLE `cuadre_diario`
  ADD CONSTRAINT `cuadre_diario_ibfk_1` FOREIGN KEY (`creado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `detalles_pedido`
--
ALTER TABLE `detalles_pedido`
  ADD CONSTRAINT `detalles_pedido_ibfk_1` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `detalles_pedido_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `detalle_compra_proveedor`
--
ALTER TABLE `detalle_compra_proveedor`
  ADD CONSTRAINT `detalle_compra_proveedor_ibfk_1` FOREIGN KEY (`compra_id`) REFERENCES `compras_proveedor` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `detalle_compra_proveedor_ibfk_2` FOREIGN KEY (`ingrediente_id`) REFERENCES `ingredientes` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `historial_detalles_pedido`
--
ALTER TABLE `historial_detalles_pedido`
  ADD CONSTRAINT `fk_historial_detalles_pedido` FOREIGN KEY (`historial_pedido_id`) REFERENCES `historial_pedidos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `ingredientes`
--
ALTER TABLE `ingredientes`
  ADD CONSTRAINT `ingredientes_ibfk_1` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `ingredientes_ibfk_2` FOREIGN KEY (`estado_id`) REFERENCES `estados_generales` (`id`);

--
-- Filtros para la tabla `menu_del_dia`
--
ALTER TABLE `menu_del_dia`
  ADD CONSTRAINT `menu_del_dia_ibfk_1` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `mesas`
--
ALTER TABLE `mesas`
  ADD CONSTRAINT `mesas_ibfk_1` FOREIGN KEY (`estado_id`) REFERENCES `estados_mesa` (`id`);

--
-- Filtros para la tabla `movimientos_inventario`
--
ALTER TABLE `movimientos_inventario`
  ADD CONSTRAINT `movimientos_inventario_ibfk_1` FOREIGN KEY (`ingrediente_id`) REFERENCES `ingredientes` (`id`),
  ADD CONSTRAINT `movimientos_inventario_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD CONSTRAINT `pagos_ibfk_1` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`),
  ADD CONSTRAINT `pagos_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `pagos_ibfk_3` FOREIGN KEY (`metodo_pago_id`) REFERENCES `metodos_pago` (`id`);

--
-- Filtros para la tabla `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD CONSTRAINT `password_reset_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD CONSTRAINT `pedidos_ibfk_1` FOREIGN KEY (`mesa_id`) REFERENCES `mesas` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `pedidos_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `pedidos_ibfk_3` FOREIGN KEY (`estado_id`) REFERENCES `estados_pedido` (`id`);

--
-- Filtros para la tabla `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `productos_ibfk_1` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `productos_ibfk_2` FOREIGN KEY (`estado_id`) REFERENCES `estados_producto` (`id`);

--
-- Filtros para la tabla `productos_ingredientes`
--
ALTER TABLE `productos_ingredientes`
  ADD CONSTRAINT `productos_ingredientes_ibfk_1` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `productos_ingredientes_ibfk_2` FOREIGN KEY (`ingrediente_id`) REFERENCES `ingredientes` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `producto_ingrediente`
--
ALTER TABLE `producto_ingrediente`
  ADD CONSTRAINT `producto_ingrediente_ibfk_1` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `producto_ingrediente_ibfk_2` FOREIGN KEY (`ingrediente_id`) REFERENCES `ingredientes` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `proveedor_ingrediente`
--
ALTER TABLE `proveedor_ingrediente`
  ADD CONSTRAINT `proveedor_ingrediente_ibfk_1` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `proveedor_ingrediente_ibfk_2` FOREIGN KEY (`ingrediente_id`) REFERENCES `ingredientes` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `rol_permisos`
--
ALTER TABLE `rol_permisos`
  ADD CONSTRAINT `rol_permisos_ibfk_1` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rol_permisos_ibfk_2` FOREIGN KEY (`permiso_id`) REFERENCES `permisos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `user_activity_log`
--
ALTER TABLE `user_activity_log`
  ADD CONSTRAINT `user_activity_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `usuarios_ibfk_2` FOREIGN KEY (`estado_id`) REFERENCES `estados_generales` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
