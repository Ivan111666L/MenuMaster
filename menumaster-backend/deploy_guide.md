# Guía de Despliegue - MenuMaster en Hostinger

## Preparación para el Despliegue

### 1. Configuración de Base de Datos en Hostinger

1. **Acceder al Panel de Control de Hostinger**
   - Ir a hPanel → Bases de Datos → MySQL

2. **Crear Base de Datos**
   - Nombre: `u[tu_usuario]_menumaster`
   - Usuario: `u[tu_usuario]_menumaster`
   - Contraseña: [generar contraseña segura]

3. **Importar Estructura de Base de Datos**
   - Usar phpMyAdmin para importar el archivo `database/menumaster.sql`
   - Ejecutar también `database/ampliar_analisis.sql` si es necesario

### 2. Configuración de Archivos

1. **Actualizar .env**
   - Copiar `.env.production` a `.env`
   - Actualizar las credenciales de base de datos con los datos reales de Hostinger
   - Cambiar `JWT_SECRET_KEY` por una clave segura única

2. **Verificar Estructura de Directorios**
   ```
   public_html/
   ├── menumaster-backend/
   │   ├── App/
   │   ├── database/
   │   ├── tests/
   │   ├── .env
   │   ├── .htaccess
   │   └── index.php
   └── menumaster-frontend/ (si aplica)
   ```

### 3. Configuración del Servidor Web

1. **Archivo .htaccess Principal** (en public_html/)
   ```apache
   RewriteEngine On
   RewriteRule ^api/(.*)$ menumaster-backend/index.php [QSA,L]
   RewriteRule ^(.*)$ menumaster-frontend/$1 [QSA,L]
   ```

2. **Archivo .htaccess del Backend** (en menumaster-backend/)
   ```apache
   RewriteEngine On
   RewriteCond %{REQUEST_FILENAME} !-f
   RewriteCond %{REQUEST_FILENAME} !-d
   RewriteRule ^(.*)$ index.php [QSA,L]
   
   # Security Headers
   Header always set X-Content-Type-Options nosniff
   Header always set X-Frame-Options DENY
   Header always set X-XSS-Protection "1; mode=block"
   Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
   
   # CORS Headers
   Header always set Access-Control-Allow-Origin "https://menumaster.site"
   Header always set Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS"
   Header always set Access-Control-Allow-Headers "Content-Type, Authorization, X-Requested-With"
   ```

### 4. Configuración de Dominio

1. **DNS Configuration**
   - Apuntar `menumaster.site` a la IP de Hostinger
   - Configurar SSL/TLS (Let's Encrypt gratuito en Hostinger)

2. **Subdominios (opcional)**
   - `api.menumaster.site` → `/public_html/menumaster-backend/`
   - `app.menumaster.site` → `/public_html/menumaster-frontend/`

### 5. Verificación Post-Despliegue

1. **Probar Endpoints de API**
   ```bash
   curl https://menumaster.site/api/auth/login
   curl https://menumaster.site/api/productos
   ```

2. **Verificar Base de Datos**
   - Conexión exitosa
   - Tablas creadas correctamente
   - Datos de prueba (si aplica)

3. **Verificar Logs**
   - Revisar logs de error de PHP
   - Verificar logs de acceso

### 6. Configuración de Seguridad

1. **Permisos de Archivos**
   ```bash
   chmod 644 .env
   chmod 755 App/
   chmod 644 App/**/*.php
   ```

2. **Protección de Archivos Sensibles**
   - `.env` no debe ser accesible vía web
   - Configurar `.htaccess` para proteger directorios sensibles

### 7. Optimización para Producción

1. **PHP Configuration**
   - `display_errors = Off`
   - `log_errors = On`
   - `error_log = /path/to/error.log`

2. **Composer Optimization**
   ```bash
   composer install --no-dev --optimize-autoloader
   ```

### 8. Monitoreo y Mantenimiento

1. **Logs a Monitorear**
   - Error logs de PHP
   - Access logs del servidor
   - Logs de aplicación (si implementados)

2. **Backups Regulares**
   - Base de datos
   - Archivos de aplicación
   - Configuraciones

## URLs de Prueba Post-Despliegue

- **Frontend**: https://menumaster.site
- **API Base**: https://menumaster.site/api/
- **Login**: https://menumaster.site/api/auth/login
- **Productos**: https://menumaster.site/api/productos
- **Permisos**: https://menumaster.site/api/permisos

## Solución de Problemas Comunes

### Error 500 - Internal Server Error
- Verificar permisos de archivos
- Revisar logs de error de PHP
- Verificar configuración de .htaccess

### Error de Conexión a Base de Datos
- Verificar credenciales en .env
- Confirmar que la base de datos existe
- Verificar que el usuario tiene permisos

### CORS Errors
- Verificar configuración de CORS en .htaccess
- Actualizar FRONTEND_URL en .env
- Verificar headers de respuesta

### SSL/HTTPS Issues
- Verificar certificado SSL en Hostinger
- Actualizar URLs a HTTPS en configuración
- Verificar redirecciones HTTP → HTTPS