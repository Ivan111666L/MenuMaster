<?php
// tests/unit/AuthTest.php

require_once __DIR__ . '/../../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use App\Controllers\AuthController;
use App\Models\UsuarioModel;
use App\Models\RolModel;
use App\Config\ConexionDb;
use Dotenv\Dotenv;

class AuthTest extends TestCase
{
    private static $db;
    private $authController;
    private $usuarioModel;
    private $rolModel;
    private $testUserId;

    /**
     * Configuración inicial para todas las pruebas
     */
    public static function setUpBeforeClass(): void
    {
        // Cargar variables de entorno
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->load();

        // Establecer conexión a la base de datos de prueba
        self::$db = ConexionDb::getConnection();
        
        // Verificar que estamos usando la base de datos correcta
        $stmt = self::$db->query("SELECT DATABASE() as db_name");
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if ($result['db_name'] !== $_ENV['DB_NAME']) {
            throw new \Exception("Error: No se está usando la base de datos correcta para las pruebas");
        }
    }

    /**
     * Configuración antes de cada prueba
     */
    protected function setUp(): void
    {
        $this->usuarioModel = new UsuarioModel(self::$db);
        $this->rolModel = new RolModel(self::$db);
        $this->authController = new AuthController(self::$db, $this->usuarioModel, $this->rolModel);
        
        // Limpiar datos de prueba anteriores
        $this->cleanupTestData();
        
        // Crear datos de prueba
        $this->createTestData();
    }

    /**
     * Limpieza después de cada prueba
     */
    protected function tearDown(): void
    {
        $this->cleanupTestData();
    }

    /**
     * Crear datos de prueba necesarios
     */
    private function createTestData(): void
    {
        // Crear usuario de prueba
        $hashedPassword = password_hash('testpassword123', PASSWORD_BCRYPT);
        
        // Obtener rol de mesero (asumiendo que existe)
        $rol = $this->rolModel->findByName('mesero');
        if (!$rol) {
            // Si no existe el rol, crearlo
            $stmt = self::$db->prepare("INSERT INTO roles (nombre) VALUES ('mesero')");
            $stmt->execute();
            $rol = ['id' => self::$db->lastInsertId(), 'nombre' => 'mesero'];
        }
        
        $this->testUserId = $this->usuarioModel->create(
            'Usuario Test',
            'test@menumaster.com',
            $hashedPassword,
            $rol['id']
        );
    }

    /**
     * Limpiar datos de prueba
     */
    private function cleanupTestData(): void
    {
        // Eliminar usuarios de prueba
        $stmt = self::$db->prepare("DELETE FROM usuarios WHERE email LIKE '%@menumaster.com' OR email LIKE '%@test.com'");
        $stmt->execute();
    }

    /**
     * Prueba de inicio de sesión exitoso
     */
    public function testLoginSuccess(): void
    {
        // Capturar la salida
        ob_start();
        
        // Simular entrada JSON
        $input = json_encode([
            'email' => 'test@menumaster.com',
            'password' => 'testpassword123'
        ]);
        
        // Simular php://input
        $this->setInputStream($input);
        
        // Ejecutar login
        $this->authController->login();
        
        // Obtener la salida
        $output = ob_get_clean();
        $response = json_decode($output, true);
        
        // Verificaciones
        $this->assertIsArray($response);
        $this->assertTrue($response['success']);
        $this->assertArrayHasKey('token', $response);
        $this->assertArrayHasKey('usuario', $response);
        $this->assertNotEmpty($response['token']);
        $this->assertEquals('test@menumaster.com', $response['usuario']['email']);
        $this->assertEquals('Usuario Test', $response['usuario']['nombre']);
    }

    /**
     * Prueba de inicio de sesión con credenciales incorrectas
     */
    public function testLoginFailsWithInvalidCredentials(): void
    {
        ob_start();
        
        $input = json_encode([
            'email' => 'test@menumaster.com',
            'password' => 'wrongpassword'
        ]);
        
        $this->setInputStream($input);
        $this->authController->login();
        
        $output = ob_get_clean();
        $response = json_decode($output, true);
        
        $this->assertIsArray($response);
        $this->assertFalse($response['success']);
        $this->assertStringContainsString('Credenciales incorrectas', $response['message']);
        $this->assertNull($response['token']);
    }

    /**
     * Prueba de inicio de sesión con email inexistente
     */
    public function testLoginFailsWithNonExistentEmail(): void
    {
        ob_start();
        
        $input = json_encode([
            'email' => 'noexiste@menumaster.com',
            'password' => 'anypassword'
        ]);
        
        $this->setInputStream($input);
        $this->authController->login();
        
        $output = ob_get_clean();
        $response = json_decode($output, true);
        
        $this->assertIsArray($response);
        $this->assertFalse($response['success']);
        $this->assertStringContainsString('Credenciales incorrectas', $response['message']);
    }

    /**
     * Prueba de inicio de sesión con datos faltantes
     */
    public function testLoginFailsWithMissingData(): void
    {
        ob_start();
        
        $input = json_encode([
            'email' => 'test@menumaster.com'
            // password faltante
        ]);
        
        $this->setInputStream($input);
        $this->authController->login();
        
        $output = ob_get_clean();
        $response = json_decode($output, true);
        
        $this->assertIsArray($response);
        $this->assertFalse($response['success']);
        $this->assertStringContainsString('Email y contraseña son obligatorios', $response['message']);
    }

