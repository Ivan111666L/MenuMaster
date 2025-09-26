-- =====================================================
-- SCRIPT MAESTRO DE INSTALACIÓN DE BASE DE DATOS
-- MenuMaster - Sistema de Gestión de Restaurantes
-- =====================================================
-- 
-- INSTRUCCIONES DE USO:
-- 1. Abrir phpMyAdmin
-- 2. Crear una nueva base de datos llamada 'menu_master' (si no existe)
-- 3. Seleccionar la base de datos 'menu_master'
-- 4. Importar este archivo completo
-- 
-- ORDEN DE EJECUCIÓN:
-- 1. Creación de base de datos y configuración inicial
-- 2. Tablas principales (sin dependencias)
-- 3. Tablas con relaciones
-- 4. Restricciones de clave foránea
-- 5. Datos iniciales
-- 6. Tablas adicionales (historial, análisis)
-- 7. Tablas de seguridad
-- =====================================================

-- Configuración inicial
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- Crear base de datos si no existe
CREATE DATABASE IF NOT EXISTS `menu_master` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `menu_master`;

-- =====================================================
-- PASO 1: TABLAS PRINCIPALES (SIN DEPENDENCIAS)
-- =====================================================

-- Tabla de estados generales
CREATE TABLE IF NOT EXISTS `estados_generales` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla de estados de mesa
CREATE TABLE IF NOT EXISTS `estados_mesa` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla de estados de pedido
CREATE TABLE IF NOT EXISTS `estados_pedido` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla de estados de producto
CREATE TABLE IF NOT EXISTS `estados_producto` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla de métodos de pago
CREATE TABLE IF NOT EXISTS `metodos_pago` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla de roles
CREATE TABLE IF NOT EXISTS `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `permisos` json DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla de proveedores
CREATE TABLE IF NOT EXISTS `proveedores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `contacto` varchar(100) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================
-- PASO 2: TABLAS CON DEPENDENCIAS SIMPLES
-- =====================================================

-- Tabla de usuarios
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol_id` int(11) NOT NULL DEFAULT 2,
  `estado_id` int(11) NOT NULL DEFAULT 1,
  `ultimo_acceso` timestamp NULL DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `rol_id` (`rol_id`),
  KEY `estado_id` (`estado_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla de categorías
