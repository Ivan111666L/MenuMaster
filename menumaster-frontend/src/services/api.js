// src/services/api.js
import axios from 'axios';
import { getAuthToken, clearAuthSession } from '@/utils/auth.js';

const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || 'http://localhost/MenuMaster/menumaster-backend/public',
  headers: {
    'Content-Type': 'application/json',
  },
});

// Interceptor de peticiones: añade el token y lo loggea
api.interceptors.request.use(
  (config) => {
    const token = getAuthToken();
    console.log('[API] Request interceptor — token:', token);

    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
      console.log('[API] Request interceptor — headers:', config.headers);
    }

    return config;
  },
  (error) => Promise.reject(error)
);

// Interceptor de respuestas: maneja 401 limpiando la sesión y redirigiendo
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      console.warn('[API] 401 Unauthorized — clearing session');
      clearAuthSession();
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);

export default api;
