import React, { useState, useEffect } from 'react';
import dashboardService from '@/features/dashboard/services/dashboardService';
import '@/styles/dashboard.css';

const TestDashboard = () => {
    const [data, setData] = useState(null);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState('');
    const [rawResponse, setRawResponse] = useState(null);

    const fetchDashboardData = async () => {
        setLoading(true);
        setError('');
        setRawResponse(null);

        try {
            // Hacer llamada directa a la API para ver la respuesta cruda
            const api = (await import('@/services/api')).default;
            const rawApiResponse = await api.get('/dashboard/summary');
            setRawResponse(rawApiResponse.data);

            // Usar el servicio de dashboard
            const dashboardData = await dashboardService.getSummary();
            setData(dashboardData);
        } catch (err) {
            setError('Error al obtener datos: ' + (err.response?.data?.error || err.message));
            console.error('Dashboard test error:', err);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchDashboardData();
    }, []);

    return (
        <div className="app-container">
            <div className="productos-form-wrapper">
                <h1 className="productos-title">Test Dashboard Data</h1>
                <p className="productos-description">
                    Prueba de conectividad y datos del dashboard
                </p>

                <div style={{ marginBottom: '2rem' }}>
                    <button
                        onClick={fetchDashboardData}
                        disabled={loading}
                        style={{
                            padding: '0.75rem 1.5rem',
                            background: loading ? '#ccc' : '#007bff',
                            color: 'white',
                            border: 'none',
                            borderRadius: '4px',
                            cursor: loading ? 'not-allowed' : 'pointer'
                        }}
                    >
                        {loading ? 'Cargando...' : 'Actualizar Datos'}
                    </button>
                </div>

                {error && (
                    <div style={{ 
                        color: 'red', 
                        background: '#ffe6e6', 
                        padding: '1rem', 
                        borderRadius: '4px',
                        marginBottom: '1rem'
                    }}>
                        {error}
                    </div>
                )}

                {data && (
                    <div style={{ marginBottom: '2rem' }}>
                        <h3>Dashboard Widgets Preview</h3>
                        <div className="dashboard-widgets">
                            <div className="widget">
                                <h3>Pedidos Activos</h3>
                                <p className="widget-value">{data.pedidosActivos || 0}</p>
                                <span className="widget-subtitle">En preparación</span>
                            </div>
                            <div className="widget">
                                <h3>Ventas del Día</h3>
                                <p className="widget-value">${(data.ventasDia || 0).toLocaleString('es-CO')}</p>
                                <span className="widget-subtitle">Ingresos hoy</span>
                            </div>
                            <div className="widget">
                                <h3>Mesas Ocupadas</h3>
                                <p className="widget-value">{`${data.mesasOcupadas || 0}/${data.mesasTotales || 0}`}</p>
                                <span className="widget-subtitle">
                                    {data.mesasTotales > 0 ? `${Math.round((data.mesasOcupadas / data.mesasTotales) * 100)}% ocupación` : 'Sin datos'}
                                </span>
                            </div>
                            <div className="widget">
                                <h3>Inventario Bajo</h3>
                                <p className="widget-value">{data.inventarioBajo || 0} items</p>
                                <span className="widget-subtitle">Requieren restock</span>
                            </div>
                        </div>

                        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '2rem' }}>
                            <div>
                                <h4>Ventas Semanales</h4>
                                {data.ventasSemanales && data.ventasSemanales.length > 0 ? (
                                    <div style={{ background: '#f5f5f5', padding: '1rem', borderRadius: '4px' }}>
                                        {data.ventasSemanales.map((day, index) => (
                                            <div key={index} style={{ 
                                                display: 'flex', 
                                                justifyContent: 'space-between',
                                                padding: '0.5rem 0',
                                                borderBottom: '1px solid #ddd'
                                            }}>
                                                <span>{day.day}</span>
                                                <span>${(day.sales || 0).toLocaleString('es-CO')}</span>
                                            </div>
                                        ))}
                                    </div>
                                ) : (
                                    <p>No hay datos de ventas semanales</p>
                                )}
                            </div>

                            <div>
                                <h4>Top Productos</h4>
                                {data.topProductos && data.topProductos.length > 0 ? (
                                    <div style={{ background: '#f5f5f5', padding: '1rem', borderRadius: '4px' }}>
                                        {data.topProductos.map((product, index) => (
                                            <div key={index} style={{ 
                                                display: 'flex', 
                                                justifyContent: 'space-between',
                                                padding: '0.5rem 0',
                                                borderBottom: '1px solid #ddd'
                                            }}>
                                                <span>{product.name}</span>
                                                <span>{product.sales} vendidos</span>
                                            </div>
                                        ))}
                                    </div>
                                ) : (
                                    <p>No hay datos de productos populares</p>
                                )}
                            </div>
                        </div>
                    </div>
                )}

                <div style={{ marginTop: '2rem' }}>
                    <h3>Debug Info</h3>
                    <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '1rem' }}>
                        <div>
                            <h4>Raw API Response:</h4>
                            <pre style={{ 
                                background: '#f5f5f5', 
                                padding: '1rem', 
                                borderRadius: '4px',
                                fontSize: '0.8rem',
                                overflow: 'auto',
                                maxHeight: '300px'
                            }}>
                                {rawResponse ? JSON.stringify(rawResponse, null, 2) : 'No data'}
                            </pre>
                        </div>
                        <div>
                            <h4>Processed Dashboard Data:</h4>
                            <pre style={{ 
                                background: '#f5f5f5', 
                                padding: '1rem', 
                                borderRadius: '4px',
                                fontSize: '0.8rem',
                                overflow: 'auto',
                                maxHeight: '300px'
                            }}>
                                {data ? JSON.stringify(data, null, 2) : 'No data'}
                            </pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default TestDashboard;