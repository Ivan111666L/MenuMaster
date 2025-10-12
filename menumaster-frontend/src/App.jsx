import React from 'react';
import { Outlet } from 'react-router-dom';
import { AuthProvider } from '@/context/AuthContext';
import { ToastContainer } from 'react-toastify';
import '@/styles/notifications.css'; // Estilos personalizados para notificaciones

function App() {
  return (
    <AuthProvider>
      <Outlet />
      <ToastContainer
        position="top-right"
        autoClose={3000}
        hideProgressBar={false}
        newestOnTop={true}
        closeOnClick
        rtl={false}
        pauseOnFocusLoss
        draggable
        pauseOnHover
        theme="colored"
        limit={5}
        toastClassName="custom-toast"
        bodyClassName="custom-toast-body"
        progressClassName="custom-toast-progress"
        closeButton={true}
        icon={true}
        style={{
          fontSize: '14px',
          fontFamily: 'inherit'
        }}
      />
    </AuthProvider>
  );
}

export default App;