<?php
// routes/categorias_api.php

require_once BASE_PATH . '/App/Controllers/CategoriaController.php';
require_once BASE_PATH . '/App/Middleware/AuthMiddleware.php';

use App\Controllers\CategoriaController;
use App\Middleware\AuthMiddleware;

try {
    // 1. Se instancian las clases necesarias
    $controller = new CategoriaController($db);
    $authMiddleware = new AuthMiddleware();

    // 2. Se protege la ruta: solo usuarios autenticados pueden ver las categorías
    $authMiddleware->handle();

    // 3. Se maneja la petición: solo se permite el método GET
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $controller->index();
    } else {
        throw new Exception("Método no permitido.", 405);
    }

} catch (Exception $e) {
    // 4. Se maneja cualquier error de forma centralizada
    $code = $e->getCode() ?: 400;
    http_response_code($code);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}