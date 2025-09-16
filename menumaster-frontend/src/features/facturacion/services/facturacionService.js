import api from '@/services/api'; // Tu instancia central de Axios

/**
 * Obtiene los pedidos que están listos para ser facturados.
 * En la cocina, estos son los pedidos marcados como 'listo para servir'.
 */
const getPedidosParaFacturar = async () => {
    const response = await api.get('/pedidos?estado=listo para servir');
    return response.data.data;
};

/**
 * Marca un pedido como 'facturado' en el backend.
 * @param {number} pedidoId - El ID del pedido a facturar.
 * @param {object} datosPago - Información del pago (método, personas, etc.).
 */
const facturarPedido = async (pedidoId, datosPago) => {
    // Usamos el endpoint que ya creamos en el backend para esto.
    const response = await api.post(`/pedidos/${pedidoId}/facturar`, datosPago);
    return response.data;
};

const facturacionService = {
    getPedidosParaFacturar,
    facturarPedido,
};

export default facturacionService;