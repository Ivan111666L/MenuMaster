import React, { useState, useEffect, useCallback } from 'react';
import { useAuth } from '@/context/AuthContext';
import dashboardService from '@/features/dashboard/services/dashboardService';
import Spinner from '@/components/Spinner';
import WaiterDashboard from '@/features/dashboard/pages/WaiterDashboard';
import CookDashboard from '@/features/dashboard/pages/CookDashboard';

// Importamos los estilos desde su propio archivo
import '@/styles/dashboard.css';

function Dashboard() {
    const { user } = useAuth(); // Obtenemos al usuario logueado del contexto
    
    // Renderizar dashboard específico según el rol del usuario
    const renderRoleSpecificDashboard = () => {
        const userRole = user?.rol?.toLowerCase();
        
        switch (userRole) {
            case 'mesero':
                return <WaiterDashboard />;
            case 'cocinero':
                return <CookDashboard />;
            case 'administrador':
            case 'admin':
            default:
                // Para administradores y roles no especificados, mostrar dashboard completo
                return <AdminDashboard />;
        }
    };

    // Si no hay usuario, mostrar spinner
    if (!user) {
        return <Spinner />;
    }

    // Renderizar el dashboard apropiado
    return renderRoleSpecificDashboard();
}

// Componente del dashboard de administrador (dashboard original)
function AdminDashboard() {
    const { user } = useAuth();
    const [data, setData] = useState(null);
    const [isLoading, setIsLoading] = useState(true);
    const [error, setError] = useState(null);
    const [lastUpdated, setLastUpdated] = useState(null);
    const [isRefreshing, setIsRefreshing] = useState(false);

    // Función para cargar datos del dashboard
    const fetchData = useCallback(async (showRefreshIndicator = false) => {
        try {
            if (showRefreshIndicator) {
                setIsRefreshing(true);
            } else {
                setIsLoading(true);
            }
            
            setError(null);
            const summaryData = await dashboardService.getSummary();
            setData(summaryData);
            setLastUpdated(new Date());
        } catch (err) {
            setError('No se pudieron cargar los datos del dashboard.');
            console.error('Dashboard fetch error:', err);
            
            // Si no tenemos datos, usar datos por defecto
            if (!data) {
                setData(dashboardService.getDefaultDashboardData());
            }
        } finally {
            setIsLoading(false);
            setIsRefreshing(false);
        }
    }, [data]);

    // useEffect para cargar los datos cuando el componente se monta
    useEffect(() => {
        // Solo hacer la llamada si hay un usuario autenticado
        if (user && user.token) {
            fetchData();
        }
    }, [fetchData, user]);

    // useEffect para auto-refresh cada 30 segundos
    useEffect(() => {
        const interval = setInterval(() => {
            // Solo hacer refresh si hay usuario autenticado y no estamos cargando
            if (user && user.token && !isLoading && !isRefreshing) {
                fetchData(true); // Usar indicador de refresh
            }
        }, 30000); // 30 segundos

        return () => clearInterval(interval);
    }, [fetchData, isLoading, isRefreshing, user]);

    // Función para refresh manual
    const handleManualRefresh = () => {
        fetchData(true);
    };

    // 1. Mientras carga, mostramos el Spinner
    if (isLoading && !data) {
        return <Spinner />;
    }

    // 2. Si hay un error y no tenemos datos, mostramos un mensaje
    if (error && !data) {
        return (
            <div className="error-message">
                <p>{error}</p>
                <button onClick={() => fetchData()} className="retry-button">
                    Reintentar
                </button>
            </div>
        );
    }
    
    // 3. Si todo está bien, mostramos el dashboard con los datos reales
    const maxSales = data && data.ventasSemanales ? Math.max(...data.ventasSemanales.map(d => d.sales)) : 0;

    return (
        <div className="dashboard-app">
            <div className="main-content">
                <header className="dashboard-header">
                    <div>
                        <h1>Panel de Control</h1>
                        <p>Bienvenido de nuevo, {user?.nombre || 'usuario'}.</p>
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
                        <h3>Pedidos Activos</h3>
                        <p className="widget-value">{data?.pedidosActivos || 0}</p>
                        <span className="widget-subtitle">En preparación</span>
                    </div>
                    <div className="widget">
                        <h3>Ventas del Día</h3>
                        <p className="widget-value">${(data?.ventasDia || 0).toLocaleString('es-CO')}</p>
                        <span className="widget-subtitle">Ingresos hoy</span>
                    </div>
                    <div className="widget">
                        <h3>Mesas Ocupadas</h3>
                        <p className="widget-value">{`${data?.mesasOcupadas || 0}/${data?.mesasTotales || 0}`}</p>
                        <span className="widget-subtitle">
                            {data?.mesasTotales > 0 ? `${Math.round((data.mesasOcupadas / data.mesasTotales) * 100)}% ocupación` : 'Sin datos'}
                        </span>
                    </div>
                    <div className="widget">
                        <h3>Inventario Bajo</h3>
                        <p className="widget-value">{data?.inventarioBajo || 0} items</p>
                        <span className="widget-subtitle">Requieren restock</span>
                    </div>
                </div>

                <div className="analysis-section">
                    <div className="chart-container">
                        <h3>Ventas de la Última Semana</h3>
                        <div className="sales-chart">
                            {data?.ventasSemanales && data.ventasSemanales.length > 0 ? (
                                data.ventasSemanales.map((d, index) => (
                                    <div key={index} className="chart-bar-group" title={`$${(d.sales || 0).toLocaleString('es-CO')}`}>
                                        <div 
                                            className="chart-bar" 
                                            style={{ height: maxSales > 0 ? `${(d.sales / maxSales) * 100}%` : '5%' }}
                                        ></div>
                                        <span className="chart-label">{d.day}</span>
                                    </div>
                                ))
                            ) : (
                                <div className="no-data">No hay datos de ventas disponibles</div>
                            )}
                        </div>
                    </div>
                    <div className="top-products-container">
                        <h3>Productos Populares</h3>
                        {data?.topProductos && data.topProductos.length > 0 ? (
                            <ul className="product-list">
                                {data.topProductos.map((product, index) => (
                                     <li key={product.id || index}>
                                        <span className="product-name">{product.name || 'Producto sin nombre'}</span>
                                        <span className="product-sales">{product.sales || 0} vendidos</span>
                                    </li>
                                ))}
                            </ul>
                        ) : (
                            <div className="no-data">No hay datos de productos disponibles</div>
                        )}
                    </div>
                </div>
            </div>
        </div>
    );
}

export default Dashboard;