import api from '@/services/api';

export const getPagos = async () => {
  const res = await api.get('/pagos');
  return res.data;
};

export const createPago = async (data) => {
  const res = await api.post('/pagos', data);
  return res.data;
};

export const getMetodosPago = async () => {
  const res = await api.get('/pagos/metodos');
  return res.data;
};

export const createMetodoPago = async (data) => {
  const res = await api.post('/pagos/metodos', data);
  return res.data;
};
