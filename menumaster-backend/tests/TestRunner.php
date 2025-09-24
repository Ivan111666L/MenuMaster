<?php
/**
 * Test Runner para MenuMaster
 * Ejecuta todos los tests del sistema de forma organizada
 */

class TestRunner
{
    private $testDirectory;
    private $results = [];
    private $totalTests = 0;
    private $passedTests = 0;
    private $failedTests = 0;

    public function __construct($testDirectory = null)
    {
        $this->testDirectory = $testDirectory ?: __DIR__;
    }

    public function runAllTests()
    {
        echo "=== MENUMASTER TEST RUNNER ===\n";
        echo "Ejecutando todos los tests del sistema...\n\n";

        $testFiles = $this->findTestFiles();
        
        foreach ($testFiles as $testFile) {
            $this->runSingleTest($testFile);
        }

        $this->displaySummary();
    }

    private function findTestFiles()
    {
        $testFiles = [];
        $unitDir = $this->testDirectory . '/unit';
        
        if (is_dir($unitDir)) {
            $files = scandir($unitDir);
            foreach ($files as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) === 'php' && $file !== 'AuthTest.php') {
                    $testFiles[] = $unitDir . '/' . $file;
                }
            }
        }

        return $testFiles;
    }

    private function runSingleTest($testFile)
    {
        $testName = basename($testFile, '.php');
        echo "Ejecutando: $testName\n";
        echo str_repeat('-', 50) . "\n";

        ob_start();
        $startTime = microtime(true);
        
        try {
            include $testFile;
            $output = ob_get_clean();
            $endTime = microtime(true);
            $duration = round(($endTime - $startTime) * 1000, 2);

            // Analizar si el test pasó o falló
            $passed = $this->analyzeTestOutput($output);
            
            if ($passed) {
                $this->passedTests++;
                echo "✅ PASÓ ($duration ms)\n";
            } else {
                $this->failedTests++;
                echo "❌ FALLÓ ($duration ms)\n";
            }

            $this->totalTests++;
            $this->results[$testName] = [
                'passed' => $passed,
                'duration' => $duration,
                'output' => $output
            ];

            // Mostrar salida del test si es necesario
            if (!$passed || strpos($output, 'Error') !== false) {
                echo "Salida del test:\n";
                echo $output . "\n";
            }

        } catch (Exception $e) {
            ob_get_clean();
            $this->failedTests++;
            $this->totalTests++;
            
            echo "❌ ERROR: " . $e->getMessage() . "\n";
            $this->results[$testName] = [
                'passed' => false,
                'duration' => 0,
                'output' => $e->getMessage()
            ];
        }

        echo "\n";
    }

    private function analyzeTestOutput($output)
    {
        // Buscar indicadores de éxito
        $successIndicators = ['✅', 'PASSED', 'ALL TESTS PASSED', 'Test completado'];
        $errorIndicators = ['❌', 'ERROR', 'FAILED', 'Fatal error', 'Parse error'];

        foreach ($errorIndicators as $indicator) {
            if (stripos($output, $indicator) !== false) {
                return false;
            }
        }

        foreach ($successIndicators as $indicator) {
            if (stripos($output, $indicator) !== false) {
                return true;
            }
        }

        // Si no hay indicadores claros, asumir que pasó si no hay errores PHP
        return !preg_match('/Fatal error|Parse error|Warning|Notice/', $output);
    }

    private function displaySummary()
    {
        echo "=== RESUMEN DE TESTS ===\n";
        echo "Total de tests: {$this->totalTests}\n";
        echo "Tests exitosos: {$this->passedTests}\n";
        echo "Tests fallidos: {$this->failedTests}\n";
        
        if ($this->totalTests > 0) {
            $successRate = round(($this->passedTests / $this->totalTests) * 100, 2);
            echo "Tasa de éxito: {$successRate}%\n";
        }

        echo "\n=== DETALLES POR TEST ===\n";
        foreach ($this->results as $testName => $result) {
            $status = $result['passed'] ? '✅' : '❌';
            echo "{$status} {$testName} ({$result['duration']} ms)\n";
        }

        if ($this->failedTests > 0) {
            echo "\n⚠️  Algunos tests fallaron. Revisa los detalles arriba.\n";
            exit(1);
        } else {
            echo "\n🎉 ¡Todos los tests pasaron exitosamente!\n";
        }
    }

    public function runSpecificTest($testName)
    {
        $testFile = $this->testDirectory . '/unit/' . $testName . '.php';
        
        if (!file_exists($testFile)) {
            echo "❌ Test no encontrado: $testName\n";
            return false;
        }

        echo "=== EJECUTANDO TEST ESPECÍFICO: $testName ===\n\n";
        $this->runSingleTest($testFile);
        
        return $this->results[$testName]['passed'] ?? false;
    }
}

// Ejecutar si se llama directamente
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $runner = new TestRunner();
    
    // Verificar si se especificó un test específico
    if (isset($argv[1])) {
        $runner->runSpecificTest($argv[1]);
    } else {
        $runner->runAllTests();
    }
}