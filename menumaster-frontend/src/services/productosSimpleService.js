// Servicio simple para productos que funciona directamente con el endpoint sin autenticación
const BASE_URL = 'http://localhost/MenuMaster/menumaster-backend/public';

export const getProductosDisponibles = async () => {
  try {
    const response = await fetch(`${BASE_URL}/simple_productos.php`, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
      },
    });

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    const data = await response.json();
    
    if (data.success && data.data) {
      console.log(`Productos disponibles cargados: ${data.data.length}`);
      return data.data;
    } else {
      console.error('Error en respuesta:', data.error || 'Formato incorrecto');
      return [];
    }
  } catch (error) {
    console.error('Error al obtener productos:', error);
    return [];
  }
};

export const getProductosByCategoriaNombre = (productos, categoriaNombre) => {
  if (!productos || !Array.isArray(productos)) {
    return [];
  }
  
  if (!categoriaNombre) {
    return productos;
  }
  
  return productos.filter(producto => 
    producto.categoria_nombre && 
    producto.categoria_nombre.toLowerCase().includes(categoriaNombre.toLowerCase())
  );
};

export const getCategoriesFromProducts = (productos) => {
  if (!productos || !Array.isArray(productos)) {
    return [];
  }
  
  const categorias = [...new Set(productos.map(p => p.categoria_nombre).filter(Boolean))];
  return categorias.sort();
};

export default {
  getProductosDisponibles,
  getProductosByCategoriaNombre,
  getCategoriesFromProducts
};