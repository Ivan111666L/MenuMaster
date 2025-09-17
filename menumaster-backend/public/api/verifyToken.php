<?php
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../../app/config/Config.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use app\config\Config;

header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

function send($success, $message, $token = null, $usuario = null) {
    echo json_encode(["success" => $success, "message" => $message, "token" => $token, "usuario" => $usuario]);
    exit;
}

$headers = getallheaders();
$auth = $headers['Authorization'] ?? $headers['authorization'] ?? null;
if (!$auth) send(false, "Token no provisto.");

if (!preg_match('/Bearer\s(\S+)/', $auth, $matches)) {
    send(false, "Formato de token inválido.");
}

$jwt = $matches[1];

try {
    $jwtConfig = Config::getJwtConfig();
    $decoded = JWT::decode($jwt, new Key($jwtConfig['secret'], $jwtConfig['algorithm']));
    $data = json_decode(json_encode($decoded), true);
    $usuario = $data['data'] ?? $data;
    send(true, "Token válido", $jwt, $usuario);
} catch (Exception $e) {
    send(false, "Token inválido: " . $e->getMessage());
}