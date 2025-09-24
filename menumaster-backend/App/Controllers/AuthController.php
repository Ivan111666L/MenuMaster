<?php
namespace App\Controllers;

use App\Models\UsuarioModel;
use App\Models\RolModel;
use App\Config\Config;
use App\Config\conexionDb;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use PDO;
use Exception;

class AuthController
{
    private $db;
    private $usuarioModel;
    private $rolModel;
    
    // Constantes para validación
    private const MIN_PASSWORD_LENGTH = 8;
    private const MAX_LOGIN_ATTEMPTS = 5;
    private const LOCKOUT_TIME = 900; // 15 minutos

    public function __construct(PDO $db = null, UsuarioModel $usuarioModel = null, RolModel $rolModel = null)
    {
        $this->db = $db ?? conexionDb::getConnection();
        $this->usuarioModel = $usuarioModel ?? new UsuarioModel($this->db);
        $this->rolModel = $rolModel ?? new RolModel($this->db);
    }

    /**
     * Registro de usuario con validaciones mejoradas
     */
    public function register(): void
    {
        try {
            // Validar Content-Type
            if (!$this->isValidContentType()) {
                $this->sendResponse(400, "Content-Type debe ser application/json");
                return;
            }

            $data = $this->getJsonInput();
            
            // Validaciones de entrada
            $validation = $this->validateRegistrationData($data);
            if (!$validation['valid']) {
                $this->sendResponse(400, $validation['message']);
                return;
            }

            // Verificar si el email ya existe
            if ($this->emailExists($data['email'])) {
                $this->sendResponse(409, "El correo electrónico ya está registrado");
                return;
            }

            // Validar rol si se proporciona
            if (isset($data['rol_id']) && !$this->isValidRole($data['rol_id'])) {
                $this->sendResponse(400, "El rol especificado no es válido");
                return;
            }

            // Crear usuario
            $userId = $this->createUser($data);
            
            // Obtener datos del usuario recién creado
            $usuario = $this->getUserWithRole($userId);
            
            if (!$usuario) {
                $this->sendResponse(500, "Error al recuperar datos del usuario");
                return;
            }

            // Generar token
            $token = $this->generateToken($usuario);
            
            // Log de registro exitoso
            $this->logUserActivity($userId, 'register', 'success');
            
            $this->sendResponse(201, "Usuario registrado correctamente", $token, $usuario);

        } catch (Exception $e) {
            error_log("Error en registro: " . $e->getMessage());
            $this->sendResponse(500, "Error interno del servidor");
        }
    }

