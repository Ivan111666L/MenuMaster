<?php
require_once '../config/database.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    // Decodificar JSON recibido
    $data = json_decode(file_get_contents("php://input"), true);

    // Validar datos requeridos
    if (!isset($data['nombre']) || !isset($data['email']) || !isset($data['password']) || !isset($data['rol'])) {
        echo json_encode(["error" => "Todos los campos son obligatorios."]);
        http_response_code(400);
        exit;
    }

    $nombre = $data['nombre'];
    $email = $data['email'];
    $password = password_hash($data['password'], PASSWORD_BCRYPT); // Encriptar contraseña
    $rol = $data['rol'];

    // Conectar a la BD
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if ($conn->connect_error) {
        echo json_encode(["error" => "Error en la conexión a la base de datos."]);
        http_response_code(500);
        exit;
    }

    // Verificar si el usuario ya existe
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo json_encode(["error" => "El usuario ya está registrado."]);
        http_response_code(409);
        exit;
    }
    $stmt->close();

    // Insertar usuario
    $stmt = $conn->prepare("INSERT INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $nombre, $email, $password, $rol);

    if ($stmt->execute()) {
        echo json_encode(["mensaje" => "Usuario creado correctamente."]);
        http_response_code(201);
    } else {
        echo json_encode(["error" => "Error al registrar el usuario."]);
        http_response_code(500);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["error" => "Metodo no permitido."]);
    http_response_code(405);
}
?>