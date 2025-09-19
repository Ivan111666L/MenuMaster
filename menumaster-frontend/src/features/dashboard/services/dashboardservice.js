import api from '@/services/api';

/**
 * Obtiene los datos de resumen para el panel de control.
 * El token de autenticación se añade automáticamente por el interceptor de 'api.js'.
 * @returns {Promise<Object>} Datos de resumen del dashboard
 */
const getSummary = async () => {
    try {
        const response = await api.get('/dashboard/summary');
        
        // Verificar que tenemos la estructura esperada
        if (response.data && response.data.success && response.data.data) {
            const data = response.data.data;
            
            // Asegurar que todos los campos necesarios están presentes con valores por defecto
            return {
                pedidosActivos: data.pedidosActivos || 0,
                ventasDia: data.ventasDia || 0,
                mesasOcupadas: data.mesasOcupadas || 0,
                mesasTotales: data.mesasTotales || 0,
                inventarioBajo: data.inventarioBajo || 0,
                ventasSemanales: Array.isArray(data.ventasSemanales) ? data.ventasSemanales : generateEmptyWeeklySales(),
                topProductos: Array.isArray(data.topProductos) ? data.topProductos : []
            };
        } else {
            console.warn('Estructura de respuesta inesperada:', response.data);
            return getDefaultDashboardData();
        }
    } catch (error) {
        console.error("Error al obtener resumen del dashboard:", error);
        
        // Si es un error de conexión, mostrar datos vacíos pero informativos
        if (error.code === 'NETWORK_ERROR' || error.response?.status >= 500) {
            console.error('Error de conexión con el servidor');
        } else if (error.response?.status === 401) {
            console.error('Token de autenticación inválido');
            // Aquí podrías disparar un logout automático
        }
        
        return getDefaultDashboardData();
    }
};

/**
 * Genera datos de ventas semanales vacíos para los últimos 7 días
 */
const generateEmptyWeeklySales = () => {
    const days = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
    const today = new Date();
    const weeklySales = [];
    
    for (let i = 6; i >= 0; i--) {
        const date = new Date(today);
        date.setDate(today.getDate() - i);
        const dayName = days[date.getDay()];
        
        weeklySales.push({
            day: dayName,
            sales: 0
        });
    }
    
    return weeklySales;
};

/**
 * Devuelve estructura de datos por defecto para el dashboard
 */
const getDefaultDashboardData = () => {
    return {
        pedidosActivos: 0,
        ventasDia: 0,
        mesasOcupadas: 0,
        mesasTotales: 0,
        inventarioBajo: 0,
        ventasSemanales: generateEmptyWeeklySales(),
        topProductos: []
    };
};

/**
 * Obtiene los datos de dashboard con refresh automático
 * @param {number} intervalSeconds - Intervalo en segundos para auto-refresh (opcional)
 * @returns {Promise<Object>} Datos de resumen del dashboard
 */
const getSummaryWithRefresh = async (intervalSeconds = null) => {
    const data = await getSummary();
    
    if (intervalSeconds && intervalSeconds > 0) {
        // Programar próxima actualización
        setTimeout(() => {
            // Aquí podrías emitir un evento o usar un callback para actualizar
            console.log('Auto-refresh dashboard data...');
        }, intervalSeconds * 1000);
    }
    
    return data;
};

const dashboardService = {
    getSummary,
    getSummaryWithRefresh,
    getDefaultDashboardData,
};

export default dashboardService;