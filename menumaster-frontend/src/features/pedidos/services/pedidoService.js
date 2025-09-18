import api from '@/services/api'; // Tu instancia central de Axios

/**
 * Obtiene los datos iniciales necesarios para tomar un pedido:
 * la lista de productos disponibles y la lista de mesas.
 */
const getTomaPedidoData = async () => {
    // Hacemos una petición a un nuevo endpoint que debe devolver ambos listados.
    axios.get('http://localhost:8000/api/toma-pedido-data')
    return response.data.data;
};

/**
 * Crea un nuevo pedido en el sistema.
 * @param {object} pedidoData - Datos del pedido (mesa_id, items, etc.).
 */
const createPedido = async (pedidoData) => {
    const response = await api.post('/pedidos', pedidoData);
    return response.data.data;
};

const pedidoService = {
    getTomaPedidoData,
    createPedido,
};

export default pedidoService;