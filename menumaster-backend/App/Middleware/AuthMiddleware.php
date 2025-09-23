<?php
namespace App\Middleware;

use App\Config\Config;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;

class AuthMiddleware
{
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
            
            // Si hay un request, añadimos la información del usuario decodificada
            if ($request) {
                $request = $request->withAttribute('user', $decoded);
                
                // Si hay un next, continuamos la cadena de middleware
                if ($next) {
                    return $next($request, $response);
                }
            }
            
            // Si no hay request/response, simplemente retornamos true
            return true;

        } catch (ExpiredException $e) {
            if ($response) {
                return $response->withJson(['error' => 'El token ha expirado'], 401);
            }
            throw new Exception("El token ha expirado.", 401);

        } catch (SignatureInvalidException $e) {
            if ($response) {
                return $response->withJson(['error' => 'La firma del token no es válida'], 401);
            }
            throw new Exception("La firma del token no es válida.", 401);
            
        } catch (Exception $e) {
            if ($response) {
                return $response->withJson(['error' => 'Token inválido'], 401);
            }
            throw new Exception("Token inválido.", 401);
        }
    }

    /**
     * Verifica si el usuario tiene los roles requeridos
     * 
     * @param array $roles Roles permitidos
     * @param mixed $request Objeto de solicitud
     * @param mixed $response Objeto de respuesta
     * @param mixed $next Función siguiente en la cadena de middleware
     * @return mixed
     */
    public function checkRole($roles, $request, $response, $next): mixed
    {
        // Primero verificamos la autenticación
        $result = $this->handle($request, $response, null);
        
        // Si el resultado no es true, significa que hubo un error de autenticación
        if ($result !== true) {
            return $result;
        }
        
        // Obtenemos el usuario del request
        $user = $request->getAttribute('user');
        
        // Verificamos si el usuario tiene el rol requerido
        if (!isset($user->role) || !in_array($user->role, $roles)) {
            return $response->withJson(['error' => 'No tienes permisos para acceder a esta ruta'], 403);
        }
        
        // Si tiene el rol, continuamos
        return $next($request, $response);
    }

    /**
     * Extrae el token del encabezado 'Authorization' de forma segura.
     * @return string|null
     */
    private function getBearerToken(): ?string
    {
        // Se usa $_SERVER para máxima compatibilidad con diferentes servidores (Apache, Nginx).
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? null;

        if (!empty($authHeader) && preg_match('/Bearer\s(\S+)/i', $authHeader, $matches)) {
            return $matches[1];
        }
        return null;
    }
    
    /**
     * Método público para ser usado internamente por otros scripts (ej. enrutadores).
     */
    public function getBearerTokenForInternalUse(): ?string
    {
        return $this->getBearerToken();
    }
}