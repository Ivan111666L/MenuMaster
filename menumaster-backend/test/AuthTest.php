<?php
// tests/AuthTest.php

// Asegúrate de que el autoloader de Composer se cargue al inicio de tus pruebas.
// Esto usualmente se configura en el archivo phpunit.xml.
require_once __DIR__ . '/../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use App\Controllers\AuthController;
use App\Models\Usuario;
use App\Models\Rol;
use PDO;
use PDOStatement;

class AuthTest extends TestCase
{
    private $dbMock;
    private $usuarioModelMock;
    private $rolModelMock;
    private $authController;

    /**
     * Este método se ejecuta antes de cada prueba.
     * Preparamos los "mocks" (objetos falsos) que simularán nuestras dependencias.
     */
    protected function setUp(): void
    {
        // Creamos un mock del objeto PDO. No se conectará a ninguna base de datos real.
        $this->dbMock = $this->createMock(PDO::class);

        // Creamos mocks de nuestros modelos.
        $this->usuarioModelMock = $this->createMock(Usuario::class);
        $this->rolModelMock = $this->createMock(Rol::class);

        // Instanciamos el controlador que vamos a probar.
        $this->authController = new AuthController($this->dbMock);
    }

    /**
     * Prueba un inicio de sesión exitoso.
     */
    public function testLoginSuccess(): void
    {
        // 1. Preparamos los datos de prueba
        $email = 'test@example.com';
        $password = 'password123';
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $userFromDb = [
            'id' => 1, 
            'nombre' => 'Test User',
            'email' => $email, 
            'password' => $hashedPassword,
            'rol_id' => 1
        ];

        // 2. Configuramos el comportamiento de los mocks
        // Cuando se llame a findByEmail en el modelo, debe devolver nuestro usuario de prueba.
        $this->usuarioModelMock->method('findByEmail')->willReturn($userFromDb);
        // Cuando se llame a find en el modelo, también debe devolver los datos del usuario.
        $this->usuarioModelMock->method('find')->willReturn($userFromDb);

        // Creamos un AuthController "falso" que usa nuestros modelos falsos
        $authController = new class($this->dbMock, $this->usuarioModelMock) extends AuthController {
            // Sobreescribimos el constructor para inyectar nuestro mock del modelo
            private $usuarioModelMock;
            public function __construct($db, $usuarioModelMock) {
                parent::__construct($db);
                $this->usuarioModelMock = $usuarioModelMock;
            }
            // Hacemos que la instanciación interna del modelo devuelva nuestro mock
            protected function getUsuarioModel() { return $this->usuarioModelMock; }
        };

        // 3. Ejecutamos el método que queremos probar
        $response = $authController->login(['email' => $email, 'password' => $password]);
        
        // 4. Verificamos el resultado (las "aserciones")
        $this->assertIsArray($response);
        $this->assertArrayHasKey('token', $response, "La respuesta de un login exitoso debe contener un token.");
        $this->assertArrayHasKey('usuario', $response, "La respuesta debe contener los datos del usuario.");
        $this->assertEquals('Test User', $response['usuario']['nombre']);
    }

    /**
     * Prueba un inicio de sesión fallido por credenciales incorrectas.
     */
    public function testLoginFailsWithInvalidCredentials(): void
    {
        // 1. Configuramos el comportamiento del mock
        // Cuando se llame a findByEmail, debe simular que no encontró al usuario.
        $this->usuarioModelMock->method('findByEmail')->willReturn(false);

        // Creamos nuestro AuthController falso
        $authController = new class($this->dbMock, $this->usuarioModelMock) extends AuthController {
            private $usuarioModelMock;
            public function __construct($db, $usuarioModelMock) { parent::__construct($db); $this->usuarioModelMock = $usuarioModelMock; }
            protected function getUsuarioModel() { return $this->usuarioModelMock; }
        };

        // 2. Declaramos que esperamos que se lance una excepción
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Credenciales incorrectas.');
        $this->expectExceptionCode(401);

        // 3. Ejecutamos el método. La prueba pasará si la excepción esperada es lanzada.
        $authController->login(['email' => 'wrong@example.com', 'password' => 'wrongpassword']);
    }

    /**
     * Prueba un registro exitoso de un nuevo usuario.
     */
    public function testRegisterSuccess(): void
    {
        $userData = [
            'nombre' => 'Nuevo Usuario',
            'email' => 'nuevo@example.com',
            'password' => 'password123',
            'rol' => 'mesero'
        ];

        // Configuramos los mocks
        $this->usuarioModelMock->method('findByEmail')->willReturn(false); // Email no existe
        $this->rolModelMock->method('findByName')->willReturn(['id' => 2, 'nombre' => 'mesero']); // Rol sí existe
        $this->usuarioModelMock->method('create')->willReturn(10); // La creación es exitosa y devuelve el ID 10
        $this->usuarioModelMock->method('find')->willReturn(['id' => 10, 'nombre' => 'Nuevo Usuario']); // find devuelve el nuevo usuario

        // Creamos nuestro AuthController falso
        $authController = new class($this->dbMock, $this->usuarioModelMock, $this->rolModelMock) extends AuthController {
            private $usuarioModelMock;
            private $rolModelMock;
            public function __construct($db, $uMock, $rMock) { parent::__construct($db); $this->usuarioModelMock = $uMock; $this->rolModelMock = $rMock; }
            protected function getUsuarioModel() { return $this->usuarioModelMock; }
            protected function getRolModel() { return $this->rolModelMock; }
        };

        // Ejecutamos el método
        $response = $authController->register($userData);

        // Verificamos el resultado
        $this->assertIsArray($response);
        $this->assertEquals("Usuario creado correctamente.", $response['mensaje']);
        $this->assertEquals(10, $response['usuario']['id']);
    }
}

// Para que esto funcione, necesitas hacer un pequeño ajuste en tu AuthController
// para que podamos "inyectar" los modelos falsos en la prueba.

// En AuthController.php, añade estos dos métodos protegidos:
/*
protected function getUsuarioModel() {
    return new Usuario($this->db);
}
protected function getRolModel() {
    return new Rol($this->db);
}
// Y en los métodos `register` y `login`, en lugar de `new Usuario($this->db)`,
// usa `$this->getUsuarioModel()`.
*/