import api from '@/services/api'; // Importar la instancia central de Axios

const getAllIngredientes = async () => {
  try {
    const response = await api.get('/ingredientes');
    return response.data.data;
  } catch (error) {
    console.error('Error al obtener todos los ingredientes:', error);
    throw error;
  }
};


const getIngredienteById = async (id) => {
  try {
    const response = await api.get(`/api/ingredientes/${id}`);
    return response.data.data;
  } catch (error) {
    console.error(`Error al obtener el ingrediente ${id}:`, error);
    throw error;
  }
};

const crearIngrediente = async (ingredienteData) => {
  try {
    const response = await api.post('/ingredientes', ingredienteData);
    return response.data.data;
  } catch (error) {
    console.error("Error al crear ingrediente:", error);
    throw error;
  }
};

const actualizarIngrediente = async (id, ingredienteData) => {
  try {
    const response = await api.put(`/api/ingredientes/${id}`, ingredienteData);
    return response.data.data;
  } catch (error) {
    console.error(`Error al actualizar el ingrediente ${id}:`, error);
    throw error;
  }
};

const eliminarIngrediente = async (id) => {
  try {
    await api.delete(`/api/ingredientes/${id}`);
  } catch (error) {
    console.error(`Error al eliminar el ingrediente ${id}:`, error);
    throw error;
  }
};

const cambiarCantidad = async (id, cantidad) => {
  try {
    const response = await api.put(`/api/ingredientes/${id}/cantidad`, { cantidad });
    return response.data.data;
  } catch (error) {
    console.error(`Error al cambiar cantidad del ingrediente ${id}:`, error);
    throw error;
  }
};

export { 
  getAllIngredientes,
  getIngredienteById, 
  crearIngrediente, 
  actualizarIngrediente, 
  eliminarIngrediente, 
  cambiarCantidad 
};;