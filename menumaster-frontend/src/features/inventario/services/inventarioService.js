import api from '@/services/api';

export const getInventario = async () => {
  const res = await api.get('/inventario');
  return res.data;
};

export const getMovimientos = async () => {
  const res = await api.get('/inventario/movimientos');
  return res.data;
};

export const createMovimiento = async (data) => {
  const res = await api.post('/inventario/movimientos', data);
  return res.data;
};

export const getAlertasStock = async () => {
  const res = await api.get('/inventario/alertas');
  return res.data;
};
