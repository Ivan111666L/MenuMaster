<?php
namespace app\Middleware;

use app\config\Config; // Asegúrate de que el namespace de Config sea correcto
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
     */
    public function handle(): void
    {
        $token = $this->getBearerToken();
        if (!$token) {
            throw new Exception("Token de autenticación faltante.", 401);
        }
        
        try {
            $jwtConfig = Config::getJwtConfig();
            JWT::decode($token, new Key($jwtConfig['secret'], $jwtConfig['algorithm']));
            
            // Si la decodificación es exitosa, la función termina y el script
            // continúa hacia el controlador sin problemas.

        } catch (ExpiredException $e) {
            // Captura el error específico cuando el token ha caducado.
            throw new Exception("El token ha expirado.", 401);

        } catch (SignatureInvalidException $e) {
            // Captura el error específico si la firma del token no es válida.
            throw new Exception("La firma del token no es válida.", 401);
            
        } catch (Exception $e) {
            // Captura cualquier otro error de la librería JWT.
            throw new Exception("Token inválido.", 401);
        }
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