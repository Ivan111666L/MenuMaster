
// Se importa únicamente la instancia 'api' que ya está configurada.
import api from '@/services/api';

/**
 * Registra un nuevo usuario.
 * @param {object} userData - Datos del nuevo usuario.
 */
const register = async (userData) => {
  const response = await api.post('/auth/register', userData);
  return response.data;
};

/**
 * Inicia sesión y devuelve los datos de la sesión desde el backend.
 * @param {object} credentials - Email y password del usuario.
 */
const login = async (credentials) => {
  const response = await api.post('/auth/login', credentials);
  console.log("Respuesta del backend login:", response.data);
  // Ajusta esta línea según la estructura real del JSON que devuelve tu backend:
  // Si tu backend devuelve directamente { user, token, expiraEn }, usa:
  return {
  user: response.data.usuario,
  token: response.data.token,
  expiraEn: Math.floor(Date.now() / 1000) + 15 * 60 // o el tiempo que uses
};

  // Si tu backend devuelve { data: { user, token, expiraEn } }, usa:
  // return response.data.data;
};

/**
 * Solicita el restablecimiento de contraseña para un email.
 * @param {object} emailData - Objeto que contiene el email.
 */
const forgotPassword = async (emailData) => {
  const response = await api.post('/auth/forgot-password', emailData);
  return response.data;
};

/**
 * Restablece la contraseña usando el token y la nueva contraseña.
 * @param {object} resetData - Datos para el reseteo.
 */
const resetPassword = async (resetData) => {
  const response = await api.post('/auth/reset-password', resetData);
  return response.data;
};

/**
 * Cierra la sesión eliminando los datos de localStorage.
 */
const logout = () => {
  localStorage.removeItem('auth_session');
};

const authService = {
  register,
  login,
  logout,
  forgotPassword,
  resetPassword,
};

export default authService;
