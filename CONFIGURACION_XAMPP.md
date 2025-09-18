# Guía de Configuración XAMPP para MenuMaster

## Requisitos Previos
- XAMPP instalado (Apache + MySQL + PHP)
- Composer instalado
- Node.js y npm instalados

## 1. Configuración de XAMPP

### 1.1 Iniciar Servicios
1. Abrir el Panel de Control de XAMPP
2. Iniciar **Apache** y **MySQL**
3. Verificar que ambos servicios estén corriendo (indicadores verdes)

### 1.2 Configuración de Apache
El proyecto ya incluye la configuración necesaria en `.htaccess`:

```apache
<IfModule mod_headers.c>
    Header set Access-Control-Allow-Origin "http://localhost:5173"
    Header set Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS"
    Header set Access-Control-Allow-Headers "Content-Type, Authorization"
</IfModule>

<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^ index.php [QSA,L]
</IfModule>
```

**Verificar que los módulos estén habilitados:**
- `mod_rewrite` (para URL amigables)
- `mod_headers` (para CORS)

## 2. Configuración de la Base de Datos

### 2.1 Crear la Base de Datos
1. Abrir phpMyAdmin: `http://localhost/phpmyadmin`
2. Crear nueva base de datos llamada `menu_master`
3. Importar el archivo SQL: `menu_master.sql`

```sql
-- La base de datos ya está configurada con:
-- - Tablas: usuarios, productos, categorias, pedidos, etc.
-- - Usuario por defecto: michaelripoll9@gmail.com / 112233
-- - Datos de prueba incluidos
```

### 2.2 Verificar Configuración de Conexión
El archivo `.env` debe contener:

```env
JWT_SECRET_KEY="c341dbcecccff64510e332e8fa9bbb9ebf85719ba6a529640b8dc6d0749ac463"
JWT_EXPIRES_SECONDS=3600
DB_HOST=localhost
DB_NAME=menu_master
DB_USER=root
DB_PASS=
DB_CHARSET=utf8mb4
FRONTEND_URL=http://localhost:5173
```

## 3. Configuración del Backend PHP

### 3.1 Ubicación del Proyecto
Copiar la carpeta `menumaster-backend` a:
- **Windows**: `C:\xampp\htdocs\menumaster-backend`
- **macOS**: `/Applications/XAMPP/htdocs/menumaster-backend`
- **Linux**: `/opt/lampp/htdocs/menumaster-backend`

### 3.2 Instalar Dependencias
```bash
cd /ruta/a/xampp/htdocs/menumaster-backend
composer install
```

### 3.3 Configurar Virtual Host (Opcional pero Recomendado)
Editar `httpd-vhosts.conf` en XAMPP:

```apache
<VirtualHost *:80>
    DocumentRoot "/Applications/XAMPP/htdocs/menumaster-backend/public"
    ServerName menumaster.local
    <Directory "/Applications/XAMPP/htdocs/menumaster-backend/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Agregar a `/etc/hosts`:
```
127.0.0.1 menumaster.local
```

## 4. Configuración del Frontend React

### 4.1 Instalar Dependencias
```bash
cd menumaster-frontend
npm install
```

### 4.2 Configurar Variables de Entorno
Crear archivo `.env` en el frontend:

```env
VITE_API_URL=http://localhost/menumaster-backend/public
# O si usas virtual host:
# VITE_API_URL=http://menumaster.local
```

## 5. URLs de Acceso

### Backend API
- **Con XAMPP directo**: `http://localhost/menumaster-backend/public`
- **Con Virtual Host**: `http://menumaster.local`

### Frontend React
- **Desarrollo**: `http://localhost:5173`

### Base de Datos
- **phpMyAdmin**: `http://localhost/phpmyadmin`

## 6. Estructura de Archivos en XAMPP

```
htdocs/
└── menumaster-backend/
    ├── .env                    # Variables de entorno
    ├── composer.json          # Dependencias PHP
    ├── public/
    │   ├── .htaccess         # Configuración Apache
    │   └── index.php         # Punto de entrada
    ├── App/
    │   ├── Controllers/      # Controladores
    │   ├── Models/          # Modelos
    │   └── config/          # Configuración DB
    └── routes/              # Rutas API
```

## 7. Verificación de la Instalación

### 7.1 Probar Backend
Visitar: `http://localhost/menumaster-backend/public`
Debería mostrar una respuesta JSON o página de estado.

### 7.2 Probar Conexión a Base de Datos
Visitar: `http://localhost/menumaster-backend/public/test_connection.php`

### 7.3 Probar API
```bash
# Ejemplo de login
curl -X POST http://localhost/menumaster-backend/public/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"michaelripoll9@gmail.com","password":"112233"}'
```

## 8. Solución de Problemas Comunes

### Error 500 - Internal Server Error
- Verificar que `mod_rewrite` esté habilitado
- Revisar permisos de archivos
- Verificar logs de Apache en `xampp/apache/logs/error.log`

### Error de Conexión a Base de Datos
- Verificar que MySQL esté corriendo
- Comprobar credenciales en `.env`
- Verificar que la base de datos `menu_master` exista

### Error CORS
- Verificar configuración en `.htaccess`
- Asegurar que `mod_headers` esté habilitado
- Verificar URL del frontend en configuración CORS

### Composer no encontrado
```bash
# Instalar Composer globalmente
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

## 9. Comandos Útiles

### Reiniciar Servicios XAMPP
```bash
# macOS/Linux
sudo /Applications/XAMPP/xamppfiles/xampp restart

# Windows (como administrador)
xampp_stop.exe
xampp_start.exe
```

### Ver Logs de Apache
```bash
tail -f /Applications/XAMPP/xamppfiles/logs/error_log
```

### Verificar PHP
```bash
php -v
php -m  # Ver módulos instalados
```

## 10. Configuración de Producción

Para producción, modificar:
- Cambiar `APP_ENV` a `production` en `.env`
- Usar contraseñas seguras para la base de datos
- Configurar HTTPS
- Restringir CORS a dominios específicos
- Habilitar logs de errores apropiados

---

**Nota**: Esta configuración está optimizada para desarrollo local con XAMPP. Para producción, considera usar un servidor web dedicado con configuraciones de seguridad adicionales.