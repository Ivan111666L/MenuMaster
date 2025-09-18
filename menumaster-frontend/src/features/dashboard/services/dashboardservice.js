import api from '@/services/api';

/**
 * Obtiene los datos de resumen para el panel de control.
 * El token de autenticación se añade automáticamente por el interceptor de 'api.js'.
 * @returns {Promise<Object>} Datos de resumen del dashboard
 */
const getSummary = async () => {
    try {
        const response = await api.get('/dashboard/summary');
        return response.data.data;
    } catch (error) {
        console.error("Error al obtener resumen del dashboard:", error);
        // Devolvemos datos vacíos para evitar errores en la UI
        return {
            pedidosActivos: 0,
            ventasDia: 0,
            mesasOcupadas: 0,
            mesasTotales: 0,
            inventarioBajo: 0,
            ventasSemanales: [],
            topProductos: []
        };
    }
};

const dashboardService = {
    getSummary,
};

export default dashboardService;