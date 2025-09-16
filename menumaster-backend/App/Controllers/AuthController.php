<?php
namespace App\Controllers;

// --- Dependencias ---
use App\Models\Usuario;
use App\Models\Rol;
use App\Config; // Tu clase de configuración centralizada
use Firebase\JWT\JWT;
use Firebase\JWT\Key; // IMPORTANTE: Usar la clase Key para la firma
use PDO;
use Exception;

class AuthController
{
    private $db;

    /**
     * El constructor recibe la conexión a la base de datos (Inyección de Dependencias).
     */
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Registra un nuevo usuario en el sistema.
     * @param array $data Datos del usuario (nombre, email, password, rol).
     * @return array Mensaje de éxito y datos del usuario creado.
     * @throws Exception Si la validación o el registro fallan.
     */
    public function register(array $data): array
    {
        $this->validarCampos($data, ['nombre', 'email', 'password', 'rol']);
        
        $nombre = trim($data['nombre']);
        $email = filter_var(trim($data['email']), FILTER_VALIDATE_EMAIL);
        $password = $data['password'];
        $rolNombre = strtolower(trim($data['rol']));

        // Validaciones adicionales de negocio
        if (!$email || strlen($password) < 8) {
            throw new Exception("Datos de entrada no válidos. El email debe ser correcto y la contraseña de al menos 8 caracteres.", 400);
        }

        $usuarioModel = new Usuario($this->db);
        $rolModel = new Rol($this->db);

        
        // Verificar si el email ya existe para evitar errores de duplicidad de la DB
        if ($usuarioModel->findByEmail($email)) {
            throw new Exception("El correo electrónico ya está registrado.", 409); // 409 Conflict
        }

        $rol = $rolModel->findByName($rolNombre);
        if (!$rol) {
            throw new Exception("El rol especificado no es válido.", 400);
        }

        $password_hash = password_hash($password, PASSWORD_BCRYPT);

        // Crear el usuario en la base de datos
        $nuevoUsuarioId = $usuarioModel->create($nombre, $email, $password_hash, $rol['id']);
        
        if (!$nuevoUsuarioId) {
            throw new Exception("No se pudo registrar el usuario en la base de datos.", 500);
        }
        
        // Devolvemos el objeto completo del usuario recién creado.
        // Esto es muy útil para que el frontend pueda actualizar su estado inmediatamente.
        $nuevoUsuario = $usuarioModel->find($nuevoUsuarioId);
        
        return ["mensaje" => "Usuario creado correctamente.", "usuario" => $nuevoUsuario];
    }
    protected function getUsuarioModel() {
    return new Usuario($this->db);
    }
    protected function getRolModel() {
        return new Rol($this->db);
    }
    
    /**
     * Autentica a un usuario y devuelve un token JWT y los datos de la sesión.
     * @param array $data Credenciales (email, password).
     * @return array Mensaje, token y datos del usuario.
     * @throws Exception Si las credenciales son incorrectas.
     */
    public function login(array $data): array
    {
        $this->validarCampos($data, ['email', 'password']);
        $email = $data['email'];
        $password = $data['password'];

        $usuarioModel = new Usuario($this->db);
        $usuario = $usuarioModel->findByEmail($email);

        // Verificar si el usuario existe y la contraseña es correcta
        if ($usuario && password_verify($password, $usuario['password'])) {
        
            $jwtConfig = Config::getJwtConfig();
            $secret_key = $jwtConfig['secret'];
            $expire_claim = time() + $jwtConfig['expiration_time'];

            $payload = [
                "iat" => time(), // Momento en que se emitió el token
                "exp" => $expire_claim, // Momento en que expira el token
                "data" => [ // Datos personalizados que queremos guardar
                    "id" => $usuario['id'],
                    "rol_id" => $usuario['rol_id']
                ]
            ];

            $jwt = JWT::encode($payload, $secret_key, $jwtConfig['algorithm']);

            // Obtenemos los datos limpios del usuario para enviar al frontend
            // El método find() del modelo no debe devolver el hash de la contraseña.
            $datosUsuarioParaFrontend = $usuarioModel->find($usuario['id']);
            
            return [
                "mensaje" => "Inicio de sesión exitoso.",
                "token" => $jwt,
                "expiraEn" => $expire_claim,
                "usuario" => $datosUsuarioParaFrontend
            ];
        } else {
            throw new Exception("Credenciales incorrectas.", 401); // 401 Unauthorized
        }
    }

    /**
     * MÉTODO ESTÁTICO AÑADIDO:
     * Decodifica un token JWT para obtener su contenido (payload).
     * Es estático para poder ser llamado desde cualquier parte sin instanciar el controlador.
     * @param string $token El token a decodificar.
     * @return array El payload del token como un array.
     */
    public static function decodeTokenData(string $token): array
    {
        $jwtConfig = Config::getJwtConfig();
        // Usamos la clase Key para mayor seguridad
        $decoded = JWT::decode($token, new Key($jwtConfig['secret'], $jwtConfig['algorithm']));
        return (array) $decoded->data;
    }
    
