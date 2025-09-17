import { useState, useEffect, useCallback } from 'react';
import usuarioService from '@/features/usuarios/services/usuarioService';

export const useUsuarios = () => {
    const [usuarios, setUsuarios] = useState([]);
    const [isLoading, setIsLoading] = useState(true);
    const [error, setError] = useState(null);

    const cargarUsuarios = useCallback(async () => {
        try {
            const data = await usuarioService.getUsuarios();
            setUsuarios(Array.isArray(data) ? data : []);
        } catch (err) {
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
        // Actualización optimista
        setUsuarios(prev => prev.map(u => u.id === id ? { ...u, ...dataToUpdate } : u));
        
        try {
            const fullData = {
                nombre: usuarioOriginal.nombre,
                email: usuarioOriginal.email,
                rol: dataToUpdate.rol || usuarioOriginal.rol,
                estado: dataToUpdate.estado || usuarioOriginal.estado
            };
            await usuarioService.updateUsuario(id, fullData);
        } catch (err) {
            alert('Error al actualizar. El cambio será revertido.');
            setUsuarios(prev => prev.map(u => u.id === id ? usuarioOriginal : u));
        }
    };

    const deleteUsuario = async (id) => {
        if (window.confirm('¿Eliminar este usuario?')) {
            try {
                await usuarioService.deleteUsuario(id);
                setUsuarios(prev => prev.filter(u => u.id !== id));
            } catch (err) {
                alert('Error al eliminar el usuario.');
            }
        }
    };

    return { usuarios, isLoading, error, updateUsuario, deleteUsuario };
};