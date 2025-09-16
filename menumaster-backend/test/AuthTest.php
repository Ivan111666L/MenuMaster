<?php
// tests/AuthTest.php

require_once __DIR__ . '/../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use App\Controllers\AuthController;
use App\Models\UsuarioModel;
use App\Models\RolModel;
use PDO;
use Exception;

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
        // 1. Preparamos datos
        $email = 'test@example.com';
        $password = 'password123';
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $userFromDb = ['id' => 1, 'nombre' => 'Test User', 'email' => $email, 'password' => $hashedPassword, 'rol_id' => 1];

        // 2. Configuramos los mocks
        $this->usuarioModelMock->method('findByEmail')->willReturn($userFromDb);
        $this->usuarioModelMock->method('find')->willReturn($userFromDb);

        // 3. Instanciamos el controlador real, inyectando nuestros mocks
        $authController = new AuthController($this->dbMock, $this->usuarioModelMock, $this->rolModelMock);
        
        // 4. Ejecutamos y verificamos (ahora el controlador debe enviar la respuesta,
        //    así que interceptamos la salida para probarla).
        
        // Se espera que el controlador llame a exit(), por lo que la prueba se ejecutará en un proceso separado.
        $this->expectOutputRegex('/"token":/');
        $authController->login(['email' => $email, 'password' => $password]);
    }

    /**
     * Prueba un inicio de sesión fallido.
     */
    public function testLoginFailsWithInvalidCredentials(): void
    {
        $this->usuarioModelMock->method('findByEmail')->willReturn(false);

        $authController = new AuthController($this->dbMock, $this->usuarioModelMock, $this->rolModelMock);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Credenciales incorrectas.');
        $this->expectExceptionCode(401);

        $authController->login(['email' => 'wrong@example.com', 'password' => 'wrongpassword']);
    }

    /**
     * Prueba un registro exitoso.
     */
    public function testRegisterSuccess(): void
    {
        $userData = ['nombre' => 'Nuevo Usuario', 'email' => 'nuevo@example.com', 'password' => 'password123', 'rol' => 'mesero'];

        $this->usuarioModelMock->method('findByEmail')->willReturn(false);
        $this->rolModelMock->method('findByName')->willReturn(['id' => 2, 'nombre' => 'mesero']);
        $this->usuarioModelMock->method('create')->willReturn(10);
        $this->usuarioModelMock->method('find')->willReturn(['id' => 10, 'nombre' => 'Nuevo Usuario']);

        $authController = new AuthController($this->dbMock, $this->usuarioModelMock, $this->rolModelMock);

        $this->expectOutputRegex('/"mensaje": "Usuario creado correctamente."/');
        $authController->register($userData);
    }
}