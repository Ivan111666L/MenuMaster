<?php

namespace App\Models;

use PDO;
use PDOException;

class UserActivityLogModel extends Model
{
    protected $table = 'user_activity_log';
    protected $primaryKey = 'id';

    public function __construct($db)
    {
        parent::__construct($db);
    }

    /**
     * Registrar una actividad de usuario
     */
    public function registrarActividad($userId, $action, $status = 'success', $ipAddress = null, $userAgent = null)
    {
        try {
            $sql = "INSERT INTO {$this->table} (user_id, action, status, ip_address, user_agent, created_at) 
                    VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP)";

            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $userId,
                $action,
                $status,
                $ipAddress,
                $userAgent
            ]);
        } catch (PDOException $e) {
            error_log("Error al registrar actividad: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener actividades de un usuario específico
     */
    public function getActividadesPorUsuario($userId, $limit = 50, $offset = 0)
    {
        try {
            $sql = "SELECT ual.*, u.nombre as usuario_nombre
                    FROM {$this->table} ual
                    LEFT JOIN usuarios u ON ual.user_id = u.id
                    WHERE ual.user_id = ?
                    ORDER BY ual.created_at DESC
                    LIMIT ? OFFSET ?";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId, $limit, $offset]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener actividades por usuario: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener todas las actividades con filtros
     */
    public function getActividades($filtros = [])
    {
        try {
            $sql = "SELECT ual.*, u.nombre as usuario_nombre, u.email as usuario_email
                    FROM {$this->table} ual
                    LEFT JOIN usuarios u ON ual.user_id = u.id
                    WHERE 1=1";

            $params = [];

            if (!empty($filtros['user_id'])) {
                $sql .= " AND ual.user_id = ?";
                $params[] = $filtros['user_id'];
            }

            if (!empty($filtros['action'])) {
                $sql .= " AND ual.action = ?";
                $params[] = $filtros['action'];
            }

            if (!empty($filtros['status'])) {
                $sql .= " AND ual.status = ?";
                $params[] = $filtros['status'];
            }

            if (!empty($filtros['fecha_inicio'])) {
                $sql .= " AND DATE(ual.created_at) >= ?";
                $params[] = $filtros['fecha_inicio'];
            }

            if (!empty($filtros['fecha_fin'])) {
                $sql .= " AND DATE(ual.created_at) <= ?";
                $params[] = $filtros['fecha_fin'];
            }

            if (!empty($filtros['ip_address'])) {
                $sql .= " AND ual.ip_address = ?";
                $params[] = $filtros['ip_address'];
            }

            $sql .= " ORDER BY ual.created_at DESC";

            if (!empty($filtros['limit'])) {
                $sql .= " LIMIT " . intval($filtros['limit']);
                if (!empty($filtros['offset'])) {
                    $sql .= " OFFSET " . intval($filtros['offset']);
                }
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener actividades: " . $e->getMessage());
            return [];
        }
    }

    public function detectarActividadesSospechosas($fechaInicio, $fechaFin) {
        $filtros['status'] = 'failed';
        $filtros['fecha_inicio'] = $fechaInicio;
        $filtros['fecha_fin'] = $fechaFin;
        return $this->getActividades($filtros);
    }

    public function registrarLoginExitoso($userId, $ipAddress, $userAgent)
    {
        return $this->registrarActividad($userId, 'login_success', 'success', $ipAddress, $userAgent);
    }

    /**
     * Obtener estadísticas de actividad
     */
    public function getEstadisticasActividad($fechaInicio = null, $fechaFin = null)
    {
        try {
            $sql = "SELECT 
                        action,
                        status,
                        COUNT(*) as total,
                        COUNT(DISTINCT user_id) as usuarios_unicos
                    FROM {$this->table}
                    WHERE 1=1";

            $params = [];

            if ($fechaInicio) {
                $sql .= " AND DATE(created_at) >= ?";
                $params[] = $fechaInicio;
            }

            if ($fechaFin) {
                $sql .= " AND DATE(created_at) <= ?";
                $params[] = $fechaFin;
            }

            $sql .= " GROUP BY action, status ORDER BY total DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener estadísticas de actividad: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener usuarios más activos
     */
    public function getUsuariosMasActivos($limit = 10, $fechaInicio = null, $fechaFin = null)
    {
        try {
            $sql = "SELECT 
                        ual.user_id,
                        u.nombre,
                        u.email,
                        COUNT(*) as total_actividades,
                        COUNT(DISTINCT DATE(ual.created_at)) as dias_activos,
                        MAX(ual.created_at) as ultima_actividad
                    FROM {$this->table} ual
                    LEFT JOIN usuarios u ON ual.user_id = u.id
                    WHERE 1=1";

            $params = [];

            if ($fechaInicio) {
                $sql .= " AND DATE(ual.created_at) >= ?";
                $params[] = $fechaInicio;
            }

            if ($fechaFin) {
                $sql .= " AND DATE(ual.created_at) <= ?";
                $params[] = $fechaFin;
            }

            $sql .= " GROUP BY ual.user_id, u.nombre, u.email
                      ORDER BY total_actividades DESC
                      LIMIT ?";
            $params[] = $limit;

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener usuarios más activos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener actividades por día
     */
    public function getActividadesPorDia($fechaInicio = null, $fechaFin = null)
    {
        try {
            $sql = "SELECT 
                        DATE(created_at) as fecha,
                        COUNT(*) as total_actividades,
                        COUNT(DISTINCT user_id) as usuarios_activos,
                        COUNT(CASE WHEN status = 'success' THEN 1 END) as exitosas,
                        COUNT(CASE WHEN status = 'error' THEN 1 END) as fallidas
                    FROM {$this->table}
                    WHERE 1=1";

            $params = [];

            if ($fechaInicio) {
                $sql .= " AND DATE(created_at) >= ?";
                $params[] = $fechaInicio;
            }

            if ($fechaFin) {
                $sql .= " AND DATE(created_at) <= ?";
                $params[] = $fechaFin;
            }

            $sql .= " GROUP BY DATE(created_at) ORDER BY fecha DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener actividades por día: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Limpiar logs antiguos
     */
    public function limpiarLogsAntiguos($diasAntiguedad = 90)
    {
        try {
            $sql = "DELETE FROM {$this->table} 
                    WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)";

            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$diasAntiguedad]);
        } catch (PDOException $e) {
            error_log("Error al limpiar logs antiguos: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener actividades sospechosas
     */
    public function getActividadesSospechosas($limit = 50)
    {
        try {
            // Buscar patrones sospechosos como múltiples fallos desde la misma IP
            $sql = "SELECT 
                        ip_address,
                        COUNT(*) as total_intentos,
                        COUNT(CASE WHEN status = 'error' THEN 1 END) as intentos_fallidos,
                        COUNT(DISTINCT user_id) as usuarios_diferentes,
                        MIN(created_at) as primer_intento,
                        MAX(created_at) as ultimo_intento
                    FROM {$this->table}
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                    AND ip_address IS NOT NULL
                    GROUP BY ip_address
                    HAVING intentos_fallidos > 5 OR usuarios_diferentes > 3
                    ORDER BY intentos_fallidos DESC, usuarios_diferentes DESC
                    LIMIT ?";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener actividades sospechosas: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Registrar login exitoso
     */
    public function registrarLogin($userId, $ipAddress = null, $userAgent = null)
    {
        return $this->registrarActividad($userId, 'login', 'success', $ipAddress, $userAgent);
    }

    /**
     * Registrar logout
     */
    public function registrarLogout($userId, $ipAddress = null, $userAgent = null)
    {
        return $this->registrarActividad($userId, 'logout', 'success', $ipAddress, $userAgent);
    }

    /**
     * Registrar intento de login fallido
     */
    public function registrarLoginFallido($userId, $ipAddress = null, $userAgent = null)
    {
        return $this->registrarActividad($userId, 'login', 'error', $ipAddress, $userAgent);
    }
}