CREATE TABLE IF NOT EXISTS `categorias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `estado_id` int(11) NOT NULL DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`),
  KEY `estado_id` (`estado_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla de mesas
CREATE TABLE IF NOT EXISTS `mesas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `numero` varchar(10) NOT NULL,
  `capacidad` int(11) NOT NULL DEFAULT 4,
  `estado_id` int(11) NOT NULL DEFAULT 1,
  `ubicacion` varchar(100) DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `numero` (`numero`),
  KEY `estado_id` (`estado_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla de ingredientes
CREATE TABLE IF NOT EXISTS `ingredientes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `unidad_medida` varchar(20) NOT NULL,
  `stock_actual` decimal(10,2) NOT NULL DEFAULT 0.00,
  `stock_minimo` decimal(10,2) NOT NULL DEFAULT 0.00,
  `precio_compra` decimal(10,2) NOT NULL DEFAULT 0.00,
  `proveedor_id` int(11) DEFAULT NULL,
  `estado_id` int(11) NOT NULL DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `proveedor_id` (`proveedor_id`),
  KEY `estado_id` (`estado_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla de productos
CREATE TABLE IF NOT EXISTS `productos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio` decimal(10,2) NOT NULL,
  `imagen_url` varchar(255) DEFAULT NULL,
  `categoria_id` int(11) DEFAULT NULL,
  `tiempo_preparacion_min` int(11) DEFAULT 0,
  `estado_id` int(11) NOT NULL DEFAULT 1,
  `destacado` tinyint(1) NOT NULL DEFAULT 0,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `categoria_id` (`categoria_id`),
  KEY `estado_id` (`estado_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================
-- PASO 3: TABLAS CON DEPENDENCIAS MÚLTIPLES
-- =====================================================

-- Tabla de pedidos
CREATE TABLE IF NOT EXISTS `pedidos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mesa_id` int(11) DEFAULT NULL,
  `usuario_id` int(11) NOT NULL,
  `estado_id` int(11) NOT NULL DEFAULT 1,
  `total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `notas` text DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `mesa_id` (`mesa_id`),
  KEY `usuario_id` (`usuario_id`),
  KEY `estado_id` (`estado_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla de detalles de pedido
CREATE TABLE IF NOT EXISTS `detalles_pedido` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pedido_id` int(11) NOT NULL,
  `producto_id` int(11) DEFAULT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `notas` text DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `pedido_id` (`pedido_id`),
  KEY `producto_id` (`producto_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla de productos-ingredientes
CREATE TABLE IF NOT EXISTS `productos_ingredientes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `producto_id` int(11) NOT NULL,
  `ingrediente_id` int(11) NOT NULL,
  `cantidad` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `producto_ingrediente` (`producto_id`,`ingrediente_id`),
  KEY `ingrediente_id` (`ingrediente_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla de menú del día
CREATE TABLE IF NOT EXISTS `menu_del_dia` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `producto_id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `precio_especial` decimal(10,2) DEFAULT NULL,
  `disponible` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `producto_fecha` (`producto_id`,`fecha`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla de movimientos de inventario
CREATE TABLE IF NOT EXISTS `movimientos_inventario` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ingrediente_id` int(11) NOT NULL,
  `tipo_movimiento` enum('entrada','salida') NOT NULL,
  `cantidad` decimal(10,2) NOT NULL,
  `motivo` varchar(255) DEFAULT NULL,
  `usuario_id` int(11) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `ingrediente_id` (`ingrediente_id`),
  KEY `usuario_id` (`usuario_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla de pagos
CREATE TABLE IF NOT EXISTS `pagos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pedido_id` int(11) NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `metodo_pago_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `fecha_pago` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `pedido_id` (`pedido_id`),
  KEY `metodo_pago_id` (`metodo_pago_id`),
  KEY `usuario_id` (`usuario_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================
-- PASO 4: DATOS INICIALES BÁSICOS
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
INSERT IGNORE INTO `metodos_pago` (`id`, `nombre`, `descripcion`) VALUES
(1, 'Efectivo', 'Pago en efectivo'),
(2, 'Tarjeta de Crédito', 'Pago con tarjeta de crédito'),
(3, 'Tarjeta de Débito', 'Pago con tarjeta de débito'),
(4, 'Transferencia', 'Transferencia bancaria');

-- Roles
INSERT IGNORE INTO `roles` (`id`, `nombre`, `descripcion`, `permisos`) VALUES
(1, 'Administrador', 'Acceso completo al sistema', '["all"]'),
(2, 'Mesero', 'Gestión de pedidos y mesas', '["pedidos", "mesas", "productos"]'),
(3, 'Cocinero', 'Gestión de cocina y preparación', '["pedidos", "productos", "inventario"]'),
(4, 'Cajero', 'Gestión de pagos y facturación', '["pedidos", "pagos", "reportes"]');

-- Categorías básicas
INSERT IGNORE INTO `categorias` (`id`, `nombre`, `descripcion`, `estado_id`) VALUES
(1, 'Entradas', 'Platos para comenzar la comida', 1),
(2, 'Platos Principales', 'Platos fuertes y principales', 1),
(3, 'Postres', 'Dulces y postres', 1),
(4, 'Bebidas', 'Bebidas y refrescos', 1);

-- Usuario administrador por defecto
INSERT IGNORE INTO `usuarios` (`id`, `nombre`, `email`, `password`, `rol_id`, `estado_id`) VALUES
(1, 'Administrador', 'admin@menumaster.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 1);

-- Mesas básicas
INSERT IGNORE INTO `mesas` (`id`, `numero`, `capacidad`, `estado_id`, `ubicacion`) VALUES
(1, '1', 4, 1, 'Área principal'),
(2, '2', 4, 1, 'Área principal'),
(3, '3', 6, 1, 'Área principal'),
(4, '4', 2, 1, 'Área VIP'),
(5, '5', 8, 1, 'Área familiar');

-- Proveedores de ejemplo
INSERT IGNORE INTO `proveedores` (`id`, `nombre`, `contacto`, `telefono`, `email`) VALUES
(1, 'Proveedor General', 'Juan Pérez', '123456789', 'proveedor1@email.com'),
(2, 'Distribuidora Central', 'María López', '987654321', 'proveedor2@email.com');

-- Ingredientes básicos
INSERT IGNORE INTO `ingredientes` (`id`, `nombre`, `descripcion`, `unidad_medida`, `stock_actual`, `stock_minimo`, `precio_compra`, `proveedor_id`, `estado_id`) VALUES
(1, 'Tomate', 'Tomate fresco', 'kg', 10.00, 2.00, 1.50, 1, 1),
(2, 'Queso', 'Queso mozzarella', 'kg', 5.00, 1.00, 5.00, 1, 1),
(3, 'Harina', 'Harina de trigo', 'kg', 20.00, 5.00, 0.80, 1, 1);

-- Productos de ejemplo
INSERT IGNORE INTO `productos` (`id`, `nombre`, `descripcion`, `precio`, `imagen_url`, `categoria_id`, `tiempo_preparacion_min`, `estado_id`, `destacado`) VALUES
(1, 'Pizza Margarita', 'Pizza con tomate y queso', 8.50, 'pizza.jpg', 2, 15, 1, 1),
(2, 'Ensalada Mixta', 'Ensalada de tomate y lechuga', 5.00, 'ensalada.jpg', 1, 10, 1, 0);

-- Relación productos-ingredientes
INSERT IGNORE INTO `productos_ingredientes` (`producto_id`, `ingrediente_id`, `cantidad`) VALUES
(1, 1, 0.20), -- Pizza Margarita lleva 0.2kg de tomate
(1, 2, 0.30), -- Pizza Margarita lleva 0.3kg de queso
(1, 3, 0.25), -- Pizza Margarita lleva 0.25kg de harina
(2, 1, 0.10); -- Ensalada Mixta lleva 0.1kg de tomate

-- =====================================================
-- PASO 5: RESTRICCIONES DE CLAVE FORÁNEA
-- =====================================================

-- Restricciones para usuarios
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `usuarios_ibfk_2` FOREIGN KEY (`estado_id`) REFERENCES `estados_generales` (`id`);

-- Restricciones para categorías
ALTER TABLE `categorias`
  ADD CONSTRAINT `categorias_ibfk_1` FOREIGN KEY (`estado_id`) REFERENCES `estados_generales` (`id`);

-- Restricciones para mesas
ALTER TABLE `mesas`
  ADD CONSTRAINT `mesas_ibfk_1` FOREIGN KEY (`estado_id`) REFERENCES `estados_mesa` (`id`);

-- Restricciones para ingredientes
ALTER TABLE `ingredientes`
  ADD CONSTRAINT `ingredientes_ibfk_1` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `ingredientes_ibfk_2` FOREIGN KEY (`estado_id`) REFERENCES `estados_generales` (`id`);

-- Restricciones para productos
ALTER TABLE `productos`
  ADD CONSTRAINT `productos_ibfk_1` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `productos_ibfk_2` FOREIGN KEY (`estado_id`) REFERENCES `estados_producto` (`id`);

-- Restricciones para pedidos
ALTER TABLE `pedidos`
  ADD CONSTRAINT `pedidos_ibfk_1` FOREIGN KEY (`mesa_id`) REFERENCES `mesas` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `pedidos_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `pedidos_ibfk_3` FOREIGN KEY (`estado_id`) REFERENCES `estados_pedido` (`id`);

-- Restricciones para detalles de pedido
ALTER TABLE `detalles_pedido`
  ADD CONSTRAINT `detalles_pedido_ibfk_1` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `detalles_pedido_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE SET NULL;

-- Restricciones para productos-ingredientes
ALTER TABLE `productos_ingredientes`
  ADD CONSTRAINT `productos_ingredientes_ibfk_1` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `productos_ingredientes_ibfk_2` FOREIGN KEY (`ingrediente_id`) REFERENCES `ingredientes` (`id`) ON DELETE CASCADE;

-- Restricciones para menú del día
ALTER TABLE `menu_del_dia`
  ADD CONSTRAINT `menu_del_dia_ibfk_1` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE;

-- Restricciones para movimientos de inventario
ALTER TABLE `movimientos_inventario`
  ADD CONSTRAINT `movimientos_inventario_ibfk_1` FOREIGN KEY (`ingrediente_id`) REFERENCES `ingredientes` (`id`),
  ADD CONSTRAINT `movimientos_inventario_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

-- Restricciones para pagos
ALTER TABLE `pagos`
  ADD CONSTRAINT `pagos_ibfk_1` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`),
  ADD CONSTRAINT `pagos_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `pagos_ibfk_3` FOREIGN KEY (`metodo_pago_id`) REFERENCES `metodos_pago` (`id`);

-- =====================================================
-- PASO 6: AUTO_INCREMENT PARA TABLAS PRINCIPALES
-- =====================================================

ALTER TABLE `categorias` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
ALTER TABLE `detalles_pedido` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `estados_generales` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
ALTER TABLE `estados_mesa` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
ALTER TABLE `estados_pedido` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
ALTER TABLE `estados_producto` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
ALTER TABLE `ingredientes` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
ALTER TABLE `menu_del_dia` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `mesas` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
ALTER TABLE `metodos_pago` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
ALTER TABLE `movimientos_inventario` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `pagos` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `pedidos` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `productos` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
ALTER TABLE `productos_ingredientes` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
ALTER TABLE `proveedores` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
ALTER TABLE `roles` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
ALTER TABLE `usuarios` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

-- =====================================================
-- PASO 7: TABLAS DE SEGURIDAD
-- =====================================================

-- Tabla para intentos de login fallidos (protección contra fuerza bruta)
CREATE TABLE IF NOT EXISTS `login_attempts` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `email` varchar(255) NOT NULL,
    `failed_attempts` int(11) DEFAULT 1,
    `last_failed_attempt` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_email` (`email`),
    KEY `idx_email` (`email`),
    KEY `idx_last_attempt` (`last_failed_attempt`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla para log de actividad de usuarios
CREATE TABLE IF NOT EXISTS `user_activity_log` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `action` varchar(50) NOT NULL,
    `status` varchar(20) NOT NULL,
    `ip_address` varchar(45) DEFAULT NULL,
    `user_agent` text DEFAULT NULL,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_action` (`action`),
    KEY `idx_created_at` (`created_at`),
    CONSTRAINT `user_activity_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla de permisos
CREATE TABLE IF NOT EXISTS `permisos` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `nombre` varchar(50) NOT NULL,
    `descripcion` text DEFAULT NULL,
    `modulo` varchar(50) NOT NULL,
    `accion` varchar(50) NOT NULL,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_permiso` (`modulo`, `accion`),
    KEY `idx_modulo` (`modulo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla de relación rol-permisos
CREATE TABLE IF NOT EXISTS `rol_permisos` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `rol_id` int(11) NOT NULL,
    `permiso_id` int(11) NOT NULL,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_rol_permiso` (`rol_id`, `permiso_id`),
    KEY `idx_rol_id` (`rol_id`),
    KEY `idx_permiso_id` (`permiso_id`),
    CONSTRAINT `rol_permisos_ibfk_1` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
    CONSTRAINT `rol_permisos_ibfk_2` FOREIGN KEY (`permiso_id`) REFERENCES `permisos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla para tokens de restablecimiento de contraseña
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `token` varchar(255) NOT NULL,
    `expires_at` timestamp NOT NULL,
    `used` tinyint(1) DEFAULT 0,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_token` (`token`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_expires_at` (`expires_at`),
    CONSTRAINT `password_reset_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================
-- PASO 8: TABLAS DE HISTORIAL
-- =====================================================

-- Tabla de historial de pedidos
CREATE TABLE IF NOT EXISTS `historial_pedidos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pedido_id` int(11) NOT NULL COMMENT 'ID del pedido original',
  `mesa_id` int(11) DEFAULT NULL,
  `mesa_numero` varchar(50) DEFAULT NULL,
  `usuario_id` int(11) NOT NULL COMMENT 'Mesero que tomó el pedido',
  `usuario_nombre` varchar(100) DEFAULT NULL,
  `estado_final` varchar(50) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `fecha_creacion` timestamp NOT NULL,
  `fecha_finalizacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `pedido_id` (`pedido_id`),
  KEY `usuario_id` (`usuario_id`),
  KEY `mesa_id` (`mesa_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla de detalles de historial de pedidos
CREATE TABLE IF NOT EXISTS `historial_detalles_pedido` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `historial_pedido_id` int(11) NOT NULL,
  `producto_id` int(11) DEFAULT NULL,
  `producto_nombre` varchar(100) DEFAULT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `costo_total` decimal(10,2) DEFAULT 0.00,
  `rentabilidad` decimal(10,2) GENERATED ALWAYS AS (`subtotal` - `costo_total`) STORED,
  PRIMARY KEY (`id`),
  KEY `historial_pedido_id` (`historial_pedido_id`),
  KEY `producto_id` (`producto_id`),
  CONSTRAINT `fk_historial_detalles_pedido` FOREIGN KEY (`historial_pedido_id`) REFERENCES `historial_pedidos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================
-- PASO 9: TABLAS DE ANÁLISIS AVANZADO
-- =====================================================

-- Tabla para cuadre diario
CREATE TABLE IF NOT EXISTS `cuadre_diario` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `fecha` date NOT NULL,
    `total_ventas` decimal(10,2) DEFAULT 0.00,
    `total_costos` decimal(10,2) DEFAULT 0.00,
    `rentabilidad` decimal(10,2) GENERATED ALWAYS AS (`total_ventas` - `total_costos`) STORED,
    `total_compras_proveedores` decimal(10,2) DEFAULT 0.00,
    `estado` varchar(20) DEFAULT 'pendiente',
    `notas` text DEFAULT NULL,
    `creado_por` int(11) DEFAULT NULL,
    `fecha_creacion` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_fecha` (`fecha`),
    KEY `idx_fecha` (`fecha`),
    KEY `idx_creado_por` (`creado_por`),
    CONSTRAINT `cuadre_diario_ibfk_1` FOREIGN KEY (`creado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla de relación producto-ingrediente (análisis)
CREATE TABLE IF NOT EXISTS `producto_ingrediente` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `producto_id` int(11) NOT NULL,
    `ingrediente_id` int(11) NOT NULL,
    `cantidad` decimal(10,2) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `producto_ingrediente_unique` (`producto_id`, `ingrediente_id`),
    KEY `idx_producto_id` (`producto_id`),
    KEY `idx_ingrediente_id` (`ingrediente_id`),
    CONSTRAINT `producto_ingrediente_ibfk_1` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE,
    CONSTRAINT `producto_ingrediente_ibfk_2` FOREIGN KEY (`ingrediente_id`) REFERENCES `ingredientes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla de relación proveedor-ingrediente
CREATE TABLE IF NOT EXISTS `proveedor_ingrediente` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `proveedor_id` int(11) NOT NULL,
    `ingrediente_id` int(11) NOT NULL,
    `precio_unitario` decimal(10,2) NOT NULL,
    `unidad_medida` varchar(20) NOT NULL,
    `tiempo_entrega` int(11) DEFAULT 1 COMMENT 'Tiempo de entrega en días',
    `es_preferido` tinyint(1) DEFAULT 0,
    PRIMARY KEY (`id`),
    UNIQUE KEY `proveedor_ingrediente_unique` (`proveedor_id`, `ingrediente_id`),
    KEY `idx_proveedor_id` (`proveedor_id`),
    KEY `idx_ingrediente_id` (`ingrediente_id`),
    CONSTRAINT `proveedor_ingrediente_ibfk_1` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`) ON DELETE CASCADE,
    CONSTRAINT `proveedor_ingrediente_ibfk_2` FOREIGN KEY (`ingrediente_id`) REFERENCES `ingredientes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla de compras a proveedores
CREATE TABLE IF NOT EXISTS `compras_proveedor` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `proveedor_id` int(11) NOT NULL,
    `fecha_compra` date NOT NULL,
    `numero_factura` varchar(50) DEFAULT NULL,
    `total` decimal(10,2) NOT NULL,
    `estado` varchar(20) DEFAULT 'pendiente',
    `notas` text DEFAULT NULL,
    `creado_por` int(11) DEFAULT NULL,
    `fecha_creacion` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_proveedor_id` (`proveedor_id`),
    KEY `idx_fecha_compra` (`fecha_compra`),
    KEY `idx_creado_por` (`creado_por`),
    CONSTRAINT `compras_proveedor_ibfk_1` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`) ON DELETE CASCADE,
    CONSTRAINT `compras_proveedor_ibfk_2` FOREIGN KEY (`creado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla de detalles de compras a proveedores
CREATE TABLE IF NOT EXISTS `detalle_compra_proveedor` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `compra_id` int(11) NOT NULL,
    `ingrediente_id` int(11) NOT NULL,
    `cantidad` decimal(10,2) NOT NULL,
    `precio_unitario` decimal(10,2) NOT NULL,
    `subtotal` decimal(10,2) NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_compra_id` (`compra_id`),
    KEY `idx_ingrediente_id` (`ingrediente_id`),
    CONSTRAINT `detalle_compra_proveedor_ibfk_1` FOREIGN KEY (`compra_id`) REFERENCES `compras_proveedor` (`id`) ON DELETE CASCADE,
    CONSTRAINT `detalle_compra_proveedor_ibfk_2` FOREIGN KEY (`ingrediente_id`) REFERENCES `ingredientes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================
-- FINALIZACIÓN
-- =====================================================

-- Restaurar configuración original
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- Confirmar transacción
COMMIT;

-- =====================================================
-- INSTALACIÓN COMPLETADA
-- =====================================================
-- 
-- La base de datos MenuMaster ha sido instalada exitosamente.
-- 
-- CREDENCIALES POR DEFECTO:
-- Email: admin@menumaster.com
-- Password: password (cambiar inmediatamente)
-- 
-- PRÓXIMOS PASOS:
-- 1. Cambiar la contraseña del administrador
-- 2. Configurar el archivo .env del backend
-- 3. Probar la conexión desde la aplicación
-- 
-- =====================================================