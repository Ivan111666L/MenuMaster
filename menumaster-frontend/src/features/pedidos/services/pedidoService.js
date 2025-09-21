import api from '@/services/api'; // Tu instancia central de Axios
import { getMesasDisponibles } from '@/services/mesasService';
import { getProductosDisponibles } from '@/services/productosSimpleService';

/**
 * Obtiene los datos iniciales necesarios para tomar un pedido:
 * la lista de productos disponibles y la lista de mesas disponibles.
 */
const getTomaPedidoData = async () => {
    try {
        // Obtenemos productos y mesas disponibles en paralelo
        const [productos, mesas] = await Promise.all([
            getProductosDisponibles(),
            getMesasDisponibles()
        ]);
        
        return {
            productos,
            mesas
        };
    } catch (error) {
        console.error('Error al obtener datos para toma de pedido:', error);
        return { productos: [], mesas: [] };
    }
};

/**
 * Crea un nuevo pedido en el sistema.
 * @param {object} pedidoData - Datos del pedido (mesa_id, items, etc.).
 */
const createPedido = async (pedidoData) => {
    const response = await api.post('/api/pedidos', pedidoData);
    return response.data.data;
};


/**
 * Obtiene la lista de pedidos creados en la base de datos
 */
const getPedidos = async () => {
    try {
    const response = await api.get('/api/pedidos');
        if (response.data && response.data.success) {
            return response.data.data;
        } else {
            console.error('Error al obtener pedidos:', response.data.error || response.statusText);
            return [];
        }
    } catch (error) {
        if (error.response && typeof error.response.data === 'string' && error.response.data.startsWith('<')) {
            console.error('Error: El backend devolvió HTML en vez de JSON. Verifica la ruta y el proxy.');
        } else {
            console.error('Error al obtener pedidos:', error);
        }
        return [];
    }
};

const pedidoService = {
    getTomaPedidoData,
    createPedido,
    getPedidos,
    /**
     * Obtiene el ticket HTML de un pedido por su ID
     */
    getPedidoTicket: async (pedidoId) => {
        const response = await api.get(`/imprimir_pedido.php?id=${pedidoId}`);
        return response.data.data ? response.data.data : response.data;
    },
};

export default pedidoService;