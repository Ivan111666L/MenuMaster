import { useCallback } from 'react';
import { toast } from 'react-toastify';

/**
 * Hook personalizado para gestionar notificaciones de manera centralizada
 * Proporciona métodos consistentes para mostrar diferentes tipos de notificaciones
 * @returns {object} Funciones para mostrar notificaciones
 */
export const useNotifications = () => {
  
  // Configuración por defecto para las notificaciones
  const defaultConfig = {
    position: "top-right",
    autoClose: 3000,
    hideProgressBar: false,
    closeOnClick: true,
    pauseOnHover: true,
    draggable: true,
    progress: undefined,
  };

  // Notificación de éxito
  const showSuccess = useCallback((message, config = {}) => {
    toast.success(message, {
      ...defaultConfig,
      ...config,
      className: 'toast-success',
    });
  }, []);

  // Notificación de error
  const showError = useCallback((message, config = {}) => {
    toast.error(message, {
      ...defaultConfig,
      autoClose: 5000, // Los errores se muestran más tiempo
      ...config,
      className: 'toast-error',
    });
  }, []);

  // Notificación de advertencia
  const showWarning = useCallback((message, config = {}) => {
    toast.warn(message, {
      ...defaultConfig,
      ...config,
      className: 'toast-warning',
    });
  }, []);

  // Notificación informativa
  const showInfo = useCallback((message, config = {}) => {
    toast.info(message, {
      ...defaultConfig,
      ...config,
      className: 'toast-info',
    });
  }, []);

  // Notificación de carga/loading
  const showLoading = useCallback((message = "Cargando...", config = {}) => {
    return toast.loading(message, {
      ...defaultConfig,
      autoClose: false,
      closeOnClick: false,
      ...config,
      className: 'toast-loading',
    });
  }, []);

  // Actualizar notificación existente (útil para loading)
  const updateNotification = useCallback((toastId, type, message, config = {}) => {
    const updateConfig = {
      ...defaultConfig,
      ...config,
      render: message,
      type: type,
      isLoading: false,
      autoClose: type === 'error' ? 5000 : 3000,
    };

    toast.update(toastId, updateConfig);
  }, []);

  // Notificación personalizada con JSX
  const showCustom = useCallback((content, config = {}) => {
    toast(content, {
      ...defaultConfig,
      ...config,
    });
  }, []);

  // Notificación de confirmación (promesa)
  const showPromise = useCallback((promise, messages, config = {}) => {
    const promiseConfig = {
      pending: messages.pending || 'Procesando...',
      success: messages.success || 'Operación completada',
      error: messages.error || 'Error en la operación',
      ...config,
    };

    return toast.promise(promise, promiseConfig, {
      ...defaultConfig,
      ...config,
    });
  }, []);

  // Limpiar todas las notificaciones
  const clearAll = useCallback(() => {
    toast.dismiss();
  }, []);

  // Notificaciones específicas del negocio
  const notifications = {
    // Pedidos
    pedidoCreado: (numeroPedido) => showSuccess(`Pedido #${numeroPedido} creado exitosamente`),
    pedidoActualizado: (numeroPedido) => showSuccess(`Pedido #${numeroPedido} actualizado`),
    pedidoEliminado: () => showSuccess('Pedido eliminado correctamente'),
    pedidoError: (error) => showError(`Error en pedido: ${error}`),

    // Mesas
    mesaCreada: (numeroMesa) => showSuccess(`Mesa ${numeroMesa} creada exitosamente`),
    mesaActualizada: (numeroMesa) => showSuccess(`Mesa ${numeroMesa} actualizada`),
    mesaEliminada: (numeroMesa) => showSuccess(`Mesa ${numeroMesa} eliminada`),
    mesaOcupada: (numeroMesa) => showInfo(`Mesa ${numeroMesa} ahora está ocupada`),
    mesaLiberada: (numeroMesa) => showInfo(`Mesa ${numeroMesa} ahora está disponible`),

    // Usuarios
    usuarioCreado: (nombre) => showSuccess(`Usuario ${nombre} creado exitosamente`),
    usuarioActualizado: (nombre) => showSuccess(`Usuario ${nombre} actualizado`),
    usuarioEliminado: (nombre) => showSuccess(`Usuario ${nombre} eliminado`),
    usuarioDesactivado: (nombre) => showWarning(`Usuario ${nombre} desactivado`),

    // Productos
    productoCreado: (nombre) => showSuccess(`Producto ${nombre} creado exitosamente`),
    productoActualizado: (nombre) => showSuccess(`Producto ${nombre} actualizado`),
    productoEliminado: (nombre) => showSuccess(`Producto ${nombre} eliminado`),
    stockBajo: (producto, cantidad) => showWarning(`Stock bajo: ${producto} (${cantidad} unidades)`),

    // Inventario
    inventarioActualizado: () => showSuccess('Inventario actualizado correctamente'),
    movimientoRegistrado: () => showSuccess('Movimiento de inventario registrado'),

    // Autenticación
    loginExitoso: (nombre) => showSuccess(`¡Bienvenido ${nombre}!`),
    logoutExitoso: () => showInfo('Sesión cerrada correctamente'),
    sesionExpirada: () => showWarning('Tu sesión ha expirado. Por favor, inicia sesión nuevamente'),
    credencialesIncorrectas: () => showError('Credenciales incorrectas'),

    // Configuración
    configuracionGuardada: () => showSuccess('Configuración guardada correctamente'),
    configuracionError: () => showError('Error al guardar la configuración'),

    // Errores de red
    errorConexion: () => showError('Error de conexión. Verifica tu conexión a internet'),
    errorServidor: () => showError('Error del servidor. Inténtalo más tarde'),
    errorPermisos: () => showError('No tienes permisos para realizar esta acción'),

    // Operaciones generales
    operacionExitosa: (operacion) => showSuccess(`${operacion} completada exitosamente`),
    operacionError: (operacion, error) => showError(`Error en ${operacion}: ${error}`),
    datosGuardados: () => showSuccess('Datos guardados correctamente'),
    cambiosDescartados: () => showInfo('Cambios descartados'),
  };

  return {
    // Métodos básicos
    showSuccess,
    showError,
    showWarning,
    showInfo,
    showLoading,
    updateNotification,
    showCustom,
    showPromise,
    clearAll,
    
    // Notificaciones específicas del negocio
    notifications,
    
    // Acceso directo al toast para casos especiales
    toast,
  };
};

export default useNotifications;