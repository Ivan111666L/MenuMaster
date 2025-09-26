import api from '@/services/api';

export const getNotificaciones = async () => {
  const res = await api.get('/notificaciones');
  return res.data;
};

export const marcarLeida = async (id) => {
  const res = await api.post(`/notificaciones/${id}/leida`);
  return res.data;
};

export const getLogs = async () => {
  const res = await api.get('/logs');
  return res.data;
};
