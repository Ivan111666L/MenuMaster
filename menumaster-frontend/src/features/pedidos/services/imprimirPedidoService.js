import api from '@/services/api';

// Obtiene el ticket imprimible de un pedido
export const getPedidoTicket = async (pedidoId) => {
  const response = await api.get(`/imprimir_pedido.php?id=${pedidoId}`);
  return response.data;
};

// Genera el ticket HTML para impresión
export const generarTicketHTML = async (pedidoId) => {
  const response = await api.post('/imprimir_pedido.php', { id: pedidoId });
  return response.data.html;
};
