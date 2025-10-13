<?php
namespace App\Models;

use PDO;
use Exception;

class ConfiguracionModel
{
    private PDO $db;
    private string $table = 'configuraciones';

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->ensureTable();
    }

    private function ensureTable(): void
    {
        // Crea la tabla si no existe; almacena configuración como JSON (TEXT por compatibilidad)
        $sql = "CREATE TABLE IF NOT EXISTS {$this->table} (
            id INT PRIMARY KEY,
            data TEXT NOT NULL,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        $this->db->exec($sql);
    }

    public function getConfiguraciones(): array
    {
        $stmt = $this->db->prepare("SELECT data FROM {$this->table} WHERE id = 1 LIMIT 1");
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row || empty($row['data'])) {
            return [
                'sistema' => [
                    'horizonte_pronostico_default' => 7
                ]
            ];
        }

        $data = json_decode($row['data'], true);
        if ($data === null) {
            // Si el contenido es inválido, devolver seguro
            return [
                'sistema' => [
                    'horizonte_pronostico_default' => 7
                ]
            ];
        }
        return $data;
    }

    public function saveConfiguraciones(array $data): array
    {
        // Validar estructura mínima
        if (!isset($data['sistema'])) {
            $data['sistema'] = [];
        }
        if (!isset($data['sistema']['horizonte_pronostico_default'])) {
            $data['sistema']['horizonte_pronostico_default'] = 7;
        }

        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            throw new Exception('Error al codificar datos de configuración');
        }

        // Upsert id=1
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("INSERT INTO {$this->table} (id, data) VALUES (1, :data)
                ON DUPLICATE KEY UPDATE data = VALUES(data)");
            $stmt->execute([':data' => $json]);
            $this->db->commit();
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }

        return $data;
    }
}
?>