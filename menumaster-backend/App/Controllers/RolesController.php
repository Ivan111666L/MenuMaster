<?php
namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Middleware\RolMiddleware;
use App\Models\RolModel;
use App\Config\conexionDb;
use PDO;
use Exception;

class RolesController extends Controller
{
    private $authMiddleware;
    private $rolMiddleware;
    private $rolModel;

    public function __construct()
    {
        $db = conexionDb::getConnection();
        parent::__construct($db);
        $this->authMiddleware = new AuthMiddleware();
        $this->rolMiddleware = new RolMiddleware();
        $this->rolModel = new RolModel($this->db);
    }

    /**
     * Obtener todos los roles del sistema
     */
    public function getRoles(): void
    {
        try {
            // Verificar autenticación y permisos
            if (!$this->rolMiddleware->checkPermission('roles.leer')) {
                $this->sendResponse(403, "No tienes permisos para ver los roles del sistema");
                return;
            }

            $roles = $this->rolModel->getAllActive();

            // Obtener permisos para cada rol
            foreach ($roles as &$rol) {
                $rol['permisos'] = $this->rolModel->getPermisos($rol['id']);
                $rol['permisos_count'] = count($rol['permisos']);
            }

            $this->sendResponse(200, "Roles obtenidos correctamente", null, [
                'roles' => $roles,
                'total' => count($roles)
            ]);

        } catch (Exception $e) {
            error_log("Error al obtener roles: " . $e->getMessage());
            $this->sendResponse(500, "Error interno del servidor");
        }
    }

    /**
     * Obtener un rol específico por ID
     */
    public function getRolById(): void
    {
        try {
            if (!$this->rolMiddleware->checkPermission('roles.leer')) {
                $this->sendResponse(403, "No tienes permisos para ver roles");
                return;
            }

            $data = $this->getJsonInput();
            
            if (!isset($data['id'])) {
                $this->sendResponse(400, "ID del rol es requerido");
                return;
            }

            $rol = $this->rolModel->findById($data['id']);
            
            if (!$rol) {
                $this->sendResponse(404, "Rol no encontrado");
                return;
            }

            // Obtener permisos del rol
            $rol['permisos'] = $this->rolModel->getPermisos($rol['id']);

            $this->sendResponse(200, "Rol obtenido correctamente", null, ['rol' => $rol]);

        } catch (Exception $e) {
            error_log("Error al obtener rol: " . $e->getMessage());
            $this->sendResponse(500, "Error interno del servidor");
        }
    }

    /**
     * Crear nuevo rol
     */
    public function crearRol(): void
    {
        try {
            if (!$this->rolMiddleware->checkPermission('roles.crear')) {
                $this->sendResponse(403, "No tienes permisos para crear roles");
                return;
            }

            $data = $this->getJsonInput();
            
            $validation = $this->validateRolData($data);
            if (!$validation['valid']) {
                $this->sendResponse(400, $validation['message']);
                return;
            }

            // Verificar que no existe un rol con el mismo nombre
            if ($this->rolModel->nameExists($data['nombre'])) {
                $this->sendResponse(409, "Ya existe un rol con ese nombre");
                return;
            }

            $rolId = $this->rolModel->create($data);

            // Asignar permisos si se proporcionaron
            if (!empty($data['permisos'])) {
                $this->rolModel->assignPermisos($rolId, $data['permisos']);
            }

            // Log de la actividad
            $usuario = $this->authMiddleware->getCurrentUser();
            $this->logActivity($usuario['id'], 'crear_rol', 'success', [
                'rol_id' => $rolId,
                'nombre' => $data['nombre']
            ]);

            $this->sendResponse(201, "Rol creado correctamente", null, ['rol_id' => $rolId]);

        } catch (Exception $e) {
            error_log("Error al crear rol: " . $e->getMessage());
            $this->sendResponse(500, "Error interno del servidor");
        }
    }

    /**
     * Actualizar rol existente
     */
    public function actualizarRol(): void
    {
        try {
            if (!$this->rolMiddleware->checkPermission('roles.actualizar')) {
                $this->sendResponse(403, "No tienes permisos para actualizar roles");
                return;
            }

            $data = $this->getJsonInput();
            
            if (!isset($data['id'])) {
                $this->sendResponse(400, "ID del rol es requerido");
                return;
            }

            $validation = $this->validateRolData($data);
            if (!$validation['valid']) {
                $this->sendResponse(400, $validation['message']);
                return;
            }

            // Verificar que el rol existe
            if (!$this->rolModel->exists($data['id'])) {
                $this->sendResponse(404, "Rol no encontrado");
                return;
            }

            // Verificar que no existe otro rol con el mismo nombre
            if ($this->rolModel->nameExists($data['nombre'], $data['id'])) {
                $this->sendResponse(409, "Ya existe otro rol con ese nombre");
                return;
            }

            $this->rolModel->update($data['id'], $data);

            // Actualizar permisos si se proporcionaron
            if (isset($data['permisos'])) {
                $this->rolModel->assignPermisos($data['id'], $data['permisos']);
            }

            // Log de la actividad
            $usuario = $this->authMiddleware->getCurrentUser();
            $this->logActivity($usuario['id'], 'actualizar_rol', 'success', [
                'rol_id' => $data['id'],
                'nombre' => $data['nombre']
            ]);

            $this->sendResponse(200, "Rol actualizado correctamente");

        } catch (Exception $e) {
            error_log("Error al actualizar rol: " . $e->getMessage());
            $this->sendResponse(500, "Error interno del servidor");
        }
    }

