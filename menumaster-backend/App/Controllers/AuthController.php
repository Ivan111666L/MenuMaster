<?php

namespace app\Controllers;

use app\Models\UsuarioModel;
use app\config\Config;
use app\config\ConexionDb; // Corregido el namespace a mayúscula inicial
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use PDO;
use Exception;
use Throwable; // Es mejor capturar Throwable para errores más genéricos

class AuthController
{
    private PDO $db;
    private UsuarioModel $usuarioModel;

    public function __construct()
    {
        // La conexión y el modelo se instancian directamente para simplificar
        $this->db = ConexionDb::getConnection();
        $this->usuarioModel = new UsuarioModel($this->db);
    }

    /**
     * Registro de un nuevo usuario.
     */
    public function register(): void
    {
        try {
            $data = json_decode(file_get_contents("php://input"), true);

            // Validación de datos de entrada
            if (empty($data['nombre']) || empty($data['email']) || empty($data['password'])) {
                $this->sendResponse(400, ["message" => "Nombre, email y contraseña son obligatorios."]);
                return;
            }

            // Validar si el email ya existe usando el modelo
            if ($this->usuarioModel->findByEmail($data['email'])) {
                $this->sendResponse(409, ["message" => "El correo electrónico ya está registrado."]);
                return;
            }

            // Crear el usuario usando el modelo
            $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);
            $rolId = $data['rol_id'] ?? 2; // Rol de "usuario" por defecto
            $estadoId = 1; // Estado "activo" por defecto

            $userId = $this->usuarioModel->create([
                'nombre' => $data['nombre'],
                'email' => $data['email'],
                'password' => $hashedPassword,
                'rol_id' => $rolId,
                'estado_id' => $estadoId
            ]);

            if (!$userId) {
                throw new Exception("No se pudo registrar el usuario en la base de datos.", 500);
            }

            // Obtener datos del usuario recién creado para la respuesta
            $newUser = $this->usuarioModel->find($userId);
            unset($newUser['password']); // Nunca devolver el hash

            // Generar token para el nuevo usuario
            $token = $this->generateToken($newUser);

            $this->sendResponse(201, [
                "message" => "Usuario registrado correctamente.",
                "token" => $token,
                "usuario" => $newUser
            ]);

        } catch (Throwable $e) {
            $this->sendResponse(500, ["message" => "Error en el servidor: " . $e->getMessage()]);
        }
    }

   public function login(): void
    {
        try {
            $data = json_decode(file_get_contents("php://input"), true);

            // Validación de datos de entrada
            if (empty($data['email']) || empty($data['password'])) {
                $this->sendResponse(400, ["message" => "Email y contraseña son obligatorios."]);
                return;
            }

            // Buscar usuario por email
            $usuario = $this->usuarioModel->findByEmail($data['email']);

            // Verificar si el usuario existe y la contraseña es correcta
            if (!$usuario || !password_verify($data['password'], $usuario['password'])) {
                $this->sendResponse(401, ["message" => "Credenciales incorrectas."]);
                return;
            }

            // Limpiar datos sensibles antes de generar el token y la respuesta
            unset($usuario['password']);

            // Generar el token
            $tokenData = $this->generateToken($usuario);

            $this->sendResponse(200, [
                "message" => "Inicio de sesión exitoso.",
                "token" => $tokenData['token'],
                "expiraEn" => $tokenData['expires_at'],
                "usuario" => $usuario
            ]);

        } catch (Throwable $e) {
            $this->sendResponse(500, ["message" => "Error en el servidor: " . $e->getMessage()]);
        }
    }

    public function verifyToken(): void
    {
        try {
            $headers = getallheaders();
            $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;

            if (!$authHeader) {
                $this->sendResponse(401, ["message" => "Token no proporcionado."]);
                return;
            }

            if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
                $this->sendResponse(401, ["message" => "Formato de token inválido."]);
                return;
            }

            $jwt = $matches[1];
            $tokenData = self::decodeTokenData($jwt); // Usamos el decodificador que ya tenías
            
            // Si la decodificación fue exitosa, buscamos al usuario para devolver datos frescos
            $usuario = $this->usuarioModel->find($tokenData['id']);
            unset($usuario['password']);

            $this->sendResponse(200, [
                "message" => "Token válido.",
                "usuario" => $usuario
            ]);

        } catch (Throwable $e) {
            $this->sendResponse(401, ["message" => "Token inválido o expirado: " . $e->getMessage()]);
        }
    }


    /**
     * Genera un token JWT y su fecha de expiración.
     */
    private function generateToken(array $usuario): array
    {
        $jwtConfig = Config::getJwtConfig();
        $issuedAt = time();
        $expirationTime = $issuedAt + $jwtConfig['expiration_time'];
        $secretKey = $_ENV['JWT_SECRET_KEY'] ?? $jwtConfig['secret'];

        $payload = [
            "iat" => $issuedAt,
            "exp" => $expirationTime,
            "data" => [
                "id" => $usuario['id'],
                "email" => $usuario['email'],
                "rol_id" => $usuario['rol_id']
            ]
        ];

        $jwt = JWT::encode($payload, $secretKey, $jwtConfig['algorithm']);

        return [
            'token' => $jwt,
            'expires_at' => $expirationTime
        ];
    }

    /**
     * Decodifica un token JWT para obtener los datos.
     */
    public static function decodeTokenData(string $token): array
    {
        $jwtConfig = Config::getJwtConfig();
        $secretKey = $_ENV['JWT_SECRET_KEY'] ?? $jwtConfig['secret'];
        $decoded = JWT::decode($token, new Key($secretKey, $jwtConfig['algorithm']));
        return (array) $decoded->data;
    }

    /**
     * Envía una respuesta JSON uniforme y termina la ejecución.
     */
    private function sendResponse(int $statusCode, array $data): void
    {
        header("Content-Type: application/json; charset=UTF-8");
        http_response_code($statusCode);

        $response = [
            "success" => $statusCode >= 200 && $statusCode < 300,
        ];

        echo json_encode($response + $data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}