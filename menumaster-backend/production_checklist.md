# Lista de Verificación para Despliegue en Producción - MenuMaster

## ✅ Preparación Completada

### Correcciones de Código
- [x] **Namespaces estandarizados**: Todos los archivos usan `App\` en lugar de `app\`
- [x] **Dependencias verificadas**: Composer install ejecutado correctamente
- [x] **Tests ejecutados**: Sistema funcional con warnings normales de headers
- [x] **Estructura de archivos**: Organización correcta de directorios

### Archivos de Configuración Creados
- [x] **`.env.production`**: Plantilla de configuración para producción
- [x] **`.htaccess`**: Configuración de Apache con seguridad y CORS
- [x] **`deploy_guide.md`**: Guía completa de despliegue
- [x] **`.env` actualizado**: Configurado para producción con comentarios para desarrollo

## 🔄 Pasos Pendientes para Hostinger

### 1. Configuración de Base de Datos
- [ ] **Crear base de datos en Hostinger**
  - Nombre: `u[usuario]_menumaster`
  - Usuario: `u[usuario]_menumaster`
  - Contraseña: [generar segura]

- [ ] **Importar estructura de base de datos**
  - Subir y ejecutar `database/menumaster.sql`
  - Ejecutar `database/ampliar_analisis.sql` si existe

### 2. Actualización de Credenciales
- [ ] **Actualizar .env con datos reales**
  ```
  DB_NAME=u[numero_real]_menumaster
  DB_USER=u[numero_real]_menumaster
  DB_PASS=[contraseña_real_de_hostinger]
  JWT_SECRET_KEY=[generar_nueva_clave_segura]
  ```

### 3. Subida de Archivos
- [ ] **Subir archivos al servidor**
  - Directorio: `/public_html/menumaster-backend/`
  - Verificar permisos: 755 para directorios, 644 para archivos
  - Proteger `.env`: chmod 600

### 4. Configuración de Dominio
- [ ] **DNS configurado**
  - `menumaster.site` apunta a IP de Hostinger
  - SSL/TLS activado (Let's Encrypt)

- [ ] **Subdominios (opcional)**
  - `api.menumaster.site` → backend
  - `app.menumaster.site` → frontend

### 5. Verificación Post-Despliegue
- [ ] **Probar endpoints principales**
  ```bash
  curl https://menumaster.site/api/auth/login
  curl https://menumaster.site/api/productos
  curl https://menumaster.site/api/permisos
  ```

- [ ] **Verificar logs**
  - Sin errores 500 en logs de Apache
  - Conexión a base de datos exitosa
  - Headers CORS funcionando

### 6. Configuración de Seguridad
- [ ] **Archivos protegidos**
  - `.env` no accesible vía web
  - Directorios sensibles protegidos
  - Headers de seguridad activos

- [ ] **SSL/HTTPS**
  - Certificado válido
  - Redirección HTTP → HTTPS
  - Headers HSTS configurados

## 🚀 URLs de Prueba Final

Una vez desplegado, probar estas URLs:

### API Endpoints
- **Base**: `https://menumaster.site/api/`
- **Login**: `https://menumaster.site/api/auth/login`
- **Productos**: `https://menumaster.site/api/productos`
- **Categorías**: `https://menumaster.site/api/categorias`
- **Usuarios**: `https://menumaster.site/api/usuarios`
- **Permisos**: `https://menumaster.site/api/permisos`
- **Dashboard**: `https://menumaster.site/api/dashboard`

### Frontend (si aplica)
- **Principal**: `https://menumaster.site`
- **Login**: `https://menumaster.site/login`
- **Dashboard**: `https://menumaster.site/dashboard`

## 🔧 Comandos Útiles para Hostinger

### Via File Manager o SSH (si disponible)
```bash
# Verificar permisos
ls -la

# Cambiar permisos si es necesario
chmod 755 App/
chmod 644 .env
chmod 644 *.php

# Ver logs de error
tail -f /path/to/error.log
```

### Via phpMyAdmin
```sql
-- Verificar tablas
SHOW TABLES;

-- Verificar usuarios (ejemplo)
SELECT * FROM usuarios LIMIT 5;

-- Verificar permisos
SELECT * FROM permisos;
```

## 📋 Notas Importantes

1. **Backup antes de desplegar**: Siempre hacer backup de la base de datos actual
2. **Probar en staging**: Si es posible, probar en un subdominio primero
3. **Monitorear logs**: Revisar logs regularmente después del despliegue
4. **Documentar cambios**: Mantener registro de configuraciones específicas de Hostinger

## 🆘 Solución de Problemas Comunes

### Error 500
1. Verificar logs de error de PHP
2. Comprobar permisos de archivos
3. Verificar sintaxis de .htaccess

### Error de Base de Datos
1. Verificar credenciales en .env
2. Confirmar que la base de datos existe
3. Verificar permisos del usuario de BD

### CORS Errors
1. Verificar headers en .htaccess
2. Comprobar FRONTEND_URL en .env
3. Verificar configuración de dominio

---

**Estado Actual**: ✅ Código preparado y listo para despliegue
**Próximo Paso**: Configurar base de datos en Hostinger y actualizar credenciales