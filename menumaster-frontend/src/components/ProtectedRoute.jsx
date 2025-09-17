// src/components/ProtectedRoute.jsx
import React from 'react';
import { Navigate } from 'react-router-dom';
import { useAuth } from '@/context/AuthContext';

function ProtectedRoute({ children }) {
  const { isAuthenticated, isLoading } = useAuth();

  if (isLoading) return null; // o puedes mostrar un Spinner

  return isAuthenticated ? children : <Navigate to="/login" replace />;
}

export default ProtectedRoute;
