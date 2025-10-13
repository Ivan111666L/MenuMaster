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
import Features from '@/features/home/pages/Features.jsx';
import Pricing from '@/features/home/pages/Pricing.jsx';
import Demo from '@/features/home/pages/Demo.jsx';
import Updates from '@/features/home/pages/Updates.jsx';
import About from '@/features/home/pages/About.jsx';
import Careers from '@/features/home/pages/Careers.jsx';
import Press from '@/features/home/pages/Press.jsx';
import Partners from '@/features/home/pages/Partners.jsx';
import Help from '@/features/home/pages/Help.jsx';
import Contact from '@/features/home/pages/Contact.jsx';
import Status from '@/features/home/pages/Status.jsx';
import Security from '@/features/home/pages/Security.jsx';
import Privacy from '@/features/home/pages/Privacy.jsx';
import Terms from '@/features/home/pages/Terms.jsx';
import Cookies from '@/features/home/pages/Cookies.jsx';
import Licenses from '@/features/home/pages/Licenses.jsx';
import DashboardAdmin from '@/features/dashboard/pages/DashboardAdmin.jsx';

import Facturacion from '@/features/facturacion/pages/Facturacion.jsx';
import Mesas from '@/features/mesas/pages/Mesas.jsx';
import Pedidos from '@/features/pedidos/pages/Pedidos.jsx';
import PedidoEditar from '@/features/pedidos/pages/PedidoEditar.jsx';
import TestPedidos from '@/features/pedidos/pages/TestPedidos.jsx';
import Configuracion from '@/features/configuracion/pages/Configuracion.jsx';

// Páginas anidadas de Inventario
import InventarioLayout from '@/features/inventario/pages/InventarioLayout.jsx';
import InventarioMenu from '@/features/inventario/components/InventarioMenu.jsx';
import IngredienteCreado from '@/features/inventario/pages/IngredienteCreado.jsx';
import IngredienteNuevo from '@/features/inventario/pages/IngredienteNuevo.jsx';
// Cocina removida: se omite paso intermedio
// Páginas anidadas de Productos
import ProductosLayout from '@/features/productos/pages/ProductosLayout.jsx';
import ProductosMenu from '@/features/productos/components/ProductosMenu.jsx';
import ProductosCreados from '@/features/productos/pages/ProductosCreados.jsx';
import ProductoNuevo from '@/features/productos/pages/ProductoNuevos.jsx';
import ConfiguracionUsuarios from '@/features/configuracion/pages/ConfiguracionUsuarios.jsx';
import ConfiguracionMesas from '@/features/configuracion/pages/ConfiguracionMesas.jsx';

// Módulo de Análisis Avanzado
import AnalisisModule from '@/features/analisis';
// Nuevos módulos para acceso completo del administrador
import CategoriaModule from '@/features/categorias/CategoriaModule.jsx';
import NotificacionesModule from '@/features/notificaciones/NotificacionesModule.jsx';
import PagoModule from '@/features/pagos/PagoModule.jsx';
import ProveedoresLista from '@/features/proveedores/pages/ProveedoresLista.jsx';
// MenuDelDia removido junto con módulo de cocina


const router = createBrowserRouter([
  {
    // El elemento raíz que contiene el AuthProvider
    element: <App />,
    // Todas las rutas son hijas de App para tener acceso al contexto
    children: [
      // --- Rutas Públicas ---
      {
        path: '/',
        element: <Navigate to="/home" replace />,
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
      {
        path: '/home',
        element: <Home />,
      },
      { path: '/features', element: <Features /> },
      { path: '/pricing', element: <Pricing /> },
      { path: '/demo', element: <Demo /> },
      { path: '/updates', element: <Updates /> },
      { path: '/about', element: <About /> },
      { path: '/careers', element: <Careers /> },
      { path: '/press', element: <Press /> },
      { path: '/partners', element: <Partners /> },
      { path: '/help', element: <Help /> },
      { path: '/contact', element: <Contact /> },
      { path: '/status', element: <Status /> },
      { path: '/security', element: <Security /> },
      { path: '/privacy', element: <Privacy /> },
      { path: '/terms', element: <Terms /> },
      { path: '/cookies', element: <Cookies /> },
      { path: '/licenses', element: <Licenses /> },

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
            element: <DashboardAdmin />,
          },
          // Ruta de cocina eliminada (flujo directo: Pedido -> Facturación)
          {
            path: 'facturacion',
            element: (
              <PrivateRoute roles={['administrador', 'cajero', 'mesero']}>
                <Facturacion />
              </PrivateRoute>
            ),
          },
          // Ruta de pagos anidada bajo facturación
          {
            path: 'facturacion/pagos',
            element: (
              <PrivateRoute roles={['administrador', 'cajero']}>
                <PagoModule />
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
            path: 'pedidos/test',
            element: (
              <PrivateRoute roles={['administrador', 'mesero']}>
                <TestPedidos />
              </PrivateRoute>
            ),
          },
          {
            path: 'inventario',
            element: (
              <PrivateRoute roles={['administrador', 'cocinero']}>
                <InventarioLayout />
              </PrivateRoute>
            ),
            children: [
              { index: true, element: <InventarioMenu /> },
              { path: 'creados', element: <IngredienteCreado /> },
              { path: 'ver', element: <IngredienteCreado /> },
              { path: 'nuevo', element: <IngredienteNuevo /> },
            ],
          },
          {
            path: 'productos',
            element: (
              <PrivateRoute roles={['administrador', 'cocinero']}>
                <ProductosLayout />
              </PrivateRoute>
            ),
            children: [
              { index: true, element: <ProductosMenu /> },
              { path: 'creados', element: <ProductosCreados /> },
              { path: 'nuevo', element: <ProductoNuevo /> },
              { path: 'nuevos', element: <ProductoNuevo /> },
            ],
          },
          {
            path: 'configuracion',
            element: (
              <PrivateRoute>
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
          // --- Accesos adicionales para administrador ---
          {
            path: 'categorias/*',
            element: (
              <PrivateRoute roles={['administrador']}>
                <CategoriaModule />
              </PrivateRoute>
            ),
          },
          {
            path: 'notificaciones/*',
            element: (
              <PrivateRoute roles={['administrador']}>
                <NotificacionesModule />
              </PrivateRoute>
            ),
          },
          {
            path: 'pagos',
            element: (
              <PrivateRoute roles={['administrador']}>
                <PagoModule />
              </PrivateRoute>
            ),
          },
          {
            path: 'proveedores',
            element: (
              <PrivateRoute roles={['administrador']}>
                <ProveedoresLista />
              </PrivateRoute>
            ),
          },          {
            path: 'pedidos/editar',
            element: (
              <PrivateRoute roles={['administrador', 'mesero']}>
                <PedidoEditar />
              </PrivateRoute>
            ),
          },
          {
            path: 'pedidos/editar/:pedidoId',
            element: (
              <PrivateRoute roles={['administrador', 'mesero']}>
                <PedidoEditar />
              </PrivateRoute>
            ),
          },
// Subruta de cocina eliminada
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


