<?php
namespace App\Controllers;

// --- Dependencias ---
use App\Models\UsuarioModel;
use App\Models\RolModel;
use App\Utils\Validator;
use App\Config\Config;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use PDO;
use Exception;

class AuthController
{
    // Las dependencias ahora son propiedades
    private $db;
    private $usuarioModel;
    private $rolModel;

    /**
     * El constructor recibe la conexión e inyecta los modelos.
     */
    public function __construct(PDO $db, UsuarioModel $usuarioModel, RolModel $rolModel)
    {
        $this->db = $db;
        $this->usuarioModel = $usuarioModel;
        $this->rolModel = $rolModel;
    }

    /**
     * Registra un nuevo usuario.
     */
    public function register(array $data): void
    {
        Validator::validate($data, [
            'nombre' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8',
            'rol' => 'required'
        ]);
        
        if ($this->usuarioModel->findByEmail($data['email'])) {
            throw new Exception("El correo electrónico ya está registrado.", 409);
        }

        $rol = $this->rolModel->findByName(strtolower(trim($data['rol'])));
        if (!$rol) {
            throw new Exception("El rol especificado no es válido.", 400);
        }

        $password_hash = password_hash($data['password'], PASSWORD_BCRYPT);
        $nuevoUsuarioId = $this->usuarioModel->create($data['nombre'], $data['email'], $password_hash, $rol['id']);
        
        if (!$nuevoUsuarioId) {
            throw new Exception("No se pudo registrar el usuario.", 500);
        }
        
        $nuevoUsuario = $this->usuarioModel->find($nuevoUsuarioId);
        $this->sendResponse(201, [
            "mensaje" => "Usuario creado correctamente.",
            "usuario" => $nuevoUsuario
        ]);
    }
    
    /**
     * Autentica a un usuario y devuelve un token JWT.
     */
    public function login(array $data): void
    {
        Validator::validate($data, ['email' => 'required|email', 'password' => 'required']);

        $usuario = $this->usuarioModel->findByEmail($data['email']);

        if ($usuario && password_verify($data['password'], $usuario['password'])) {
            $jwtConfig = Config::getJwtConfig();
            $secret_key = $_ENV['JWT_SECRET_KEY'] ?? $jwtConfig['secret'];
            $expire_claim = time() + $jwtConfig['expiration_time'];

            $payload = [
                "iat" => time(),
                "exp" => $expire_claim,
                "data" => ["id" => $usuario['id'], "rol_id" => $usuario['rol_id']]
            ];

            $jwt = JWT::encode($payload, $secret_key, $jwtConfig['algorithm']);
            $datosUsuarioParaFrontend = $this->usuarioModel->find($usuario['id']);
            
            $this->sendResponse(200, [
                "mensaje" => "Inicio de sesión exitoso.",
                "token" => $jwt,
                "expiraEn" => $expire_claim,
                "usuario" => $datosUsuarioParaFrontend
            ]);
        } else {
            throw new Exception("Credenciales incorrectas.", 401);
        }
    }

    /**
     * Maneja la solicitud de restablecimiento de contraseña.
     */
    public function forgotPassword(array $data): void
    {
        Validator::validate($data, ['email' => 'required|email']);
        $usuario = $this->usuarioModel->findByEmail($data['email']);

        if ($usuario) {
            $token = bin2hex(random_bytes(32));
            $tokenHash = hash('sha256', $token);
            $expiresAt = (new \DateTime('+1 hour'))->format('Y-m-d H:i:s');
            $this->usuarioModel->setResetToken($usuario['id'], $tokenHash, $expiresAt);
            $this->sendPasswordResetEmail($data['email'], $token);
        }

        $this->sendResponse(200, ["mensaje" => "Si una cuenta con ese correo existe, hemos enviado un enlace para restablecer la contraseña."]);
    }

    /**
     * Restablece la contraseña de un usuario usando un token válido.
     */
    public function resetPassword(array $data): void
    {
        Validator::validate($data, [
            'token' => 'required',
            'password' => 'required|min:8'
        ]);

        $tokenHash = hash('sha256', $data['token']);
        $usuario = $this->usuarioModel->findByResetToken($tokenHash);
        if (!$usuario) {
            throw new Exception("Token inválido o expirado.", 400);
        }

        $newPasswordHash = password_hash($data['password'], PASSWORD_BCRYPT);
        $success = $this->usuarioModel->updatePasswordAndClearResetToken($usuario['id'], $newPasswordHash);
        if (!$success) {
            throw new Exception("Hubo un error al actualizar la contraseña.", 500);
        }

        $this->sendResponse(200, ["mensaje" => "Contraseña actualizada exitosamente."]);
    }
    
    /**
     * Decodifica un token JWT para obtener su contenido (payload).
     */
    public static function decodeTokenData(string $token): array
    {
        $jwtConfig = Config::getJwtConfig();
        $secret_key = $_ENV['JWT_SECRET_KEY'] ?? $jwtConfig['secret'];
        $decoded = JWT::decode($token, new Key($secret_key, $jwtConfig['algorithm']));
        return (array) $decoded->data;
    }

    /**
     * Envía el correo de restablecimiento (método de ayuda).
     */
    private function sendPasswordResetEmail(string $email, string $token): void
    {
        $resetLink = ($_ENV['FRONTEND_URL'] ?? 'http://localhost:5173') . "/reset-password?token=" . $token;
        $asunto = "Restablecimiento de Contraseña - MenuMaster";
        $cuerpo = "<h1>Restablece tu Contraseña</h1><p>Haz clic en el siguiente enlace para continuar:</p><a href='{$resetLink}'>Restablecer Contraseña</a><p>El enlace expirará en 1 hora.</p>";
        
        file_put_contents(BASE_PATH . '/logs/emails.log', "--- EMAIL TO: {$email} ---\nLINK: {$resetLink}\n\n", FILE_APPEND);
    }
    
    /**
     * Envía la respuesta HTTP en formato JSON y termina la ejecución.
     */
    private function sendResponse(int $statusCode, $data): void
    {
        http_response_code($statusCode);
        echo json_encode(['success' => true, 'data' => $data]);
        exit;
    }
}