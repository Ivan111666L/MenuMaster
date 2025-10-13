<?php
namespace App\Middleware;

use App\Config\ConexionDb;
use App\Models\RolModel;
use Exception;

/**
 * RolMiddleware - Maneja la autorización basada en roles y permisos
 * Requiere que AuthMiddleware haya validado la autenticación previamente
 */
class RolMiddleware
{
    private $db;
    private $rolModel;
    private $authMiddleware;

    public function __construct()
    {
        $this->db = ConexionDb::getConnection();
        $this->rolModel = new RolModel($this->db);
        $this->authMiddleware = new AuthMiddleware();
    }

    /**
     * Verificar si el usuario tiene un rol específico
     */
    public function checkRole(string $rolNombre): bool
    {
        $usuario = $this->authMiddleware->getCurrentUser();
        
        if (!$usuario) {
            return false;
        }

        return strtolower($usuario['rol']) === strtolower($rolNombre);
    }

    /**
     * Verificar si el usuario tiene alguno de los roles especificados
     */
    public function checkAnyRole(array $roles): bool
    {
        foreach ($roles as $rol) {
            if ($this->checkRole($rol)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Verificar si el usuario tiene todos los roles especificados
     */
    public function checkAllRoles(array $roles): bool
    {
        foreach ($roles as $rol) {
            if (!$this->checkRole($rol)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Verificar permisos específicos por nombre
     */
    public function checkPermission(string $permiso): bool
    {
        $usuario = $this->authMiddleware->getCurrentUser();
        
        if (!$usuario) {
            return false;
        }

        // Verificar si el usuario tiene el permiso específico
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count
            FROM rol_permisos rp
            INNER JOIN permisos p ON rp.permiso_id = p.id
            WHERE rp.rol_id = :rol_id 
            AND p.nombre = :permiso
        ");
        
        $stmt->execute([
            ':rol_id' => $usuario['rol_id'],
            ':permiso' => $permiso
        ]);
        
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }

    /**
     * Verificar permisos específicos por módulo y acción
     */
    public function checkModulePermission(string $modulo, string $accion): bool
    {
        $usuario = $this->authMiddleware->getCurrentUser();
        
        if (!$usuario) {
            return false;
        }

        // Verificar si el usuario tiene el permiso específico por módulo y acción
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count
            FROM rol_permisos rp
            INNER JOIN permisos p ON rp.permiso_id = p.id
            WHERE rp.rol_id = :rol_id 
            AND p.modulo = :modulo 
            AND p.accion = :accion
        ");
        
        $stmt->execute([
            ':rol_id' => $usuario['rol_id'],
            ':modulo' => $modulo,
            ':accion' => $accion
        ]);
        
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }

    /**
     * Obtener todos los permisos del usuario actual
     */
    public function getUserPermissions(): array
    {
        $usuario = $this->authMiddleware->getCurrentUser();
        
        if (!$usuario) {
            return [];
        }

        $stmt = $this->db->prepare("
            SELECT p.id, p.nombre, p.descripcion, p.modulo, p.accion
            FROM rol_permisos rp
            INNER JOIN permisos p ON rp.permiso_id = p.id
            WHERE rp.rol_id = :rol_id
            ORDER BY p.modulo, p.accion
        ");
        
        $stmt->execute([':rol_id' => $usuario['rol_id']]);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Obtener permisos agrupados por módulo
     */
    public function getUserPermissionsByModule(): array
    {
        $permisos = $this->getUserPermissions();
        $grouped = [];
        
        foreach ($permisos as $permiso) {
            $modulo = $permiso['modulo'];
            if (!isset($grouped[$modulo])) {
                $grouped[$modulo] = [];
            }
            $grouped[$modulo][] = $permiso;
        }
        
        return $grouped;
    }

    /**
     * Verificar múltiples permisos (OR)
     */
    public function checkAnyPermission(array $permisos): bool
    {
        foreach ($permisos as $permiso) {
            if ($this->checkPermission($permiso)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Verificar múltiples permisos (AND)
     */
    public function checkAllPermissions(array $permisos): bool
    {
        foreach ($permisos as $permiso) {
            if (!$this->checkPermission($permiso)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Middleware para rutas que requieren roles específicos
     */
    public function requireRole(string $rol): void
    {
        // Primero verificar autenticación
        $this->authMiddleware->requireAuth();
        
        if (!$this->checkRole($rol)) {
            $this->sendForbiddenResponse("No tienes el rol necesario para acceder a este recurso");
            exit;
        }
    }

    /**
     * Middleware para rutas que requieren alguno de los roles especificados
     */
    public function requireAnyRole(array $roles): void
    {
        // Primero verificar autenticación
        $this->authMiddleware->requireAuth();
        
        if (!$this->checkAnyRole($roles)) {
            $rolesStr = implode(', ', $roles);
            $this->sendForbiddenResponse("Necesitas uno de estos roles para acceder: {$rolesStr}");
            exit;
        }
    }

    /**
     * Middleware para rutas que requieren permisos específicos
     */
    public function requirePermission(string $permiso): void
    {
        // Primero verificar autenticación
        $this->authMiddleware->requireAuth();
        
        if (!$this->checkPermission($permiso)) {
            $this->sendForbiddenResponse("No tienes permisos para realizar esta acción");
            exit;
        }
    }

    /**
     * Middleware para rutas que requieren alguno de los permisos especificados
     */
    public function requireAnyPermission(array $permisos): void
    {
        // Primero verificar autenticación
        $this->authMiddleware->requireAuth();
        
        if (!$this->checkAnyPermission($permisos)) {
            $this->sendForbiddenResponse("No tienes los permisos necesarios para realizar esta acción");
            exit;
        }
    }

    /**
     * Middleware para rutas que requieren todos los permisos especificados
     */
    public function requireAllPermissions(array $permisos): void
    {
        // Primero verificar autenticación
        $this->authMiddleware->requireAuth();
        
        if (!$this->checkAllPermissions($permisos)) {
            $this->sendForbiddenResponse("No tienes todos los permisos necesarios para realizar esta acción");
            exit;
        }
    }

    /**
     * Middleware para rutas que requieren permiso por módulo y acción
     */
    public function requireModulePermission(string $modulo, string $accion): void
    {
        // Primero verificar autenticación
        $this->authMiddleware->requireAuth();

        if (!$this->checkModulePermission($modulo, $accion)) {
            $this->sendForbiddenResponse("No tienes permisos para el módulo '{$modulo}' y acción '{$accion}'");
            exit;
        }
    }

    /**
     * Verificar si el usuario es administrador
     */
    public function isAdmin(): bool
    {
        return $this->checkRole('Administrador') || $this->checkRole('Admin');
    }

    /**
     * Middleware para rutas que requieren permisos de administrador
     */
    public function requireAdmin(): void
    {
        // Primero verificar autenticación
        $this->authMiddleware->requireAuth();
        
        if (!$this->isAdmin()) {
            $this->sendForbiddenResponse("Se requieren permisos de administrador");
            exit;
        }
    }

    /**
     * Obtener permisos del usuario actual
     */
    public function getCurrentUserPermissions(): array
    {
        $usuario = $this->authMiddleware->getCurrentUser();
        
        if (!$usuario) {
            return [];
        }

        $stmt = $this->db->prepare("
            SELECT p.nombre, p.descripcion, p.modulo, p.accion
            FROM rol_permisos rp
            INNER JOIN permisos p ON rp.permiso_id = p.id
            WHERE rp.rol_id = :rol_id
            ORDER BY p.modulo, p.nombre
        ");
        
        $stmt->execute([':rol_id' => $usuario['rol_id']]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Obtener permisos del usuario por rol específico
     */
    public function getUserPermissionsByRolId(int $rolId): array
    {
        $stmt = $this->db->prepare("
            SELECT p.nombre, p.descripcion, p.modulo, p.accion
            FROM rol_permisos rp
            INNER JOIN permisos p ON rp.permiso_id = p.id
            WHERE rp.rol_id = :rol_id 
            AND p.estado_id = 1
            ORDER BY p.modulo, p.nombre
        ");
        
        $stmt->execute([':rol_id' => $rolId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Verificar si el usuario puede acceder a un módulo específico
     */
    public function canAccessModule(string $modulo): bool
    {
        $usuario = $this->authMiddleware->getCurrentUser();
        
        if (!$usuario) {
            return false;
        }

        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count
            FROM rol_permisos rp
            INNER JOIN permisos p ON rp.permiso_id = p.id
            WHERE rp.rol_id = :rol_id 
            AND p.modulo = :modulo
        ");
        
        $stmt->execute([
            ':rol_id' => $usuario['rol_id'],
            ':modulo' => $modulo
        ]);
        
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }

    /**
     * Middleware para verificar acceso a módulos
     */
    public function requireModuleAccess(string $modulo): void
    {
        // Primero verificar autenticación
        $this->authMiddleware->requireAuth();
        
        if (!$this->canAccessModule($modulo)) {
            $this->sendForbiddenResponse("No tienes acceso al módulo: {$modulo}");
            exit;
        }
    }

    private function sendForbiddenResponse(string $message): void
    {
        http_response_code(403);
        header('Content-Type: application/json; charset=utf-8');
        
        echo json_encode([
            "success" => false,
            "message" => $message,
            "error_code" => "FORBIDDEN",
            "timestamp" => date('c')
        ], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Método para enviar respuestas JSON estandarizadas
     */
    public function sendJsonResponse(int $statusCode, string $message, mixed $data = null, ?string $errorCode = null): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        
        $response = [
            "success" => $statusCode < 400,
            "message" => $message,
            "timestamp" => date('Y-m-d H:i:s')
        ];

        if ($data !== null) {
            $response["data"] = $data;
        }

        if ($errorCode) {
            $response["error_code"] = $errorCode;
        }
        
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
    }
}
