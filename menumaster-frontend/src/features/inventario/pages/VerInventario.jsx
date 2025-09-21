import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import inventarioService from '../services/inventarioService';
import { toast } from 'react-toastify';

function VerInventario() {
    const [inventario, setInventario] = useState([]);
    const [loading, setLoading] = useState(true);
    const [filtro, setFiltro] = useState('');
    const [ordenarPor, setOrdenarPor] = useState('nombre');
    const [orden, setOrden] = useState('asc');
    const navigate = useNavigate();

    useEffect(() => {
        cargarInventario();
    }, []);

    const cargarInventario = async () => {
        try {
            const data = await inventarioService.getInventario();
            setInventario(data);
            setLoading(false);
        } catch (error) {
            toast.error(error.message || 'Error al cargar el inventario');
            setLoading(false);
        }
    };

    const handleFiltroChange = (e) => {
        setFiltro(e.target.value);
    };

    const handleOrdenarPorChange = (campo) => {
        if (campo === ordenarPor) {
            setOrden(orden === 'asc' ? 'desc' : 'asc');
        } else {
            setOrdenarPor(campo);
            setOrden('asc');
        }
    };

    const inventarioFiltrado = inventario
        .filter(item => 
            item.nombre.toLowerCase().includes(filtro.toLowerCase()) ||
            item.categoria.toLowerCase().includes(filtro.toLowerCase())
        )
        .sort((a, b) => {
            const modifier = orden === 'asc' ? 1 : -1;
            if (ordenarPor === 'nombre') {
                return a.nombre.localeCompare(b.nombre) * modifier;
            } else if (ordenarPor === 'cantidad') {
                return (a.cantidad - b.cantidad) * modifier;
            }
            return 0;
        });

    if (loading) {
        return (
            <div className="loading-container">
                <div className="spinner"></div>
                <p>Cargando inventario...</p>
            </div>
        );
    }

    return (
        <div className="inventario-container">
            <div className="inventario-header">
                <h1>Inventario</h1>
                <div className="inventario-actions">
                    <input
                        type="text"
                        placeholder="Buscar en inventario..."
                        value={filtro}
                        onChange={handleFiltroChange}
                        className="search-input"
                    />
                </div>
            </div>

            <div className="table-responsive">
                <table className="inventory-table">
                    <thead>
                        <tr>
                            <th onClick={() => handleOrdenarPorChange('nombre')}>
                                Nombre {ordenarPor === 'nombre' && (orden === 'asc' ? '↑' : '↓')}
                            </th>
                            <th onClick={() => handleOrdenarPorChange('cantidad')}>
                                Cantidad {ordenarPor === 'cantidad' && (orden === 'asc' ? '↑' : '↓')}
                            </th>
                            <th>Unidad</th>
                            <th>Categoría</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        {inventarioFiltrado.map((item) => (
                            <tr key={item.id}>
                                <td>{item.nombre}</td>
                                <td>{item.cantidad}</td>
                                <td>{item.unidad}</td>
                                <td>{item.categoria}</td>
                                <td>
                                    <span className={`estado-badge ${item.cantidad <= item.stock_minimo ? 'bajo' : 'normal'}`}>
                                        {item.cantidad <= item.stock_minimo ? 'Bajo Stock' : 'Normal'}
                                    </span>
                                </td>
                                <td>
                                    <button
                                        onClick={() => navigate(`/inventario/movimiento/${item.id}`)}
                                        className="btn-action"
                                    >
                                        Registrar Movimiento
                                    </button>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </div>
    );
}

export default VerInventario;