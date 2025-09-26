import { createBrowserRouter, Navigate } from 'react-router-dom';

// --- Componentes de la Arquitectura ---
import App from '@/App';
import Layout from '@/components/Layout.jsx';
import PrivateRoute from '@/router/PrivateRoute.jsx';
import NotFoundPage from '@/features/common/pages/NotFoundPage.jsx';
import Unauthorized from '@/features/common/pages/Unauthorized.jsx';
import ForgotPassword from '@/features/auth/pages/ForgotPassword';
import ResetPassword from '@/features/auth/pages/ResetPassword';

import Login from '@/features/auth/pages/Login';
import Register from '@/features/auth/pages/Register';
import Home from '@/features/home/pages/Home';
import Dashboard from '@/features/dashboard/pages/Dashboard';

import Facturacion from '@/features/facturacion/pages/Facturacion.jsx';
import Mesas from '@/features/mesas/pages/Mesas.jsx';
import Pedidos from '@/features/pedidos/pages/Pedidos.jsx';
import Configuracion from '@/features/configuracion/pages/Configuracion.jsx';

// Páginas anidadas de Inventario
import InventarioLayout from '@/features/inventario/pages/InventarioLayout.jsx';
import InventarioMenu from '@/features/inventario/components/InventarioMenu.jsx';
import IngredienteCreado from '@/features/inventario/pages/IngredienteCreado.jsx';
import IngredienteNuevo from '@/features/inventario/pages/IngredienteNuevo.jsx';
import Cocina from '@/features/cocina/pages/Cocina.jsx';
// Páginas anidadas de Productos
import ProductosLayout from '@/features/productos/pages/ProductosLayout.jsx';
import ProductosMenu from '@/features/productos/components/ProductosMenu.jsx';
import ProductosCreados from '@/features/productos/pages/ProductosCreados.jsx';
import ProductoNuevo from '@/features/productos/pages/ProductoNuevos.jsx';
import ConfiguracionUsuarios from '@/features/configuracion/pages/ConfiguracionUsuarios.jsx';
import ConfiguracionMesas from '@/features/configuracion/pages/ConfiguracionMesas.jsx';

// Módulo de Análisis Avanzado
import AnalisisModule from '@/features/analisis';


const router = createBrowserRouter([
  {
    // El elemento raíz que contiene el AuthProvider
    element: <App />,
    // Todas las rutas son hijas de App para tener acceso al contexto
    children: [
      // --- Rutas Públicas ---
      {
        path: '/home',
        element: <Home />,
      },
      {
        path: '/login',
        element: <Login />,
      },
      {
        path: '/register',
        element: <Register />,
      },
      {
        path: '/forgot-password',
        element: <ForgotPassword />,
      },
      {
        path: '/reset-password',
        element: <ResetPassword />,
      },
      {
        path: '/unauthorized',
        element: <Unauthorized />,
      },

      // --- Rutas Protegidas con Layout ---
      {
        path: '/',
        element: (
          <PrivateRoute>
            <Layout />
          </PrivateRoute>
        ),
        children: [
          {
            index: true,
            element: <Navigate to="/dashboard" replace />,
          },
          {
            path: 'dashboard',
            element: <Dashboard />,
          },
          {
            path: 'cocina',
            element: (
              <PrivateRoute roles={['administrador', 'cocinero']}>
                <Cocina />
              </PrivateRoute>
            ),
          },
          {
            path: 'facturacion',
            element: (
              <PrivateRoute roles={['administrador', 'cajero', 'mesero']}>
                <Facturacion />
              </PrivateRoute>
            ),
          },
          {
            path: 'mesas',
            element: (
              <PrivateRoute roles={['administrador', 'mesero']}>
                <Mesas />
              </PrivateRoute>
            ),
          },
          {
            path: 'pedidos',
            element: (
              <PrivateRoute roles={['administrador', 'mesero']}>
                <Pedidos />
              </PrivateRoute>
            ),
          },
          {
            path: 'inventario',
            element: <InventarioLayout />,
            children: [
              { index: true, element: <InventarioMenu /> },
              { path: 'ver', element: <IngredienteCreado /> },
              { path: 'nuevo', element: <IngredienteNuevo /> },
            ],
          },
          {
            path: 'productos',
            element: <ProductosLayout />,
            children: [
              { index: true, element: <ProductosMenu /> },
              { path: 'creados', element: <ProductosCreados /> },
              { path: 'nuevos', element: <ProductoNuevo /> },
            ],
          },
          {
            path: 'configuracion',
            element: (
              <PrivateRoute roles={['administrador']}>
                <Configuracion />
              </PrivateRoute>
            ),
            children: [
              {
                path: 'usuarios',
                element: (
                  <PrivateRoute roles={['administrador']}>
                    <ConfiguracionUsuarios />
                  </PrivateRoute>
                ),
              },
              {
                path: 'mesas',
                element: (
                  <PrivateRoute roles={['administrador']}>
                    <ConfiguracionMesas />
                  </PrivateRoute>
                ),
              },
            ],
          },
          {
            path: 'analisis/*',
            element: (
              <PrivateRoute roles={['administrador']}>
                <AnalisisModule />
              </PrivateRoute>
            ),
          },
        ],
      },

      // --- Ruta 404 ---
      {
        path: '*',
        element: <NotFoundPage />,
      },
    ],
  },
]);

export default router;

