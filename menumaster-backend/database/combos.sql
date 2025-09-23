-- Estructura para el sistema de combos

-- Tabla para los combos
CREATE TABLE IF NOT EXISTS `combos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio` decimal(10,2) NOT NULL,
  `descuento` decimal(10,2) DEFAULT 0.00,
  `imagen_url` varchar(255) DEFAULT NULL,
  `estado_id` int(11) NOT NULL DEFAULT 1,
  `destacado` tinyint(1) DEFAULT 0,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla para los elementos del combo
CREATE TABLE IF NOT EXISTS `combo_elementos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `combo_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL DEFAULT 1,
  `opcional` tinyint(1) DEFAULT 0 COMMENT 'Si es 1, el cliente puede elegir no incluirlo',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_combo_elementos_combo` (`combo_id`),
  KEY `fk_combo_elementos_producto` (`producto_id`),
  CONSTRAINT `fk_combo_elementos_combo` FOREIGN KEY (`combo_id`) REFERENCES `combos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_combo_elementos_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Modificar la tabla de detalles_pedido para soportar combos
ALTER TABLE `detalles_pedido` 
ADD COLUMN IF NOT EXISTS `combo_id` int(11) DEFAULT NULL AFTER `producto_id`,
ADD COLUMN IF NOT EXISTS `es_combo` tinyint(1) DEFAULT 0 AFTER `combo_id`,
ADD CONSTRAINT `fk_detalles_pedido_combo` FOREIGN KEY (`combo_id`) REFERENCES `combos` (`id`) ON DELETE SET NULL;

-- Tabla para elementos de combo en pedidos (para poder cancelar elementos individuales)
CREATE TABLE IF NOT EXISTS `detalles_pedido_combo_elementos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `detalle_pedido_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL DEFAULT 1,
  `precio_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `cancelado` tinyint(1) DEFAULT 0,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_detalles_combo_detalle` (`detalle_pedido_id`),
  KEY `fk_detalles_combo_producto` (`producto_id`),
  CONSTRAINT `fk_detalles_combo_detalle` FOREIGN KEY (`detalle_pedido_id`) REFERENCES `detalles_pedido` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_detalles_combo_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Modificar la tabla menu_del_dia para agregar límite de stock
ALTER TABLE `menu_del_dia` 
ADD COLUMN IF NOT EXISTS `stock_limite` int(11) DEFAULT NULL COMMENT 'Límite de unidades disponibles',
ADD COLUMN IF NOT EXISTS `stock_actual` int(11) DEFAULT NULL COMMENT 'Unidades disponibles actualmente';

-- Actualizar stock_actual si es NULL
UPDATE `menu_del_dia` SET `stock_actual` = `stock_limite` WHERE `stock_limite` IS NOT NULL AND `stock_actual` IS NULL;