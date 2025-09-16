import React, { useState, useEffect } from 'react';
import { useAuth } from '@/context/AuthContext';
import dashboardService from '../services/dashboardService';
import Spinner from '@/components/Spinner';

// Importamos los estilos desde su propio archivo
import '@/styles/dashboard.css';

function Dashboard() {
    const { user } = useAuth(); // Obtenemos al usuario logueado del contexto
    const [data, setData] = useState(null);
    const [isLoading, setIsLoading] = useState(true);
    const [error, setError] = useState(null);

    // useEffect para cargar los datos cuando el componente se monta
    useEffect(() => {
        const fetchData = async () => {
            try {
                const summaryData = await dashboardService.getSummary();
                setData(summaryData);
            } catch (err) {
                setError('No se pudieron cargar los datos del dashboard.');
                console.error(err);
            } finally {
                setIsLoading(false);
            }
        };

        fetchData();
    }, []); // El array vacío asegura que se ejecute solo una vez

    // 1. Mientras carga, mostramos el Spinner
    if (isLoading) {
        return <Spinner />;
    }

    // 2. Si hay un error, mostramos un mensaje
    if (error) {
        return <div className="error-message">{error}</div>;
    }
    
    // 3. Si todo está bien, mostramos el dashboard con los datos reales
    const maxSales = data ? Math.max(...data.ventasSemanales.map(d => d.sales)) : 0;

    return (
        <div className="dashboard-app">
            <div className="main-content">
                <header className="dashboard-header">
                    {/* Saludamos al usuario por su nombre */}
                    <h1>Panel de Control</h1>
                    <p>Bienvenido de nuevo, {user?.nombre || 'usuario'}.</p>
                </header>

                <div className="dashboard-widgets">
                    <div className="widget">
                        <h3>Pedidos Activos</h3>
                        <p className="widget-value">{data.pedidosActivos}</p>
                    </div>
                    <div className="widget">
                        <h3>Ventas del Día</h3>
                        <p className="widget-value">${data.ventasDia.toLocaleString('es-CO')}</p>
                    </div>
                    <div className="widget">
                        <h3>Mesas Ocupadas</h3>
                        <p className="widget-value">{`${data.mesasOcupadas}/${data.mesasTotales}`}</p>
                    </div>
                    <div className="widget">
                        <h3>Inventario Bajo</h3>
                        <p className="widget-value">{data.inventarioBajo} items</p>
                    </div>
                </div>

                <div className="analysis-section">
                    <div className="chart-container">
                        <h3>Ventas de la Última Semana</h3>
                        <div className="sales-chart">
                            {data.ventasSemanales.map(d => (
                                <div key={d.day} className="chart-bar-group" title={`$${d.sales.toLocaleString('es-CO')}`}>
                                    <div 
                                        className="chart-bar" 
                                        style={{ height: maxSales > 0 ? `${(d.sales / maxSales) * 100}%` : '0%' }}
                                    ></div>
                                    <span className="chart-label">{d.day}</span>
                                </div>
                            ))}
                        </div>
                    </div>
                    <div className="top-products-container">
                        <h3>Productos Populares</h3>
                        <ul className="product-list">
                            {data.topProductos.sort((a,b) => b.sales - a.sales).map(product => (
                                 <li key={product.id}>
                                    <span className="product-name">{product.name}</span>
                                    <span className="product-sales">{product.sales} vendidos</span>
                                </li>
                            ))}
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default Dashboard;