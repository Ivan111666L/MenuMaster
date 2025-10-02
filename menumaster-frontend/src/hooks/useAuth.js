import { useContext } from 'react';
import { AuthContext } from '@/context/AuthContext'; // Asegúrate que la ruta sea correcta

/**
 * Hook personalizado para acceder al contexto de autenticación.
 * Proporciona una forma limpia y segura de obtener el estado y las funciones de AuthContext.
 *
 * @returns {object} El valor del contexto de autenticación (ej: { user, token, login, logout, isAuthenticated }).
 * @throws {Error} Si el hook se usa fuera de un AuthProvider.
 */
export const useAuth = () => {
  const context = useContext(AuthContext);

  if (!context) {
    throw new Error('useAuth debe ser utilizado dentro de un AuthProvider');
  }

  return context;
};

// No necesitas un "export default" si solo exportas una función.
// export default useAuth; // Esto también es válido si prefieres la exportación por defecto.