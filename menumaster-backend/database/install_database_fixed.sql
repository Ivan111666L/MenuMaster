-- =====================================================
-- INSTALACIÓN COMPLETA DE BASE DE DATOS MENUMASTER
-- Basado en la estructura original de menu_master.sql
-- =====================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- =====================================================
-- CONFIGURACIÓN INICIAL
-- =====================================================

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- =====================================================
-- CREACIÓN DE TABLAS PRINCIPALES
-- =====================================================

-- Tabla categorias
CREATE TABLE IF NOT EXISTS `categorias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `estado_id` int(11) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla estados_generales
CREATE TABLE IF NOT EXISTS `estados_generales` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla estados_mesa
CREATE TABLE IF NOT EXISTS `estados_mesa` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla estados_pedido
CREATE TABLE IF NOT EXISTS `estados_pedido` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla estados_producto
CREATE TABLE IF NOT EXISTS `estados_producto` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla ingredientes
CREATE TABLE IF NOT EXISTS `ingredientes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `unidad_medida` varchar(20) DEFAULT NULL,
  `stock_actual` decimal(10,2) DEFAULT 0.00,
  `stock_minimo` decimal(10,2) DEFAULT 0.00,
  `precio_compra` decimal(10,2) DEFAULT 0.00,
  `proveedor_id` int(11) DEFAULT NULL,
  `estado_id` int(11) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla menu_del_dia
CREATE TABLE IF NOT EXISTS `menu_del_dia` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `producto_id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `precio_especial` decimal(10,2) DEFAULT NULL,
  `disponible` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla mesas
CREATE TABLE IF NOT EXISTS `mesas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `numero` int(11) NOT NULL,
  `capacidad` int(11) NOT NULL,
  `estado_id` int(11) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `numero` (`numero`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla metodos_pago
CREATE TABLE IF NOT EXISTS `metodos_pago` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla movimientos_inventario
CREATE TABLE IF NOT EXISTS `movimientos_inventario` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ingrediente_id` int(11) NOT NULL,
  `tipo_movimiento` enum('entrada','salida') NOT NULL,
  `cantidad` decimal(10,2) NOT NULL,
  `motivo` varchar(255) DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `fecha_movimiento` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla productos
CREATE TABLE IF NOT EXISTS `productos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio` decimal(10,2) NOT NULL,
  `imagen_url` varchar(255) DEFAULT NULL,
  `categoria_id` int(11) DEFAULT NULL,
  `tiempo_preparacion_min` int(11) DEFAULT 0,
  `estado_id` int(11) DEFAULT 1,
  `destacado` tinyint(1) DEFAULT 0,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla productos_ingredientes
CREATE TABLE IF NOT EXISTS `productos_ingredientes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `producto_id` int(11) NOT NULL,
  `ingrediente_id` int(11) NOT NULL,
  `cantidad_necesaria` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla proveedores
CREATE TABLE IF NOT EXISTS `proveedores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `contacto` varchar(100) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `estado_id` int(11) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla roles
CREATE TABLE IF NOT EXISTS `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `permisos` text DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla usuarios
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` varchar(50) DEFAULT 'mesero',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla pedidos
CREATE TABLE IF NOT EXISTS `pedidos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mesa_id` int(11) DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `estado_id` int(11) DEFAULT 1,
  `total` decimal(10,2) DEFAULT 0.00,
  `fecha_pedido` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla detalles_pedido
CREATE TABLE IF NOT EXISTS `detalles_pedido` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pedido_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `notas` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla pagos
CREATE TABLE IF NOT EXISTS `pagos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pedido_id` int(11) NOT NULL,
  `metodo_pago_id` int(11) NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `fecha_pago` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================
-- DATOS INICIALES
-- =====================================================

-- Estados generales
INSERT IGNORE INTO `estados_generales` (`id`, `nombre`) VALUES
(1, 'activo'),
(2, 'inactivo');

-- Estados de mesa
INSERT IGNORE INTO `estados_mesa` (`id`, `nombre`) VALUES
(1, 'disponible'),
(2, 'ocupada'),
(3, 'reservada');

-- Estados de pedido
INSERT IGNORE INTO `estados_pedido` (`id`, `nombre`) VALUES
(1, 'pendiente'),
(2, 'en preparacion'),
(3, 'servido'),
(4, 'pagado'),
(5, 'cancelado');

-- Estados de producto
INSERT IGNORE INTO `estados_producto` (`id`, `nombre`) VALUES
(1, 'disponible'),
(2, 'no disponible');

-- Métodos de pago
INSERT IGNORE INTO `metodos_pago` (`id`, `nombre`) VALUES
(1, 'efectivo'),
(2, 'tarjeta'),
(3, 'transferencia'),
(4, 'otro');

-- Roles
INSERT IGNORE INTO `roles` (`id`, `nombre`, `descripcion`, `permisos`) VALUES
(1, 'admin', 'Administrador del sistema', 'all'),
(2, 'mesero', 'Mesero del restaurante', 'pedidos,mesas'),
(3, 'cocinero', 'Cocinero del restaurante', 'pedidos,productos'),
(4, 'cajero', 'Cajero del restaurante', 'pedidos,pagos');

-- Usuario administrador por defecto
INSERT IGNORE INTO `usuarios` (`id`, `nombre`, `email`, `password`, `rol`) VALUES
(1, 'Administrador', 'admin@menumaster.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Categorías básicas
INSERT IGNORE INTO `categorias` (`id`, `nombre`, `descripcion`, `estado_id`) VALUES
(1, 'Entradas', 'Platos de entrada', 1),
(2, 'Platos Principales', 'Platos principales del menú', 1),
(3, 'Postres', 'Postres y dulces', 1),
(4, 'Bebidas', 'Bebidas frías y calientes', 1);

-- Proveedores básicos
INSERT IGNORE INTO `proveedores` (`id`, `nombre`, `contacto`, `telefono`, `email`, `estado_id`) VALUES
(1, 'Proveedor General', 'Contacto General', '123456789', 'proveedor@email.com', 1);

-- Mesas básicas
INSERT IGNORE INTO `mesas` (`id`, `numero`, `capacidad`, `estado_id`) VALUES
(1, 1, 4, 1),
(2, 2, 4, 1),
(3, 3, 6, 1),
(4, 4, 2, 1),
(5, 5, 8, 1);

-- =====================================================
-- CONFIGURACIÓN DE AUTO_INCREMENT
-- =====================================================

ALTER TABLE `categorias` AUTO_INCREMENT = 5;
ALTER TABLE `estados_generales` AUTO_INCREMENT = 3;
ALTER TABLE `estados_mesa` AUTO_INCREMENT = 4;
ALTER TABLE `estados_pedido` AUTO_INCREMENT = 6;
ALTER TABLE `estados_producto` AUTO_INCREMENT = 3;
ALTER TABLE `ingredientes` AUTO_INCREMENT = 1;
ALTER TABLE `menu_del_dia` AUTO_INCREMENT = 1;
ALTER TABLE `mesas` AUTO_INCREMENT = 6;
ALTER TABLE `metodos_pago` AUTO_INCREMENT = 5;
ALTER TABLE `movimientos_inventario` AUTO_INCREMENT = 1;
ALTER TABLE `productos` AUTO_INCREMENT = 1;
ALTER TABLE `productos_ingredientes` AUTO_INCREMENT = 1;
ALTER TABLE `proveedores` AUTO_INCREMENT = 2;
ALTER TABLE `roles` AUTO_INCREMENT = 5;
ALTER TABLE `usuarios` AUTO_INCREMENT = 2;
ALTER TABLE `pedidos` AUTO_INCREMENT = 1;
ALTER TABLE `detalles_pedido` AUTO_INCREMENT = 1;
ALTER TABLE `pagos` AUTO_INCREMENT = 1;

-- =====================================================
-- FOREIGN KEY CONSTRAINTS
-- =====================================================

ALTER TABLE `categorias`
  ADD CONSTRAINT `fk_categorias_estado` FOREIGN KEY (`estado_id`) REFERENCES `estados_generales` (`id`);

ALTER TABLE `detalles_pedido`
  ADD CONSTRAINT `fk_detalles_pedido_pedido` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_detalles_pedido_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`);

