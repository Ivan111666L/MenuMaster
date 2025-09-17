// src/utils/auth.js
const LOCAL_STORAGE_KEY = 'auth_session';

/**
 * Devuelve el token JWT o null si no existe.
 */
export function getAuthToken() {
  try {
    const session = JSON.parse(localStorage.getItem(LOCAL_STORAGE_KEY));
    return session?.token || null;
  } catch {
    return null;
  }
}

/**
 * Borra la sesión (token + user) de localStorage.
 */
export function clearAuthSession() {
  localStorage.removeItem(LOCAL_STORAGE_KEY);
}
