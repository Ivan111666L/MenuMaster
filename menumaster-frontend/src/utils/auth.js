// src/utils/auth.js

const SESSION_KEY = 'auth_session';

/**
 * Obtiene el token de autenticación desde localStorage.
 * @returns {string|null} El token JWT o null si no existe.
 */
export const getAuthToken = () => {
    try {
        const sessionString = localStorage.getItem(SESSION_KEY);
        if (!sessionString) return null;
        
        const session = JSON.parse(sessionString);
        return session.token;
    } catch (error) {
        console.error("Error al obtener la sesión de auth:", error);
        return null;
    }
};

/**
 * Limpia la sesión de localStorage.
 */
export const clearAuthSession = () => {
    localStorage.removeItem(SESSION_KEY);
};