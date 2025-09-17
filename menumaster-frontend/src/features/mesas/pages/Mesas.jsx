import React, { useEffect, useState, useCallback } from 'react';
import mesaService from '../services/mesaService'; // Nuestro servicio de API
import Button from '@/components/Button';
import Spinner from '@/components/Spinner';
import '@/styles/mesas.css'; // Nuestros estilos dedicados

function Mesas() {
    const [mesas, setMesas] = useState([]);
    const [isLoading, setIsLoading] = useState(true);
    const [error, setError] = useState(null);

    const cargarMesas = useCallback(async () => {
        try {
            // No seteamos isLoading aquí para que el refresco sea en segundo plano
            const data = await mesaService.getMesas();
            setMesas(Array.isArray(data) ? data : []);
        } catch (err) {
            setError('Error al cargar las mesas. Por favor, recarga la página.');
        } finally {
            setIsLoading(false); // Solo se ejecuta la primera vez
        }
    }, []);

    useEffect(() => {
        cargarMesas(); // Carga inicial
        // Polling: refrescar las mesas cada 10 segundos
        const intervalId = setInterval(cargarMesas, 10000);
        // Limpieza al desmontar el componente
        return () => clearInterval(intervalId);
    }, [cargarMesas]);

    const cambiarEstado = async (id, estadoActual) => {
        const estadosCiclo = {
            'disponible': 'ocupada',
            'ocupada': 'reservada',
            'reservada': 'disponible'
        };
        const nuevoEstado = estadosCiclo[estadoActual];

        // Actualización optimista: cambiamos el estado en la UI al instante
        const mesasOriginales = [...mesas];
        setMesas(prev => prev.map(m => m.id === id ? { ...m, estado: nuevoEstado } : m));

        try {
            await mesaService.updateMesa(id, { estado_nombre: nuevoEstado });
        } catch (err) {
            alert('Error al actualizar la mesa. Revirtiendo cambio.');
            setMesas(mesasOriginales); // Si falla, revertimos al estado anterior
        }
    };

    const resetearMesas = async () => {
        if (window.confirm('¿Estás seguro de que quieres poner todas las mesas como disponibles?')) {
            try {
                await mesaService.resetAllMesas();
                cargarMesas(); // Recargamos la lista
            } catch (err) {
                alert('No se pudo resetear las mesas.');
            }
        }
    };
    
    if (isLoading) {
        return <div className="loader-container"><Spinner /></div>;
    }

    if (error) {
        return <div className="error-message">{error}</div>;
    }

    return (
        <div className="gestion-mesas-app">
            <div className="gestion-mesas-container">
                <div className="header-controles">
                    <h1>Gestión de Mesas</h1>
                    <Button onClick={resetearMesas} variant="danger" disabled={isLoading}>
                        Resetear Mesas
                    </Button>
                </div>

                <div className="mesa-container">
                    {mesas.length > 0 ? (
                        mesas.map((mesa) => (
                            <div
                                key={mesa.id}
                                className={`mesa-card ${mesa.estado}`}
                                onClick={() => cambiarEstado(mesa.id, mesa.estado)}
                            >
                                <h3>Mesa {mesa.numero}</h3>
                                <p><strong>Capacidad:</strong> {mesa.capacidad} personas</p>
                                <p><strong>Ubicación:</strong> {mesa.ubicacion}</p>
                                <span className="estado-badge">{mesa.estado}</span>
                            </div>
                        ))
                    ) : (
                        <p className="no-mesas">No hay mesas configuradas.</p>
                    )}
                </div>
            </div>
        </div>
    );
};

export default Mesas;