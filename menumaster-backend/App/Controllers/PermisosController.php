<?php
namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Config\conexionDb;
use PDO;
use Exception;

class PermisosController
{
    private $db;
    private $authMiddleware;

    public function __construct()
    {
        $this->db = conexionDb::getConnection();
        $this->authMiddleware = new AuthMiddleware();
    }

    /**
     * Obtener todos los permisos del sistema
     */
    public function getPermisos(): void
    {
        try {
            // Verificar autenticación y permisos
            if (!$this->authMiddleware->checkPermission('roles.leer')) {
                $this->sendResponse(403, "No tienes permisos para ver los permisos del sistema");
                return;
            }

            $stmt = $this->db->prepare("
                SELECT id, nombre, descripcion, modulo, accion, estado_id, created_at
                FROM permisos 
                WHERE estado_id = 1
                ORDER BY modulo, nombre
            ");
            
            $stmt->execute();
            $permisos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Agrupar permisos por módulo
            $permisosPorModulo = [];
            foreach ($permisos as $permiso) {
                $permisosPorModulo[$permiso['modulo']][] = $permiso;
            }

            $this->sendResponse(200, "Permisos obtenidos correctamente", null, [
                'permisos' => $permisos,
                'permisos_por_modulo' => $permisosPorModulo,
                'total' => count($permisos)
            ]);

        } catch (Exception $e) {
            error_log("Error al obtener permisos: " . $e->getMessage());
            $this->sendResponse(500, "Error interno del servidor");
        }
    }

    /**
     * Obtener permisos de un rol específico
     */
    public function getPermisosByRol(): void
    {
        try {
            if (!$this->authMiddleware->checkPermission('roles.leer')) {
                $this->sendResponse(403, "No tienes permisos para ver los permisos de roles");
                return;
            }

            $data = $this->getJsonInput();
            
            if (!isset($data['rol_id'])) {
                $this->sendResponse(400, "ID del rol es requerido");
                return;
            }

            $stmt = $this->db->prepare("
                SELECT p.id, p.nombre, p.descripcion, p.modulo, p.accion,
                       CASE WHEN rp.rol_id IS NOT NULL THEN 1 ELSE 0 END as asignado
                FROM permisos p
                LEFT JOIN rol_permisos rp ON p.id = rp.permiso_id AND rp.rol_id = :rol_id
                WHERE p.estado_id = 1
                ORDER BY p.modulo, p.nombre
            ");
            
            $stmt->execute([':rol_id' => $data['rol_id']]);
            $permisos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $this->sendResponse(200, "Permisos del rol obtenidos correctamente", null, [
                'permisos' => $permisos,
                'rol_id' => $data['rol_id']
            ]);

        } catch (Exception $e) {
            error_log("Error al obtener permisos del rol: " . $e->getMessage());
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
            
            if (!isset($data['rol_id'], $data['permisos'])) {
                $this->sendResponse(400, "ID del rol y lista de permisos son requeridos");
                return;
            }

            // Verificar que el rol existe
            if (!$this->rolExists($data['rol_id'])) {
                $this->sendResponse(404, "El rol especificado no existe");
                return;
            }

            $this->db->beginTransaction();

            try {
                // Eliminar permisos actuales del rol
                $stmt = $this->db->prepare("DELETE FROM rol_permisos WHERE rol_id = :rol_id");
                $stmt->execute([':rol_id' => $data['rol_id']]);

                // Asignar nuevos permisos
                if (!empty($data['permisos'])) {
                    $stmt = $this->db->prepare("
                        INSERT INTO rol_permisos (rol_id, permiso_id) 
                        VALUES (:rol_id, :permiso_id)
                    ");

                    foreach ($data['permisos'] as $permisoId) {
                        if ($this->permisoExists($permisoId)) {
                            $stmt->execute([
                                ':rol_id' => $data['rol_id'],
                                ':permiso_id' => $permisoId
                            ]);
                        }
                    }
                }

                $this->db->commit();

                // Log de la actividad
                $usuario = $this->authMiddleware->getCurrentUser();
                $this->logActivity($usuario['id'], 'asignar_permisos', 'success', [
                    'rol_id' => $data['rol_id'],
                    'permisos_count' => count($data['permisos'])
                ]);

                $this->sendResponse(200, "Permisos asignados correctamente");

            } catch (Exception $e) {
                $this->db->rollBack();
                throw $e;
            }

        } catch (Exception $e) {
            error_log("Error al asignar permisos: " . $e->getMessage());
            $this->sendResponse(500, "Error interno del servidor");
        }
    }

    /**
     * Crear nuevo permiso
     */
    public function crearPermiso(): void
    {
        try {
            if (!$this->authMiddleware->checkPermission('roles.crear')) {
                $this->sendResponse(403, "No tienes permisos para crear permisos");
                return;
            }

            $data = $this->getJsonInput();
            
            $validation = $this->validatePermisoData($data);
            if (!$validation['valid']) {
                $this->sendResponse(400, $validation['message']);
                return;
            }

            // Verificar que no existe un permiso con el mismo nombre
            if ($this->permisoNameExists($data['nombre'])) {
                $this->sendResponse(409, "Ya existe un permiso con ese nombre");
                return;
            }

            $stmt = $this->db->prepare("
                INSERT INTO permisos (nombre, descripcion, modulo, accion, estado_id, created_at)
                VALUES (:nombre, :descripcion, :modulo, :accion, 1, NOW())
            ");
            
            $stmt->execute([
                ':nombre' => $data['nombre'],
                ':descripcion' => $data['descripcion'] ?? '',
                ':modulo' => $data['modulo'],
                ':accion' => $data['accion']
            ]);

            $permisoId = $this->db->lastInsertId();

            // Log de la actividad
            $usuario = $this->authMiddleware->getCurrentUser();
            $this->logActivity($usuario['id'], 'crear_permiso', 'success', [
                'permiso_id' => $permisoId,
                'nombre' => $data['nombre']
            ]);

            $this->sendResponse(201, "Permiso creado correctamente", null, [
                'permiso_id' => $permisoId
            ]);

        } catch (Exception $e) {
            error_log("Error al crear permiso: " . $e->getMessage());
            $this->sendResponse(500, "Error interno del servidor");
        }
    }

    /**
     * Obtener permisos del usuario actual
     */
    public function getMisPermisos(): void
    {
        try {
            $permisos = $this->authMiddleware->getCurrentUserPermissions();
            $usuario = $this->authMiddleware->getCurrentUser();

            $this->sendResponse(200, "Permisos del usuario obtenidos correctamente", null, [
                'permisos' => $permisos,
                'usuario' => [
                    'id' => $usuario['id'],
                    'nombre' => $usuario['nombre'],
                    'rol' => $usuario['rol']
                ]
            ]);

        } catch (Exception $e) {
            error_log("Error al obtener permisos del usuario: " . $e->getMessage());
            $this->sendResponse(500, "Error interno del servidor");
        }
    }

    /**
     * Verificar si el usuario tiene un permiso específico
     */
    public function verificarPermiso(): void
    {
        try {
            $data = $this->getJsonInput();
            
            if (!isset($data['permiso'])) {
                $this->sendResponse(400, "Nombre del permiso es requerido");
                return;
            }

            $tienePermiso = $this->authMiddleware->checkPermission($data['permiso']);

            $this->sendResponse(200, "Verificación de permiso completada", null, [
                'permiso' => $data['permiso'],
                'tiene_permiso' => $tienePermiso
            ]);

        } catch (Exception $e) {
            error_log("Error al verificar permiso: " . $e->getMessage());
            $this->sendResponse(500, "Error interno del servidor");
        }
    }

    /**
     * Obtener permisos del usuario actual (alias para getMisPermisos)
     */
    public function getCurrentUserPermisos(): void
    {
        $this->getMisPermisos();
    }

    /**
     * Verificar permiso específico (alias para verificarPermiso)
     */
    public function checkPermiso(): void
    {
        $this->verificarPermiso();
    }

    // Métodos privados de validación y utilidad

    private function getJsonInput(): array
    {
        $input = file_get_contents("php://input");
        $data = json_decode($input, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("JSON inválido");
        }
        
        return $data ?? [];
    }

    private function validatePermisoData(array $data): array
    {
        if (!isset($data['nombre'], $data['modulo'], $data['accion'])) {
            return ['valid' => false, 'message' => 'Nombre, módulo y acción son obligatorios'];
        }

        if (strlen(trim($data['nombre'])) < 3) {
            return ['valid' => false, 'message' => 'El nombre debe tener al menos 3 caracteres'];
        }

        if (strlen(trim($data['modulo'])) < 2) {
            return ['valid' => false, 'message' => 'El módulo debe tener al menos 2 caracteres'];
        }

        if (strlen(trim($data['accion'])) < 2) {
            return ['valid' => false, 'message' => 'La acción debe tener al menos 2 caracteres'];
        }

        return ['valid' => true, 'message' => ''];
    }

    private function rolExists(int $rolId): bool
    {
        $stmt = $this->db->prepare("SELECT id FROM roles WHERE id = :id AND estado_id = 1 LIMIT 1");
        $stmt->execute([':id' => $rolId]);
        return $stmt->fetch() !== false;
    }

    private function permisoExists(int $permisoId): bool
    {
        $stmt = $this->db->prepare("SELECT id FROM permisos WHERE id = :id AND estado_id = 1 LIMIT 1");
        $stmt->execute([':id' => $permisoId]);
        return $stmt->fetch() !== false;
    }

    private function permisoNameExists(string $nombre): bool
    {
        $stmt = $this->db->prepare("SELECT id FROM permisos WHERE nombre = :nombre LIMIT 1");
        $stmt->execute([':nombre' => $nombre]);
        return $stmt->fetch() !== false;
    }

    private function logActivity(int $userId, string $action, string $status, array $details = []): void
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO user_activity_log (user_id, action, status, ip_address, user_agent, details, created_at)
                VALUES (:user_id, :action, :status, :ip_address, :user_agent, :details, NOW())
            ");
            
            $stmt->execute([
                ':user_id' => $userId,
                ':action' => $action,
                ':status' => $status,
                ':ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
                ':details' => json_encode($details)
            ]);
        } catch (Exception $e) {
            error_log("Error logging activity: " . $e->getMessage());
        }
    }

    private function sendResponse(
        int $statusCode,
        string $message,
        ?string $token = null,
        ?array $data = null
    ): void {
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

        if ($data !== null) {
            $response["data"] = $data;
        }

        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}