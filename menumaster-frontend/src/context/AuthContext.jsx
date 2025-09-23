
// src/context/AuthContext.jsx
import React, {
  createContext,
  useContext,
  useState,
  useEffect,
  useCallback,
  useMemo
} from 'react';
import { useNavigate } from 'react-router-dom';
import authService from '@/features/auth/services/authService';

const AuthContext = createContext(null);

const INACTIVITY_LIMIT_MS = 15 * 60 * 1000; // 15 minutes
const LOCAL_STORAGE_KEY = 'auth_session';
const TOKEN_EXPIRATION_BUFFER_S = 30; // security margin

export function AuthProvider({ children }) {
  const [session, setSession] = useState(null);
  const [isLoading, setIsLoading] = useState(true);
  const navigate = useNavigate();

  // Load session from localStorage
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
      console.error("Error loading session:", error);
      localStorage.removeItem(LOCAL_STORAGE_KEY);
    } finally {
      setIsLoading(false);
    }
  }, []);

  // Logout handler
  const logout = useCallback(() => {
    authService.logout();
    setSession(null);
    localStorage.removeItem(LOCAL_STORAGE_KEY);
    navigate('/login', { replace: true });
  }, [navigate]);

  // Login handler
  const login = useCallback(async (credentials) => {
    try {
      const sessionData = await authService.login(credentials);

      if (!sessionData?.user || !sessionData?.token || !sessionData?.expiraEn) {
        console.error("Incomplete session data:", sessionData);
        throw new Error("Server response missing required data");
      }

      const newSession = {
        user: sessionData.user,
        token: sessionData.token,
        expiraEn: sessionData.expiraEn
      };

      localStorage.setItem(LOCAL_STORAGE_KEY, JSON.stringify(newSession));
      setSession(newSession);

      const roleRedirects = {
        administrador: '/dashboard',
        cajero: '/facturacion',
        cocinero: '/cocina',
        mesero: '/mesas'
      };
      const path = roleRedirects[sessionData.user.rol] || '/dashboard';
      navigate(path);

      return newSession;
    } catch (error) {
      console.error("Login failed in AuthContext:", error);
      localStorage.removeItem(LOCAL_STORAGE_KEY);
      setSession(null);
      throw error;
    }
  }, [navigate]);

  // Tab synchronization
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

  // Auto expiration and inactivity handling
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

    return () => {
      clearTimeout(expirationTimer);
      clearTimeout(inactivityTimer);
      events.forEach(event => window.removeEventListener(event, resetInactivityTimer));
    };
  }, [session, logout]);

  const value = useMemo(() => ({
    user: session?.user || null,
    token: session?.token || null,
    rol: session?.user?.rol || null,
    isAuthenticated: !!session?.token,
    isLoading,
    login,
    logout
  }), [session, isLoading, login, logout]);

  return (
    <AuthContext.Provider value={value}>
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth() {
  const context = useContext(AuthContext);
  if (context === null) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return context;
}

export default AuthProvider;
