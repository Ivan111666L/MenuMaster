import { useState, useEffect, useCallback } from 'react';
import mesaService from '@/features/mesas/services/mesaService';

/**
 * Hook personalizado para gestionar el estado y operaciones de mesas
 * @returns {object} Estado y funciones para manejar mesas
 */
export const useMesas = () => {
  const [mesas, setMesas] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  // Cargar mesas inicialmente
  const fetchMesas = useCallback(async () => {
    try {
      setLoading(true);
      setError(null);
      const data = await mesaService.getMesas();
      setMesas(data);
    } catch (err) {
      setError(err.message || 'Error al cargar las mesas');
      console.error('Error fetching mesas:', err);
    } finally {
      setLoading(false);
    }
  }, []);

  // Crear nueva mesa
  const createMesa = useCallback(async (mesaData) => {
    try {
      setError(null);
      const nuevaMesa = await mesaService.createMesa(mesaData);
      setMesas(prev => [...prev, nuevaMesa]);
      return nuevaMesa;
    } catch (err) {
      setError(err.message || 'Error al crear la mesa');
      throw err;
    }
  }, []);

  // Actualizar mesa existente
  const updateMesa = useCallback(async (id, mesaData) => {
    try {
      setError(null);
      const mesaActualizada = await mesaService.updateMesa(id, mesaData);
      setMesas(prev => prev.map(mesa => 
        mesa.id === id ? { ...mesa, ...mesaActualizada } : mesa
      ));
      return mesaActualizada;
    } catch (err) {
      setError(err.message || 'Error al actualizar la mesa');
      throw err;
    }
  }, []);

  // Eliminar mesa
  const deleteMesa = useCallback(async (id) => {
    try {
      setError(null);
      await mesaService.deleteMesa(id);
      setMesas(prev => prev.filter(mesa => mesa.id !== id));
    } catch (err) {
      setError(err.message || 'Error al eliminar la mesa');
      throw err;
    }
  }, []);

  // Cambiar estado de mesa
  const changeEstadoMesa = useCallback(async (id, nuevoEstado) => {
    try {
      setError(null);
      const mesaActualizada = await mesaService.updateMesa(id, { estado_nombre: nuevoEstado });
      // Actualizamos el campo 'estado' que es el que devuelve el backend en listados
      setMesas(prev => prev.map(mesa => 
        mesa.id === id ? { ...mesa, estado: nuevoEstado } : mesa
      ));
      // Disparamos evento global para refrescar otras vistas (Mesas.jsx)
      window.dispatchEvent(new Event('mesas:update'));
      return mesaActualizada;
    } catch (err) {
      setError(err.message || 'Error al cambiar el estado de la mesa');
      throw err;
    }
  }, []);

  // Obtener mesa por ID
  const getMesaById = useCallback((id) => {
    return mesas.find(mesa => mesa.id === parseInt(id));
  }, [mesas]);

  // Filtrar mesas por estado
  const getMesasByEstado = useCallback((estado) => {
    return mesas.filter(mesa => mesa.estado === estado);
  }, [mesas]);

  // Obtener estadísticas de mesas
  const getEstadisticasMesas = useCallback(() => {
    const total = mesas.length;
    const disponibles = mesas.filter(m => m.estado === 'disponible').length;
    const ocupadas = mesas.filter(m => m.estado === 'ocupada').length;
    const reservadas = mesas.filter(m => m.estado === 'reservada').length;
    const mantenimiento = mesas.filter(m => m.estado === 'mantenimiento').length;

    return {
      total,
      disponibles,
      ocupadas,
      reservadas,
      mantenimiento,
      porcentajeOcupacion: total > 0 ? Math.round((ocupadas / total) * 100) : 0
    };
  }, [mesas]);

  // Cargar mesas al montar el componente
  useEffect(() => {
    fetchMesas();
  }, [fetchMesas]);

  return {
    // Estado
    mesas,
    loading,
    error,
    
    // Acciones CRUD
    fetchMesas,
    createMesa,
    updateMesa,
    deleteMesa,
    changeEstadoMesa,
    
    // Utilidades
    getMesaById,
    getMesasByEstado,
    getEstadisticasMesas,
    
    // Limpiar error
    clearError: () => setError(null)
  };
};

export default useMesas;