<?php
namespace App\Middleware;

use App\Config\Config;
use App\Models\UsuarioModel;
use App\Config\ConexionDb;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;

/**
 * AuthMiddleware - Maneja únicamente la autenticación JWT
 * La autorización basada en roles se maneja en RolMiddleware
 */
class AuthMiddleware
{
    private $db;
    private $usuarioModel;

    public function __construct()
    {
        $this->db = ConexionDb::getConnection();
        $this->usuarioModel = new UsuarioModel($this->db);
    }

    /**
     * Maneja la verificación del token JWT.
     * Si el token es válido, permite que la petición continúe.
     * Si no, lanza una excepción para detener la ejecución.
     * 
     * @param mixed $request Opcional - Objeto de solicitud
     * @param mixed $response Opcional - Objeto de respuesta
     * @param mixed $next Opcional - Función siguiente en la cadena de middleware
     * @return mixed
     */
    public function handle($request = null, $response = null, $next = null): mixed
    {
        $token = $this->getBearerToken();
        if (!$token) {
            if ($response) {
                return $response->withJson(['error' => 'Token de autenticación faltante'], 401);
            }
            throw new Exception("Token de autenticación faltante.", 401);
        }
        
        try {
            $jwtConfig = Config::getJwtConfig();
            $decoded = JWT::decode($token, new Key($jwtConfig['secret'], $jwtConfig['algorithm']));
            
            // Verificar que el usuario aún existe y está activo
            $usuario = $this->getUserById($decoded->data->id);
            
            if (!$usuario || $usuario['estado_id'] != 1) {
                if ($response) {
                    return $response->withJson(['error' => 'Usuario no válido o inactivo'], 401);
                }
                throw new Exception("Usuario no válido o inactivo.", 401);
            }
            
            // Si hay un request, añadimos la información del usuario decodificada
            if ($request) {
                $request = $request->withAttribute('user', $decoded);
                $request = $request->withAttribute('user_data', $usuario);
                
                // Si hay un next, continuamos la cadena de middleware
                if ($next) {
                    return $next($request, $response);
                }
            }
            
            // Si no hay request/response, simplemente retornamos true
            return true;

        } catch (ExpiredException $e) {
            if ($response) {
                return $response->withJson(['error' => 'Token expirado'], 401);
            }
            throw new Exception("Token expirado.", 401);
        } catch (SignatureInvalidException $e) {
            if ($response) {
                return $response->withJson(['error' => 'Token inválido'], 401);
            }
            throw new Exception("Token inválido.", 401);
        } catch (Exception $e) {
            if ($response) {
                return $response->withJson(['error' => 'Error de autenticación: ' . $e->getMessage()], 401);
            }
            throw new Exception("Error de autenticación: " . $e->getMessage(), 401);
        }
    }

    /**
     * Verificar autenticación básica
     */
    public function authenticate(): ?array
    {
        try {
            $token = $this->getBearerToken();
            
            if (!$token) {
                $this->sendUnauthorizedResponse("Token no proporcionado");
                return null;
            }

            $jwtConfig = Config::getJwtConfig();
            $decoded = JWT::decode($token, new Key($jwtConfig['secret'], $jwtConfig['algorithm']));
            
            // Verificar que el usuario aún existe y está activo
            $usuario = $this->getUserById($decoded->data->id);
            
            if (!$usuario || $usuario['estado_id'] != 1) {
                $this->sendUnauthorizedResponse("Usuario no válido o inactivo");
                return null;
            }

            return $usuario;

        } catch (Exception $e) {
            $this->sendUnauthorizedResponse("Token inválido o expirado");
            return null;
        }
    }

    /**
     * Middleware para rutas que requieren autenticación
     */
    public function requireAuth(): void
    {
        $usuario = $this->authenticate();
        if (!$usuario) {
            exit; // La respuesta ya fue enviada en authenticate()
        }
    }

    /**
     * Obtener información del usuario actual
     */
    public function getCurrentUser(): ?array
    {
        return $this->authenticate();
    }

    /**
     * Extrae el token Bearer del header Authorization
     */
    public function getBearerToken(): ?string
    {
        // Función auxiliar para obtener headers cuando getallheaders() no está disponible
        $headers = function_exists('getallheaders') ? getallheaders() : [];
        
        // Si getallheaders() no está disponible, construir headers manualmente
        if (empty($headers)) {
            foreach ($_SERVER as $key => $value) {
                if (strpos($key, 'HTTP_') === 0) {
                    $header = str_replace('_', '-', substr($key, 5));
                    $headers[$header] = $value;
                }
            }
        }
        
        // Buscar en diferentes formatos posibles
        $authHeader = $headers['Authorization'] ?? 
                     $headers['authorization'] ?? 
                     $_SERVER['HTTP_AUTHORIZATION'] ?? 
                     null;

        if ($authHeader && preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return $matches[1];
        }

        return null;
    }
    public function checkPermission(string $permission): bool
    {
        $usuario = $this->getCurrentUser();
        if (!$usuario) {
            return false;
        }
        return in_array($permission, $usuario['permisos']);
    }

    public function getCurrentUserPermissions(): ?array
    {
        $usuario = $this->getCurrentUser();
        if (!$usuario) {
            return null;
        }
        return $usuario['permisos'] ?? [];
    }

    /**
     * Método público para uso interno en controladores
     */
    public function getBearerTokenForInternalUse(): ?string
    {
        return $this->getBearerToken();
    }

    /**
     * Obtener información del usuario por ID (método público)
     */
    public function getUserById(int $userId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT u.*, r.nombre AS rol
            FROM usuarios u
            LEFT JOIN roles r ON u.rol_id = r.id
            WHERE u.id = :id
        ");
        $stmt->execute([":id" => $userId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Validar token JWT y obtener datos del usuario
     */
    public function validateToken(string $token): ?array
    {
        try {
            $jwtConfig = Config::getJwtConfig();
            $decoded = JWT::decode($token, new Key($jwtConfig['secret'], $jwtConfig['algorithm']));
            
            // Verificar que el usuario aún existe y está activo
            $usuario = $this->getUserById($decoded->data->id);
            
            if (!$usuario || $usuario['estado_id'] != 1) {
                return null;
            }

            return $usuario;

        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Obtener datos del usuario desde el token JWT
     */
    public function getUserFromToken(): ?array
    {
        $token = $this->getBearerToken();
        if (!$token) {
            return null;
        }

        return $this->validateToken($token);
    }

    private function sendUnauthorizedResponse(string $message): void
    {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        
        echo json_encode([
            "success" => false,
            "message" => $message,
            "error_code" => "UNAUTHORIZED",
            "timestamp" => date('c')
        ], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Método para enviar respuestas JSON estandarizadas
     */
    public function sendJsonResponse(int $statusCode, string $message, $data = null, string $errorCode = null): void
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