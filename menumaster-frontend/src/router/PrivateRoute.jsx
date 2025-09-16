import React from 'react';
import PropTypes from 'prop-types';
import { Navigate } from 'react-router-dom';
import { useAuth } from '@/context/AuthContext'; // Hook del contexto de autenticación

const PrivateRoute = ({ children, allowedRoles = [] }) => {
  const { user, rol } = useAuth(); // Obtén datos del contexto

  // Si no hay un usuario autenticado, redirige al login
  if (!user) {
    return <Navigate to="/login" replace />;
  }

  // Si el usuario no tiene uno de los roles permitidos
  if (allowedRoles.length > 0 && !allowedRoles.includes(rol)) {
    return <Navigate to="/unauthorized" replace />;
  }

  // Acceso permitido
  return children;
};

PrivateRoute.propTypes = {
  children: PropTypes.node.isRequired,
  allowedRoles: PropTypes.arrayOf(PropTypes.string) // Asegura que los roles sean un array de strings
};

export default PrivateRoute;