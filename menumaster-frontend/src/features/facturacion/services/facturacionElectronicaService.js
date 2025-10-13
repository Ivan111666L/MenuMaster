import api from '@/services/api';

export const emitirFacturaElectronica = async (pedidoId, email) => {
  const res = await api.post('/facturacion-electronica/emitir', { pedido_id: pedidoId, email });
  return res.data;
};

export const enviarFacturaPorCorreo = async (pedidoId, email) => {
  const res = await api.post('/facturacion-electronica/enviar-correo', { pedido_id: pedidoId, email });
  return res.data;
};

export const consultarEstadoFactura = async (pedidoId) => {
  const res = await api.get(`/facturacion-electronica/estado?pedido_id=${pedidoId}`);
  return res.data?.data ?? res.data;
};

const service = { emitirFacturaElectronica, enviarFacturaPorCorreo, consultarEstadoFactura };
export default service;