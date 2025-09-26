<?php
namespace App\Controllers;

use PDO;
use Exception;

/**
 * Clase base para todos los controladores
 * Proporciona métodos comunes para respuestas JSON y manejo de errores
 */
class Controller
{
    protected $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Envía una respuesta JSON con headers de seguridad
     */
    protected function jsonResponse(array $data, int $statusCode = 200): void
    {
        // Headers de seguridad
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('X-XSS-Protection: 1; mode=block');
        
        http_response_code($statusCode);
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * Envía una respuesta JSON estructurada
     */
    protected function sendResponse(
        int $statusCode,
        ?string $message = null,
        ?string $token = null,
        ?array $data = null
    ): void {
        $response = [
            "success" => $statusCode >= 200 && $statusCode < 300,
            "timestamp" => date('c')
        ];

        if ($message !== null) {
            $response["message"] = $message;
        }

        if ($token !== null) {
            $response["token"] = $token;
        }

        if ($data !== null) {
            $response["data"] = $data;
        }

        $this->jsonResponse($response, $statusCode);
    }

    /**
     * Valida que los datos de entrada contengan los campos requeridos
     */
    protected function validateRequiredFields(array $data, array $requiredFields): void
    {
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                throw new Exception("El campo '{$field}' es obligatorio.", 400);
            }
        }
    }

    /**
     * Obtiene los datos JSON de la entrada
     */
    protected function getJsonInput(): array
    {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Datos JSON inválidos", 400);
        }
        
        return $data ?? [];
    }

    /**
     * Valida que el Content-Type sea application/json
     */
    protected function isValidContentType(): bool
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        return strpos($contentType, 'application/json') !== false;
    }

    /**
     * Maneja errores de forma consistente
     */
    protected function handleError(Exception $e, string $defaultMessage = "Error interno del servidor"): void
    {
        error_log("Error en controlador: " . $e->getMessage());
        
        $statusCode = (int)$e->getCode();
        if ($statusCode < 400 || $statusCode >= 600) {
            $statusCode = 500;
        }
        
        $message = $statusCode === 500 ? $defaultMessage : $e->getMessage();
        $this->sendResponse($statusCode, $message);
    }
}