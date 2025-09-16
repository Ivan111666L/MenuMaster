import api from '../api/api'; // Importar la misma instancia configurada.

const ENDPOINT = 'routes/ingredientes_api.php';

async function handleRequest(request) {
  try {
    const response = await request();
    return response.data;
  } catch (error) {
    console.error("Error en la solicitud de ingredientes:", error.response?.data?.error || error.message);
    throw error;
  }
}

// --- Funciones del Servicio de Ingredientes ---

export const getIngredienteById = (id) => {
  return handleRequest(() => api.get(ENDPOINT, { params: { action: 'obtenerPorId', id } }));
};

export const crearIngrediente = (ingredienteData) => {
  return handleRequest(() => api.post(ENDPOINT, ingredienteData, { params: { action: 'crear' } }));
};

export const actualizarIngrediente = (id, ingredienteData) => {
  return handleRequest(() => api.put(ENDPOINT, ingredienteData, { params: { action: 'actualizar', id } }));
};

export const eliminarIngrediente = (id) => {
  return handleRequest(() => api.delete(ENDPOINT, { params: { action: 'eliminar', id } }));
};