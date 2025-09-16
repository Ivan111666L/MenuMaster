import axios from 'axios';
import { getAuthToken } from '@/utils/auth'; // Se importa la función de ayuda

const API_URL = 'http://localhost/MenuMaster/menumaster-backend/public/api/dashboard';

/**
 * Obtiene los datos de resumen para el panel de control.
 * Requiere autenticación, por lo que se envía el token en la cabecera.
 */
const getSummary = async () => {
    const token = getAuthToken(); // Leemos el token de localStorage
    if (!token) {
        // Si no hay token, no tiene sentido hacer la petición
        throw new Error('No se encontró el token de autenticación.');
    }

    const response = await axios.get(`${API_URL}/summary`, {
        headers: {
            'Authorization': `Bearer ${token}`
        }
    });
    
    // Devolvemos la data que está dentro de la respuesta de la API
    return response.data.data;
};

const dashboardService = {
    getSummary,
};

export default dashboardService;