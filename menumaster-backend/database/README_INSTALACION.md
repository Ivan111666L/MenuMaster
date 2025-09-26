# Guía de Instalación de Base de Datos - MenuMaster

## 📋 Resumen

Esta guía te ayudará a instalar correctamente todas las bases de datos del sistema MenuMaster sin errores en phpMyAdmin.

## 🚀 Instalación Rápida (Recomendada)

### Opción 1: Instalación Completa con un Solo Archivo

1. **Abrir phpMyAdmin**
   - Ir a `http://localhost/phpmyadmin`
   - Iniciar sesión con tus credenciales de MySQL

2. **Crear la Base de Datos**
   - Hacer clic en "Nueva" en el panel izquierdo
   - Nombre: `menu_master`
   - Cotejamiento: `utf8mb4_general_ci`
   - Hacer clic en "Crear"

3. **Importar el Archivo Maestro**
   - Seleccionar la base de datos `menu_master`
   - Ir a la pestaña "Importar"
   - Seleccionar el archivo: `install_database.sql`
   - Hacer clic en "Continuar"

4. **Verificar la Instalación**
   - Deberías ver todas las tablas creadas
   - Verificar que no hay errores en la consola

## 📁 Estructura de Archivos de Base de Datos

```
database/
├── install_database.sql          ← ARCHIVO MAESTRO (usar este)
├── menu_master.sql              ← Base principal (alternativo)
├── schema.sql                   ← Esquema básico (alternativo)
├── combos.sql                   ← Sistema de combos (alternativo)
├── historial_pedidos.sql        ← Historial (alternativo)
├── ampliar_analisis.sql         ← Análisis avanzado (alternativo)
└── migrations/
    ├── create_security_tables.sql
    └── create_password_reset_tokens_table.sql
```

## 🔧 Instalación Manual (Si la automática falla)

### Paso 1: Base Principal
```sql
-- Ejecutar en este orden:
1. menu_master.sql
2. migrations/create_security_tables.sql
3. migrations/create_password_reset_tokens_table.sql
4. historial_pedidos.sql
5. ampliar_analisis.sql
```

### Paso 2: Verificar Tablas Creadas
Después de la instalación, deberías tener estas tablas:

**Tablas Principales:**
- `usuarios`
- `roles`
- `categorias`
- `productos`
- `ingredientes`
- `mesas`
- `pedidos`
- `detalles_pedido`

**Tablas de Estado:**
- `estados_generales`
- `estados_mesa`
- `estados_pedido`
- `estados_producto`

**Tablas de Relación:**
- `productos_ingredientes`
- `movimientos_inventario`
- `pagos`
- `metodos_pago`

**Tablas de Seguridad:**
- `login_attempts`
- `user_activity_log`
- `permisos`
- `rol_permisos`
- `password_reset_tokens`

**Tablas de Historial:**
- `historial_pedidos`
- `historial_detalles_pedido`

**Tablas de Análisis:**
- `cuadre_diario`
- `producto_ingrediente`
- `proveedor_ingrediente`
- `compras_proveedor`
- `detalle_compra_proveedor`

## 🔑 Credenciales por Defecto

**Usuario Administrador:**
- Email: `admin@menumaster.com`
- Password: `password`

⚠️ **IMPORTANTE:** Cambiar esta contraseña inmediatamente después de la instalación.

## 🛠️ Configuración del Backend

Después de instalar la base de datos, configurar el archivo `.env`:

```env
DB_HOST=localhost
DB_NAME=menu_master
DB_USER=root
DB_PASS=
DB_PORT=3306
```

## ✅ Verificación de la Instalación

### 1. Verificar Conexión
```php
// Ejecutar desde: menumaster-backend/test_connection.php
<?php
require_once 'App/config/database.php';
echo "Conexión exitosa a la base de datos!";
?>
```

### 2. Verificar Tablas
```sql
-- Ejecutar en phpMyAdmin:
SHOW TABLES;
-- Debería mostrar todas las tablas listadas arriba
```

### 3. Verificar Datos Iniciales
```sql
-- Verificar roles:
SELECT * FROM roles;

-- Verificar usuario admin:
SELECT * FROM usuarios WHERE email = 'admin@menumaster.com';

-- Verificar estados:
SELECT * FROM estados_generales;
```

## 🚨 Solución de Problemas Comunes

### Error: "Table already exists"
```sql
-- Limpiar base de datos y empezar de nuevo:
DROP DATABASE IF EXISTS menu_master;
CREATE DATABASE menu_master;
USE menu_master;
-- Luego importar install_database.sql
```

### Error: "Foreign key constraint fails"
- Asegúrate de ejecutar los archivos en el orden correcto
- Usa el archivo `install_database.sql` que maneja las dependencias automáticamente

### Error: "Unknown column 'last_login'"
```sql
-- Agregar columna faltante:
ALTER TABLE usuarios ADD COLUMN last_login TIMESTAMP NULL;
```

### Error: "Table 'login_attempts' doesn't exist"
```sql
-- Crear tabla faltante:
CREATE TABLE login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    failed_attempts INT DEFAULT 1,
    last_failed_attempt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_email (email)
);
```

## 🔄 Actualización de Base de Datos Existente

Si ya tienes una instalación previa:

```sql
-- Hacer backup primero:
mysqldump -u root -p menu_master > backup_menu_master.sql

-- Luego ejecutar migraciones:
SOURCE migrations/create_security_tables.sql;
SOURCE migrations/create_password_reset_tokens_table.sql;
SOURCE historial_pedidos.sql;
SOURCE ampliar_analisis.sql;
```

## 📞 Soporte

Si encuentras problemas:

1. **Verificar logs de MySQL** en XAMPP Control Panel
2. **Revisar sintaxis SQL** en phpMyAdmin
3. **Comprobar permisos** de usuario MySQL
4. **Verificar versión** de MySQL/MariaDB (recomendado: 10.4+)

## 🎯 Próximos Pasos

Después de la instalación exitosa:

1. ✅ Cambiar contraseña del administrador
2. ✅ Configurar archivo `.env` del backend
3. ✅ Probar endpoints de la API
4. ✅ Configurar el frontend para conectar con el backend
5. ✅ Crear usuarios adicionales según sea necesario

---

**¡La instalación de MenuMaster está completa!** 🎉