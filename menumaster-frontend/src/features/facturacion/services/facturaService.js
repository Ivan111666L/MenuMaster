import axios from 'axios';

const API_URL = '/api/facturas';

export const getFacturas = async () => {
  const res = await axios.get(API_URL);
  return res.data;
};

export const getFactura = async (id) => {
  const res = await axios.get(`${API_URL}/${id}`);
  return res.data;
};

export const reimprimirFactura = async (id) => {
  const res = await axios.post(`${API_URL}/${id}/reimprimir`);
  return res.data;
};
