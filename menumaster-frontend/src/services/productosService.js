import api from './api';

export const getProductos = async () => {
  try {
    // Modificamos para obtener todos los productos activos sin filtros
    const response = await api.get('/productos?todos=true');
    
    // Aseguramos que todos los productos estén disponibles
    if (response.data && response.data.data && Array.isArray(response.data.data)) {
      console.log(`Productos cargados: ${response.data.data.length}`);
      return response.data.data;
    } else if (response.data && Array.isArray(response.data)) {
      console.log(`Productos cargados: ${response.data.length}`);
      return response.data;
    } else {
      console.error('Formato de respuesta incorrecto:', response.data);
      return [];
    }
  } catch (error) {
    console.error('Error al obtener productos:', error);
    // En caso de error, devolvemos un array vacío para evitar errores en la UI
    return [];
  }
};

export const getProductoById = async (id) => {
  try {
    const response = await api.get(`/productos/${id}`);
    return response.data;
  } catch (error) {
    console.error(`Error al obtener producto ${id}:`, error);
    throw error;
  }
};

export const getProductosByCategoria = async (categoriaId) => {
  try {
    const response = await api.get(`/productos/categoria/${categoriaId}`);
    return response.data;
  } catch (error) {
    console.error(`Error al obtener productos de la categoría ${categoriaId}:`, error);
    throw error;
  }
};