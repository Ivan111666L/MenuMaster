<?php
/**
 * Test de Integridad del Sistema MenuMaster
 * Verifica que todos los componentes estén funcionando correctamente
 */

require_once 'App/config/conexionDb.php';

// Usar autoloader si existe o cargar manualmente
if (file_exists('vendor/autoload.php')) {
    require_once 'vendor/autoload.php';
}

use App\Config\ConexionDb;

class SystemIntegrityTest {
    private $db;
    private $results = [];
    
    public function __construct() {
        try {
            $this->db = ConexionDb::getConnection();
            $this->addResult('Database Connection', 'SUCCESS', 'Conexión a base de datos establecida');
        } catch (Exception $e) {
            $this->addResult('Database Connection', 'ERROR', 'Error de conexión: ' . $e->getMessage());
            die("No se puede continuar sin conexión a la base de datos\n");
        }
    }
    
    private function addResult($test, $status, $message) {
        $this->results[] = [
            'test' => $test,
            'status' => $status,
            'message' => $message,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
    
    public function testDatabaseTables() {
        echo "=== Verificando Tablas de Base de Datos ===\n";
        
        $requiredTables = [
            'usuarios', 'productos', 'categorias', 'pedidos', 'ingredientes',
            'proveedores', 'roles', 'permisos', 'rol_permisos', 'mesas',
            'estados_pedido', 'estados_mesa', 'estados_producto', 'estados_general',
            'metodos_pago', 'login_attempts', 'user_activity_log',
            'historial_pedidos', 'historial_detalles_pedido', 'compras_proveedor',
            'detalle_compra_proveedor', 'movimientos_inventario'
        ];
        
        foreach ($requiredTables as $table) {
            try {
                $stmt = $this->db->prepare("SHOW TABLES LIKE ?");
                $stmt->execute([$table]);
                
                if ($stmt->rowCount() > 0) {
                    $this->addResult("Table: $table", 'SUCCESS', 'Tabla existe');
                } else {
                    $this->addResult("Table: $table", 'WARNING', 'Tabla no encontrada');
                }
            } catch (Exception $e) {
                $this->addResult("Table: $table", 'ERROR', 'Error verificando tabla: ' . $e->getMessage());
            }
        }
    }
    
    public function testModels() {
        echo "=== Verificando Modelos ===\n";
        
        $models = [
            'UsuarioModel', 'ProductoModel', 'CategoriaModel', 'PedidoModel',
            'IngredienteModel', 'ProveedorModel', 'RolModel', 'LoginAttemptsModel',
            'UserActivityLogModel', 'HistorialPedidosModel', 'ComprasProveedorModel',
            'DashboardModel', 'MesaModel', 'ComboModel', 'CuadreDiarioModel'
        ];
        
        foreach ($models as $modelName) {
            $file = "App/models/{$modelName}.php";
            if (file_exists($file)) {
                try {
                    // Verificar sintaxis del archivo
                    $output = shell_exec("php -l \"$file\" 2>&1");
                    if (strpos($output, 'No syntax errors') !== false) {
                        $this->addResult("Model: $modelName", 'SUCCESS', 'Modelo sin errores de sintaxis');
                    } else {
                        $this->addResult("Model: $modelName", 'ERROR', 'Error de sintaxis en modelo');
                    }
                } catch (Exception $e) {
                    $this->addResult("Model: $modelName", 'ERROR', 'Error verificando modelo: ' . $e->getMessage());
                }
            } else {
                $this->addResult("Model: $modelName", 'WARNING', 'Archivo de modelo no encontrado');
            }
        }
    }
    
    public function testControllers() {
        echo "=== Verificando Controladores ===\n";
        
        $controllers = [
            'AuthController', 'UsuarioController', 'ProductoController', 
            'CategoriaController', 'PedidoController', 'IngredienteController',
            'ProveedorController', 'RolesController', 'PermisosController',
            'DashboardController', 'HistorialController', 'ComprasController',
            'SecurityController', 'AnalisisController', 'ComboController',
            'CuadreDiarioController', 'MenuDelDiaController', 'MesaController',
            'MovimientoInventarioController', 'PrinterController'
        ];
        
        foreach ($controllers as $controller) {
            $file = "App/Controllers/{$controller}.php";
            if (file_exists($file)) {
                try {
                    require_once $file;
                    $this->addResult("Controller: $controller", 'SUCCESS', 'Controlador cargado correctamente');
                } catch (Exception $e) {
                    $this->addResult("Controller: $controller", 'ERROR', 'Error cargando controlador: ' . $e->getMessage());
                }
            } else {
                $this->addResult("Controller: $controller", 'WARNING', 'Archivo de controlador no encontrado');
            }
        }
    }
    
    public function testMiddleware() {
        echo "=== Verificando Middleware ===\n";
        
        $middlewares = ['AuthMiddleware', 'RolMiddleware'];
        
        foreach ($middlewares as $middleware) {
            $file = "App/Middleware/{$middleware}.php";
            if (file_exists($file)) {
                try {
                    require_once $file;
                    $this->addResult("Middleware: $middleware", 'SUCCESS', 'Middleware cargado correctamente');
                } catch (Exception $e) {
                    $this->addResult("Middleware: $middleware", 'ERROR', 'Error cargando middleware: ' . $e->getMessage());
                }
            } else {
                $this->addResult("Middleware: $middleware", 'WARNING', 'Archivo de middleware no encontrado');
            }
        }
    }
    
    public function testUtils() {
        echo "=== Verificando Utilidades ===\n";
        
        $utils = ['AuthHelpers', 'ResponseUtil', 'Validator', 'ReportePDF', 'PrinterManager'];
        
        foreach ($utils as $util) {
            $file = "App/Utils/{$util}.php";
            if (file_exists($file)) {
                try {
                    require_once $file;
                    $this->addResult("Util: $util", 'SUCCESS', 'Utilidad cargada correctamente');
                } catch (Exception $e) {
                    $this->addResult("Util: $util", 'ERROR', 'Error cargando utilidad: ' . $e->getMessage());
                }
            } else {
                $this->addResult("Util: $util", 'WARNING', 'Archivo de utilidad no encontrado');
            }
        }
    }
    
    public function testConfig() {
        echo "=== Verificando Configuración ===\n";
        
        $configs = ['Config', 'Constantes', 'conexionDb'];
        
        foreach ($configs as $config) {
            $file = "App/config/{$config}.php";
            if (file_exists($file)) {
                try {
                    require_once $file;
                    $this->addResult("Config: $config", 'SUCCESS', 'Configuración cargada correctamente');
                } catch (Exception $e) {
                    $this->addResult("Config: $config", 'ERROR', 'Error cargando configuración: ' . $e->getMessage());
                }
            } else {
                $this->addResult("Config: $config", 'WARNING', 'Archivo de configuración no encontrado');
            }
        }
    }
    
    public function testRoutes() {
        echo "=== Verificando Rutas ===\n";
        
        $routes = [
            'auth_api', 'usuarios_api', 'productos_api', 'categorias_api',
            'pedidos_api', 'ingredientes_api', 'proveedores_api', 'roles_api',
            'permisos_api', 'dashboard_api', 'historial_api', 'compras_api',
            'security_api', 'analisis_api', 'combos_api', 'cuadre_diario_api',
            'menudeldia_api', 'mesas_api', 'movimientos_inventario_api'
        ];
        
        foreach ($routes as $route) {
            $file = "routes/{$route}.php";
            if (file_exists($file)) {
                try {
                    // Solo verificar sintaxis, no incluir para evitar conflictos
                    $output = shell_exec("php -l $file 2>&1");
                    if (strpos($output, 'No syntax errors') !== false) {
                        $this->addResult("Route: $route", 'SUCCESS', 'Ruta sin errores de sintaxis');
                    } else {
                        $this->addResult("Route: $route", 'ERROR', 'Error de sintaxis en ruta');
                    }
                } catch (Exception $e) {
                    $this->addResult("Route: $route", 'ERROR', 'Error verificando ruta: ' . $e->getMessage());
                }
            } else {
                $this->addResult("Route: $route", 'WARNING', 'Archivo de ruta no encontrado');
            }
        }
    }
    
    public function runAllTests() {
        echo "Iniciando Test de Integridad del Sistema MenuMaster...\n\n";
        
        $this->testDatabaseTables();
        $this->testModels();
        $this->testControllers();
        $this->testMiddleware();
        $this->testUtils();
        $this->testConfig();
        $this->testRoutes();
        
        $this->generateReport();
    }
    
    public function generateReport() {
        echo "\n=== REPORTE DE INTEGRIDAD DEL SISTEMA ===\n";
        
        $success = 0;
        $warnings = 0;
        $errors = 0;
        
        foreach ($this->results as $result) {
            $status = $result['status'];
            $color = '';
            
            switch ($status) {
                case 'SUCCESS':
                    $success++;
                    $color = "\033[32m"; // Verde
                    break;
                case 'WARNING':
                    $warnings++;
                    $color = "\033[33m"; // Amarillo
                    break;
                case 'ERROR':
                    $errors++;
                    $color = "\033[31m"; // Rojo
                    break;
            }
            
            echo sprintf(
                "%s[%s]\033[0m %s: %s\n",
                $color,
                $status,
                $result['test'],
                $result['message']
            );
        }
        
        echo "\n=== RESUMEN ===\n";
        echo "✅ Éxitos: $success\n";
        echo "⚠️  Advertencias: $warnings\n";
        echo "❌ Errores: $errors\n";
        echo "📊 Total de pruebas: " . count($this->results) . "\n";
        
        $percentage = ($success / count($this->results)) * 100;
        echo "📈 Porcentaje de éxito: " . number_format($percentage, 2) . "%\n";
        
        if ($errors == 0) {
            echo "\n🎉 ¡Sistema funcionando correctamente!\n";
        } elseif ($errors < 5) {
            echo "\n⚠️  Sistema funcional con algunos problemas menores\n";
        } else {
            echo "\n❌ Sistema requiere atención inmediata\n";
        }
    }
}

// Ejecutar tests
$test = new SystemIntegrityTest();
$test->runAllTests();