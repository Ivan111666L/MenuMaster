<?php
/**
 * Test Básico de Conectividad del Sistema MenuMaster
 * Verifica componentes sin requerir conexión a base de datos
 */

class BasicConnectivityTest {
    private $results = [];
    
    private function addResult($test, $status, $message) {
        $this->results[] = [
            'test' => $test,
            'status' => $status,
            'message' => $message,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
    
    public function testFileStructure() {
        echo "=== Verificando Estructura de Archivos ===\n";
        
        $requiredDirs = [
            'App/Controllers',
            'App/models',
            'App/Middleware',
            'App/Utils',
            'App/config',
            'routes',
            'public'
        ];
        
        foreach ($requiredDirs as $dir) {
            if (is_dir($dir)) {
                $this->addResult("Directory: $dir", 'SUCCESS', 'Directorio existe');
            } else {
                $this->addResult("Directory: $dir", 'ERROR', 'Directorio no encontrado');
            }
        }
    }
    
    public function testControllerSyntax() {
        echo "=== Verificando Sintaxis de Controladores ===\n";
        
        $controllers = glob('App/Controllers/*.php');
        
        foreach ($controllers as $controller) {
            $filename = basename($controller);
            $output = shell_exec("php -l \"$controller\" 2>&1");
            
            if (strpos($output, 'No syntax errors') !== false) {
                $this->addResult("Controller: $filename", 'SUCCESS', 'Sin errores de sintaxis');
            } else {
                $this->addResult("Controller: $filename", 'ERROR', 'Error de sintaxis detectado');
            }
        }
    }
    
    public function testModelSyntax() {
        echo "=== Verificando Sintaxis de Modelos ===\n";
        
        $models = glob('App/models/*.php');
        
        foreach ($models as $model) {
            $filename = basename($model);
            $output = shell_exec("php -l \"$model\" 2>&1");
            
            if (strpos($output, 'No syntax errors') !== false) {
                $this->addResult("Model: $filename", 'SUCCESS', 'Sin errores de sintaxis');
            } else {
                $this->addResult("Model: $filename", 'ERROR', 'Error de sintaxis detectado');
            }
        }
    }
    
    public function testMiddlewareSyntax() {
        echo "=== Verificando Sintaxis de Middleware ===\n";
        
        $middlewares = glob('App/Middleware/*.php');
        
        foreach ($middlewares as $middleware) {
            $filename = basename($middleware);
            $output = shell_exec("php -l \"$middleware\" 2>&1");
            
            if (strpos($output, 'No syntax errors') !== false) {
                $this->addResult("Middleware: $filename", 'SUCCESS', 'Sin errores de sintaxis');
            } else {
                $this->addResult("Middleware: $filename", 'ERROR', 'Error de sintaxis detectado');
            }
        }
    }
    
    public function testUtilsSyntax() {
        echo "=== Verificando Sintaxis de Utilidades ===\n";
        
        $utils = glob('App/Utils/*.php');
        
        foreach ($utils as $util) {
            $filename = basename($util);
            $output = shell_exec("php -l \"$util\" 2>&1");
            
            if (strpos($output, 'No syntax errors') !== false) {
                $this->addResult("Util: $filename", 'SUCCESS', 'Sin errores de sintaxis');
            } else {
                $this->addResult("Util: $filename", 'ERROR', 'Error de sintaxis detectado');
            }
        }
    }
    
    public function testConfigSyntax() {
        echo "=== Verificando Sintaxis de Configuración ===\n";
        
        $configs = glob('App/config/*.php');
        
        foreach ($configs as $config) {
            $filename = basename($config);
            $output = shell_exec("php -l \"$config\" 2>&1");
            
            if (strpos($output, 'No syntax errors') !== false) {
                $this->addResult("Config: $filename", 'SUCCESS', 'Sin errores de sintaxis');
            } else {
                $this->addResult("Config: $filename", 'ERROR', 'Error de sintaxis detectado');
            }
        }
    }
    
    public function testRoutesSyntax() {
        echo "=== Verificando Sintaxis de Rutas ===\n";
        
        $routes = glob('routes/*.php');
        
        foreach ($routes as $route) {
            $filename = basename($route);
            $output = shell_exec("php -l \"$route\" 2>&1");
            
            if (strpos($output, 'No syntax errors') !== false) {
                $this->addResult("Route: $filename", 'SUCCESS', 'Sin errores de sintaxis');
            } else {
                $this->addResult("Route: $filename", 'ERROR', 'Error de sintaxis detectado');
            }
        }
    }
    
    public function testPublicFiles() {
        echo "=== Verificando Archivos Públicos ===\n";
        
        $publicFiles = ['public/index.php', 'public/.htaccess'];
        
        foreach ($publicFiles as $file) {
            if (file_exists($file)) {
                if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                    $output = shell_exec("php -l \"$file\" 2>&1");
                    if (strpos($output, 'No syntax errors') !== false) {
                        $this->addResult("Public: " . basename($file), 'SUCCESS', 'Sin errores de sintaxis');
                    } else {
                        $this->addResult("Public: " . basename($file), 'ERROR', 'Error de sintaxis detectado');
                    }
                } else {
                    $this->addResult("Public: " . basename($file), 'SUCCESS', 'Archivo existe');
                }
            } else {
                $this->addResult("Public: " . basename($file), 'WARNING', 'Archivo no encontrado');
            }
        }
    }
    
    public function testNamespaceConsistency() {
        echo "=== Verificando Consistencia de Namespaces ===\n";
        
        // Verificar modelos
        $models = glob('App/models/*.php');
        $inconsistentNamespaces = 0;
        
        foreach ($models as $model) {
            $content = file_get_contents($model);
            if (preg_match('/namespace\s+([^;]+);/', $content, $matches)) {
                $namespace = trim($matches[1]);
                if ($namespace !== 'App\\Models') {
                    $inconsistentNamespaces++;
                    $this->addResult("Namespace: " . basename($model), 'WARNING', "Namespace inconsistente: $namespace");
                }
            }
        }
        
        if ($inconsistentNamespaces === 0) {
            $this->addResult("Namespace Consistency", 'SUCCESS', 'Todos los namespaces son consistentes');
        } else {
            $this->addResult("Namespace Consistency", 'WARNING', "$inconsistentNamespaces archivos con namespaces inconsistentes");
        }
    }
    
    public function runAllTests() {
        echo "Iniciando Test Básico de Conectividad del Sistema MenuMaster...\n\n";
        
        $this->testFileStructure();
        $this->testControllerSyntax();
        $this->testModelSyntax();
        $this->testMiddlewareSyntax();
        $this->testUtilsSyntax();
        $this->testConfigSyntax();
        $this->testRoutesSyntax();
        $this->testPublicFiles();
        $this->testNamespaceConsistency();
        
        $this->generateReport();
    }
    
    public function generateReport() {
        echo "\n=== REPORTE DE CONECTIVIDAD BÁSICA ===\n";
        
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
            echo "\n🎉 ¡Estructura del sistema correcta!\n";
        } elseif ($errors < 5) {
            echo "\n⚠️  Sistema funcional con algunos problemas menores\n";
        } else {
            echo "\n❌ Sistema requiere correcciones importantes\n";
        }
        
        echo "\n=== RECOMENDACIONES ===\n";
        if ($errors > 0) {
            echo "- Corregir errores de sintaxis encontrados\n";
        }
        if ($warnings > 0) {
            echo "- Revisar advertencias para mejorar la consistencia\n";
        }
        echo "- Verificar configuración de base de datos para pruebas completas\n";
        echo "- Ejecutar pruebas de integración una vez corregidos los errores\n";
    }
}

// Ejecutar tests
$test = new BasicConnectivityTest();
$test->runAllTests();