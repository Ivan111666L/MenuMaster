/**
 * URL base del backend.
 * Se utiliza una variable de entorno de Vite para mayor flexibilidad.
 * Si la variable no está definida, se usa un valor por defecto para desarrollo.
 */
export const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://localhost/MenuMaster/menumaster-backend/public/index.php/api';

// Aquí podrías agregar otras constantes que uses en tu app
// export const APP_NAME = 'Menu Master';
// export const DEFAULT_THEME = 'light';