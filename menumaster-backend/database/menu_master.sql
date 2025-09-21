-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 15-09-2025 a las 11:10:45
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
(1, 'Entradas', 'Platos para comenzar la comida', 1, '2025-09-15 02:07:13', '2025-09-15 02:07:13'),
(2, 'Platos Principales', 'Platos fuertes y principales', 1, '2025-09-15 02:07:13', '2025-09-15 02:07:13'),
(3, 'Postres', 'Dulces y postres', 1, '2025-09-15 02:07:13', '2025-09-15 02:07:13'),
(4, 'Bebidas', 'Bebidas y refrescos', 1, '2025-09-15 02:07:13', '2025-09-15 02:07:13');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalles_pedido`
--

CREATE TABLE `detalles_pedido` (
  `id` int(11) NOT NULL,
  `pedido_id` int(11) NOT NULL,
  `producto_id` int(11) DEFAULT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL COMMENT 'Precio del producto al momento de la venta',
  `subtotal` decimal(10,2) GENERATED ALWAYS AS (`cantidad` * `precio_unitario`) STORED,
  `notas` text DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estados_generales`
--

CREATE TABLE `estados_generales` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `estados_generales`
--

INSERT INTO `estados_generales` (`id`, `nombre`) VALUES
(1, 'activo'),
(2, 'inactivo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estados_mesa`
--

CREATE TABLE `estados_mesa` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `estados_mesa`
--

INSERT INTO `estados_mesa` (`id`, `nombre`) VALUES
(1, 'disponible'),
(2, 'ocupada'),
(3, 'reservada');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estados_pedido`
--

CREATE TABLE `estados_pedido` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `estados_pedido`
--

INSERT INTO `estados_pedido` (`id`, `nombre`) VALUES
(5, 'cancelado'),
(2, 'en preparacion'),
(4, 'pagado'),
(1, 'pendiente'),
(3, 'servido');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estados_producto`
--

CREATE TABLE `estados_producto` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `estados_producto`
--

INSERT INTO `estados_producto` (`id`, `nombre`) VALUES
(1, 'disponible'),
(2, 'no disponible');

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
  `precio_compra` decimal(10,2) DEFAULT NULL,
  `proveedor_id` int(11) DEFAULT NULL,
  `estado_id` int(11) NOT NULL DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `menu_del_dia`
--

CREATE TABLE `menu_del_dia` (
  `id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `fecha` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mesas`
--

CREATE TABLE `mesas` (
  `id` int(11) NOT NULL,
  `numero` varchar(10) NOT NULL,
  `capacidad` int(11) NOT NULL,
  `ubicacion` varchar(50) DEFAULT NULL,
  `estado_id` int(11) NOT NULL DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `mesas`
--

INSERT INTO `mesas` (`id`, `numero`, `capacidad`, `ubicacion`, `estado_id`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, 'M1', 2, 'Interior', 1, '2025-09-15 02:07:13', '2025-09-15 02:07:13'),
(2, 'M2', 4, 'Interior', 1, '2025-09-15 02:07:13', '2025-09-15 02:07:13'),
(3, 'M3', 6, 'Terraza', 1, '2025-09-15 02:07:13', '2025-09-15 02:07:13'),
(4, 'M4', 4, 'Terraza', 1, '2025-09-15 02:07:13', '2025-09-15 02:07:13');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `metodos_pago`
--

CREATE TABLE `metodos_pago` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `metodos_pago`
--

INSERT INTO `metodos_pago` (`id`, `nombre`) VALUES
(1, 'efectivo'),
(4, 'otro'),
(2, 'tarjeta'),
(3, 'transferencia');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `movimientos_inventario`
--

CREATE TABLE `movimientos_inventario` (
  `id` int(11) NOT NULL,
  `ingrediente_id` int(11) NOT NULL,
  `tipo_movimiento` enum('entrada','salida','ajuste') NOT NULL,
  `cantidad` decimal(10,2) NOT NULL,
  `motivo` varchar(100) DEFAULT NULL,
  `usuario_id` int(11) NOT NULL,
  `fecha_movimiento` timestamp NOT NULL DEFAULT current_timestamp()
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
  `usuario_id` int(11) NOT NULL COMMENT 'Cajero que procesó el pago',
  `fecha_pago` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidos`
--

CREATE TABLE `pedidos` (
  `id` int(11) NOT NULL,
  `mesa_id` int(11) DEFAULT NULL,
  `usuario_id` int(11) NOT NULL COMMENT 'Mesero que tomó el pedido',
  `estado_id` int(11) NOT NULL DEFAULT 1,
  `notas` text DEFAULT NULL,
  `total` decimal(10,2) GENERATED ALWAYS AS (0) VIRTUAL COMMENT 'El total debe calcularse desde la app o con un trigger',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `tiempo_preparacion_min` int(11) DEFAULT NULL COMMENT 'Tiempo estimado de preparación en minutos',
  `estado_id` int(11) NOT NULL DEFAULT 1,
  `destacado` tinyint(1) DEFAULT 0,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos_ingredientes`
--

CREATE TABLE `productos_ingredientes` (
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
  `email` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id`, `nombre`) VALUES
(1, 'administrador'),
(4, 'cajero'),
(3, 'cocinero'),
(2, 'mesero');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL COMMENT 'Debe almacenarse un HASH, no texto plano',
  `rol_id` int(11) NOT NULL,
  `estado_id` int(11) NOT NULL DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_token_expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `email`, `password`, `rol_id`, `estado_id`, `fecha_creacion`, `fecha_actualizacion`, `reset_token`, `reset_token_expires_at`) VALUES
(1, 'Michael', 'michaelripoll9@gmail.com', '112233', 1, 1, '2025-09-15 02:07:13', '2025-09-15 02:07:13', NULL, NULL);

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
-- Indices de la tabla `detalles_pedido`
--
ALTER TABLE `detalles_pedido`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pedido_id` (`pedido_id`),
  ADD KEY `producto_id` (`producto_id`);

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
-- Indices de la tabla `ingredientes`
--
ALTER TABLE `ingredientes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`),
  ADD KEY `proveedor_id` (`proveedor_id`),
  ADD KEY `estado_id` (`estado_id`);

--
-- Indices de la tabla `menu_del_dia`
--
ALTER TABLE `menu_del_dia`
  ADD PRIMARY KEY (`id`),
  ADD KEY `producto_id` (`producto_id`);

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
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `metodo_pago_id` (`metodo_pago_id`);

--
-- Indices de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mesa_id` (`mesa_id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `estado_id` (`estado_id`);

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
  ADD PRIMARY KEY (`producto_id`,`ingrediente_id`),
  ADD KEY `ingrediente_id` (`ingrediente_id`);

--
-- Indices de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

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
-- AUTO_INCREMENT de la tabla `detalles_pedido`
--
ALTER TABLE `detalles_pedido`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `estados_generales`
--
ALTER TABLE `estados_generales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `estados_mesa`
--
ALTER TABLE `estados_mesa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `estados_pedido`
--
ALTER TABLE `estados_pedido`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `estados_producto`
--
ALTER TABLE `estados_producto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `ingredientes`
--
ALTER TABLE `ingredientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `menu_del_dia`
--
ALTER TABLE `menu_del_dia`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `mesas`
--
ALTER TABLE `mesas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

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
-- AUTO_INCREMENT de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD CONSTRAINT `categorias_ibfk_1` FOREIGN KEY (`estado_id`) REFERENCES `estados_generales` (`id`);

--
-- Filtros para la tabla `detalles_pedido`
--
ALTER TABLE `detalles_pedido`
  ADD CONSTRAINT `detalles_pedido_ibfk_1` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `detalles_pedido_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE SET NULL;

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
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `usuarios_ibfk_2` FOREIGN KEY (`estado_id`) REFERENCES `estados_generales` (`id`);
COMMIT;

-- DATOS SIMULADOS PARA USO COMPLETO DEL SOFTWARE

-- Proveedores
INSERT INTO proveedores (nombre, contacto, telefono, email) VALUES
('Proveedor 1', 'Juan Perez', '123456789', 'proveedor1@email.com'),
('Proveedor 2', 'Maria Lopez', '987654321', 'proveedor2@email.com');

-- Ingredientes
INSERT INTO ingredientes (nombre, descripcion, unidad_medida, stock_actual, stock_minimo, precio_compra, proveedor_id, estado_id)
VALUES
('Tomate', 'Tomate fresco', 'kg', 10, 2, 1.50, 1, 1),
('Queso', 'Queso mozzarella', 'kg', 5, 1, 5.00, 2, 1),
('Harina', 'Harina de trigo', 'kg', 20, 5, 0.80, 1, 1);

-- Productos
INSERT INTO productos (nombre, descripcion, precio, imagen_url, categoria_id, tiempo_preparacion_min, estado_id, destacado)
VALUES
('Pizza Margarita', 'Pizza con tomate y queso', 8.50, 'pizza.jpg', 2, 15, 1, 1),
('Ensalada Mixta', 'Ensalada de tomate y lechuga', 5.00, 'ensalada.jpg', 1, 10, 1, 0);

-- Relación productos-ingredientes
INSERT INTO productos_ingredientes (producto_id, ingrediente_id, cantidad)
VALUES
(1, 1, 0.2),
(1, 2, 0.3),
(1, 3, 0.25),
(2, 1, 0.1);

-- Mesas
INSERT INTO mesas (numero, capacidad, ubicacion, estado_id) VALUES
('M1', 2, 'Interior', 1),
('M2', 4, 'Terraza', 1);

-- Usuarios
INSERT INTO usuarios (nombre, email, password, rol_id, estado_id) VALUES
('Admin', 'admin@email.com', 'adminpass', 1, 1),
('Mesero', 'mesero@email.com', 'meseropass', 2, 1);

-- Pedidos
INSERT INTO pedidos (mesa_id, usuario_id, estado_id, notas)
VALUES
(1, 1, 1, 'Sin sal en la ensalada');

-- Detalles del pedido
INSERT INTO detalles_pedido (pedido_id, producto_id, cantidad, precio_unitario, notas)
VALUES
(1, 1, 2, 8.50, 'Sin orégano'),
(1, 2, 1, 5.00, 'Sin cebolla');

-- Pagos
INSERT INTO pagos (pedido_id, monto, metodo_pago_id, usuario_id)
VALUES
(1, 22.00, 1, 1);

-- Datos de ejemplo para pruebas del aplicativo MenuMaster

-- Ingredientes
INSERT INTO ingredientes (nombre, descripcion, unidad_medida, stock_actual, stock_minimo, precio_compra, proveedor_id, estado_id)
VALUES
('Tomate', 'Tomate fresco', 'kg', 10, 2, 1.50, 1, 1),
('Queso', 'Queso mozzarella', 'kg', 5, 1, 5.00, 1, 1),
('Harina', 'Harina de trigo', 'kg', 20, 5, 0.80, 1, 1);

-- Productos
INSERT INTO productos (nombre, descripcion, precio, imagen_url, categoria_id, tiempo_preparacion_min, estado_id, destacado)
VALUES
('Pizza Margarita', 'Pizza con tomate y queso', 8.50, 'pizza.jpg', 2, 15, 1, 1),
('Ensalada Mixta', 'Ensalada de tomate y lechuga', 5.00, 'ensalada.jpg', 1, 10, 1, 0);

-- Relación productos-ingredientes
INSERT INTO productos_ingredientes (producto_id, ingrediente_id, cantidad)
VALUES
(1, 1, 0.2), -- Pizza Margarita lleva 0.2kg de tomate
(1, 2, 0.3), -- Pizza Margarita lleva 0.3kg de queso
(1, 3, 0.25), -- Pizza Margarita lleva 0.25kg de harina
(2, 1, 0.1); -- Ensalada Mixta lleva 0.1kg de tomate

-- Pedidos
INSERT INTO pedidos (mesa_id, usuario_id, estado_id, notas)
VALUES
(1, 1, 1, 'Sin sal en la ensalada');

-- Detalles del pedido
INSERT INTO detalles_pedido (pedido_id, producto_id, cantidad, precio_unitario, notas)
VALUES
(1, 1, 2, 8.50, 'Sin orégano'),
(1, 2, 1, 5.00, 'Sin cebolla');

-- Pagos
INSERT INTO pagos (pedido_id, monto, metodo_pago_id, usuario_id)
VALUES
(1, 22.00, 1, 1);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
