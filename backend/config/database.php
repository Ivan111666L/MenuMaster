<?php
// Habilitar el manejo de errores para depuración
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Definir las credenciales de la base de datos
define('DB_HOST', 'localhost'); // Servidor de la base de datos
define('DB_NAME', 'menu_master'); // Nombre de la base de datos
define('DB_USER', 'root'); // Usuario de MySQL (por defecto en XAMPP es 'root')
define('DB_PASS', ''); // Contraseña de MySQL (vacía por defecto en XAMPP)

// Clase para manejar la conexión a la base de datos
class Database {
    private $host = DB_HOST;
    private $db_name = DB_NAME;
    private $username = DB_USER;
    private $password = DB_PASS;
    public $conn;

    // Método para conectar a la base de datos
    public function getConnection() {
        $this->conn = null;
        try {
            // Se crea la conexión usando PDO
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            // Configuración para manejar caracteres especiales correctamente
            $this->conn->exec("set names utf8");
            // Configuración para lanzar excepciones en caso de error
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $exception) {
            // Capturar errores de conexión
            echo "Error de conexión: " . $exception->getMessage();
        }
        return $this->conn;
    }
}
?>