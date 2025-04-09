<?php

// Incluir la conexión a la base de datos
include_once '../config/database.php';

class Usuario {
    private $conn;
    private $table_name = "usuarios";

    // Propiedades del usuario
    public $id;
    public $nombre;
    public $email;
    public $password;
    public $rol;

    // Constructor que recibe la conexión a la base de datos
    public function __construct($db){
        $this->conn = $db;
    }

    // Función para el registro de los usuarios
    public function crear(){
        $query = "INSERT INTO " . $this->table_name . "(nombre, email, password, rol) VALUES (:nombre, :email, :password, :rol)";

        $stmt = $this->conn->prepare($query);

        // Limpiar los datos de la base de datos
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->password = htmlspecialchars(strip_tags($this->password, PASSWORD_BCRYPT));
        $this->rol = htmlspecialchars(strip_tags($this->rol));

        // Enlazar los valores 
        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password", $this->password);
        $stmt->bindParam(":rol", $this->rol);

        // Ejecutar la consulta
        if ($stmt->execute()){
            return true;
        }

        return false;
    }

    // Función para obtener todos los usuarios
    public function obtenerUsuarios(){
        $query = "SELECT id, nombre, email, password, rol FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}

?>