    /**
     * Prueba de registro exitoso
     */
    public function testRegisterSuccess(): void
    {
        ob_start();
        
        $input = json_encode([
            'nombre' => 'Nuevo Usuario',
            'email' => 'nuevo@test.com',
            'password' => 'newpassword123',
            'rol_id' => 2 // Asumiendo que existe
        ]);
        
        $this->setInputStream($input);
        $this->authController->register();
        
        $output = ob_get_clean();
        $response = json_decode($output, true);
        
        $this->assertIsArray($response);
        $this->assertTrue($response['success']);
        $this->assertStringContainsString('Usuario registrado correctamente', $response['message']);
        $this->assertArrayHasKey('token', $response);
        $this->assertArrayHasKey('usuario', $response);
        $this->assertEquals('nuevo@test.com', $response['usuario']['email']);
    }

    /**
     * Prueba de registro con email duplicado
     */
    public function testRegisterFailsWithDuplicateEmail(): void
    {
        ob_start();
        
        $input = json_encode([
            'nombre' => 'Otro Usuario',
            'email' => 'test@menumaster.com', // Email ya existente
            'password' => 'password123',
            'rol_id' => 2
        ]);
        
        $this->setInputStream($input);
        $this->authController->register();
        
        $output = ob_get_clean();
        $response = json_decode($output, true);
        
        $this->assertIsArray($response);
        $this->assertFalse($response['success']);
        $this->assertStringContainsString('El correo ya está registrado', $response['message']);
    }

    /**
     * Prueba de registro con datos faltantes
     */
    public function testRegisterFailsWithMissingData(): void
    {
        ob_start();
        
        $input = json_encode([
            'nombre' => 'Usuario Incompleto',
            'email' => 'incompleto@test.com'
            // password faltante
        ]);
        
        $this->setInputStream($input);
        $this->authController->register();
        
        $output = ob_get_clean();
        $response = json_decode($output, true);
        
        $this->assertIsArray($response);
        $this->assertFalse($response['success']);
        $this->assertStringContainsString('Nombre, email y contraseña son obligatorios', $response['message']);
    }

    /**
     * Prueba de validación de token
     */
    public function testTokenValidation(): void
    {
        // Primero hacer login para obtener un token
        ob_start();
        
        $input = json_encode([
            'email' => 'test@menumaster.com',
            'password' => 'testpassword123'
        ]);
        
        $this->setInputStream($input);
        $this->authController->login();
        
        $output = ob_get_clean();
        $response = json_decode($output, true);
        
        $this->assertTrue($response['success']);
        $token = $response['token'];
        
        // Ahora verificar el token
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;
        
        ob_start();
        $this->authController->verifyToken();
        $output = ob_get_clean();
        $verifyResponse = json_decode($output, true);
        
        $this->assertIsArray($verifyResponse);
        $this->assertTrue($verifyResponse['success']);
        $this->assertStringContainsString('Token válido', $verifyResponse['message']);
    }

    /**
     * Prueba con token inválido
     */
    public function testInvalidToken(): void
    {
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer invalid_token_here';
        
        ob_start();
        $this->authController->verifyToken();
        $output = ob_get_clean();
        $response = json_decode($output, true);
        
        $this->assertIsArray($response);
        $this->assertFalse($response['success']);
        $this->assertStringContainsString('Token inválido', $response['message']);
    }

    /**
     * Prueba sin token
     */
    public function testMissingToken(): void
    {
        unset($_SERVER['HTTP_AUTHORIZATION']);
        
        ob_start();
        $this->authController->verifyToken();
        $output = ob_get_clean();
        $response = json_decode($output, true);
        
        $this->assertIsArray($response);
        $this->assertFalse($response['success']);
        $this->assertStringContainsString('Token no proporcionado', $response['message']);
    }

    /**
     * Prueba de formato de email inválido
     */
    public function testInvalidEmailFormat(): void
    {
        ob_start();
        
        $input = json_encode([
            'email' => 'invalid-email-format',
            'password' => 'testpassword123'
        ]);
        
        $this->setInputStream($input);
        $this->authController->login();
        
        $output = ob_get_clean();
        $response = json_decode($output, true);
        
        $this->assertIsArray($response);
        $this->assertFalse($response['success']);
    }

    /**
     * Prueba de contraseña muy corta en registro
     */
    public function testShortPasswordInRegister(): void
    {
        ob_start();
        
        $input = json_encode([
            'nombre' => 'Usuario Test',
            'email' => 'shortpass@test.com',
            'password' => '123', // Contraseña muy corta
            'rol_id' => 2
        ]);
        
        $this->setInputStream($input);
        $this->authController->register();
        
        $output = ob_get_clean();
        $response = json_decode($output, true);
        
        $this->assertIsArray($response);
        // La validación de contraseña corta debería fallar
        $this->assertFalse($response['success']);
    }

    /**
     * Helper para simular php://input en tests
     */
    private function setInputStream(string $input): void
    {
        // Crear un stream temporal
        $stream = fopen('php://temp', 'r+');
        fwrite($stream, $input);
        rewind($stream);
        
        // Reemplazar php://input temporalmente
        stream_wrapper_unregister('php');
        stream_wrapper_register('php', 'TestStreamWrapper');
        TestStreamWrapper::$inputData = $input;
    }
}

/**
 * Wrapper personalizado para simular php://input en tests
 */
class TestStreamWrapper
{
    public static $inputData = '';
    private $position = 0;

    public function stream_open($path, $mode, $options, &$opened_path)
    {
        if ($path === 'php://input') {
            $this->position = 0;
            return true;
        }
        return false;
    }

    public function stream_read($count)
    {
        $ret = substr(self::$inputData, $this->position, $count);
        $this->position += strlen($ret);
        return $ret;
    }

    public function stream_eof()
    {
        return $this->position >= strlen(self::$inputData);
    }

    public function stream_stat()
    {
        return [];
    }
}