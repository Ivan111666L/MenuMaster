import React from 'react';
import { useNotifications } from '../hooks/useNotifications';

/**
 * Componente de demostración para mostrar las diferentes notificaciones disponibles
 * Útil para testing y para que los desarrolladores vean las opciones disponibles
 */
const NotificationDemo = () => {
  const { 
    showSuccess, 
    showError, 
    showWarning, 
    showInfo, 
    showLoading, 
    updateNotification,
    showPromise,
    notifications,
    clearAll 
  } = useNotifications();

  // Función para demostrar notificación de loading con actualización
  const demoLoadingWithUpdate = async () => {
    const toastId = showLoading('Procesando pedido...');
    
    // Simular proceso asíncrono
    setTimeout(() => {
      updateNotification(toastId, 'success', '¡Pedido procesado exitosamente!');
    }, 2000);
  };

  // Función para demostrar notificación con promesa
  const demoPromiseNotification = () => {
    const fakePromise = new Promise((resolve, reject) => {
      setTimeout(() => {
        Math.random() > 0.5 ? resolve('Operación exitosa') : reject('Error en la operación');
      }, 2000);
    });

    showPromise(fakePromise, {
      pending: 'Procesando operación...',
      success: 'Operación completada con éxito',
      error: 'Error al procesar la operación'
    });
  };

  return (
    <div className="notification-demo p-4">
      <h2 className="mb-4">Demostración de Notificaciones</h2>
      
      <div className="row">
        <div className="col-md-6">
          <h4>Notificaciones Básicas</h4>
          <div className="d-grid gap-2 mb-4">
            <button 
              className="btn btn-success" 
              onClick={() => showSuccess('¡Operación exitosa!')}
            >
              Mostrar Éxito
            </button>
            
            <button 
              className="btn btn-danger" 
              onClick={() => showError('Error en la operación')}
            >
              Mostrar Error
            </button>
            
            <button 
              className="btn btn-warning" 
              onClick={() => showWarning('Advertencia importante')}
            >
              Mostrar Advertencia
            </button>
            
            <button 
              className="btn btn-info" 
              onClick={() => showInfo('Información útil')}
            >
              Mostrar Información
            </button>
            
            <button 
              className="btn btn-secondary" 
              onClick={demoLoadingWithUpdate}
            >
              Loading con Actualización
            </button>
            
            <button 
              className="btn btn-primary" 
              onClick={demoPromiseNotification}
            >
              Notificación con Promesa
            </button>
            
            <button 
              className="btn btn-outline-secondary" 
              onClick={clearAll}
            >
              Limpiar Todas
            </button>
          </div>
        </div>
        
        <div className="col-md-6">
          <h4>Notificaciones del Negocio</h4>
          <div className="d-grid gap-2 mb-4">
            <button 
              className="btn btn-success" 
              onClick={() => notifications.pedidoCreado('001')}
            >
              Pedido Creado
            </button>
            
            <button 
              className="btn btn-info" 
              onClick={() => notifications.mesaOcupada('5')}
            >
              Mesa Ocupada
            </button>
            
            <button 
              className="btn btn-success" 
              onClick={() => notifications.usuarioCreado('Juan Pérez')}
            >
              Usuario Creado
            </button>
            
            <button 
              className="btn btn-warning" 
              onClick={() => notifications.stockBajo('Hamburguesa', 3)}
            >
              Stock Bajo
            </button>
            
            <button 
              className="btn btn-success" 
              onClick={() => notifications.loginExitoso('Admin')}
            >
              Login Exitoso
            </button>
            
            <button 
              className="btn btn-warning" 
              onClick={() => notifications.sesionExpirada()}
            >
              Sesión Expirada
            </button>
            
            <button 
              className="btn btn-danger" 
              onClick={() => notifications.errorConexion()}
            >
              Error de Conexión
            </button>
            
            <button 
              className="btn btn-success" 
              onClick={() => notifications.configuracionGuardada()}
            >
              Configuración Guardada
            </button>
          </div>
        </div>
      </div>
      
      <div className="alert alert-info mt-4">
        <h5>Características del Sistema de Notificaciones:</h5>
        <ul className="mb-0">
          <li>Notificaciones con colores temáticos</li>
          <li>Animaciones suaves de entrada y salida</li>
          <li>Límite de 5 notificaciones simultáneas</li>
          <li>Cierre automático configurable</li>
          <li>Soporte para notificaciones de carga con actualización</li>
          <li>Notificaciones específicas del negocio</li>
          <li>Responsive design</li>
          <li>Accesibilidad mejorada</li>
        </ul>
      </div>
    </div>
  );
};

export default NotificationDemo;