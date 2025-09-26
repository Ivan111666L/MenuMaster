import React from 'react';
import { Navigate, Outlet } from 'react-router-dom';
import { useAuth } from '@/context/AuthContext';

function PrivateRoute({ roles, children }) {
  const { isAuthenticated, isLoading, rol } = useAuth();

  if (isLoading) return null; // o un <Spinner /> si prefieres

  if (!isAuthenticated) {
    return <Navigate to="/login" replace />;
  }

  // Normalizar roles para comparación (tanto mayúscula como minúscula)
  if (roles && roles.length > 0) {
    const normalizedRoles = roles.map(r => r.toLowerCase());
    const userRol = rol ? rol.toLowerCase() : '';
    
    if (!normalizedRoles.includes(userRol)) {
      return <Navigate to="/unauthorized" replace />;
    }
  }

  return children || <Outlet />;
  
}

export default PrivateRoute;
