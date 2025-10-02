import React, { useState, useEffect, useCallback } from 'react';
import { useAuth } from '@/context/AuthContext';
import dashboardService from '@/features/dashboard/services/dashboardService';
import Spinner from '@/components/Spinner';
import '@/styles/dashboard.css';

function CookDashboard() {
    const { user } = useAuth();
    const [data, setData] = useState(null);
    const [isLoading, setIsLoading] = useState(true);
    const [error, setError] = useState(null);
    const [lastUpdated, setLastUpdated] = useState(null);
    const [isRefreshing, setIsRefreshing] = useState(false);

    // Función para cargar datos específicos del cocinero
    const fetchCookData = useCallback(async (showRefreshIndicator = false) => {
        try {
            if (showRefreshIndicator) {
                setIsRefreshing(true);
            } else {
                setIsLoading(true);
            }
            
            setError(null);
            const cookData = await dashboardService.getCookSummary();
            setData(cookData);
            setLastUpdated(new Date());
        } catch (err) {
            setError('No se pudieron cargar los datos del dashboard.');
            console.error('Cook dashboard fetch error:', err);
            
            if (!data) {
                setData(dashboardService.getDefaultCookData());
            }
        } finally {
            setIsLoading(false);
            setIsRefreshing(false);
        }
    }, [data]);

    useEffect(() => {
        // Solo hacer la llamada si hay un usuario autenticado
        if (user && user.token) {
            fetchCookData();
        }
    }, [fetchCookData, user]);

    useEffect(() => {
        const interval = setInterval(() => {
            // Solo hacer refresh si hay usuario autenticado y no estamos cargando
            if (user && user.token && !isLoading && !isRefreshing) {
                fetchCookData(true);
            }
        }, 30000);

        return () => clearInterval(interval);
    }, [fetchCookData, isLoading, isRefreshing, user]);

    const handleManualRefresh = () => {
        fetchCookData(true);
    };

    if (isLoading && !data) {
        return <Spinner />;
    }

    if (error && !data) {
        return (
            <div className="error-message">
                <p>{error}</p>
                <button onClick={() => fetchCookData()} className="retry-button">
                    Reintentar
                </button>
            </div>
        );
    }

    return (
        <div className="dashboard-app cook-dashboard">
            <div className="main-content">
                <header className="dashboard-header">
                    <div>
                        <h1>Panel de Cocina</h1>
                        <p>Bienvenido, {user?.nombre || 'Chef'}. Controla tu cocina desde aquí.</p>
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
                        <h3>Pedidos en Cola</h3>
                        <p className="widget-value">{data?.pedidosEnCola || 0}</p>
                        <span className="widget-subtitle">Esperando preparación</span>
                    </div>
                    <div className="widget">
                        <h3>Pedidos en Preparación</h3>
                        <p className="widget-value">{data?.pedidosEnPreparacion || 0}</p>
                        <span className="widget-subtitle">En proceso</span>
                    </div>
                    <div className="widget">
                        <h3>Alertas de Stock</h3>
                        <p className="widget-value critical">{data?.alertasStock || 0}</p>
                        <span className="widget-subtitle">Ingredientes críticos</span>
                    </div>
                    <div className="widget">
                        <h3>Platos Completados</h3>
                        <p className="widget-value">{data?.platosCompletados || 0}</p>
                        <span className="widget-subtitle">Hoy</span>
                    </div>
                </div>

                <div className="analysis-section">
                    <div className="alerts-container">
                        <h3>🚨 Alertas de Inventario</h3>
                        {data?.stockCritico && data.stockCritico.length > 0 ? (
                            <div className="stock-alerts">
                                {data.stockCritico.map((item, index) => (
                                    <div key={item.id || index} className="alert-item critical">
                                        <div className="alert-info">
                                            <h4>{item.nombre || 'Ingrediente'}</h4>
                                            <p>Stock actual: <span className="stock-amount">{item.cantidad || 0} {item.unidad || 'unidades'}</span></p>
                                            <p>Mínimo requerido: <span className="min-stock">{item.minimo || 0} {item.unidad || 'unidades'}</span></p>
                                        </div>
                                        <div className="supplier-info">
                                            <h5>Proveedor:</h5>
                                            <p><strong>{item.proveedor?.nombre || 'Sin proveedor'}</strong></p>
                                            <p>📞 {item.proveedor?.telefono || 'Sin teléfono'}</p>
                                            <p>✉️ {item.proveedor?.email || 'Sin email'}</p>
                                            {item.proveedor?.telefono && (
                                                <button 
                                                    className="contact-button"
                                                    onClick={() => window.open(`tel:${item.proveedor.telefono}`)}
                                                >
                                                    Llamar Ahora
                                                </button>
                                            )}
                                        </div>
                                    </div>
                                ))}
                            </div>
                        ) : (
                            <div className="no-alerts">
                                ✅ Todos los ingredientes tienen stock suficiente
                            </div>
                        )}
                    </div>

                    <div className="popular-products-container">
                        <h3>🔥 Productos Más Pedidos Hoy</h3>
                        {data?.productosMasPedidos && data.productosMasPedidos.length > 0 ? (
                            <div className="popular-products">
                                {data.productosMasPedidos.map((product, index) => (
                                    <div key={product.id || index} className="popular-product-item">
                                        <div className="product-rank">
                                            <span className="rank-number">#{index + 1}</span>
                                        </div>
                                        <div className="product-details">
                                            <h4>{product.nombre || 'Producto'}</h4>
                                            <p>Pedidos: <strong>{product.cantidad || 0}</strong></p>
                                            <p>Tiempo promedio: <strong>{product.tiempoPromedio || 0} min</strong></p>
                                        </div>
                                        <div className="product-ingredients">
                                            <h5>Ingredientes principales:</h5>
                                            <ul>
                                                {product.ingredientes && product.ingredientes.length > 0 ? (
                                                    product.ingredientes.map((ing, idx) => (
                                                        <li key={idx}>{ing.nombre} ({ing.cantidad} {ing.unidad})</li>
                                                    ))
                                                ) : (
                                                    <li>Sin ingredientes registrados</li>
                                                )}
                                            </ul>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        ) : (
                            <div className="no-data">No hay datos de productos populares</div>
                        )}
                    </div>
                </div>

                <div className="suppliers-section">
                    <h3>📋 Contactos de Proveedores</h3>
                    {data?.proveedores && data.proveedores.length > 0 ? (
                        <div className="suppliers-grid">
                            {data.proveedores.map((proveedor, index) => (
                                <div key={proveedor.id || index} className="supplier-card">
                                    <div className="supplier-header">
                                        <h4>{proveedor.nombre || 'Proveedor'}</h4>
                                        <span className={`supplier-status ${proveedor.activo ? 'active' : 'inactive'}`}>
                                            {proveedor.activo ? '✅ Activo' : '❌ Inactivo'}
                                        </span>
                                    </div>
                                    <div className="supplier-contact">
                                        <p><strong>Contacto:</strong> {proveedor.contacto || 'Sin contacto'}</p>
                                        <p><strong>Teléfono:</strong> {proveedor.telefono || 'Sin teléfono'}</p>
                                        <p><strong>Email:</strong> {proveedor.email || 'Sin email'}</p>
                                    </div>
                                    <div className="supplier-products">
                                        <p><strong>Productos:</strong> {proveedor.productos?.join(', ') || 'Sin productos'}</p>
                                    </div>
                                    <div className="supplier-actions">
                                        {proveedor.telefono && (
                                            <button 
                                                className="contact-button phone"
                                                onClick={() => window.open(`tel:${proveedor.telefono}`)}
                                            >
                                                📞 Llamar
                                            </button>
                                        )}
                                        {proveedor.email && (
                                            <button 
                                                className="contact-button email"
                                                onClick={() => window.open(`mailto:${proveedor.email}`)}
                                            >
                                                ✉️ Email
                                            </button>
                                        )}
                                    </div>
                                </div>
                            ))}
                        </div>
                    ) : (
                        <div className="no-data">No hay proveedores registrados</div>
                    )}
                </div>
            </div>
        </div>
    );
}

export default CookDashboard;