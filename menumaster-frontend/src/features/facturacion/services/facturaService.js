import api from '@/services/api';

export const getFacturas = async () => {
  const res = await api.get('/facturas');
  return res.data;
};

export const getFactura = async (id) => {
  const res = await api.get(`/facturas/${id}`);
  return res.data;
};

export const reimprimirFactura = async (id) => {
  const res = await api.post(`/facturas/${id}/reimprimir`);
  return res.data;
};
