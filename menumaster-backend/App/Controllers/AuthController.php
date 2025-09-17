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

<<<<<<< HEAD
=======
    /**
     * Registro de un nuevo usuario.
     */
>>>>>>> a2ca148a248aab40c706b3d47be13d2e02759886
    public function register(): void
    {
        try {
            $data = json_decode(file_get_contents("php://input"), true);

            // Validación de datos de entrada
            if (empty($data['nombre']) || empty($data['email']) || empty($data['password'])) {
                $this->sendResponse(400, ["message" => "Nombre, email y contraseña son obligatorios."]);
                return;
            }

<<<<<<< HEAD
            $stmt = $this->db->prepare("SELECT id FROM usuarios WHERE email = :email LIMIT 1");
            $stmt->execute([":email" => $data['email']]);
            if ($stmt->fetch()) {
                $this->sendResponse(409, "El correo ya está registrado.");
            }

=======
            // Validar si el email ya existe usando el modelo
            if ($this->usuarioModel->findByEmail($data['email'])) {
                $this->sendResponse(409, ["message" => "El correo electrónico ya está registrado."]);
                return;
            }

            // Crear el usuario usando el modelo
>>>>>>> a2ca148a248aab40c706b3d47be13d2e02759886
            $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);
            $rolId = $data['rol_id'] ?? 2; // Rol de "usuario" por defecto
            $estadoId = 1; // Estado "activo" por defecto

<<<<<<< HEAD
            $stmt = $this->db->prepare("
                INSERT INTO usuarios (nombre, email, password, rol_id, estado_id)
                VALUES (:nombre, :email, :password, :rol_id, :estado_id)
            ");
            $stmt->execute([
                ":nombre" => $data['nombre'],
                ":email" => $data['email'],
                ":password" => $hashedPassword,
                ":rol_id" => $data['rol_id'] ?? 2,
                ":estado_id" => 1
=======
            $userId = $this->usuarioModel->create([
                'nombre' => $data['nombre'],
                'email' => $data['email'],
                'password' => $hashedPassword,
                'rol_id' => $rolId,
                'estado_id' => $estadoId
>>>>>>> a2ca148a248aab40c706b3d47be13d2e02759886
            ]);

            if (!$userId) {
                throw new Exception("No se pudo registrar el usuario en la base de datos.", 500);
            }

<<<<<<< HEAD
            $stmt = $this->db->prepare("
                SELECT u.id, u.nombre, u.email, u.rol_id, u.estado_id, r.nombre AS rol
                FROM usuarios u
                INNER JOIN roles r ON u.rol_id = r.id
                WHERE u.id = :id
            ");
            $stmt->execute([":id" => $userId]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
=======
            // Obtener datos del usuario recién creado para la respuesta
            $newUser = $this->usuarioModel->find($userId);
            unset($newUser['password']); // Nunca devolver el hash
>>>>>>> a2ca148a248aab40c706b3d47be13d2e02759886

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

<<<<<<< HEAD
    public function login(): void
=======
   public function login(): void
>>>>>>> a2ca148a248aab40c706b3d47be13d2e02759886
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

<<<<<<< HEAD
            unset($usuario['password']);
            $token = $this->generateToken($usuario);
            $this->sendResponse(200, "Inicio de sesión exitoso.", $token, $usuario);
=======
            // Limpiar datos sensibles antes de generar el token y la respuesta
            unset($usuario['password']);

            // Generar el token
            $tokenData = $this->generateToken($usuario);
>>>>>>> a2ca148a248aab40c706b3d47be13d2e02759886

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

<<<<<<< HEAD
    private function generateToken(array $usuario): string
=======
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
>>>>>>> a2ca148a248aab40c706b3d47be13d2e02759886
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

<<<<<<< HEAD
=======
    /**
     * Decodifica un token JWT para obtener los datos.
     */
>>>>>>> a2ca148a248aab40c706b3d47be13d2e02759886
    public static function decodeTokenData(string $token): array
    {
        $jwtConfig = Config::getJwtConfig();
        $secretKey = $_ENV['JWT_SECRET_KEY'] ?? $jwtConfig['secret'];
        $decoded = JWT::decode($token, new Key($secretKey, $jwtConfig['algorithm']));
        return (array) $decoded->data;
    }

<<<<<<< HEAD
    private function sendResponse(
        int $statusCode,
        string $message,
        ?string $token = null,
        ?array $usuario = null
    ): void {
        http_response_code($statusCode);

        $expiraEn = time() + Config::getJwtConfig()['expiration_time'];

        echo json_encode([
            "success"  => $statusCode >= 200 && $statusCode < 300,
            "message"  => $message,
            "token"    => $token,
            "usuario"  => $usuario,
            "expiraEn" => $expiraEn
        ], JSON_UNESCAPED_UNICODE);
=======
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
>>>>>>> a2ca148a248aab40c706b3d47be13d2e02759886

        echo json_encode($response + $data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
