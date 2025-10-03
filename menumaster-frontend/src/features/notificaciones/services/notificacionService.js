import api from '@/services/api';

export const getNotificaciones = async () => {
  const res = await api.get('/notificaciones');
  // Backend responde { success, data }
  return res?.data?.data ?? [];
};

export const marcarLeida = async (id) => {
  const res = await api.post(`/notificaciones/${id}/leida`);
  return res.data;
};

export const getLogs = async () => {
  const res = await api.get('/logs');
  // Backend responde { success, data }
  return res?.data?.data ?? [];
};
