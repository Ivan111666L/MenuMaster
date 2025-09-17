import React from 'react';
import { Outlet } from 'react-router-dom';
import { AuthProvider } from './context/AuthContext';
import { ToastContainer } from 'react-toastify'; // Importar el contenedor
import 'react-toastify/dist/ReactToastify.css'; // Importar los estilos

function App() {
  return (
    <AuthProvider>
      <Outlet />
      {/* Añade el ToastContainer aquí */}
      <ToastContainer
        position="top-right"
        autoClose={3000}
        hideProgressBar={false}
        newestOnTop={false}
        closeOnClick
        rtl={false}
        pauseOnFocusLoss
        draggable
        pauseOnHover
        theme="light"
      />
    </AuthProvider>
  );
}

export default App;