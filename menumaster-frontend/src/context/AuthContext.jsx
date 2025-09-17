
import React, { createContext, useContext, useState, useEffect, useCallback, useMemo } from 'react';
import { useNavigate } from 'react-router-dom';
import authService from '@/features/auth/services/authService';

const AuthContext = createContext(null);

// --- Constantes para un código más limpio y fácil de mantener ---
const INACTIVITY_LIMIT_MS = 15 * 60 * 1000; // 15 minutos
const LOCAL_STORAGE_KEY = 'auth_session';
const TOKEN_EXPIRATION_BUFFER_S = 30; // 30 segundos de margen

export function AuthProvider({ children }) {
    const [session, setSession] = useState(null);
    const [isLoading, setIsLoading] = useState(true);
    const navigate = useNavigate();

    // Efecto para cargar la sesión desde localStorage al iniciar la app
    useEffect(() => {
        try {
            const savedSession = JSON.parse(localStorage.getItem(LOCAL_STORAGE_KEY));
            if (savedSession?.token && savedSession?.user && savedSession.expiraEn) {
                const nowInSeconds = Math.floor(Date.now() / 1000);
                if (savedSession.expiraEn > nowInSeconds) {
                    setSession(savedSession);
                } else {
                    localStorage.removeItem(LOCAL_STORAGE_KEY);
                }
            }
        } catch (error) {
            console.error("Error al cargar la sesión:", error);
            localStorage.removeItem(LOCAL_STORAGE_KEY);
        } finally {
            setIsLoading(false); // Termina la carga inicial
        }
    }, []);

    // Función de logout, envuelta en useCallback para optimización
    const logout = useCallback(() => {
        authService.logout(); // El servicio limpia localStorage
        setSession(null);
        navigate('/login', { replace: true });
    }, [navigate]);

    // Función de login, envuelta en useCallback
    const login = useCallback(async (credentials) => {
        try {
            const sessionData = await authService.login(credentials);
            
            const newSession = {
                user: sessionData.user, 


                
                token: sessionData.token,
                expiraEn: sessionData.expiraEn,
            };
            localStorage.setItem(LOCAL_STORAGE_KEY, JSON.stringify(newSession));
            setSession(newSession);
            
            const roleRedirects = {
                administrador: '/dashboard',
                cajero: '/facturacion',
                cocinero: '/cocina',
                mesero: '/mesas',
            };
            const path = roleRedirects[sessionData.user.rol] || '/dashboard';
            navigate(path);
            
            return newSession;

        } catch (error) {
            console.error("Fallo el login en AuthContext:", error);
            localStorage.removeItem(LOCAL_STORAGE_KEY);
            setSession(null);
            throw error;
        }
    }, [navigate]);

    // Efecto para sincronización entre pestañas
    useEffect(() => {
        const handleStorageChange = (event) => {
            if (event.key === LOCAL_STORAGE_KEY) {
                if (!event.newValue) {
                    setSession(null);
                } else {
                    setSession(JSON.parse(event.newValue));
                }
            }
        };
        window.addEventListener('storage', handleStorageChange);
        return () => window.removeEventListener('storage', handleStorageChange);
    }, []);

    // Efecto para gestionar timers de expiración e inactividad
    useEffect(() => {
        if (!session) return;

        const nowInSeconds = Math.floor(Date.now() / 1000);
        const expiresInMs = (session.expiraEn - nowInSeconds - TOKEN_EXPIRATION_BUFFER_S) * 1000;

        if (expiresInMs <= 0) {
            logout();
            return;
        }
        const expirationTimer = setTimeout(logout, expiresInMs);

        let inactivityTimer;
        const resetInactivityTimer = () => {
            clearTimeout(inactivityTimer);
            inactivityTimer = setTimeout(logout, INACTIVITY_LIMIT_MS);
        };
        const events = ['mousemove', 'mousedown', 'keydown', 'touchstart', 'scroll'];
        events.forEach(event => window.addEventListener(event, resetInactivityTimer));
        resetInactivityTimer();

        // Limpieza de timers y eventos
        return () => {
            clearTimeout(expirationTimer);
            clearTimeout(inactivityTimer);
            events.forEach(event => window.removeEventListener(event, resetInactivityTimer));
        };
    }, [session, logout]);

    // Optimizamos el valor del contexto con useMemo
    const value = useMemo(() => ({
        user: session?.user || null,
        token: session?.token || null,
        rol: session?.user?.rol || null,
        isAuthenticated: !!session?.token,
        isLoading,
        login,
        logout,
    }), [session, isLoading, login, logout]);

    return (
        <AuthContext.Provider value={value}>
            {children}
        </AuthContext.Provider>
    );
}

// Hook personalizado para un uso más fácil
export function useAuth() {
    const context = useContext(AuthContext);
    if (context === null) {
        throw new Error('useAuth debe ser usado dentro de un AuthProvider');
    }
    return context;
}