<?php

namespace App\Models;

use PDO;
use PDOException;
            

class LoginAttemptsModel extends Model
{
    protected $table = 'login_attempts';
    protected $primaryKey = 'id';

    public function __construct($db)
    {
        parent::__construct($db);
    }

    /**
     * Registrar un intento de login fallido
     */
    public function registrarIntentoFallido($email, $ipAddress, $userAgent)
    {
        try {
            // Verificar si ya existe un registro para este email
            $sql = "SELECT id, failed_attempts FROM {$this->table} WHERE email = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$email]);
            $existingRecord = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existingRecord) {
                // Actualizar el registro existente
                $sql = "UPDATE {$this->table} 
                    SET failed_attempts = failed_attempts + 1, 
                        last_failed_attempt = CURRENT_TIMESTAMP,
                        ip_address = ?,
                        user_agent = ?
                    WHERE email = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$ipAddress, $userAgent, $email]);
            } else {
                // Crear nuevo registro
                $sql = "INSERT INTO {$this->table} (email, failed_attempts, last_failed_attempt, ip_address, user_agent) 
                        VALUES (?, 1, CURRENT_TIMESTAMP, ?, ?)";
                $stmt = $this->db->prepare($sql);
                return $stmt->execute([$email, $ipAddress, $userAgent]);
            }
        } catch (PDOException $e) {
            error_log("Error al registrar intento fallido: " . $e->getMessage());
            return false;
        }
    }
    

    /**
     * Limpiar intentos fallidos después de un login exitoso
     */
    public function limpiarIntentos($email)
    {
        try {
            $sql = "DELETE FROM {$this->table} WHERE email = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$email]);
        } catch (PDOException $e) {
            error_log("Error al limpiar intentos: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verificar si una cuenta está bloqueada
     */
    public function estaBloqueda($email, $maxIntentos = 5, $tiempoBloqueoMinutos = 15)
    {
        try {
            $sql = "SELECT failed_attempts, last_failed_attempt 
                    FROM {$this->table} 
                    WHERE email = ? AND failed_attempts >= ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$email, $maxIntentos]);
            $record = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$record) {
                return false;
            }

            // Verificar si el tiempo de bloqueo ha expirado
            $tiempoUltimoIntento = strtotime($record['last_failed_attempt']);
            $tiempoActual = time();
            $tiempoTranscurrido = ($tiempoActual - $tiempoUltimoIntento) / 60; // en minutos

            if ($tiempoTranscurrido >= $tiempoBloqueoMinutos) {
                // El bloqueo ha expirado, limpiar intentos
                $this->limpiarIntentos($email);
                return false;
            }

            return true;
        } catch (PDOException $e) {
            error_log("Error al verificar bloqueo: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener información de intentos de login
     */
    public function getInformacionIntentos($email)
    {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE email = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$email]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener información de intentos: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener tiempo restante de bloqueo en minutos
     */
    public function getTiempoRestanteBloqueo($email, $tiempoBloqueoMinutos = 15)
    {
        try {
            $info = $this->getInformacionIntentos($email);
            
            if (!$info) {
                return 0;
            }

            $tiempoUltimoIntento = strtotime($info['last_failed_attempt']);
            $tiempoActual = time();
            $tiempoTranscurrido = ($tiempoActual - $tiempoUltimoIntento) / 60; // en minutos

            $tiempoRestante = $tiempoBloqueoMinutos - $tiempoTranscurrido;
            
            return max(0, ceil($tiempoRestante));
        } catch (PDOException $e) {
            error_log("Error al calcular tiempo restante: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Limpiar intentos antiguos (para mantenimiento)
     */
    public function limpiarIntentosAntiguos($diasAntiguedad = 30)
    {
        try {
            $sql = "DELETE FROM {$this->table} 
                    WHERE last_failed_attempt < DATE_SUB(NOW(), INTERVAL ? DAY)";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$diasAntiguedad]);
        } catch (PDOException $e) {
            error_log("Error al limpiar intentos antiguos: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener estadísticas de intentos de login
     */
    public function getEstadisticasIntentos($fechaInicio = null, $fechaFin = null)
    {
        try {
            $sql = "SELECT 
                        COUNT(*) as total_intentos,
                        COUNT(DISTINCT email) as emails_unicos,
                        AVG(failed_attempts) as promedio_intentos,
                        MAX(failed_attempts) as max_intentos
                    FROM {$this->table}
                    WHERE 1=1";

            $params = [];

            if ($fechaInicio) {
                $sql .= " AND DATE(last_failed_attempt) >= ?";
                $params[] = $fechaInicio;
            }

            if ($fechaFin) {
                $sql .= " AND DATE(last_failed_attempt) <= ?";
                $params[] = $fechaFin;
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener estadísticas: " . $e->getMessage());
            return [];
        }
    }
    
    public function estaBloqueado($email, $maxIntentos = 5, $tiempoBloqueoMinutos = 15)
    {
        return $this->verificarBloqueo($email, $maxIntentos, $tiempoBloqueoMinutos);
    }
    public function verificarBloqueo($email, $maxIntentos = 5, $tiempoBloqueoMinutos = 15)
    {
        try {
            $sql = "SELECT failed_attempts, last_failed_attempt 
                    FROM {$this->table} 
                    WHERE email = ? 
                    ORDER BY last_failed_attempt DESC 
                    LIMIT 1";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$email]);
            $record = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$record) {
                return false;
            }
            
            $intentosFallidos = $record['failed_attempts'];
            $ultimoIntento = strtotime($record['last_failed_attempt']);
            $tiempoActual = time();
            $tiempoTranscurrido = ($tiempoActual - $ultimoIntento) / 60; // en minutos
            
            if ($intentosFallidos >= $maxIntentos && $tiempoTranscurrido <= $tiempoBloqueoMinutos) {
                return true;
            }
            
            return false;
        } catch (PDOException $e) {
            error_log("Error al verificar bloqueo: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener IPs con más intentos fallidos
     */
    public function getTopIPsConIntentos($limit = 10)
    {
        try {
            // Nota: Esta funcionalidad requeriría almacenar la IP en la tabla
            // Por ahora retornamos los emails con más intentos
            $sql = "SELECT email, failed_attempts, last_failed_attempt
                    FROM {$this->table}
                    ORDER BY failed_attempts DESC
                    LIMIT ?";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener top IPs: " . $e->getMessage());
            return [];
        }
    }
}
