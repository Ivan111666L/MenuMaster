<?php
// config/constantes.php

/**
 * Este archivo centraliza las constantes de la aplicación que no son sensibles.
 * Se utilizan clases para agrupar constantes relacionadas, evitando el uso de define() global.
 * * ❌ NO DEFINAS AQUÍ:
 * - Credenciales de base de datos (DB_HOST, DB_USER, etc.)
 * - Claves secretas (JWT_SECRET)
 * ✅ ESA INFORMACIÓN PERTENECE A TU ARCHIVO .env Y SE ACCEDE A TRAVÉS DE Config.php
 */

namespace App;

/**
 * Define los IDs de los roles de usuario.
 * Uso: \App\Roles::ADMINISTRADOR
 */
class Roles
{
    const ADMINISTRADOR = 1;
    const MESERO        = 2;
    const COCINERO      = 3;
    const CAJERO        = 4;
}

/**
 * Define los IDs de los estados generales (activo/inactivo).
 * Uso: \App\EstadosGenerales::ACTIVO
 */
class EstadosGenerales
{
    const ACTIVO   = 1;
    const INACTIVO = 2;
}

/**
 * Define los IDs para los estados específicos de un pedido.
 * Uso: \App\EstadosPedido::PENDIENTE
 */
class EstadosPedido
{
    const PENDIENTE      = 1;
    const EN_PREPARACION = 2;
    const SERVIDO        = 3;
    const PAGADO         = 4;
    const CANCELADO      = 5;
}

/**
 * Define los IDs para los estados específicos de una mesa.
 * Uso: \App\EstadosMesa::DISPONIBLE
 */
class EstadosMesa
{
    const DISPONIBLE = 1;
    const OCUPADA    = 2;
    const RESERVADA  = 3;
}


// ❌ NO AÑADAS CÓDIGO EJECUTABLE AQUÍ.
// La línea '$rol_id_defecto = ROL_MESERO;' fue eliminada porque la asignación
// de variables debe ocurrir en la lógica de negocio (ej. en un controlador),
// no en un archivo de configuración/definición.