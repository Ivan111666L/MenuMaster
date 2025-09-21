import api from '@/services/api';

export const getRoles = async () => {
  const response = await api.get('/roles');
  return response.data.data;
};

export const getRolById = async (id) => {
  const response = await api.get(`/roles/${id}`);
  return response.data.data;
};

export const createRol = async (data) => {
  const response = await api.post('/roles', data);
  return response.data.data;
};

export const updateRol = async (id, data) => {
  const response = await api.put(`/roles/${id}`, data);
  return response.data.data;
};

export const deleteRol = async (id) => {
  const response = await api.delete(`/roles/${id}`);
  return response.data.success;
};
