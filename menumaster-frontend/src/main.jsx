import React from 'react';
import ReactDOM from 'react-dom/client';
import { RouterProvider } from 'react-router-dom';
import router from '@/router/router';
import '@/styles/global.css';

ReactDOM.createRoot(document.getElementById('root')).render(
  <React.StrictMode>
    {/* Ahora solo renderizamos el RouterProvider.
        AuthProvider ya está dentro de la definición de nuestras rutas. */}
    <RouterProvider router={router} />
  </React.StrictMode>
);