ALTER TABLE `ingredientes`
  ADD CONSTRAINT `fk_ingredientes_proveedor` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`),
  ADD CONSTRAINT `fk_ingredientes_estado` FOREIGN KEY (`estado_id`) REFERENCES `estados_generales` (`id`);

ALTER TABLE `menu_del_dia`
  ADD CONSTRAINT `fk_menu_del_dia_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE;

ALTER TABLE `mesas`
  ADD CONSTRAINT `fk_mesas_estado` FOREIGN KEY (`estado_id`) REFERENCES `estados_mesa` (`id`);

ALTER TABLE `movimientos_inventario`
  ADD CONSTRAINT `fk_movimientos_inventario_ingrediente` FOREIGN KEY (`ingrediente_id`) REFERENCES `ingredientes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_movimientos_inventario_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

ALTER TABLE `pagos`
  ADD CONSTRAINT `fk_pagos_pedido` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_pagos_metodo_pago` FOREIGN KEY (`metodo_pago_id`) REFERENCES `metodos_pago` (`id`);

ALTER TABLE `pedidos`
  ADD CONSTRAINT `fk_pedidos_mesa` FOREIGN KEY (`mesa_id`) REFERENCES `mesas` (`id`),
  ADD CONSTRAINT `fk_pedidos_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `fk_pedidos_estado` FOREIGN KEY (`estado_id`) REFERENCES `estados_pedido` (`id`);

ALTER TABLE `productos`
  ADD CONSTRAINT `fk_productos_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`),
  ADD CONSTRAINT `fk_productos_estado` FOREIGN KEY (`estado_id`) REFERENCES `estados_producto` (`id`);

ALTER TABLE `productos_ingredientes`
  ADD CONSTRAINT `fk_productos_ingredientes_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_productos_ingredientes_ingrediente` FOREIGN KEY (`ingrediente_id`) REFERENCES `ingredientes` (`id`) ON DELETE CASCADE;

ALTER TABLE `proveedores`
  ADD CONSTRAINT `fk_proveedores_estado` FOREIGN KEY (`estado_id`) REFERENCES `estados_generales` (`id`);

-- =====================================================
-- TABLAS DE SEGURIDAD (OPCIONAL)
-- =====================================================

-- Tabla login_attempts
CREATE TABLE IF NOT EXISTS `login_attempts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `failed_attempts` int(11) DEFAULT 1,
  `last_failed_attempt` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;