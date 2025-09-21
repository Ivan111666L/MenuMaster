import axios from 'axios';

const API_URL = '/api/inventario';

export const getInventario = async () => {
  const res = await axios.get(API_URL);
  return res.data;
};

export const getMovimientos = async () => {
  const res = await axios.get(`${API_URL}/movimientos`);
  return res.data;
};

export const createMovimiento = async (data) => {
  const res = await axios.post(`${API_URL}/movimientos`, data);
  return res.data;
};

export const getAlertasStock = async () => {
  const res = await axios.get(`${API_URL}/alertas`);
  return res.data;
};