    /**
     * Validador simple para campos requeridos.
     */
    private function validarCampos(array $data, array $camposRequeridos): void
    {
        foreach ($camposRequeridos as $campo) {
            if (empty($data[$campo])) {
                throw new Exception("El campo '{$campo}' es obligatorio.", 400);
            }
        }
    }

    /**
 * Maneja la solicitud de restablecimiento de contraseña.
 */
public function forgotPassword(array $data): array
{
    $this->validarCampos($data, ['email']);
    $email = filter_var($data['email'], FILTER_VALIDATE_EMAIL);
    if (!$email) {
        throw new Exception("El formato del correo electrónico no es válido.", 400);
    }

    $usuarioModel = new Usuario($this->db);
    $usuario = $usuarioModel->findByEmail($email);

    // NOTA DE SEGURIDAD:
    // Incluso si el usuario no existe, devolvemos un mensaje de éxito genérico.
    // Esto previene que un atacante pueda adivinar qué correos están registrados.
    if ($usuario) {
        // 1. Generar un token seguro
        $token = bin2hex(random_bytes(32)); // Token real que se enviará por email
        $tokenHash = hash('sha256', $token); // Hash que se guardará en la DB

        // 2. Establecer fecha de expiración (ej. 1 hora)
        $expiresAt = (new \DateTime('+1 hour'))->format('Y-m-d H:i:s');
        
        // 3. Guardar en la base de datos
        $usuarioModel->setResetToken($usuario['id'], $tokenHash, $expiresAt);

        // 4. Enviar el correo electrónico
        $this->sendPasswordResetEmail($email, $token);
    }

    return ["mensaje" => "Si una cuenta con ese correo existe, hemos enviado un enlace para restablecer la contraseña."];
    }

    /**
     * Envía el correo de restablecimiento. (Método de ayuda)
     */
    private function sendPasswordResetEmail(string $email, string $token): void
    {
        // Lógica para enviar el correo. Se recomienda usar una librería como PHPMailer.
        // Este es un ejemplo y necesitará ser configurado.
        
        $resetLink = "http://localhost:5173/reset-password?token=" . $token; // URL de tu frontend

        $asunto = "Restablecimiento de Contraseña - MenuMaster";
        $cuerpo = "<h1>Restablece tu Contraseña</h1>";
        $cuerpo .= "<p>Has solicitado restablecer tu contraseña. Haz clic en el siguiente enlace para continuar:</p>";
        $cuerpo .= "<a href='{$resetLink}'>Restablecer Contraseña</a>";
        $cuerpo .= "<p>El enlace expirará en 1 hora.</p>";
        $cuerpo .= "<p>Si no solicitaste esto, puedes ignorar este correo.</p>";

        // Aquí iría el código de PHPMailer u otro servicio de email.
        // Ejemplo simple con la función mail() de PHP (no recomendado para producción):
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From: <no-reply@menumaster.com>' . "\r\n";
        
        // mail($email, $asunto, $cuerpo, $headers);
        
        // Para desarrollo, es mejor guardar el email en un log en lugar de enviarlo.
        file_put_contents(BASE_PATH . '/logs/emails.log', "--- EMAIL TO: {$email} ---\nLINK: {$resetLink}\n\n", FILE_APPEND);
    }

    /**
 * Restablece la contraseña de un usuario usando un token válido.
 */
public function resetPassword(array $data): array
{
    $this->validarCampos($data, ['token', 'password', 'confirmPassword']);

    if (strlen($data['password']) < 8) {
        throw new Exception("La contraseña debe tener al menos 8 caracteres.", 400);
    }
    if ($data['password'] !== $data['confirmPassword']) {
        throw new Exception("Las contraseñas no coinciden.", 400);
    }

    $token = $data['token'];
    $tokenHash = hash('sha256', $token); // Hasheamos el token recibido para compararlo con la BD

    $usuarioModel = new Usuario($this->db);
    $usuario = $usuarioModel->findByResetToken($tokenHash);

    if (!$usuario) {
        throw new Exception("Token inválido o expirado.", 400);
    }

    // Si el token es válido, actualizamos la contraseña
    $newPasswordHash = password_hash($data['password'], PASSWORD_BCRYPT);
    $success = $usuarioModel->updatePasswordAndClearResetToken($usuario['id'], $newPasswordHash);

    if (!$success) {
        throw new Exception("Hubo un error al actualizar la contraseña.", 500);
    }

    return ["mensaje" => "Contraseña actualizada exitosamente."];
    }
}
