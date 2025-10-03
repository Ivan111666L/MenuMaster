import api from '@/services/api';

const getProveedores = async () => {
  const response = await api.get('/proveedores');
  return response.data.data;
};

const getProveedorById = async (id) => {
  const response = await api.get(`/proveedores/${id}`);
  return response.data.data;
};

const createProveedor = async (data) => {
  const response = await api.post('/proveedores', data);
  return response.data.data;
};

const updateProveedor = async (id, data) => {
  const response = await api.put(`/proveedores/${id}`, data);
  return response.data.data;
};

const deleteProveedor = async (id) => {
  const response = await api.delete(`/proveedores/${id}`);
  return response.data.success;
};

export default {
  getProveedores,
  getProveedorById,
  createProveedor,
  updateProveedor,
  deleteProveedor,
  // Relaciones proveedor-ingrediente
  asociarIngrediente: async (proveedorId, ingredienteId) => {
    const response = await api.post(`/proveedores/${proveedorId}/asociar-ingrediente`, { ingrediente_id: ingredienteId });
    return response.data.success;
  },
  desasociarIngrediente: async (ingredienteId) => {
    const response = await api.delete(`/proveedores/desasociar-ingrediente/${ingredienteId}`);
    return response.data.success;
  }
};
