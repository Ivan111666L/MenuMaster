// CORRECCIÓN: Solo importamos nuestra instancia 'api' ya configurada.
import api from '@/services/api';

// La constante API_URL ya no es necesaria, porque la baseURL está en api.js

/**
 * Registra un nuevo usuario.
 * @param {object} userData - Datos del nuevo usuario (nombre, email, password, rol).
 */
const register = async (userData) => {
  // Se usa 'api.post' y una ruta relativa.
  const response = await api.post('auth/register', userData);
  return response.data;
};

/**
 * Inicia sesión, obtiene un token y guarda la sesión en localStorage.
 * @param {object} credentials - Email y password del usuario.
 */
const login = async (credentials) => {
  // Se usa 'api.post' y una ruta relativa.
  const response = await api.post('auth/login', credentials);
  
  // CORRECCIÓN: Lógica para guardar la sesión completada.
  if (response.data?.token) {
    const session = {
        user: response.data.usuario,
        token: response.data.token,
        expiraEn: response.data.expiraEn,
    };
    localStorage.setItem('auth_session', JSON.stringify(session));
  }
  
  return response.data;
};

/**
 * Solicita el restablecimiento de contraseña para un email.
 * @param {object} emailData - Objeto que contiene el email.
 */
const forgotPassword = async (emailData) => {
  // Se usa 'api.post' y una ruta relativa.
  const response = await api.post('auth/forgot-password', emailData);
  return response.data;
};

/**
 * Restablece la contraseña usando el token y la nueva contraseña.
 * @param {object} resetData - Objeto con token, password y confirmPassword.
 */
const resetPassword = async (resetData) => {
  // Se usa 'api.post' y una ruta relativa.
  const response = await api.post('auth/reset-password', resetData);
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