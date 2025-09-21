import axios from 'axios';

const API_URL = '/api/notificaciones';

export const getNotificaciones = async () => {
  const res = await axios.get(API_URL);
  return res.data;
};

export const marcarLeida = async (id) => {
  const res = await axios.post(`${API_URL}/${id}/leida`);
  return res.data;
};

const LOGS_URL = '/api/logs';
export const getLogs = async () => {
  const res = await axios.get(LOGS_URL);
  return res.data;
};
