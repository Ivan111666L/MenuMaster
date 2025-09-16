import axios from 'axios';
// Importamos las funciones desde el archivo de utilidades.
import { getAuthToken, clearAuthSession } from '@/utils/auth.js';

// 1. Creamos la instancia de Axios.
const api = axios.create({
  // CORRECCIÓN: Se usa la variable de entorno para apuntar al backend.
  // Esto leerá la URL correcta: 'http://localhost/MenuMaster/menumaster-backend/public/api'
  baseURL: import.meta.env.VITE_API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
  },
});

// 2. Interceptor de Peticiones: Añade el token a cada llamada.
// (Esta parte ya estaba perfecta)
api.interceptors.request.use(
  (config) => {
    const token = getAuthToken();
    if (token) {
      config.headers['Authorization'] = `Bearer ${token}`;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// 3. Interceptor de Respuestas: Maneja errores globales como tokens expirados.
// (Esta parte también estaba perfecta)
api.interceptors.response.use(
  (response) => response,
  (error) => {
    // Si el token expiró o no es válido (error 401), cerramos la sesión.
    if (error.response && error.response.status === 401) {
      clearAuthSession();
      window.location.href = '/login'; 
    }
    return Promise.reject(error);
  }
);

export default api;