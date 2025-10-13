<?php
namespace App\Controllers;

use Exception;
use App\Models\ConfiguracionModel;
use PDO;

class ConfiguracionController
{
    private PDO $db;
    private ConfiguracionModel $model;

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->model = new ConfiguracionModel($db);
    }

    public function getConfiguraciones()
    {
        try {
            $data = $this->model->getConfiguraciones();
            return $this->respondJson(true, $data);
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }

    public function saveConfiguraciones()
    {
        try {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            if ($data === null) {
                throw new Exception('JSON inválido en la solicitud');
            }

            $saved = $this->model->saveConfiguraciones($data);
            return $this->respondJson(true, $saved);
        } catch (Exception $e) {
            return $this->respondError($e->getMessage());
        }
    }

    private function respondJson($success, $data)
    {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => $success,
            'data' => $data
        ]);
    }

    private function respondError($message)
    {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => $message
        ]);
    }
}
?>