// CORRECCIÓN: Se importa la instancia 'api' en lugar de 'axios' y 'getAuthToken'.
import api from '@/services/api';

/**
 * Obtiene los datos de resumen para el panel de control.
 * El token de autenticación se añade automáticamente por el interceptor de 'api.js'.
 */
const getSummary = async () => {
    // CORRECCIÓN: Se usa 'api.get' y una ruta relativa.
    // Ya no es necesario obtener y añadir el token manualmente.
    const response = await api.get('/dashboard/summary');
    
    // Devolvemos la data que está dentro de la respuesta de la API.
    return response.data.data;
};

const dashboardService = {
    getSummary,
};

export default dashboardService;