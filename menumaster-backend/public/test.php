<?php
// Simple test file to check CORS headers
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(204);
    exit;
}

header("Content-Type: application/json");
echo json_encode(["message" => "CORS test successful", "method" => $_SERVER['REQUEST_METHOD']]);
?>