<?php

namespace App\Controllers;

use App\Models\LoginAttemptsModel;
use App\Models\UserActivityLogModel;
use App\Models\PasswordResetTokensModel;
use App\Models\UsuarioModel;
use Exception;

class SecurityController extends Controller
{
    private $loginAttemptsModel;
    private $userActivityModel;
    private $passwordResetModel;
    private $usuarioModel;

    public function __construct($db)
    {
        parent::__construct($db);
        $this->loginAttemptsModel = new LoginAttemptsModel($db);
        $this->userActivityModel = new UserActivityLogModel($db);
        $this->passwordResetModel = new PasswordResetTokensModel($db);
        $this->usuarioModel = new UsuarioModel($db);
    }

    /**
     * Registrar intento de login fallido
     */
    public function registrarIntentoFallido()
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            $email = $data['email'] ?? null;
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;

            if (!$email) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Email es requerido'
                ], 400);
                return;
            }

            $resultado = $this->loginAttemptsModel->registrarIntentoFallido($email, $ipAddress, $userAgent);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Intento fallido registrado',
                'data' => $resultado
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error al registrar intento fallido: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verificar si una cuenta está bloqueada
     */
    public function verificarBloqueo($email)
    {
        try {
            $bloqueado = $this->loginAttemptsModel->estaBloqueado($email);
            $tiempoRestante = 0;

            if ($bloqueado) {
                $tiempoRestante = $this->loginAttemptsModel->getTiempoRestanteBloqueo($email);
            }

            $this->jsonResponse([
                'success' => true,
                'data' => [
                    'bloqueado' => $bloqueado,
                    'tiempo_restante_minutos' => $tiempoRestante
                ]
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error al verificar bloqueo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Limpiar intentos después de login exitoso
     */
    public function limpiarIntentos()
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $email = $data['email'] ?? null;

            if (!$email) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Email es requerido'
                ], 400);
                return;
            }

            $this->loginAttemptsModel->limpiarIntentos($email);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Intentos limpiados correctamente'
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error al limpiar intentos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener estadísticas de intentos de login
     */
    public function getEstadisticasLogin()
    {
        try {
            $fechaInicio = $_GET['fecha_inicio'] ?? null;
            $fechaFin = $_GET['fecha_fin'] ?? null;

            $estadisticas = $this->loginAttemptsModel->getEstadisticasIntentos($fechaInicio, $fechaFin);

            $this->jsonResponse([
                'success' => true,
                'data' => $estadisticas
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error al obtener estadísticas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Registrar actividad de usuario
     */
    public function registrarActividad()
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            $usuarioId = $data['usuario_id'] ?? null;
            $accion = $data['accion'] ?? null;
            $descripcion = $data['descripcion'] ?? null;
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;

            if (!$usuarioId || !$accion) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Usuario ID y acción son requeridos'
                ], 400);
                return;
            }

            $resultado = $this->userActivityModel->registrarActividad(
                $usuarioId, 
                $accion, 
                $descripcion, 
                $ipAddress, 
                $userAgent
            );

            $this->jsonResponse([
                'success' => true,
                'message' => 'Actividad registrada',
                'data' => $resultado
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error al registrar actividad: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener actividades de un usuario
     */
    public function getActividadesUsuario($usuarioId)
    {
        try {
            $limit = $_GET['limit'] ?? 50;
            $offset = $_GET['offset'] ?? 0;

            $actividades = $this->userActivityModel->getActividadesPorUsuario($usuarioId, $limit, $offset);

            $this->jsonResponse([
                'success' => true,
                'data' => $actividades
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error al obtener actividades: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener actividades con filtros
     */
    public function getActividades()
    {
        try {
            $filtros = [
                'usuario_id' => $_GET['usuario_id'] ?? null,
                'accion' => $_GET['accion'] ?? null,
                'fecha_inicio' => $_GET['fecha_inicio'] ?? null,
                'fecha_fin' => $_GET['fecha_fin'] ?? null,
                'ip_address' => $_GET['ip_address'] ?? null,
                'limit' => $_GET['limit'] ?? 50,
                'offset' => $_GET['offset'] ?? 0
            ];

            $actividades = $this->userActivityModel->getActividades($filtros);

            $this->jsonResponse([
                'success' => true,
                'data' => $actividades
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error al obtener actividades: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener estadísticas de actividad
     */
    public function getEstadisticasActividad()
    {
        try {
            $fechaInicio = $_GET['fecha_inicio'] ?? null;
            $fechaFin = $_GET['fecha_fin'] ?? null;

            $estadisticas = $this->userActivityModel->getEstadisticasActividad($fechaInicio, $fechaFin);

            $this->jsonResponse([
                'success' => true,
                'data' => $estadisticas
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error al obtener estadísticas de actividad: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener usuarios más activos
     */
    public function getUsuariosMasActivos()
    {
        try {
            $fechaInicio = $_GET['fecha_inicio'] ?? null;
            $fechaFin = $_GET['fecha_fin'] ?? null;
            $limit = $_GET['limit'] ?? 10;

            $usuarios = $this->userActivityModel->getUsuariosMasActivos($limit, $fechaInicio, $fechaFin);

            $this->jsonResponse([
                'success' => true,
                'data' => $usuarios
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error al obtener usuarios más activos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Detectar actividades sospechosas
     */
    public function getActividadesSospechosas()
    {
        try {
            $fechaInicio = $_GET['fecha_inicio'] ?? null;
            $fechaFin = $_GET['fecha_fin'] ?? null;

            $actividades = $this->userActivityModel->detectarActividadesSospechosas($fechaInicio, $fechaFin);

            $this->jsonResponse([
                'success' => true,
                'data' => $actividades
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error al detectar actividades sospechosas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear token de reseteo de contraseña
     */
    public function crearTokenReset()
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $email = $data['email'] ?? null;

            if (!$email) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Email es requerido'
                ], 400);
                return;
            }

            // Verificar si el usuario existe
            $usuario = $this->usuarioModel->getUsuarioPorEmail($email);
            if (!$usuario) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Usuario no encontrado'
                ], 404);
                return;
            }

            // Verificar límite de intentos
            if (!$this->passwordResetModel->puedeCrearToken($email)) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Límite de intentos de reseteo alcanzado. Intente más tarde.'
                ], 429);
                return;
            }

            $token = $this->passwordResetModel->crearToken($usuario['id'], $email);

            // Aquí normalmente enviarías el email con el token
            // Por ahora solo devolvemos el token (en producción NO hacer esto)
            
            $this->jsonResponse([
                'success' => true,
                'message' => 'Token de reseteo creado',
                'token' => $token // Solo para desarrollo, remover en producción
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error al crear token de reseteo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verificar token de reseteo
     */
    public function verificarTokenReset()
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $token = $data['token'] ?? null;

            if (!$token) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Token es requerido'
                ], 400);
                return;
            }

            $tokenData = $this->passwordResetModel->verificarToken($token);

            if (!$tokenData) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Token inválido o expirado'
                ], 400);
                return;
            }

            $this->jsonResponse([
                'success' => true,
                'message' => 'Token válido',
                'data' => [
                    'usuario_id' => $tokenData['usuario_id'],
                    'email' => $tokenData['email']
                ]
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error al verificar token: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Resetear contraseña con token
     */
    public function resetearPassword()
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $token = $data['token'] ?? null;
            $nuevaPassword = $data['nueva_password'] ?? null;

            if (!$token || !$nuevaPassword) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Token y nueva contraseña son requeridos'
                ], 400);
                return;
            }

            // Verificar token
            $tokenData = $this->passwordResetModel->verificarToken($token);
            if (!$tokenData) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Token inválido o expirado'
                ], 400);
                return;
            }

            // Actualizar contraseña
            $passwordHash = password_hash($nuevaPassword, PASSWORD_DEFAULT);
            $resultado = $this->usuarioModel->actualizarPassword($tokenData['usuario_id'], $passwordHash);

            if ($resultado) {
                // Marcar token como usado
                $this->passwordResetModel->marcarComoUsado($token);

                // Registrar actividad
                $this->userActivityModel->registrarActividad(
                    $tokenData['usuario_id'],
                    'password_reset',
                    'Contraseña restablecida mediante token',
                    $_SERVER['REMOTE_ADDR'] ?? null,
                    $_SERVER['HTTP_USER_AGENT'] ?? null
                );

                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Contraseña actualizada correctamente'
                ]);
            } else {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Error al actualizar contraseña'
                ], 500);
            }
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error al resetear contraseña: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener estadísticas de tokens de reseteo
     */
    public function getEstadisticasTokens()
    {
        try {
            $fechaInicio = $_GET['fecha_inicio'] ?? null;
            $fechaFin = $_GET['fecha_fin'] ?? null;

            $estadisticas = $this->passwordResetModel->getEstadisticasTokens($fechaInicio, $fechaFin);

            $this->jsonResponse([
                'success' => true,
                'data' => $estadisticas
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error al obtener estadísticas de tokens: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Limpiar datos antiguos de seguridad
     */
    public function limpiarDatosAntiguos()
    {
        try {
            $diasAntiguedad = $_GET['dias'] ?? 30;

            // Limpiar intentos de login antiguos
            $intentosLimpiados = $this->loginAttemptsModel->limpiarIntentosAntiguos($diasAntiguedad);

            // Limpiar logs de actividad antiguos
            $logsLimpiados = $this->userActivityModel->limpiarLogsAntiguos($diasAntiguedad);

            // Limpiar tokens expirados
            $tokensLimpiados = $this->passwordResetModel->limpiarTokensExpirados();

            $this->jsonResponse([
                'success' => true,
                'message' => 'Limpieza completada',
                'data' => [
                    'intentos_limpiados' => $intentosLimpiados,
                    'logs_limpiados' => $logsLimpiados,
                    'tokens_limpiados' => $tokensLimpiados
                ]
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error al limpiar datos antiguos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener reporte de seguridad completo
     */
    public function getReporteSeguridad()
    {
        try {
            $fechaInicio = $_GET['fecha_inicio'] ?? null;
            $fechaFin = $_GET['fecha_fin'] ?? null;

            $reporte = [
                'intentos_login' => $this->loginAttemptsModel->getEstadisticasIntentos($fechaInicio, $fechaFin),
                'actividad_usuarios' => $this->userActivityModel->getEstadisticasActividad($fechaInicio, $fechaFin),
                'usuarios_mas_activos' => $this->userActivityModel->getUsuariosMasActivos(10, $fechaInicio, $fechaFin),
                'actividades_sospechosas' => $this->userActivityModel->detectarActividadesSospechosas($fechaInicio, $fechaFin),
                'tokens_reset' => $this->passwordResetModel->getEstadisticasTokens($fechaInicio, $fechaFin)
            ];

            $this->jsonResponse([
                'success' => true,
                'data' => $reporte,
                'periodo' => [
                    'fecha_inicio' => $fechaInicio,
                    'fecha_fin' => $fechaFin
                ]
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error al generar reporte de seguridad: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Registrar login exitoso
     */
    public function registrarLoginExitoso()
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $usuarioId = $data['usuario_id'] ?? null;

            if (!$usuarioId) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Usuario ID es requerido'
                ], 400);
                return;
            }

            $this->userActivityModel->registrarLoginExitoso(
                $usuarioId,
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SERVER['HTTP_USER_AGENT'] ?? null
            );

            $this->jsonResponse([
                'success' => true,
                'message' => 'Login exitoso registrado'
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error al registrar login exitoso: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Registrar logout
     */
    public function registrarLogout()
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $usuarioId = $data['usuario_id'] ?? null;

            if (!$usuarioId) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Usuario ID es requerido'
                ], 400);
                return;
            }

            $this->userActivityModel->registrarLogout(
                $usuarioId,
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SERVER['HTTP_USER_AGENT'] ?? null
            );

            $this->jsonResponse([
                'success' => true,
                'message' => 'Logout registrado'
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error al registrar logout: ' . $e->getMessage()
            ], 500);
        }
    }
}