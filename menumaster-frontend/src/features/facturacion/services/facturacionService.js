import api from '@/services/api'; // Tu instancia central de Axios

/**
 * Obtiene los pedidos que están listos para ser facturados
 */
const getPedidosParaFacturar = async () => {
<<<<<<< HEAD
    const response = await api.get('/pedidos?estado=listo para servir');
    return response.data.data;
=======
    try {
        const response = await api.get('/api/pedidos', {
            params: {
                estado: 'listo'  // Asumiendo que este es el estado correcto en la base de datos
            }
        });
        return response.data;
    } catch (error) {
        throw error.response?.data || { error: 'Error al obtener los pedidos' };
    }
>>>>>>> 08efd0c4780d33dc8d783703a7238e0d6b0d370a
};

/**
 * Marca un pedido como 'facturado' en el backend.
 * @param {number} pedidoId - El ID del pedido a facturar.
 * @param {object} datosPago - Información del pago (método, personas, etc.).
 */
const facturarPedido = async (pedidoId, datosPago) => {
    try {
        const response = await api.post(`/api/pedidos/${pedidoId}/facturar`, datosPago);
        return response.data;
    } catch (error) {
        throw error.response?.data || { error: 'Error al facturar el pedido' };
    }
};

const facturacionService = {
    getPedidosParaFacturar,
    facturarPedido,
};

export default facturacionService;