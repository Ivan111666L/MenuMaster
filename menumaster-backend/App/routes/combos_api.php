<?php

use App\Controllers\ComboController;
use App\Middleware\AuthMiddleware;
// Middleware de autenticación y roles integrado en AuthMiddleware

// Instanciar el controlador y middleware
$comboController = new ComboController();
$authMiddleware = new AuthMiddleware();
$roles = ['administrador', 'gerente'];

// Rutas para combos
$app->get('/combos', function($request) use ($comboController, $authMiddleware) {
    $authMiddleware->handle($request, null, null);
    return $comboController->getCombos();
});

$app->get('/combos/:id', function($request) use ($comboController, $authMiddleware) {
    return $authMiddleware->handle($request, null, function($req, $res) use ($comboController, $request) {
        return $comboController->getCombo($request['params']['id']);
    });
});

$app->post('/combos', function($request) use ($comboController, $authMiddleware, $roles) {
    return $authMiddleware->checkRole($roles, $request, null, function($req, $res) use ($comboController) {
        return $comboController->createCombo();
    });
});

$app->put('/combos/:id', function($request) use ($comboController, $authMiddleware, $roles) {
    return $authMiddleware->checkRole($roles, $request, null, function($req, $res) use ($comboController, $request) {
        return $comboController->updateCombo($request['params']['id']);
    });
});

$app->delete('/combos/:id', function($request) use ($comboController, $authMiddleware, $roles) {
    return $authMiddleware->checkRole($roles, $request, null, function($req, $res) use ($comboController, $request) {
        return $comboController->deleteCombo($request['params']['id']);
    });
});

$app->patch('/combos/:id/status', function($request) use ($comboController, $authMiddleware, $roles) {
    return $authMiddleware->checkRole($roles, $request, null, function($req, $res) use ($comboController, $request) {
        return $comboController->changeComboStatus($request['params']['id']);
    });
});