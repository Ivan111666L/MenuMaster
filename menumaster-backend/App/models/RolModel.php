<?php
namespace app\Models;

use PDO;

class RolModel {
    private $db;
    private $table = 'roles';

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /**
     * Busca un rol por su nombre.
     * @param string $nombre El nombre del rol (ej. 'administrador')
     * @return array|false
     */
    public function findByName(string $nombre): array|false {
        $sql = "SELECT id, nombre FROM {$this->table} WHERE nombre = :nombre";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}