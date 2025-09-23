-- Ampliar la tabla historial_detalles_pedido para incluir costos
ALTER TABLE historial_detalles_pedido 
ADD COLUMN costo_total DECIMAL(10,2) DEFAULT 0.00 AFTER subtotal,
ADD COLUMN rentabilidad DECIMAL(10,2) GENERATED ALWAYS AS (subtotal - costo_total) STORED;

-- Crear tabla para cuadre diario
CREATE TABLE IF NOT EXISTS cuadre_diario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fecha DATE NOT NULL,
    total_ventas DECIMAL(10,2) DEFAULT 0.00,
    total_costos DECIMAL(10,2) DEFAULT 0.00,
    rentabilidad DECIMAL(10,2) GENERATED ALWAYS AS (total_ventas - total_costos) STORED,
    total_compras_proveedores DECIMAL(10,2) DEFAULT 0.00,
    estado VARCHAR(20) DEFAULT 'pendiente',
    notas TEXT,
    creado_por INT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (creado_por) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Modificar la tabla productos para incluir relación con ingredientes
CREATE TABLE IF NOT EXISTS producto_ingrediente (
    id INT AUTO_INCREMENT PRIMARY KEY,
    producto_id INT NOT NULL,
    ingrediente_id INT NOT NULL,
    cantidad DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE,
    FOREIGN KEY (ingrediente_id) REFERENCES ingredientes(id) ON DELETE CASCADE,
    UNIQUE KEY producto_ingrediente_unique (producto_id, ingrediente_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Modificar la tabla proveedores para incluir relación con ingredientes
CREATE TABLE IF NOT EXISTS proveedor_ingrediente (
    id INT AUTO_INCREMENT PRIMARY KEY,
    proveedor_id INT NOT NULL,
    ingrediente_id INT NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    unidad_medida VARCHAR(20) NOT NULL,
    tiempo_entrega INT DEFAULT 1 COMMENT 'Tiempo de entrega en días',
    es_preferido BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (proveedor_id) REFERENCES proveedores(id) ON DELETE CASCADE,
    FOREIGN KEY (ingrediente_id) REFERENCES ingredientes(id) ON DELETE CASCADE,
    UNIQUE KEY proveedor_ingrediente_unique (proveedor_id, ingrediente_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Crear tabla para compras a proveedores
CREATE TABLE IF NOT EXISTS compras_proveedor (
    id INT AUTO_INCREMENT PRIMARY KEY,
    proveedor_id INT NOT NULL,
    fecha_compra DATE NOT NULL,
    total DECIMAL(10,2) DEFAULT 0.00,
    estado VARCHAR(20) DEFAULT 'pendiente',
    fecha_recepcion DATE,
    notas TEXT,
    creado_por INT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (proveedor_id) REFERENCES proveedores(id) ON DELETE CASCADE,
    FOREIGN KEY (creado_por) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Crear tabla para detalles de compras a proveedores
CREATE TABLE IF NOT EXISTS detalle_compra_proveedor (
    id INT AUTO_INCREMENT PRIMARY KEY,
    compra_id INT NOT NULL,
    ingrediente_id INT NOT NULL,
    cantidad DECIMAL(10,2) NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) GENERATED ALWAYS AS (cantidad * precio_unitario) STORED,
    FOREIGN KEY (compra_id) REFERENCES compras_proveedor(id) ON DELETE CASCADE,
    FOREIGN KEY (ingrediente_id) REFERENCES ingredientes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Trigger para actualizar el total de la compra
DELIMITER //
CREATE TRIGGER actualizar_total_compra AFTER INSERT ON detalle_compra_proveedor
FOR EACH ROW
BEGIN
    UPDATE compras_proveedor 
    SET total = (
        SELECT SUM(subtotal) 
        FROM detalle_compra_proveedor 
        WHERE compra_id = NEW.compra_id
    )
    WHERE id = NEW.compra_id;
END//

CREATE TRIGGER actualizar_total_compra_delete AFTER DELETE ON detalle_compra_proveedor
FOR EACH ROW
BEGIN
    UPDATE compras_proveedor 
    SET total = IFNULL((
        SELECT SUM(subtotal) 
        FROM detalle_compra_proveedor 
        WHERE compra_id = OLD.compra_id
    ), 0)
    WHERE id = OLD.compra_id;
END//
DELIMITER ;