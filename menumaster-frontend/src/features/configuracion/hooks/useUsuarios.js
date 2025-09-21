import { useState, useEffect, useCallback } from 'react';
import usuarioService from '@/features/usuarios/services/usuarioService';

const useUsuarios = () => {
    const [usuarios, setUsuarios] = useState([]);
    const [isLoading, setIsLoading] = useState(true);
    const [error, setError] = useState(null);

    const cargarUsuarios = useCallback(async () => {
        try {
            setIsLoading(true);
            const data = await usuarioService.getUsuarios();
            setUsuarios(Array.isArray(data) ? data : []);
            setError(null);
        } catch (err) {
            console.error('Error al cargar usuarios:', err);
            setError('Error al cargar los usuarios.');
        } finally {
            setIsLoading(false);
        }
    }, []);

    useEffect(() => {
        cargarUsuarios();
    }, [cargarUsuarios]);

    const updateUsuario = async (id, dataToUpdate) => {
        const usuarioOriginal = usuarios.find(u => u.id === id);
        if (!usuarioOriginal) {
            throw new Error('Usuario no encontrado');
        }

        // Actualización optimista
        setUsuarios(prev => prev.map(u => 
            u.id === id ? { ...u, ...dataToUpdate } : u
        ));
        
        try {
            await usuarioService.actualizarUsuario(id, {
                ...usuarioOriginal,
                ...dataToUpdate
            });
            await cargarUsuarios(); // Recargamos para asegurar consistencia
        } catch (error) {
            // Revertimos en caso de error
            setUsuarios(prev => prev.map(u => 
                u.id === id ? usuarioOriginal : u
            ));
            throw error;
        }
    };

    const deleteUsuario = async (id) => {
        const usuarioOriginal = usuarios.find(u => u.id === id);
        if (!usuarioOriginal) {
            throw new Error('Usuario no encontrado');
        }

        // Eliminación optimista
        setUsuarios(prev => prev.filter(u => u.id !== id));

        try {
            await usuarioService.eliminarUsuario(id);
            await cargarUsuarios(); // Recargamos para asegurar consistencia
        } catch (error) {
            // Revertimos en caso de error
            setUsuarios(prev => [...prev, usuarioOriginal]);
            throw error;
        }
    };

    return {
        usuarios,
        isLoading,
        error,
        updateUsuario,
        deleteUsuario,
        recargarUsuarios: cargarUsuarios
    };
};

export default useUsuarios;