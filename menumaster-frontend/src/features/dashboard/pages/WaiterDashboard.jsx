import React, { useState, useEffect, useCallback } from 'react';
import { useAuth } from '@/context/AuthContext';
import dashboardService from '@/features/dashboard/services/dashboardService';
import Spinner from '@/components/Spinner';
import '@/styles/dashboard.css';

function WaiterDashboard() {
    const { user } = useAuth();
    const [data, setData] = useState(null);
    const [isLoading, setIsLoading] = useState(true);
    const [error, setError] = useState(null);
    const [lastUpdated, setLastUpdated] = useState(null);
    const [isRefreshing, setIsRefreshing] = useState(false);

    // Función para cargar datos específicos del mesero
    const fetchWaiterData = useCallback(async (showRefreshIndicator = false) => {
        try {
            if (showRefreshIndicator) {
                setIsRefreshing(true);
            } else {
                setIsLoading(true);
            }
            
            setError(null);
            const waiterData = await dashboardService.getWaiterSummary();
            setData(waiterData);
            setLastUpdated(new Date());
        } catch (err) {
            setError('No se pudieron cargar los datos del dashboard.');
            console.error('Waiter dashboard fetch error:', err);
            
            if (!data) {
                setData(dashboardService.getDefaultWaiterData());
            }
        } finally {
            setIsLoading(false);
            setIsRefreshing(false);
        }
    }, [data]);

    useEffect(() => {
        // Solo hacer la llamada si hay un usuario autenticado
        if (user && user.token) {
            fetchWaiterData();
        }
    }, [fetchWaiterData, user]);

    useEffect(() => {
        const interval = setInterval(() => {
            // Solo hacer refresh si hay usuario autenticado y no estamos cargando
            if (user && user.token && !isLoading && !isRefreshing) {
                fetchWaiterData(true);
            }
        }, 30000);

        return () => clearInterval(interval);
    }, [fetchWaiterData, isLoading, isRefreshing, user]);

    const handleManualRefresh = () => {
        fetchWaiterData(true);
    };

    if (isLoading && !data) {
        return <Spinner />;
    }

    if (error && !data) {
        return (
            <div className="error-message">
                <p>{error}</p>
                <button onClick={() => fetchWaiterData()} className="retry-button">
                    Reintentar
                </button>
            </div>
        );
    }

    return (
        <div className="dashboard-app waiter-dashboard">
            <div className="main-content">
                <header className="dashboard-header">
                    <div>
                        <h1>Panel del Mesero</h1>
                        <p>Bienvenido, {user?.nombre || 'Mesero'}. Aquí tienes tu información del día.</p>
                    </div>
                    <div className="dashboard-actions">
                        {lastUpdated && (
                            <span className="last-updated">
                                Última actualización: {lastUpdated.toLocaleTimeString()}
                            </span>
                        )}
                        <button 
                            onClick={handleManualRefresh} 
                            className={`refresh-button ${isRefreshing ? 'refreshing' : ''}`}
                            disabled={isRefreshing}
                        >
                            {isRefreshing ? '🔄' : '↻'} {isRefreshing ? 'Actualizando...' : 'Actualizar'}
                        </button>
                    </div>
                </header>

                {error && (
                    <div className="error-banner">
                        ⚠️ {error} - Mostrando últimos datos disponibles
                    </div>
                )}

                <div className="dashboard-widgets">
                    <div className="widget">
                        <h3>Mis Ventas del Día</h3>
                        <p className="widget-value">${(data?.misVentasDia || 0).toLocaleString('es-CO')}</p>
                        <span className="widget-subtitle">Total vendido hoy</span>
                    </div>
                    <div className="widget">
                        <h3>Mis Pedidos Activos</h3>
                        <p className="widget-value">{data?.misPedidosActivos || 0}</p>
                        <span className="widget-subtitle">En preparación</span>
                    </div>
                    <div className="widget">
                        <h3>Mesas Asignadas</h3>
                        <p className="widget-value">{data?.mesasAsignadas || 0}</p>
                        <span className="widget-subtitle">Bajo mi responsabilidad</span>
                    </div>
                    <div className="widget">
                        <h3>Comisión del Día</h3>
                        <p className="widget-value">${(data?.comisionDia || 0).toLocaleString('es-CO')}</p>
                        <span className="widget-subtitle">5% de mis ventas</span>
                    </div>
                </div>

                <div className="analysis-section">
                    <div className="chart-container">
                        <h3>Productos Disponibles Hoy</h3>
                        <div className="products-grid">
                            {data?.productosDisponibles && data.productosDisponibles.length > 0 ? (
                                data.productosDisponibles.map((product, index) => (
                                    <div key={product.id || index} className="product-card">
                                        <div className="product-info">
                                            <h4>{product.nombre || 'Producto sin nombre'}</h4>
                                            <p className="product-price">${(product.precio || 0).toLocaleString('es-CO')}</p>
                                            <span className={`product-status ${product.disponible ? 'available' : 'unavailable'}`}>
                                                {product.disponible ? '✅ Disponible' : '❌ Agotado'}
                                            </span>
                                        </div>
                                    </div>
                                ))
                            ) : (
                                <div className="no-data">No hay productos disponibles</div>
                            )}
                        </div>
                    </div>

                    <div className="menu-container">
                        <h3>Menú del Día</h3>
                        {data?.menuDelDia && data.menuDelDia.length > 0 ? (
                            <div className="menu-items">
                                {data.menuDelDia.map((item, index) => (
                                    <div key={item.id || index} className="menu-item">
                                        <div className="menu-item-info">
                                            <h4>{item.nombre || 'Plato sin nombre'}</h4>
                                            <p className="menu-description">{item.descripcion || 'Sin descripción'}</p>
                                            <span className="menu-price">${(item.precio || 0).toLocaleString('es-CO')}</span>
                                        </div>
                                        <div className="menu-category">
                                            <span className="category-badge">{item.categoria || 'General'}</span>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        ) : (
                            <div className="no-data">No hay menú especial para hoy</div>
                        )}
                    </div>
                </div>

                <div className="ranking-section">
                    <h3>Ranking de Meseros del Día</h3>
                    {data?.rankingMeseros && data.rankingMeseros.length > 0 ? (
                        <div className="ranking-list">
                            {data.rankingMeseros.map((mesero, index) => (
                                <div key={mesero.id || index} className={`ranking-item ${mesero.id === user?.id ? 'current-user' : ''}`}>
                                    <div className="ranking-position">
                                        <span className="position-number">#{index + 1}</span>
                                    </div>
                                    <div className="ranking-info">
                                        <h4>{mesero.nombre || 'Mesero'}</h4>
                                        <p>Ventas: ${(mesero.ventas || 0).toLocaleString('es-CO')}</p>
                                        <p>Pedidos: {mesero.pedidos || 0}</p>
                                    </div>
                                    <div className="ranking-badge">
                                        {index === 0 && '🥇'}
                                        {index === 1 && '🥈'}
                                        {index === 2 && '🥉'}
                                        {mesero.id === user?.id && '👤'}
                                    </div>
                                </div>
                            ))}
                        </div>
                    ) : (
                        <div className="no-data">No hay datos de ranking disponibles</div>
                    )}
                </div>
            </div>
        </div>
    );
}

export default WaiterDashboard;