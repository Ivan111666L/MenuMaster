-- Tabla para intentos de login fallidos (protección contra fuerza bruta)
CREATE TABLE IF NOT EXISTS login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    failed_attempts INT DEFAULT 1,
    last_failed_attempt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_email (email),
    INDEX idx_email (email),
    INDEX idx_last_attempt (last_failed_attempt)
);

-- Tabla para log de actividad de usuarios
CREATE TABLE IF NOT EXISTS user_activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action VARCHAR(50) NOT NULL,
    status VARCHAR(20) NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at),
    FOREIGN KEY (user_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Agregar columna last_login a la tabla usuarios si no existe
ALTER TABLE usuarios 
ADD COLUMN IF NOT EXISTS last_login TIMESTAMP NULL,
ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

-- Tabla para permisos del sistema
CREATE TABLE IF NOT EXISTS permisos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    descripcion TEXT,
    modulo VARCHAR(50) NOT NULL,
    accion VARCHAR(50) NOT NULL,
    estado_id INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_modulo (modulo),
    INDEX idx_estado (estado_id)
);

-- Tabla para asignar permisos a roles
CREATE TABLE IF NOT EXISTS rol_permisos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rol_id INT NOT NULL,
    permiso_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_rol_permiso (rol_id, permiso_id),
    FOREIGN KEY (rol_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (permiso_id) REFERENCES permisos(id) ON DELETE CASCADE
);

-- Insertar permisos básicos del sistema
INSERT IGNORE INTO permisos (nombre, descripcion, modulo, accion) VALUES
-- Usuarios
('usuarios.crear', 'Crear nuevos usuarios', 'usuarios', 'crear'),
('usuarios.leer', 'Ver lista de usuarios', 'usuarios', 'leer'),
('usuarios.actualizar', 'Actualizar información de usuarios', 'usuarios', 'actualizar'),
('usuarios.eliminar', 'Eliminar usuarios', 'usuarios', 'eliminar'),

-- Roles
('roles.crear', 'Crear nuevos roles', 'roles', 'crear'),
('roles.leer', 'Ver lista de roles', 'roles', 'leer'),
('roles.actualizar', 'Actualizar roles', 'roles', 'actualizar'),
('roles.eliminar', 'Eliminar roles', 'roles', 'eliminar'),

-- Productos
('productos.crear', 'Crear nuevos productos', 'productos', 'crear'),
('productos.leer', 'Ver lista de productos', 'productos', 'leer'),
('productos.actualizar', 'Actualizar productos', 'productos', 'actualizar'),
('productos.eliminar', 'Eliminar productos', 'productos', 'eliminar'),

-- Pedidos
('pedidos.crear', 'Crear nuevos pedidos', 'pedidos', 'crear'),
('pedidos.leer', 'Ver pedidos', 'pedidos', 'leer'),
('pedidos.actualizar', 'Actualizar pedidos', 'pedidos', 'actualizar'),
('pedidos.eliminar', 'Eliminar pedidos', 'pedidos', 'eliminar'),

-- Mesas
('mesas.crear', 'Crear nuevas mesas', 'mesas', 'crear'),
('mesas.leer', 'Ver mesas', 'mesas', 'leer'),
('mesas.actualizar', 'Actualizar mesas', 'mesas', 'actualizar'),
('mesas.eliminar', 'Eliminar mesas', 'mesas', 'eliminar'),

-- Dashboard
('dashboard.ver', 'Ver dashboard principal', 'dashboard', 'ver'),
('dashboard.estadisticas', 'Ver estadísticas avanzadas', 'dashboard', 'estadisticas'),

-- Reportes
('reportes.ventas', 'Ver reportes de ventas', 'reportes', 'ventas'),
('reportes.inventario', 'Ver reportes de inventario', 'reportes', 'inventario'),
('reportes.personal', 'Ver reportes de personal', 'reportes', 'personal'),

-- Configuración
('configuracion.sistema', 'Configurar sistema', 'configuracion', 'sistema'),
('configuracion.usuarios', 'Configurar usuarios', 'configuracion', 'usuarios');

-- Asignar permisos básicos al rol administrador (asumiendo que el rol 1 es administrador)
INSERT IGNORE INTO rol_permisos (rol_id, permiso_id)
SELECT 1, id FROM permisos WHERE estado_id = 1;

-- Asignar permisos básicos al rol mesero (asumiendo que el rol 2 es mesero)
INSERT IGNORE INTO rol_permisos (rol_id, permiso_id)
SELECT 2, id FROM permisos 
WHERE nombre IN (
    'productos.leer', 
    'pedidos.crear', 
    'pedidos.leer', 
    'pedidos.actualizar',
    'mesas.leer',
    'mesas.actualizar',
    'dashboard.ver'
);