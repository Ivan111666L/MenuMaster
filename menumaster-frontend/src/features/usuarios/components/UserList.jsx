import React, { useState, useEffect } from 'react';
import { getUsuarios } from '@/services/api'; // Importa la función del servicio

function UserList() {
    const [usuarios, setUsuarios] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        const fetchUsuarios = async () => {
            try {
                const data = await getUsuarios();
                setUsuarios(data);
            } catch (err) {
                setError("No se pudieron cargar los datos del servidor.");
            } finally {
                setLoading(false);
            }
        };

        fetchUsuarios();
    }, []); // El array vacío asegura que se ejecute solo una vez

    if (loading) return <p>Cargando usuarios...</p>;
    if (error) return <p style={{ color: 'red' }}>{error}</p>;

    return (
        <div>
            <h1>Lista de Usuarios</h1>
            <ul>
                {usuarios.map(usuario => (
                    <li key={usuario.id}>
                        <strong>{usuario.nombre}</strong> - ({usuario.email})
                    </li>
                ))}
            </ul>
        </div>
    );
}

export default UserList;