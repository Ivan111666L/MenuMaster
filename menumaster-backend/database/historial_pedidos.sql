-- Estructura de tabla para almacenar el historial de pedidos
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

-- Estructura de tabla para almacenar los detalles de pedidos históricos
CREATE TABLE IF NOT EXISTS `historial_detalles_pedido` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `historial_pedido_id` int(11) NOT NULL,
  `producto_id` int(11) DEFAULT NULL,
  `producto_nombre` varchar(100) DEFAULT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `historial_pedido_id` (`historial_pedido_id`),
  KEY `producto_id` (`producto_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Añadir restricciones de clave foránea
ALTER TABLE `historial_detalles_pedido`
  ADD CONSTRAINT `fk_historial_detalles_pedido` FOREIGN KEY (`historial_pedido_id`) REFERENCES `historial_pedidos` (`id`) ON DELETE CASCADE;