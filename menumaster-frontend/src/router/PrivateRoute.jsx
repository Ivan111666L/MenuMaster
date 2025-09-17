import React from 'react';
import { Navigate, Outlet } from 'react-router-dom';
import { useAuth } from '@/context/AuthContext';

function PrivateRoute({ roles, children }) {
  const { isAuthenticated, isLoading, rol } = useAuth();

  if (isLoading) return null; // o un <Spinner /> si prefieres

  if (!isAuthenticated) {
    return <Navigate to="/login" replace />;
  }

  if (roles && !roles.includes(rol)) {
    return <Navigate to="/unauthorized" replace />;
  }

  return children || <Outlet />;
  
}

export default PrivateRoute;
