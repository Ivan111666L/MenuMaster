import api from '@/services/api'; // Tu instancia central de Axios
import { getMesasDisponibles } from '@/services/mesasService';
import { getProductos } from '@/services/productosService';

/**
 * Obtiene los datos iniciales necesarios para tomar un pedido:
 * la lista de productos disponibles y la lista de mesas disponibles.
 */
const getTomaPedidoData = async () => {
    try {
        // Obtenemos productos y mesas disponibles en paralelo
        const [productos, mesas] = await Promise.all([
            getProductos(),
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

const pedidoService = {
    getTomaPedidoData,
    createPedido,
};

export default pedidoService;