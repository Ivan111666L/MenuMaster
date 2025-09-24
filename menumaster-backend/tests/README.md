# Tests para MenuMaster

Este directorio contiene todos los tests del sistema MenuMaster, organizados de manera estructurada para facilitar el mantenimiento y la ejecución.

## Estructura de Tests

```
tests/
├── README.md           # Este archivo
├── TestRunner.php      # Script para ejecutar todos los tests
└── unit/              # Tests unitarios
    ├── AuthTest.php           # Tests de autenticación
    ├── DatabaseConnectionTest.php  # Tests de conexión a BD
    ├── IngredientTest.php     # Tests de ingredientes
    ├── PermisosTest.php       # Tests de permisos
    ├── ProductTest.php        # Tests de productos
    └── README.md              # Documentación de tests unitarios
```

## Cómo Ejecutar los Tests

### Ejecutar Todos los Tests
```bash
php TestRunner.php
```

### Ejecutar un Test Específico
```bash
php TestRunner.php DatabaseConnectionTest
php TestRunner.php IngredientTest
php TestRunner.php PermisosTest
php TestRunner.php ProductTest
```

## Descripción de Tests

### DatabaseConnectionTest.php
- Verifica la conexión a la base de datos
- Prueba la creación de AuthMiddleware
- Ejecuta consultas básicas de permisos

### IngredientTest.php
- Prueba la creación de ingredientes
- Verifica el modelo IngredienteModel
- Tests de CRUD básico para ingredientes

### PermisosTest.php
- Prueba el sistema de permisos
- Verifica PermisosController
- Tests de autenticación y autorización

### ProductTest.php
- Prueba la funcionalidad de productos
- Verifica productos con ingredientes
- Tests de ProductoModel y ProductoIngredientesModel

## Requisitos

- PHP 7.4 o superior
- Composer instalado
- Base de datos MySQL configurada
- Variables de entorno configuradas en .env

## Configuración

Antes de ejecutar los tests, asegúrate de que:

1. Las dependencias estén instaladas: `composer install`
2. El archivo `.env` esté configurado correctamente
3. La base de datos esté accesible
4. Los permisos de archivos sean correctos

## Interpretación de Resultados

- ✅ Test exitoso
- ❌ Test fallido
- El TestRunner muestra un resumen con estadísticas
- Los tests fallidos muestran detalles del error

## Agregar Nuevos Tests

Para agregar un nuevo test:

1. Crea un archivo en `unit/` con el nombre `NombreTest.php`
2. Sigue la estructura de los tests existentes
3. Incluye las dependencias necesarias con rutas relativas
4. El TestRunner lo detectará automáticamente

## Solución de Problemas

### Error de Conexión a BD
- Verifica las credenciales en `.env`
- Asegúrate de que MySQL esté ejecutándose
- Comprueba que la base de datos exista

### Errores de Rutas
- Los tests usan rutas relativas desde `tests/unit/`
- Verifica que los archivos existan en las rutas especificadas
- Asegúrate de que los namespaces sean correctos (App\ no app\)

### Errores de Permisos
- Algunos tests requieren tokens de autenticación válidos
- Verifica que los roles y permisos estén configurados en la BD