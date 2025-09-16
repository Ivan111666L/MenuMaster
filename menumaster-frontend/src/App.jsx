import React from 'react';
import { Outlet } from 'react-router-dom';
import { AuthProvider } from '@/context/AuthContext';

function App() {
  return (
    // AuthProvider envuelve a Outlet para que todas las rutas hijas
    // tengan acceso al contexto de autenticación.
    <AuthProvider>
      <Outlet />
    </AuthProvider>
  );
}

export default App;