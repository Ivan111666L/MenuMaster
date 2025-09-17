<?php
// Ubicación: App/Config/Config.php

// CORRECCIÓN: Se ajusta el namespace a la ubicación del archivo.
namespace app\config;

class Config 
{
    /**
     * Devuelve la configuración para la generación de tokens JWT.
     * Lee la clave secreta desde las variables de entorno para mayor seguridad.
     * @return array
     */
    public static function getJwtConfig(): array
    {
        return [
            // La clave secreta se lee desde tu archivo .env
            'secret' => $_ENV['JWT_SECRET_KEY'] ?? 'una_clave_por_defecto_muy_insegura',
            
            // Tiempo de expiración del token en segundos (ej. 1 hora = 3600 segundos)
            'expiration_time' => 3600, 
            
            // Algoritmo de encriptación
            'algorithm' => 'HS256'
        ];
    }

    // CORRECCIÓN: Se eliminaron los métodos getDbConfig(), decodenTokenData() y getJwtToken().
    // La lógica de esos métodos era incorrecta o pertenecía a otras clases (Database y AuthController).
}