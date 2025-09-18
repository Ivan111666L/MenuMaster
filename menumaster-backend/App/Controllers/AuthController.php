<?php
namespace app\Controllers;

use app\Models\UsuarioModel;
use app\Models\RolModel;
use app\Config\Config;
use app\Config\conexionDb;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use PDO;
use Exception;

class AuthController
{
    private $db;
    private $usuarioModel;
    private $rolModel;

    public function __construct(PDO $db = null, UsuarioModel $usuarioModel = null, RolModel $rolModel = null)
    {
        $this->db = $db ?? conexionDb::getConnection();
        $this->usuarioModel = $usuarioModel ?? new UsuarioModel($this->db);
        $this->rolModel = $rolModel ?? new RolModel($this->db);
    }

    /**
     * Registro de usuario con JWT en la respuesta
     */
    public function register(): void
    {
        try {
            $data = json_decode(file_get_contents("php://input"), true);

            if (!isset($data['nombre'], $data['email'], $data['password'])) {
                $this->sendResponse(400, "Nombre, email y contraseña son obligatorios.");
            }

            // Validar si ya existe el email
            $stmt = $this->db->prepare("SELECT id FROM usuarios WHERE email = :email LIMIT 1");
            $stmt->execute([":email" => $data['email']]);
            if ($stmt->fetch()) {
                $this->sendResponse(409, "El correo ya está registrado.");
            }

            // Insertar usuario
            $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);

            $stmt = $this->db->prepare("
                INSERT INTO usuarios (nombre, email, password, rol_id, estado_id)
                VALUES (:nombre, :email, :password, :rol_id, :estado_id)
            ");
            $stmt->execute([
                ":nombre" => $data['nombre'],
                ":email" => $data['email'],
                ":password" => $hashedPassword,
                ":rol_id" => $data['rol_id'] ?? 2, // rol por defecto
                ":estado_id" => 1
            ]);

            $userId = $this->db->lastInsertId();

            // Obtener datos del usuario recién creado
            $stmt = $this->db->prepare("
                SELECT u.id, u.nombre, u.email, u.rol_id, u.estado_id, r.nombre AS rol
                FROM usuarios u
                INNER JOIN roles r ON u.rol_id = r.id
                WHERE u.id = :id
            ");
            $stmt->execute([":id" => $userId]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            $token = $this->generateToken($usuario);
            $this->sendResponse(201, "Usuario registrado correctamente.", $token, $usuario);

        } catch (Exception $e) {
            $this->sendResponse(500, "Error en el servidor: " . $e->getMessage());
        }
    }

    /**
     * Login con JWT
     */
    public function login(): void
    {
        try {
            $data = json_decode(file_get_contents("php://input"), true);

            if (!isset($data['email'], $data['password'])) {
                $this->sendResponse(400, "Email y contraseña son obligatorios.");
            }

            $usuario = $this->usuarioModel->findByEmail($data['email']);

            if (!$usuario || !password_verify($data['password'], $usuario['password'])) {
                $this->sendResponse(401, "Credenciales incorrectas.");
            }

            unset($usuario['password']); // nunca enviar el hash
            $token = $this->generateToken($usuario);

            $this->sendResponse(200, "Inicio de sesión exitoso.", $token, $usuario);

        } catch (Exception $e) {
            $this->sendResponse(500, "Error en el servidor: " . $e->getMessage());
        }
    }

    /**
     * Genera token JWT
     */
    private function generateToken(array $usuario): string
    {
        $jwtConfig = Config::getJwtConfig();
        $issuedAt = time();
        $expirationTime = $issuedAt + $jwtConfig['expiration'];

        $payload = [
            "iat" => $issuedAt,
            "exp" => $expirationTime,
            "data" => [
                "id" => $usuario['id'],
                "email" => $usuario['email'],
                "rol" => $usuario['rol'] ?? null
            ]
        ];

        return JWT::encode($payload, $jwtConfig['secret'], $jwtConfig['algorithm']);
    }

    /**
     * Decodifica un token JWT
     */
    public static function decodeTokenData(string $token): array
    {
        $jwtConfig = Config::getJwtConfig();
        $decoded = JWT::decode($token, new Key($jwtConfig['secret'], $jwtConfig['algorithm']));
        return (array) $decoded;
    }

    /**
     * Respuesta uniforme
     */
    private function sendResponse(
        int $statusCode,
        string $message,
        ?string $token = null,
        ?array $usuario = null
    ): void {
        http_response_code($statusCode);

        echo json_encode([
            "success" => $statusCode >= 200 && $statusCode < 300,
            "message" => $message,
            "token"   => $token,
            "usuario" => $usuario
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }
}