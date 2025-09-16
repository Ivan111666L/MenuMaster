<?php
namespace App\Middleware;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Config;
use Exception;
// IMPORTANTE: Importar las excepciones específicas de la librería JWT
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;

class AuthMiddleware
{
    /**
     * CORRECCIÓN: El middleware no debe ser estático. Se crea una instancia por cada petición.
     * Su trabajo es "vigilar" la ruta. Si la autenticación es correcta, no hace nada
     * y permite el paso. Si falla, lanza una excepción y detiene la ejecución.
     * Por eso, el tipo de retorno es 'void'.
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
            
            // Si la decodificación es exitosa, la función termina y el script continúa hacia el controlador.
            // No es necesario retornar los datos del usuario aquí. El controlador puede decodificar el token de nuevo
            // si necesita los datos, ya que en este punto se ha verificado que es seguro hacerlo.

        } catch (ExpiredException $e) {
            // CORRECCIÓN: Capturar errores específicos para dar mensajes más claros.
            throw new Exception("El token ha expirado.", 401);

        } catch (SignatureInvalidException $e) {
            throw new Exception("La firma del token no es válida.", 401);
            
        } catch (Exception $e) {
            // Captura cualquier otro error de la librería JWT o de la configuración.
            throw new Exception("Token inválido.", 401);
        }
    }

    /**
     * CORRECCIÓN: Se usa $_SERVER en lugar de getallheaders() para mayor portabilidad.
     * La función getallheaders() no siempre está disponible (ej. en servidores Nginx).
     * @return string|null
     */
    private function getBearerToken(): ?string
    {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? null;

        if (!empty($authHeader) && preg_match('/Bearer\s(\S+)/i', $authHeader, $matches)) {
            // El modificador /i hace que 'Bearer' no distinga mayúsculas/minúsculas.
            return $matches[1];
        }
        return null;
    }
    /**
     * Método público para obtener el token desde otros scripts.
     * Reutiliza la lógica privada que ya teníamos.
     */
    public function getBearerTokenForInternalUse(): ?string
    {
        // Llama al método privado que ya existe en esta misma clase
        return $this->getBearerToken();
    }
    

}