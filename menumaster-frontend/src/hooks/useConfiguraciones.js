import { useState, useEffect, useCallback } from 'react';

/**
 * Hook personalizado para gestionar configuraciones del sistema
 * @returns {object} Estado y funciones para manejar configuraciones
 */
export const useConfiguraciones = () => {
  const [configuraciones, setConfiguraciones] = useState({
    restaurante: {
      nombre: '',
      direccion: '',
      telefono: '',
      email: '',
      logo: null
    },
    sistema: {
      moneda: 'COP',
      idioma: 'es',
      zona_horaria: 'America/Bogota',
      formato_fecha: 'DD/MM/YYYY',
      decimales_precio: 2
    },
    facturacion: {
      iva_incluido: true,
      porcentaje_iva: 19,
      propina_sugerida: 10,
      metodos_pago: ['efectivo', 'tarjeta', 'transferencia']
    },
    notificaciones: {
      email_pedidos: true,
      sonido_nuevos_pedidos: true,
      alertas_inventario_bajo: true,
      reportes_automaticos: false
    },
    mesas: {
      tiempo_limite_ocupacion: 120, // minutos
      auto_liberar_mesas: false,
      mostrar_plano_mesas: true,
      colores_estado: {
        disponible: '#28a745',
        ocupada: '#dc3545',
        reservada: '#ffc107',
        mantenimiento: '#6c757d'
      }
    }
  });
  
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const [hasChanges, setHasChanges] = useState(false);

  // Cargar configuraciones desde localStorage o API
  const loadConfiguraciones = useCallback(async () => {
    try {
      setLoading(true);
      setError(null);
      
      // Intentar cargar desde localStorage primero
      const savedConfig = localStorage.getItem('menumaster_configuraciones');
      if (savedConfig) {
        const parsedConfig = JSON.parse(savedConfig);
        setConfiguraciones(prev => ({ ...prev, ...parsedConfig }));
      }
      
      // Aquí se podría hacer una llamada a la API para sincronizar
      // const apiConfig = await configService.getConfiguraciones();
      // setConfiguraciones(apiConfig);
      
    } catch (err) {
      setError(err.message || 'Error al cargar las configuraciones');
      console.error('Error loading configuraciones:', err);
    } finally {
      setLoading(false);
    }
  }, []);

  // Actualizar configuración específica
  const updateConfiguracion = useCallback((seccion, campo, valor) => {
    setConfiguraciones(prev => ({
      ...prev,
      [seccion]: {
        ...prev[seccion],
        [campo]: valor
      }
    }));
    setHasChanges(true);
  }, []);

  // Actualizar sección completa
  const updateSeccion = useCallback((seccion, datos) => {
    setConfiguraciones(prev => ({
      ...prev,
      [seccion]: {
        ...prev[seccion],
        ...datos
      }
    }));
    setHasChanges(true);
  }, []);

  // Guardar configuraciones
  const saveConfiguraciones = useCallback(async () => {
    try {
      setLoading(true);
      setError(null);
      
      // Guardar en localStorage
      localStorage.setItem('menumaster_configuraciones', JSON.stringify(configuraciones));
      
      // Aquí se podría hacer una llamada a la API para guardar
      // await configService.saveConfiguraciones(configuraciones);
      
      setHasChanges(false);
      return true;
    } catch (err) {
      setError(err.message || 'Error al guardar las configuraciones');
      throw err;
    } finally {
      setLoading(false);
    }
  }, [configuraciones]);

  // Resetear configuraciones a valores por defecto
  const resetConfiguraciones = useCallback(() => {
    const defaultConfig = {
      restaurante: {
        nombre: '',
        direccion: '',
        telefono: '',
        email: '',
        logo: null
      },
      sistema: {
        moneda: 'COP',
        idioma: 'es',
        zona_horaria: 'America/Bogota',
        formato_fecha: 'DD/MM/YYYY',
        decimales_precio: 2
      },
      facturacion: {
        iva_incluido: true,
        porcentaje_iva: 19,
        propina_sugerida: 10,
        metodos_pago: ['efectivo', 'tarjeta', 'transferencia']
      },
      notificaciones: {
        email_pedidos: true,
        sonido_nuevos_pedidos: true,
        alertas_inventario_bajo: true,
        reportes_automaticos: false
      },
      mesas: {
        tiempo_limite_ocupacion: 120,
        auto_liberar_mesas: false,
        mostrar_plano_mesas: true,
        colores_estado: {
          disponible: '#28a745',
          ocupada: '#dc3545',
          reservada: '#ffc107',
          mantenimiento: '#6c757d'
        }
      }
    };
    
    setConfiguraciones(defaultConfig);
    setHasChanges(true);
  }, []);

  // Obtener configuración específica
  const getConfiguracion = useCallback((seccion, campo = null) => {
    if (campo) {
      return configuraciones[seccion]?.[campo];
    }
    return configuraciones[seccion];
  }, [configuraciones]);

  // Validar configuraciones
  const validateConfiguraciones = useCallback(() => {
    const errors = [];
    
    // Validar datos del restaurante
    if (!configuraciones.restaurante.nombre?.trim()) {
      errors.push('El nombre del restaurante es requerido');
    }
    
    // Validar configuraciones del sistema
    if (configuraciones.sistema.decimales_precio < 0 || configuraciones.sistema.decimales_precio > 4) {
      errors.push('Los decimales de precio deben estar entre 0 y 4');
    }
    
    // Validar configuraciones de facturación
    if (configuraciones.facturacion.porcentaje_iva < 0 || configuraciones.facturacion.porcentaje_iva > 100) {
      errors.push('El porcentaje de IVA debe estar entre 0 y 100');
    }
    
    // Validar configuraciones de mesas
    if (configuraciones.mesas.tiempo_limite_ocupacion < 30) {
      errors.push('El tiempo límite de ocupación debe ser al menos 30 minutos');
    }
    
    return errors;
  }, [configuraciones]);

  // Exportar configuraciones
  const exportConfiguraciones = useCallback(() => {
    const dataStr = JSON.stringify(configuraciones, null, 2);
    const dataBlob = new Blob([dataStr], { type: 'application/json' });
    const url = URL.createObjectURL(dataBlob);
    
    const link = document.createElement('a');
    link.href = url;
    link.download = `menumaster-config-${new Date().toISOString().split('T')[0]}.json`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
  }, [configuraciones]);

  // Importar configuraciones
  const importConfiguraciones = useCallback((file) => {
    return new Promise((resolve, reject) => {
      const reader = new FileReader();
      reader.onload = (e) => {
        try {
          const importedConfig = JSON.parse(e.target.result);
          setConfiguraciones(prev => ({ ...prev, ...importedConfig }));
          setHasChanges(true);
          resolve(importedConfig);
        } catch (err) {
          reject(new Error('Archivo de configuración inválido'));
        }
      };
      reader.onerror = () => reject(new Error('Error al leer el archivo'));
      reader.readAsText(file);
    });
  }, []);

  // Cargar configuraciones al montar el componente
  useEffect(() => {
    loadConfiguraciones();
  }, [loadConfiguraciones]);

  return {
    // Estado
    configuraciones,
    loading,
    error,
    hasChanges,
    
    // Acciones
    loadConfiguraciones,
    updateConfiguracion,
    updateSeccion,
    saveConfiguraciones,
    resetConfiguraciones,
    
    // Utilidades
    getConfiguracion,
    validateConfiguraciones,
    exportConfiguraciones,
    importConfiguraciones,
    
    // Limpiar error
    clearError: () => setError(null)
  };
};

export default useConfiguraciones;