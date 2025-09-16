<?php
// config/config.php
namespace App;

class Config {
    
    public static function getDbConfig(): array
    {
        return [
            'host'   => $_ENV['DB_HOST'] ?? 'localhost',
            'dbname' => $_ENV['DB_NAME'] ?? 'menu_master',
            'user'   => $_ENV['DB_USER'] ?? 'root',
            'pass'   => $_ENV['DB_PASS'] ?? ''
        ];
    }

   public static function getJwtConfig(): array
    {
        return [
            'secret'    => $_ENV['JWT_SECRET_KEY'] ?? 'clave_por_defecto_solo_para_desarrollo',
            'algorithm' => 'HS256',
            'expiration_time' => 3600 // 1 hora en segundos
        ];
    }
}