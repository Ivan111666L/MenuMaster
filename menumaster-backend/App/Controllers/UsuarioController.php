<?php
namespace App\Controllers;

// --- Dependencias ---
use App\Models\UsuarioModel;
use App\Models\RolModel;
use App\Models\EstadoGeneralModel;
use App\Utils\Validator;
use App\Middleware\AuthMiddleware;
use App\Controllers\AuthController;
use PDO;
use Exception;

class UsuarioController
{
    // --- Propiedades para los modelos ---
    private $usuarioModel;
    private $rolModel;
    private $estadoGeneralModel;

    /**
     * El constructor recibe la conexión a la DB e instancia todos los modelos necesarios.
     */
    public function __construct(PDO $db)
    {
        $this->usuarioModel = new UsuarioModel($db);
        $this->rolModel = new RolModel($db);
        $this->estadoGeneralModel = new EstadoGeneralModel($db);
    }

    /**
     * Obtiene una lista de todos los usuarios.
     * Corresponde a: GET /api/usuarios
     */
    public function index(): void
    {
        $usuarios = $this->usuarioModel->findAll();
        $this->sendResponse(200, $usuarios);
    }

    /**
     * Obtiene un único usuario por su ID.
     * Corresponde a: GET /api/usuarios/{id}
     */
    public function show(int $id): void
    {
        $usuario = $this->usuarioModel->find($id);
        if (!$usuario) {
            throw new Exception("Usuario no encontrado", 404);
        }
        $this->sendResponse(200, $usuario);
    }

    /**
     * Crea un nuevo usuario.
     * Corresponde a: POST /api/usuarios
     */
    public function store(array $data): void
    {
        // Se usa el Validator para una validación limpia y centralizada.
        Validator::validate($data, [
            'nombre' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8',
            'rol' => 'required' // El frontend envía el nombre del rol
        ]);
        
        if ($this->usuarioModel->findByEmail($data['email'])) {
            throw new Exception("El correo electrónico ya está registrado.", 409); // 409 Conflict
        }
        
        $rol = $this->rolModel->findByName($data['rol']);
        if (!$rol) {
            throw new Exception("El rol '{$data['rol']}' no es válido.", 400);
        }

        $password_hash = password_hash($data['password'], PASSWORD_BCRYPT);
        
        $nuevoId = $this->usuarioModel->create($data['nombre'], $data['email'], $password_hash, $rol['id']);
        if (!$nuevoId) {
            throw new Exception("No se pudo crear el usuario.", 500);
        }
        
        $nuevoUsuario = $this->usuarioModel->find($nuevoId);
        $this->sendResponse(201, $nuevoUsuario);
    }

    /**
     * Actualiza un usuario existente.
     * Corresponde a: PUT /api/usuarios/{id}
     */
    public function update(int $id, array $data): void
    {
        // El validador se adapta para los campos de actualización
        Validator::validate($data, [
            'nombre' => 'required',
            'email' => 'required|email',
            'rol' => 'required',
            'estado' => 'required'
        ]);
        
        if (!$this->usuarioModel->find($id)) {
            throw new Exception("Usuario no encontrado.", 404);
        }

        $rol = $this->rolModel->findByName($data['rol']);
        $estado = $this->estadoGeneralModel->findByName($data['estado']);
        if (!$rol || !$estado) {
            throw new Exception("El rol o estado especificado no es válido.", 400);
        }

        if (!$this->usuarioModel->update($id, $data['nombre'], $data['email'], $rol['id'], $estado['id'])) {
            throw new Exception("No se pudo actualizar el usuario.", 500);
        }

        $usuarioActualizado = $this->usuarioModel->find($id);
        $this->sendResponse(200, $usuarioActualizado);
    }

    /**
     * Elimina un usuario.
     * Corresponde a: DELETE /api/usuarios/{id}
     */
    public function destroy(int $id): void
    {
        if (!$this->usuarioModel->find($id)) {
            throw new Exception("Usuario no encontrado.", 404);
        }
        if (!$this->usuarioModel->delete($id)) {
            throw new Exception("No se pudo eliminar el usuario.", 500);
        }
        $this->sendResponse(204, null);
    }

    /**
     * Obtiene el perfil del usuario autenticado actualmente a través de su token.
     * Corresponde a: GET /api/usuarios/perfil
     */
    public function getProfile(): void
    {
        $token = (new AuthMiddleware())->getBearerTokenForInternalUse();
        if (!$token) {
            throw new Exception("Token de autorización no encontrado.", 401);
        }

        $payload = AuthController::decodeTokenData($token);
        $usuarioId = $payload['data']['id'] ?? null;
        if (!$usuarioId) {
            throw new Exception("Token inválido: ID de usuario no encontrado.", 401);
        }

        $usuario = $this->usuarioModel->find($usuarioId);
        if (!$usuario) {
            throw new Exception("El usuario asociado al token ya no existe.", 404);
        }
        $this->sendResponse(200, $usuario);
    }

    /**
     * Desactiva la cuenta de un usuario específico.
     * Corresponde a: PUT /api/usuarios/{id}/desactivar
     */
    public function deactivate(int $id): void
    {
        $estadoInactivo = $this->estadoGeneralModel->findByName('inactivo');
        if (!$estadoInactivo) {
            throw new Exception("El estado 'inactivo' no está configurado en la base de datos.", 500);
        }

        if (!$this->usuarioModel->updateStatus($id, $estadoInactivo['id'])) {
            throw new Exception("No se pudo desactivar el usuario.", 500);
        }

        $this->sendResponse(200, ["mensaje" => "Usuario desactivado correctamente."]);
    }

    // --- Métodos de Ayuda ---

    /**
     * Envía la respuesta HTTP en formato JSON y termina la ejecución del script.
     */
    private function sendResponse(int $statusCode, $data): void
    {
        http_response_code($statusCode);
        if ($statusCode !== 204) {
            // Se estandariza la respuesta de éxito
            echo json_encode(['success' => true, 'data' => $data]);
        }
        exit;
    }
}