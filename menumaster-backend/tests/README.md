# Pruebas del Sistema MenuMaster

Este directorio contiene las pruebas para el sistema MenuMaster.

## Estructura

- `unit/` - Pruebas unitarias para componentes individuales
- `integration/` - Pruebas de integración entre componentes

## Archivos de prueba existentes

Los siguientes archivos de prueba se encuentran en la raíz del proyecto y pueden ser movidos a esta estructura:

- `test_connection.php` - Prueba de conexión a la base de datos
- `test_dashboard.php` - Prueba del dashboard
- `test_dashboard_controller.php` - Prueba del controlador de dashboard
- `test_db.php` - Prueba de la base de datos
- `test_ingredient.php` - Prueba de ingredientes
- `test_products.php` - Prueba de productos
- `test_products_api.php` - Prueba de API de productos
- `test_sistema_completo.php` - Prueba del sistema completo

## Cómo ejecutar las pruebas

Para ejecutar todas las pruebas:

```bash
php test_sistema_completo.php
```

Para ejecutar pruebas específicas:

```bash
php tests/unit/[nombre_del_archivo].php
```