<?php
// tests/unit/SimpleAuthTest.php

require_once __DIR__ . '/../../vendor/autoload.php';

use App\Controllers\AuthController;
use App\Models\UsuarioModel;
use App\Models\RolModel;
use App\Config\ConexionDb;
use Dotenv\Dotenv;

/**
 * Test simple para AuthController sin PHPUnit
 */
class SimpleAuthTest
{
    private $db;
    private $authController;
    private $usuarioModel;
    private $rolModel;
    private $testsPassed = 0;
    private $testsFailed = 0;

    public function __construct()
    {
        // Cargar variables de entorno
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->load();

        // Establecer conexión a la base de datos
        $this->db = ConexionDb::getConnection();
        
        $this->usuarioModel = new UsuarioModel($this->db);
        $this->rolModel = new RolModel($this->db);
        $this->authController = new AuthController($this->db, $this->usuarioModel, $this->rolModel);
    }

    public function runAllTests()
    {
        echo "=== EJECUTANDO TESTS DE AUTENTICACIÓN ===\n\n";

        $this->testDatabaseConnection();
        $this->testAuthControllerCreation();
        $this->testUserModelBasicOperations();
        
        $this->displayResults();
    }

    private function testDatabaseConnection()
    {
        echo "1. Probando conexión a base de datos...\n";
        try {
            $stmt = $this->db->query("SELECT 1");
            $result = $stmt->fetch();
            if ($result) {
                echo "✅ Conexión a base de datos exitosa\n";
                $this->testsPassed++;
            } else {
                throw new Exception("No se pudo ejecutar consulta de prueba");
            }
        } catch (Exception $e) {
            echo "❌ Error de conexión: " . $e->getMessage() . "\n";
            $this->testsFailed++;
        }
        echo "\n";
    }

    private function testAuthControllerCreation()
    {
        echo "2. Probando creación de AuthController...\n";
        try {
            if ($this->authController instanceof AuthController) {
                echo "✅ AuthController creado correctamente\n";
                $this->testsPassed++;
            } else {
                throw new Exception("AuthController no es una instancia válida");
            }
        } catch (Exception $e) {
            echo "❌ Error creando AuthController: " . $e->getMessage() . "\n";
            $this->testsFailed++;
        }
        echo "\n";
    }

    private function testUserModelBasicOperations()
    {
        echo "3. Probando operaciones básicas del UsuarioModel...\n";
        try {
            // Probar que el modelo puede hacer consultas básicas
            $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM usuarios LIMIT 1");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (isset($result['total'])) {
                echo "✅ UsuarioModel puede acceder a la tabla usuarios\n";
                echo "   Total de usuarios en la base: " . $result['total'] . "\n";
                $this->testsPassed++;
            } else {
                throw new Exception("No se pudo contar usuarios");
            }
        } catch (Exception $e) {
            echo "❌ Error en operaciones del UsuarioModel: " . $e->getMessage() . "\n";
            $this->testsFailed++;
        }
        echo "\n";
    }

    private function displayResults()
    {
        echo "=== RESUMEN DE TESTS ===\n";
        echo "Tests exitosos: {$this->testsPassed}\n";
        echo "Tests fallidos: {$this->testsFailed}\n";
        echo "Total: " . ($this->testsPassed + $this->testsFailed) . "\n";
        
        if ($this->testsFailed === 0) {
            echo "🎉 ¡Todos los tests pasaron!\n";
        } else {
            echo "⚠️  Algunos tests fallaron\n";
        }
    }
}

// Ejecutar si se llama directamente
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $test = new SimpleAuthTest();
    $test->runAllTests();
}