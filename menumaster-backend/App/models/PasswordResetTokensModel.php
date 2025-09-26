<?php

namespace App\Models;

use PDO;
use PDOException;

class PasswordResetTokensModel extends Model
{
    protected $table = 'password_reset_tokens';
    protected $primaryKey = 'id';

    public function __construct($db)
    {
        parent::__construct($db);
    }

    /**
     * Crear un token de restablecimiento de contraseña
     */
    public function crearToken($email, $token, $expiresAt = null)
    {
        try {
            // Si no se especifica tiempo de expiración, usar 1 hora por defecto
            if (!$expiresAt) {
                $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));
            }

            // Primero eliminar tokens existentes para este email
            $this->eliminarTokensPorEmail($email);

            $sql = "INSERT INTO {$this->table} (email, token, expires_at, created_at) 
                    VALUES (?, ?, ?, CURRENT_TIMESTAMP)";

            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$email, $token, $expiresAt]);
        } catch (PDOException $e) {
            error_log("Error al crear token de restablecimiento: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verificar si un token es válido
     */
    public function verificarToken($token)
    {
        try {
            $sql = "SELECT * FROM {$this->table} 
                    WHERE token = ? AND expires_at > CURRENT_TIMESTAMP AND used_at IS NULL";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$token]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al verificar token: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Marcar un token como usado
     */
    public function marcarTokenComoUsado($token)
    {
        try {
            $sql = "UPDATE {$this->table} 
                    SET used_at = CURRENT_TIMESTAMP 
                    WHERE token = ?";

            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$token]);
        } catch (PDOException $e) {
            error_log("Error al marcar token como usado: " . $e->getMessage());
            return false;
        }
    }
    public function puedeCrearToken($email)
    {
        return !$this->getTokenPorEmail($email);
    }

    public function marcarComoUsado($token)
    {
        return $this->marcarTokenComoUsado($token);
    }

    /**
     * Eliminar tokens por email
     */
    public function eliminarTokensPorEmail($email)
    {
        try {
            $sql = "DELETE FROM {$this->table} WHERE email = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$email]);
        } catch (PDOException $e) {
            error_log("Error al eliminar tokens por email: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener token por email (el más reciente)
     */
    public function getTokenPorEmail($email)
    {
        try {
            $sql = "SELECT * FROM {$this->table} 
                    WHERE email = ? AND expires_at > CURRENT_TIMESTAMP AND used_at IS NULL
                    ORDER BY created_at DESC 
                    LIMIT 1";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$email]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener token por email: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Limpiar tokens expirados
     */
    public function limpiarTokensExpirados()
    {
        try {
            $sql = "DELETE FROM {$this->table} 
                    WHERE expires_at <= CURRENT_TIMESTAMP OR used_at IS NOT NULL";

            $stmt = $this->db->prepare($sql);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al limpiar tokens expirados: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener estadísticas de tokens
     */
    public function getEstadisticasTokens($fechaInicio = null, $fechaFin = null)
    {
        try {
            $sql = "SELECT 
                        COUNT(*) as total_tokens,
                        COUNT(CASE WHEN used_at IS NOT NULL THEN 1 END) as tokens_usados,
                        COUNT(CASE WHEN expires_at <= CURRENT_TIMESTAMP AND used_at IS NULL THEN 1 END) as tokens_expirados,
                        COUNT(CASE WHEN expires_at > CURRENT_TIMESTAMP AND used_at IS NULL THEN 1 END) as tokens_activos
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

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener estadísticas de tokens: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener historial de tokens por email
     */
    public function getHistorialTokensPorEmail($email, $limit = 10)
    {
        try {
            $sql = "SELECT 
                        token,
                        created_at,
                        expires_at,
                        used_at,
                        CASE 
                            WHEN used_at IS NOT NULL THEN 'usado'
                            WHEN expires_at <= CURRENT_TIMESTAMP THEN 'expirado'
                            ELSE 'activo'
                        END as estado
                    FROM {$this->table}
                    WHERE email = ?
                    ORDER BY created_at DESC
                    LIMIT ?";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$email, $limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener historial de tokens: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Verificar si un email tiene demasiados intentos recientes
     */
    public function verificarLimiteIntentos($email, $limiteTiempo = 3600, $maxIntentos = 5)
    {
        try {
            $sql = "SELECT COUNT(*) as total_intentos
                    FROM {$this->table}
                    WHERE email = ? 
                    AND created_at >= DATE_SUB(CURRENT_TIMESTAMP, INTERVAL ? SECOND)";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$email, $limiteTiempo]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

            return $resultado['total_intentos'] >= $maxIntentos;
        } catch (PDOException $e) {
            error_log("Error al verificar límite de intentos: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Generar un token único
     */
    public function generarToken($longitud = 64)
    {
        return bin2hex(random_bytes($longitud / 2));
    }

    /**
     * Obtener tiempo restante de un token
     */
    public function getTiempoRestanteToken($token)
    {
        try {
            $sql = "SELECT 
                        expires_at,
                        TIMESTAMPDIFF(SECOND, CURRENT_TIMESTAMP, expires_at) as segundos_restantes
                    FROM {$this->table}
                    WHERE token = ? AND used_at IS NULL";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$token]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($resultado && $resultado['segundos_restantes'] > 0) {
                return $resultado['segundos_restantes'];
            }

            return 0;
        } catch (PDOException $e) {
            error_log("Error al obtener tiempo restante del token: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Obtener tokens que expiran pronto
     */
    public function getTokensProximosAExpirar($minutos = 10)
    {
        try {
            $sql = "SELECT 
                        email,
                        token,
                        expires_at,
                        TIMESTAMPDIFF(MINUTE, CURRENT_TIMESTAMP, expires_at) as minutos_restantes
                    FROM {$this->table}
                    WHERE used_at IS NULL 
                    AND expires_at > CURRENT_TIMESTAMP
                    AND expires_at <= DATE_ADD(CURRENT_TIMESTAMP, INTERVAL ? MINUTE)
                    ORDER BY expires_at ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$minutos]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener tokens próximos a expirar: " . $e->getMessage());
            return [];
        }
    }
}
