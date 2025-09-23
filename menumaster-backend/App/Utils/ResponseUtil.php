<?php

namespace App\Utils;

class ResponseUtil
{
    /**
     * Creates a success response with data
     */
    public static function success($data = null, string $message = 'Success', int $code = 200): array
    {
        return [
            'success' => true,
            'message' => $message,
            'data' => $data,
            'code' => $code
        ];
    }

    /**
     * Creates an error response
     */
    public static function error(string $message = 'Error', int $code = 400, $errors = null): array 
    {
        return [
            'success' => false,
            'message' => $message,
            'errors' => $errors,
            'code' => $code
        ];
    }

    /**
     * Creates a validation error response
     */
    public static function validationError($errors, string $message = 'Validation Error'): array
    {
        return self::error($message, 422, $errors);
    }

    /**
     * Creates an unauthorized error response
     */
    public static function unauthorized(string $message = 'Unauthorized'): array
    {
        return self::error($message, 401);
    }

    /**
     * Creates a not found error response
     */
    public static function notFound(string $message = 'Resource not found'): array
    {
        return self::error($message, 404);
    }

    /**
     * Creates a forbidden error response
     */
    public static function forbidden(string $message = 'Forbidden'): array
    {
        return self::error($message, 403);
    }
    public static function badRequest(string $message = 'Bad Request'): array
    {
        return self::error($message, 400);
    }
    public static function created(string $message = 'Created'): array
    {
        $data = is_array($message) ? $message : null;
        $message = is_array($message) ? 'Created' : $message;
        return self::success($data, $message, 201);
    }
        
}