    /**
     * Login con protección contra ataques de fuerza bruta
     */
    public function login(): void
    {
        try {
            // Validar Content-Type
            if (!$this->isValidContentType()) {
                $this->sendResponse(400, "Content-Type debe ser application/json");
                return;
            }

            $data = $this->getJsonInput();
            
            // Validaciones básicas
            if (!isset($data['email'], $data['password'])) {
                $this->sendResponse(400, "Email y contraseña son obligatorios");
                return;
            }

            // Validar formato de email
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $this->sendResponse(400, "Formato de email inválido");
                return;
            }

            // Verificar intentos de login
            if ($this->isAccountLocked($data['email'])) {
                $this->sendResponse(429, "Cuenta bloqueada temporalmente por múltiples intentos fallidos");
                return;
            }

            // Buscar usuario
            $usuario = $this->usuarioModel->findByEmail($data['email']);

            if (!$usuario) {
                $this->recordFailedAttempt($data['email']);
                $this->sendResponse(401, "Credenciales incorrectas");
                return;
            }

            // Verificar estado del usuario
            if ($usuario['estado_id'] != 1) {
                $this->sendResponse(403, "Usuario inactivo o suspendido");
                return;
            }

            // Verificar contraseña
            if (!password_verify($data['password'], $usuario['password'])) {
                $this->recordFailedAttempt($data['email']);
                $this->logUserActivity($usuario['id'], 'login', 'failed');
                $this->sendResponse(401, "Credenciales incorrectas");
                return;
            }

            // Login exitoso - limpiar intentos fallidos
            $this->clearFailedAttempts($data['email']);
            
            // Remover contraseña de la respuesta
            unset($usuario['password']);
            
            // Actualizar último login
            $this->updateLastLogin($usuario['id']);
            
            // Generar token
            $token = $this->generateToken($usuario);
            
            // Log de login exitoso
            $this->logUserActivity($usuario['id'], 'login', 'success');

            $this->sendResponse(200, "Inicio de sesión exitoso", $token, $usuario);

        } catch (Exception $e) {
            error_log("Error en login: " . $e->getMessage());
            $this->sendResponse(500, "Error interno del servidor");
        }
    }

    /**
     * Verificación de token mejorada
     */
    public function verifyToken(): void
    {
        try {
            $token = $this->extractTokenFromHeader();
            
            if (!$token) {
                $this->sendResponse(401, "Token no proporcionado");
                return;
            }

            $decoded = $this->decodeTokenData($token);
            
            // Verificar que el usuario aún existe y está activo
            $usuario = $this->getUserById($decoded['data']['id']);
            
            if (!$usuario || $usuario['estado_id'] != 1) {
                $this->sendResponse(401, "Usuario no válido o inactivo");
                return;
            }

            $this->sendResponse(200, "Token válido", null, [
                'user_id' => $usuario['id'],
                'email' => $usuario['email'],
                'rol' => $usuario['rol'],
                'expires_at' => $decoded['exp']
            ]);

        } catch (Exception $e) {
            $this->sendResponse(401, "Token inválido o expirado");
        }
    }

    /**
     * Logout - invalidar token (opcional: lista negra de tokens)
     */
    public function logout(): void
    {
        try {
            $token = $this->extractTokenFromHeader();
            
            if ($token) {
                $decoded = $this->decodeTokenData($token);
                $this->logUserActivity($decoded['data']['id'], 'logout', 'success');
            }

            $this->sendResponse(200, "Sesión cerrada correctamente");

        } catch (Exception $e) {
            $this->sendResponse(200, "Sesión cerrada");
        }
    }

    /**
     * Cambio de contraseña
     */
    public function changePassword(): void
    {
        try {
            $token = $this->extractTokenFromHeader();
            
            if (!$token) {
                $this->sendResponse(401, "Token requerido");
                return;
            }

            $decoded = $this->decodeTokenData($token);
            $data = $this->getJsonInput();

            // Validaciones
            if (!isset($data['current_password'], $data['new_password'])) {
                $this->sendResponse(400, "Contraseña actual y nueva son requeridas");
                return;
            }

            if (!$this->isValidPassword($data['new_password'])) {
                $this->sendResponse(400, "La nueva contraseña no cumple los requisitos de seguridad");
                return;
            }

            // Verificar contraseña actual
            $usuario = $this->getUserById($decoded['data']['id']);
            
            if (!password_verify($data['current_password'], $usuario['password'])) {
                $this->sendResponse(401, "Contraseña actual incorrecta");
                return;
            }

            // Actualizar contraseña
            $hashedPassword = password_hash($data['new_password'], PASSWORD_BCRYPT);
            $this->updateUserPassword($usuario['id'], $hashedPassword);
            
            $this->logUserActivity($usuario['id'], 'password_change', 'success');
            
            $this->sendResponse(200, "Contraseña actualizada correctamente");

        } catch (Exception $e) {
            error_log("Error en cambio de contraseña: " . $e->getMessage());
            $this->sendResponse(500, "Error interno del servidor");
        }
    }

    /**
     * Solicitud de restablecimiento de contraseña
     */
    public function forgotPassword(): void
    {
        try {
            // Validar Content-Type
            if (!$this->isValidContentType()) {
                $this->sendResponse(400, "Content-Type debe ser application/json");
                return;
            }

            $data = $this->getJsonInput();
            
            // Validaciones básicas
            if (!isset($data['email'])) {
                $this->sendResponse(400, "Email es obligatorio");
                return;
            }

            // Validar formato de email
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $this->sendResponse(400, "Formato de email inválido");
                return;
            }

            // Buscar usuario
            $usuario = $this->usuarioModel->findByEmail($data['email']);

            // Por seguridad, siempre devolvemos el mismo mensaje
            // independientemente de si el usuario existe o no
            if ($usuario && $usuario['estado_id'] == 1) {
                // Generar token de restablecimiento
                $resetToken = $this->generateResetToken($usuario['id']);
                
                // Guardar token en la base de datos
                $this->saveResetToken($usuario['id'], $resetToken);
                
                // En un entorno real, aquí enviarías el email
                // Por ahora, solo registramos la actividad
                $this->logUserActivity($usuario['id'], 'password_reset_request', 'success');
                
                // NOTE: Email sending functionality not implemented yet
                // Future enhancement: implement email service for password reset
                // $this->sendResetEmail($usuario['email'], $resetToken);
            }

            // Siempre devolvemos éxito por seguridad
            $this->sendResponse(200, "Si el correo existe, se han enviado las instrucciones de restablecimiento");

        } catch (Exception $e) {
            error_log("Error en forgot password: " . $e->getMessage());
            $this->sendResponse(500, "Error interno del servidor");
        }
    }

    /**
     * Restablecimiento de contraseña con token
     */
    public function resetPassword(): void
    {
        try {
            // Validar Content-Type
            if (!$this->isValidContentType()) {
                $this->sendResponse(400, "Content-Type debe ser application/json");
                return;
            }

            $data = $this->getJsonInput();
            
            // Validaciones básicas
            if (!isset($data['token'], $data['password'])) {
                $this->sendResponse(400, "Token y nueva contraseña son obligatorios");
                return;
            }

            if (!$this->isValidPassword($data['password'])) {
                $this->sendResponse(400, "La nueva contraseña no cumple los requisitos de seguridad");
                return;
            }

            // Verificar token de restablecimiento
            $userId = $this->verifyResetToken($data['token']);
            
            if (!$userId) {
                $this->sendResponse(400, "Token inválido o expirado");
                return;
            }

            // Verificar que el usuario existe y está activo
            $usuario = $this->getUserById($userId);
            
            if (!$usuario || $usuario['estado_id'] != 1) {
                $this->sendResponse(400, "Usuario no válido");
                return;
            }

            // Actualizar contraseña
            $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);
            $this->updateUserPassword($userId, $hashedPassword);
            
            // Eliminar token usado
            $this->deleteResetToken($data['token']);
            
            // Limpiar intentos fallidos de login
            $this->clearFailedAttempts($usuario['email']);
            
            $this->logUserActivity($userId, 'password_reset', 'success');
            
            $this->sendResponse(200, "Contraseña restablecida correctamente");

        } catch (Exception $e) {
            error_log("Error en reset password: " . $e->getMessage());
            $this->sendResponse(500, "Error interno del servidor");
        }
    }

    /**
     * Genera token JWT con información adicional
     */
    private function generateToken(array $usuario): string
    {
        $jwtConfig = Config::getJwtConfig();
        $issuedAt = time();
        $expirationTime = $issuedAt + $jwtConfig['expiration'];

        $payload = [
            "iat" => $issuedAt,
            "exp" => $expirationTime,
            "iss" => "MenuMaster",
            "data" => [
                "id" => $usuario['id'],
                "email" => $usuario['email'],
                "rol" => $usuario['rol'] ?? null,
                "rol_id" => $usuario['rol_id'] ?? null
            ]
        ];

        return JWT::encode($payload, $jwtConfig['secret'], $jwtConfig['algorithm']);
    }

    /**
     * Genera token para restablecimiento de contraseña
     */
    private function generateResetToken(int $userId): string
    {
        $payload = [
            "iat" => time(),
            "exp" => time() + 3600, // 1 hora de expiración
            "type" => "password_reset",
            "user_id" => $userId
        ];

        $jwtConfig = Config::getJwtConfig();
        return JWT::encode($payload, $jwtConfig['secret'], $jwtConfig['algorithm']);
    }

    /**
     * Guarda el token de restablecimiento en la base de datos
     */
    private function saveResetToken(int $userId, string $token): void
    {
        // Primero eliminar tokens anteriores del usuario
        $stmt = $this->db->prepare("DELETE FROM password_reset_tokens WHERE user_id = :user_id");
        $stmt->execute([":user_id" => $userId]);

        // Insertar nuevo token
        $stmt = $this->db->prepare("
            INSERT INTO password_reset_tokens (user_id, token, expires_at, created_at)
            VALUES (:user_id, :token, DATE_ADD(NOW(), INTERVAL 1 HOUR), NOW())
        ");
        
        $stmt->execute([
            ":user_id" => $userId,
            ":token" => $token
        ]);
    }

    /**
     * Verifica el token de restablecimiento
     */
    private function verifyResetToken(string $token): ?int
    {
        try {
            // Verificar token JWT
            $jwtConfig = Config::getJwtConfig();
            $decoded = JWT::decode($token, new Key($jwtConfig['secret'], $jwtConfig['algorithm']));
            $payload = (array) $decoded;

            if ($payload['type'] !== 'password_reset') {
                return null;
            }

            // Verificar que el token existe en la base de datos y no ha expirado
            $stmt = $this->db->prepare("
                SELECT user_id FROM password_reset_tokens 
                WHERE token = :token AND expires_at > NOW()
            ");
            $stmt->execute([":token" => $token]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $result ? (int)$result['user_id'] : null;

        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Elimina el token de restablecimiento usado
     */
    private function deleteResetToken(string $token): void
    {
        $stmt = $this->db->prepare("DELETE FROM password_reset_tokens WHERE token = :token");
        $stmt->execute([":token" => $token]);
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

    // Métodos de validación y utilidad

    private function isValidContentType(): bool
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        return strpos($contentType, 'application/json') !== false;
    }

    private function getJsonInput(): array
    {
        $input = file_get_contents("php://input");
        $data = json_decode($input, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("JSON inválido");
        }
        
        return $data ?? [];
    }

    private function validateRegistrationData(array $data): array
    {
        if (!isset($data['nombre'], $data['email'], $data['password'])) {
            return ['valid' => false, 'message' => 'Nombre, email y contraseña son obligatorios'];
        }

        if (strlen(trim($data['nombre'])) < 2) {
            return ['valid' => false, 'message' => 'El nombre debe tener al menos 2 caracteres'];
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return ['valid' => false, 'message' => 'Formato de email inválido'];
        }

        if (!$this->isValidPassword($data['password'])) {
            return ['valid' => false, 'message' => 'La contraseña debe tener al menos 8 caracteres, incluir mayúsculas, minúsculas y números'];
        }

        return ['valid' => true, 'message' => ''];
    }

    private function isValidPassword(string $password): bool
    {
        return strlen($password) >= self::MIN_PASSWORD_LENGTH &&
               preg_match('/[A-Z]/', $password) &&
               preg_match('/[a-z]/', $password) &&
               preg_match('/[0-9]/', $password);
    }

    private function emailExists(string $email): bool
    {
        $stmt = $this->db->prepare("SELECT id FROM usuarios WHERE email = :email LIMIT 1");
        $stmt->execute([":email" => $email]);
        return $stmt->fetch() !== false;
    }

    private function isValidRole(int $rolId): bool
    {
        $stmt = $this->db->prepare("SELECT id FROM roles WHERE id = :id AND estado_id = 1 LIMIT 1");
        $stmt->execute([":id" => $rolId]);
        return $stmt->fetch() !== false;
    }

    private function createUser(array $data): int
    {
        $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);

        $stmt = $this->db->prepare("
            INSERT INTO usuarios (nombre, email, password, rol_id, estado_id, created_at)
            VALUES (:nombre, :email, :password, :rol_id, :estado_id, NOW())
        ");
        
        $stmt->execute([
            ":nombre" => trim($data['nombre']),
            ":email" => strtolower(trim($data['email'])),
            ":password" => $hashedPassword,
            ":rol_id" => $data['rol_id'] ?? 2,
            ":estado_id" => 1
        ]);

        return $this->db->lastInsertId();
    }

    private function getUserWithRole(int $userId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT u.id, u.nombre, u.email, u.rol_id, u.estado_id, r.nombre AS rol
            FROM usuarios u
            INNER JOIN roles r ON u.rol_id = r.id
            WHERE u.id = :id
        ");
        $stmt->execute([":id" => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    private function getUserById(int $userId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT u.*, r.nombre AS rol
            FROM usuarios u
            LEFT JOIN roles r ON u.rol_id = r.id
            WHERE u.id = :id
        ");
        $stmt->execute([":id" => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    private function extractTokenFromHeader(): ?string
    {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        
        if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return $matches[1];
        }
        
        return null;
    }

    private function isAccountLocked(string $email): bool
    {
        $stmt = $this->db->prepare("
            SELECT failed_attempts, last_failed_attempt 
            FROM login_attempts 
            WHERE email = :email
        ");
        $stmt->execute([":email" => $email]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            return false;
        }

        if ($result['failed_attempts'] >= self::MAX_LOGIN_ATTEMPTS) {
            $lockoutExpiry = strtotime($result['last_failed_attempt']) + self::LOCKOUT_TIME;
            return time() < $lockoutExpiry;
        }

        return false;
    }

    private function recordFailedAttempt(string $email): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO login_attempts (email, failed_attempts, last_failed_attempt)
            VALUES (:email, 1, NOW())
            ON DUPLICATE KEY UPDATE
            failed_attempts = failed_attempts + 1,
            last_failed_attempt = NOW()
        ");
        $stmt->execute([":email" => $email]);
    }

    private function clearFailedAttempts(string $email): void
    {
        $stmt = $this->db->prepare("DELETE FROM login_attempts WHERE email = :email");
        $stmt->execute([":email" => $email]);
    }

    private function updateLastLogin(int $userId): void
    {
        $stmt = $this->db->prepare("UPDATE usuarios SET last_login = NOW() WHERE id = :id");
        $stmt->execute([":id" => $userId]);
    }

    private function updateUserPassword(int $userId, string $hashedPassword): void
    {
        $stmt = $this->db->prepare("UPDATE usuarios SET password = :password WHERE id = :id");
        $stmt->execute([":password" => $hashedPassword, ":id" => $userId]);
    }

    private function logUserActivity(int $userId, string $action, string $status): void
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO user_activity_log (user_id, action, status, ip_address, user_agent, created_at)
                VALUES (:user_id, :action, :status, :ip_address, :user_agent, NOW())
            ");
            
            $stmt->execute([
                ":user_id" => $userId,
                ":action" => $action,
                ":status" => $status,
                ":ip_address" => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                ":user_agent" => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
            ]);
        } catch (Exception $e) {
            // Log error but don't fail the main operation
            error_log("Error logging user activity: " . $e->getMessage());
        }
    }

    /**
     * Respuesta uniforme con headers de seguridad
     */
    private function sendResponse(
        int $statusCode,
        string $message,
        ?string $token = null,
        ?array $usuario = null
    ): void {
        // Headers de seguridad
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('X-XSS-Protection: 1; mode=block');
        
        http_response_code($statusCode);

        $response = [
            "success" => $statusCode >= 200 && $statusCode < 300,
            "message" => $message,
            "timestamp" => date('c')
        ];

        if ($token !== null) {
            $response["token"] = $token;
        }

        if ($usuario !== null) {
            $response["usuario"] = $usuario;
        }

        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}