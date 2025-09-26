<?php
/**
 * Test de Endpoints API del Sistema MenuMaster
 * Verifica que todos los endpoints estén funcionando correctamente
 */

class APIEndpointTest {
    private $baseUrl;
    private $token;
    private $results = [];
    
    public function __construct() {
        $this->baseUrl = 'http://localhost/MenuMaster/menumaster-backend/public';
    }
    
    private function addResult($test, $status, $message, $httpCode = null) {
        $this->results[] = [
            'test' => $test,
            'status' => $status,
            'message' => $message,
            'http_code' => $httpCode,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
    
    private function makeRequest($endpoint, $method = 'GET', $data = null, $headers = []) {
        $url = $this->baseUrl . $endpoint;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        
        // Headers por defecto
        $defaultHeaders = [
            'Content-Type: application/json',
            'Accept: application/json'
        ];
        
        if ($this->token) {
            $defaultHeaders[] = 'Authorization: Bearer ' . $this->token;
        }
        
        $allHeaders = array_merge($defaultHeaders, $headers);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $allHeaders);
        
        switch (strtoupper($method)) {
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, true);
                if ($data) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                }
                break;
            case 'PUT':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                if ($data) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                }
                break;
            case 'DELETE':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return [
                'success' => false,
                'error' => $error,
                'http_code' => 0
            ];
        }
        
        return [
            'success' => true,
            'data' => json_decode($response, true),
            'raw_response' => $response,
            'http_code' => $httpCode
        ];
    }
    
    public function testAuthentication() {
        echo "=== Probando Autenticación ===\n";
        
        // Test login
        $loginData = [
            'email' => 'admin@menumaster.com',
            'password' => 'admin123'
        ];
        
        $response = $this->makeRequest('/api/auth/login', 'POST', $loginData);
        
        if ($response['success'] && $response['http_code'] === 200) {
            $data = $response['data'];
            if (isset($data['data']['token'])) {
                $this->token = $data['data']['token'];
                $this->addResult('Auth Login', 'SUCCESS', 'Login exitoso, token obtenido', $response['http_code']);
            } else {
                $this->addResult('Auth Login', 'ERROR', 'Login exitoso pero sin token', $response['http_code']);
            }
        } else {
            $this->addResult('Auth Login', 'ERROR', 'Error en login: ' . ($response['error'] ?? 'HTTP ' . $response['http_code']), $response['http_code']);
        }
        
        // Test verify token si tenemos token
        if ($this->token) {
            $verifyResponse = $this->makeRequest('/api/auth/verify', 'POST');
            if ($verifyResponse['success'] && $verifyResponse['http_code'] === 200) {
                $this->addResult('Auth Verify', 'SUCCESS', 'Token válido', $verifyResponse['http_code']);
            } else {
                $this->addResult('Auth Verify', 'WARNING', 'Error verificando token', $verifyResponse['http_code']);
            }
        }
    }
    
    public function testCRUDEndpoints() {
        echo "=== Probando Endpoints CRUD ===\n";
        
        $endpoints = [
            // Endpoints básicos
            '/api/usuarios' => 'Usuarios',
            '/api/productos' => 'Productos',
            '/api/categorias' => 'Categorías',
            '/api/ingredientes' => 'Ingredientes',
            '/api/proveedores' => 'Proveedores',
            '/api/roles' => 'Roles',
            '/api/permisos' => 'Permisos',
            '/api/mesas' => 'Mesas',
            '/api/pedidos' => 'Pedidos',
            
            // Endpoints especiales
            '/api/dashboard' => 'Dashboard',
            '/api/historial' => 'Historial',
            '/api/compras' => 'Compras',
            '/api/security' => 'Security',
            '/api/analisis' => 'Análisis',
            '/api/combos' => 'Combos',
            '/api/cuadre-diario' => 'Cuadre Diario',
            '/api/menu-del-dia' => 'Menú del Día',
            '/api/movimientos-inventario' => 'Movimientos Inventario'
        ];
        
        foreach ($endpoints as $endpoint => $name) {
            $response = $this->makeRequest($endpoint, 'GET');
            
            if ($response['success']) {
                if ($response['http_code'] === 200) {
                    $this->addResult("GET $name", 'SUCCESS', 'Endpoint responde correctamente', $response['http_code']);
                } elseif ($response['http_code'] === 401) {
                    $this->addResult("GET $name", 'WARNING', 'Requiere autenticación', $response['http_code']);
                } elseif ($response['http_code'] === 403) {
                    $this->addResult("GET $name", 'WARNING', 'Sin permisos suficientes', $response['http_code']);
                } else {
                    $this->addResult("GET $name", 'ERROR', 'Error HTTP', $response['http_code']);
                }
            } else {
                $this->addResult("GET $name", 'ERROR', 'Error de conexión: ' . $response['error'], 0);
            }
        }
    }
    
    public function testSpecificEndpoints() {
        echo "=== Probando Endpoints Específicos ===\n";
        
        // Test endpoints con parámetros específicos
        $specificTests = [
            [
                'endpoint' => '/api/dashboard/stats',
                'name' => 'Dashboard Stats',
                'method' => 'GET'
            ],
            [
                'endpoint' => '/api/productos/categoria/1',
                'name' => 'Productos por Categoría',
                'method' => 'GET'
            ],
            [
                'endpoint' => '/api/historial/pedidos',
                'name' => 'Historial Pedidos',
                'method' => 'GET'
            ],
            [
                'endpoint' => '/api/security/login-attempts',
                'name' => 'Intentos de Login',
                'method' => 'GET'
            ],
            [
                'endpoint' => '/api/compras/proveedores',
                'name' => 'Compras Proveedores',
                'method' => 'GET'
            ]
        ];
        
        foreach ($specificTests as $test) {
            $response = $this->makeRequest($test['endpoint'], $test['method']);
            
            if ($response['success']) {
                if (in_array($response['http_code'], [200, 201])) {
                    $this->addResult($test['name'], 'SUCCESS', 'Endpoint específico funciona', $response['http_code']);
                } elseif ($response['http_code'] === 401) {
                    $this->addResult($test['name'], 'WARNING', 'Requiere autenticación', $response['http_code']);
                } elseif ($response['http_code'] === 404) {
                    $this->addResult($test['name'], 'WARNING', 'Endpoint no encontrado', $response['http_code']);
                } else {
                    $this->addResult($test['name'], 'ERROR', 'Error HTTP', $response['http_code']);
                }
            } else {
                $this->addResult($test['name'], 'ERROR', 'Error de conexión', 0);
            }
        }
    }
    
    public function testServerConnectivity() {
        echo "=== Probando Conectividad del Servidor ===\n";
        
        // Test básico del servidor
        $response = $this->makeRequest('/', 'GET');
        
        if ($response['success']) {
            $this->addResult('Server Basic', 'SUCCESS', 'Servidor responde', $response['http_code']);
        } else {
            $this->addResult('Server Basic', 'ERROR', 'Servidor no responde: ' . $response['error'], 0);
            return false;
        }
        
        // Test del index.php
        $response = $this->makeRequest('/index.php', 'GET');
        
        if ($response['success']) {
            $this->addResult('Server Index', 'SUCCESS', 'Index.php accesible', $response['http_code']);
        } else {
            $this->addResult('Server Index', 'ERROR', 'Index.php no accesible', $response['http_code']);
        }
        
        return true;
    }
    
    public function runAllTests() {
        echo "Iniciando Test de Endpoints API del Sistema MenuMaster...\n\n";
        
        // Verificar conectividad básica primero
        if (!$this->testServerConnectivity()) {
            echo "❌ No se puede conectar al servidor. Verificar que XAMPP esté ejecutándose.\n";
            return;
        }
        
        $this->testAuthentication();
        $this->testCRUDEndpoints();
        $this->testSpecificEndpoints();
        
        $this->generateReport();
    }
    
    public function generateReport() {
        echo "\n=== REPORTE DE ENDPOINTS API ===\n";
        
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
            
            $httpInfo = $result['http_code'] ? " (HTTP {$result['http_code']})" : "";
            echo sprintf(
                "%s[%s]\033[0m %s: %s%s\n",
                $color,
                $status,
                $result['test'],
                $result['message'],
                $httpInfo
            );
        }
        
        echo "\n=== RESUMEN ===\n";
        echo "✅ Éxitos: $success\n";
        echo "⚠️  Advertencias: $warnings\n";
        echo "❌ Errores: $errors\n";
        echo "📊 Total de pruebas: " . count($this->results) . "\n";
        
        if (count($this->results) > 0) {
            $percentage = ($success / count($this->results)) * 100;
            echo "📈 Porcentaje de éxito: " . number_format($percentage, 2) . "%\n";
        }
        
        if ($errors == 0) {
            echo "\n🎉 ¡Todos los endpoints funcionan correctamente!\n";
        } elseif ($errors < 3) {
            echo "\n⚠️  API funcional con algunos problemas menores\n";
        } else {
            echo "\n❌ API requiere atención inmediata\n";
        }
        
        echo "\n=== RECOMENDACIONES ===\n";
        if ($errors > 0) {
            echo "- Verificar configuración del servidor web\n";
            echo "- Revisar logs de errores de PHP\n";
            echo "- Confirmar que la base de datos esté funcionando\n";
        }
        if ($warnings > 0) {
            echo "- Revisar permisos de usuario y autenticación\n";
            echo "- Verificar configuración de rutas\n";
        }
        echo "- Ejecutar pruebas de carga para verificar rendimiento\n";
    }
}

// Ejecutar tests
$test = new APIEndpointTest();
$test->runAllTests();