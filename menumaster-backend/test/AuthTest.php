<?php
// tests/AuthTest.php

require_once __DIR__ . '/../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
// Required PHPUnit packages for mocking and testing
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Exception as MockObjectException;
use App\Controllers\AuthController;
use App\Models\UsuarioModel;
use App\Models\RolModel;
use PDO;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthTest extends TestCase
{
    private $dbMock;
    private $usuarioModelMock;
    private $rolModelMock;

    /**
     * Este método se ejecuta antes de cada prueba.
     */
    protected function setUp(): void
    {
        $this->dbMock = $this->createMock(PDO::class);
        $this->usuarioModelMock = $this->createMock(UsuarioModel::class);
        $this->rolModelMock = $this->createMock(RolModel::class);
    }

    /**
     * Prueba un inicio de sesión exitoso.
     */
    public function testLoginSuccess(): void
    {
        $email = 'test@example.com';
        $password = 'password123';
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $userFromDb = [
            'id' => 1, 
            'nombre' => 'Test User', 
            'email' => $email, 
            'password' => $hashedPassword, 
            'rol_id' => 1,
            'rol' => ['id' => 1, 'nombre' => 'admin'] // Added rol information
        ];

        $this->usuarioModelMock->method('findByEmail')->willReturn($userFromDb);
        $this->usuarioModelMock->method('find')->willReturn($userFromDb);
        $this->rolModelMock->method('find')->willReturn(['id' => 1, 'nombre' => 'admin']);

        $authController = new AuthController($this->dbMock, $this->usuarioModelMock, $this->rolModelMock);

        $input = json_encode(['email' => $email, 'password' => $password]);
        $this->setInputStream($input);

        $this->expectOutputRegex('/"token":/');
        $authController->login();
    }

    /**
     * Prueba un inicio de sesión fallido.
     */
    public function testLoginFailsWithInvalidCredentials(): void
    {
        $this->usuarioModelMock->method('findByEmail')->willReturn(false);

        $authController = new AuthController($this->dbMock, $this->usuarioModelMock, $this->rolModelMock);

        $input = json_encode(['email' => 'wrong@example.com', 'password' => 'wrongpassword']);
        $this->setInputStream($input);

        $this->expectOutputRegex('/"success":false|"Credenciales incorrectas"/');
        $authController->login();
    }

    /**
     * Prueba un registro exitoso.
     */
    public function testRegisterSuccess(): void
    {
        $userData = [
            'nombre' => 'Nuevo Usuario', 
            'email' => 'nuevo@example.com', 
            'password' => 'password123', 
            'rol' => 'mesero'
        ];

        $this->usuarioModelMock->method('findByEmail')->willReturn(false);
        $this->rolModelMock->method('findByName')->willReturn(['id' => 2, 'nombre' => 'mesero']);
        $this->usuarioModelMock->method('create')->willReturn(10);
        $this->usuarioModelMock->method('find')->willReturn([
            'id' => 10, 
            'nombre' => 'Nuevo Usuario',
            'email' => 'nuevo@example.com',
            'rol' => ['id' => 2, 'nombre' => 'mesero']
        ]);

        $authController = new AuthController($this->dbMock, $this->usuarioModelMock, $this->rolModelMock);

        $input = json_encode($userData);
        $this->setInputStream($input);

        $this->expectOutputRegex('/"message":"Usuario registrado correctamente"/');
        $authController->register();
    }

    /**
     * Helper para simular php://input en tests
     */
    private function setInputStream(string $input): void
    {
        $stream = fopen('php://memory', 'r+');
        fwrite($stream, $input);
        rewind($stream);
    }
}