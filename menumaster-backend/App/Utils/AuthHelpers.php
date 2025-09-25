<?php
namespace App\Utils;

use App\Middleware\RolMiddleware;

/**
 * AuthHelpers - Funciones auxiliares para autenticación y autorización
 */
class AuthHelpers
{
    /**
     * Verificar si el usuario actual es administrador
     */
    public static function requireAdmin(): void
    {
        $rolMiddleware = new RolMiddleware();
        $rolMiddleware->requireAdmin();
    }
}

// Función global para compatibilidad con el código existente
function requireAdmin(): void
{
    \App\Utils\AuthHelpers::requireAdmin();
}