    /**
     * Eliminar rol (cambiar estado a inactivo)
     */
    public function eliminarRol(): void
    {
        try {
            if (!$this->rolMiddleware->checkPermission('roles.eliminar')) {
                $this->sendResponse(403, "No tienes permisos para eliminar roles");
                return;
            }

            $data = $this->getJsonInput();
            
            if (!isset($data['id'])) {
                $this->sendResponse(400, "ID del rol es requerido");
                return;
            }

            // Verificar que el rol existe
            if (!$this->rolModel->exists($data['id'])) {
                $this->sendResponse(404, "Rol no encontrado");
                return;
            }

            // Verificar que no es un rol del sistema (administrador, etc.)
            $rol = $this->rolModel->findById($data['id']);
            if (in_array($rol['nombre'], ['administrador', 'super_admin'])) {
                $this->sendResponse(400, "No se puede eliminar un rol del sistema");
                return;
            }

            // Verificar que no hay usuarios asignados a este rol
            $usuariosCount = $this->countUsuariosByRol($data['id']);
            if ($usuariosCount > 0) {
                $this->sendResponse(400, "No se puede eliminar el rol porque tiene usuarios asignados");
                return;
            }

            $this->rolModel->delete($data['id']);

            // Log de la actividad
            $usuario = $this->authMiddleware->getCurrentUser();
            $this->logActivity($usuario['id'], 'eliminar_rol', 'success', [
                'rol_id' => $data['id'],
                'nombre' => $rol['nombre']
            ]);

            $this->sendResponse(200, "Rol eliminado correctamente");

        } catch (Exception $e) {
            error_log("Error al eliminar rol: " . $e->getMessage());
            $this->sendResponse(500, "Error interno del servidor");
        }
    }

    /**
     * Asignar permisos a un rol
     */
    public function asignarPermisos(): void
    {
        try {
            if (!$this->authMiddleware->checkPermission('roles.actualizar')) {
                $this->sendResponse(403, "No tienes permisos para asignar permisos");
                return;
            }

            $data = $this->getJsonInput();
            
            if (!isset($data['rol_id']) || !isset($data['permisos'])) {
                $this->sendResponse(400, "ID del rol y lista de permisos son requeridos");
                return;
            }

            // Verificar que el rol existe
            if (!$this->rolModel->exists($data['rol_id'])) {
                $this->sendResponse(404, "Rol no encontrado");
                return;
            }

            // Verificar que todos los permisos existen
            foreach ($data['permisos'] as $permisoId) {
                if (!$this->permisoExists($permisoId)) {
                    $this->sendResponse(400, "Permiso con ID {$permisoId} no existe");
                    return;
                }
            }

            $this->rolModel->assignPermisos($data['rol_id'], $data['permisos']);

            // Log de la actividad
            $usuario = $this->authMiddleware->getCurrentUser();
            $this->logActivity($usuario['id'], 'asignar_permisos_rol', 'success', [
                'rol_id' => $data['rol_id'],
                'permisos_count' => count($data['permisos'])
            ]);

            $this->sendResponse(200, "Permisos asignados correctamente");

        } catch (Exception $e) {
            error_log("Error al asignar permisos: " . $e->getMessage());
            $this->sendResponse(500, "Error interno del servidor");
        }
    }

    // --- Métodos privados de ayuda ---

    /**
     * Obtener datos JSON de la petición
     */
    protected function getJsonInput(): array
    {
        $raw = file_get_contents("php://input");
        $decoded = json_decode($raw, true);

        // Si el JSON es inválido o está vacío, devolver array vacío
        return (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) ? $decoded : [];
    }

    /**
     * Validar datos del rol
     */
    private function validateRolData(array $data): array
    {
        if (empty($data['nombre'])) {
            return ['valid' => false, 'message' => 'El nombre del rol es requerido'];
        }

        if (strlen($data['nombre']) < 3) {
            return ['valid' => false, 'message' => 'El nombre del rol debe tener al menos 3 caracteres'];
        }

        if (strlen($data['nombre']) > 50) {
            return ['valid' => false, 'message' => 'El nombre del rol no puede exceder 50 caracteres'];
        }

        if (!preg_match('/^[a-zA-Z0-9_\s]+$/', $data['nombre'])) {
            return ['valid' => false, 'message' => 'El nombre del rol solo puede contener letras, números, guiones bajos y espacios'];
        }

        return ['valid' => true];
    }

    /**
     * Verificar si un permiso existe
     */
    private function permisoExists(int $permisoId): bool
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM permisos WHERE id = :id AND estado_id = 1");
        $stmt->execute([':id' => $permisoId]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Contar usuarios asignados a un rol
     */
    private function countUsuariosByRol(int $rolId): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM usuarios WHERE rol_id = :rol_id AND estado_id = 1");
        $stmt->execute([':rol_id' => $rolId]);
        return $stmt->fetchColumn();
    }

    /**
     * Registrar actividad del usuario
     */
    private function logActivity(int $userId, string $action, string $status, array $details = []): void
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO user_activity_log (user_id, action, status, details, ip_address, user_agent, created_at)
                VALUES (:user_id, :action, :status, :details, :ip_address, :user_agent, NOW())
            ");
            
            $stmt->execute([
                ':user_id' => $userId,
                ':action' => $action,
                ':status' => $status,
                ':details' => json_encode($details),
                ':ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
            ]);
        } catch (Exception $e) {
            error_log("Error al registrar actividad: " . $e->getMessage());
        }
    }

    /**
     * Enviar respuesta JSON
     */
    protected function sendResponse(
        int $statusCode,
        ?string $message = null,
        ?string $token = null,
        ?array $data = null
    ): void {
        $response = [
            "success" => $statusCode >= 200 && $statusCode < 300,
            "timestamp" => date('c')
        ];

        if ($message !== null) {
            $response["message"] = $message;
        }

        if ($token !== null) {
            $response["token"] = $token;
        }

        if ($data !== null) {
            $response["data"] = $data;
        }

        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}