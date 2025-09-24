import { useState, useEffect, useCallback } from 'react';
import usuarioService from '@/features/usuarios/services/usuarioService';

/**
 * Hook personalizado para gestionar el estado y operaciones de usuarios
 * @returns {object} Estado y funciones para manejar usuarios
 */
export const useUsuarios = () => {
  const [usuarios, setUsuarios] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  // Cargar usuarios inicialmente
  const fetchUsuarios = useCallback(async () => {
    try {
      setLoading(true);
      setError(null);
      const data = await usuarioService.getUsuarios();
      setUsuarios(data);
    } catch (err) {
      setError(err.message || 'Error al cargar los usuarios');
      console.error('Error fetching usuarios:', err);
    } finally {
      setLoading(false);
    }
  }, []);

  // Crear nuevo usuario
  const createUsuario = useCallback(async (usuarioData) => {
    try {
      setError(null);
      const nuevoUsuario = await usuarioService.createUsuario(usuarioData);
      setUsuarios(prev => [...prev, nuevoUsuario]);
      return nuevoUsuario;
    } catch (err) {
      setError(err.message || 'Error al crear el usuario');
      throw err;
    }
  }, []);

  // Actualizar usuario existente
  const updateUsuario = useCallback(async (id, usuarioData) => {
    try {
      setError(null);
      const usuarioActualizado = await usuarioService.updateUsuario(id, usuarioData);
      setUsuarios(prev => prev.map(usuario => 
        usuario.id === id ? { ...usuario, ...usuarioActualizado } : usuario
      ));
      return usuarioActualizado;
    } catch (err) {
      setError(err.message || 'Error al actualizar el usuario');
      throw err;
    }
  }, []);

  // Eliminar usuario
  const deleteUsuario = useCallback(async (id) => {
    try {
      setError(null);
      await usuarioService.deleteUsuario(id);
      setUsuarios(prev => prev.filter(usuario => usuario.id !== id));
    } catch (err) {
      setError(err.message || 'Error al eliminar el usuario');
      throw err;
    }
  }, []);

  // Cambiar estado de usuario (activo/inactivo)
  const toggleUsuarioEstado = useCallback(async (id) => {
    try {
      setError(null);
      const usuario = usuarios.find(u => u.id === id);
      if (!usuario) throw new Error('Usuario no encontrado');
      
      const nuevoEstado = usuario.activo ? 0 : 1;
      const usuarioActualizado = await usuarioService.updateUsuario(id, { activo: nuevoEstado });
      
      setUsuarios(prev => prev.map(u => 
        u.id === id ? { ...u, activo: nuevoEstado } : u
      ));
      
      return usuarioActualizado;
    } catch (err) {
      setError(err.message || 'Error al cambiar el estado del usuario');
      throw err;
    }
  }, [usuarios]);

  // Cambiar rol de usuario
  const changeUsuarioRol = useCallback(async (id, nuevoRol) => {
    try {
      setError(null);
      const usuarioActualizado = await usuarioService.updateUsuario(id, { rol: nuevoRol });
      setUsuarios(prev => prev.map(usuario => 
        usuario.id === id ? { ...usuario, rol: nuevoRol } : usuario
      ));
      return usuarioActualizado;
    } catch (err) {
      setError(err.message || 'Error al cambiar el rol del usuario');
      throw err;
    }
  }, []);

  // Obtener usuario por ID
  const getUsuarioById = useCallback((id) => {
    return usuarios.find(usuario => usuario.id === parseInt(id));
  }, [usuarios]);

  // Filtrar usuarios por rol
  const getUsuariosByRol = useCallback((rol) => {
    return usuarios.filter(usuario => usuario.rol === rol);
  }, [usuarios]);

  // Filtrar usuarios por estado
  const getUsuariosByEstado = useCallback((activo) => {
    return usuarios.filter(usuario => usuario.activo === activo);
  }, [usuarios]);

  // Buscar usuarios por nombre o email
  const searchUsuarios = useCallback((query) => {
    if (!query.trim()) return usuarios;
    
    const searchTerm = query.toLowerCase();
    return usuarios.filter(usuario => 
      usuario.nombre?.toLowerCase().includes(searchTerm) ||
      usuario.email?.toLowerCase().includes(searchTerm)
    );
  }, [usuarios]);

  // Obtener estadísticas de usuarios
  const getEstadisticasUsuarios = useCallback(() => {
    const total = usuarios.length;
    const activos = usuarios.filter(u => u.activo === 1).length;
    const inactivos = usuarios.filter(u => u.activo === 0).length;
    
    const porRol = usuarios.reduce((acc, usuario) => {
      acc[usuario.rol] = (acc[usuario.rol] || 0) + 1;
      return acc;
    }, {});

    return {
      total,
      activos,
      inactivos,
      porRol,
      porcentajeActivos: total > 0 ? Math.round((activos / total) * 100) : 0
    };
  }, [usuarios]);

  // Validar email único
  const isEmailUnique = useCallback((email, excludeId = null) => {
    return !usuarios.some(usuario => 
      usuario.email === email && usuario.id !== excludeId
    );
  }, [usuarios]);

  // Cargar usuarios al montar el componente
  useEffect(() => {
    fetchUsuarios();
  }, [fetchUsuarios]);

  return {
    // Estado
    usuarios,
    loading,
    error,
    
    // Acciones CRUD
    fetchUsuarios,
    createUsuario,
    updateUsuario,
    deleteUsuario,
    
    // Acciones específicas
    toggleUsuarioEstado,
    changeUsuarioRol,
    
    // Utilidades
    getUsuarioById,
    getUsuariosByRol,
    getUsuariosByEstado,
    searchUsuarios,
    getEstadisticasUsuarios,
    isEmailUnique,
    
    // Limpiar error
    clearError: () => setError(null)
  };
};

export default useUsuarios;