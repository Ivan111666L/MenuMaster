import axios from 'axios';

const API_URL = '/api/pagos';

export const getPagos = async () => {
  const res = await axios.get(API_URL);
  return res.data;
};

export const createPago = async (data) => {
  const res = await axios.post(API_URL, data);
  return res.data;
};

export const getMetodosPago = async () => {
  const res = await axios.get(`${API_URL}/metodos`);
  return res.data;
};

export const createMetodoPago = async (data) => {
  const res = await axios.post(`${API_URL}/metodos`, data);
  return res.data;
};
