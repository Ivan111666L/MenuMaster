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


const router = createBrowserRouter([
  {
    // El elemento raíz que contiene el AuthProvider
    element: <App />,
    // Todas las rutas son hijas de App para tener acceso al contexto
    children: [
      // --- Rutas Públicas ---
      {
        path: '/',
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

      // --- Nido de Rutas Protegidas ---
      // Todas estas rutas requieren login y se muestran dentro del Layout
      {
        element: (
          <PrivateRoute>
            <Layout />
          </PrivateRoute>
        ),
        children: [
          {
            path: '/dashboard', // CORRECCIÓN: La ruta raíz de los protegidos
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

// import { createBrowserRouter, Navigate } from 'react-router-dom';

// import Layout from './components/Layout';
// import Login from './pages/Login';
// import Register from './pages/Register';
// import Home from './pages/Home';
// import Dashboard from './pages/Dashboard';
// import Cocina from './pages/Cocina';
// import Facturacion from './pages/Facturacion';
// import Inventario from './pages/Inventario';
// import Pedidos from './pages/Pedidos';
// import Configuracion from './pages/Configuracion';
// import ConfiguracionMesas from './pages/ConfiguracionMesas';
// import ConfiguracionUsuarios from './pages/ConfiguracionUsuarios';
// import Mesas from './pages/Mesas';
// import PedidoForm from './components/PedidoForm';
// import PedidoResumen from './components/PedidoResumen';
// import Productos from './pages/Productos';
// import Unauthorized from './pages/Unauthorized';
// import ProductosCreados from './pages/ProductosCreados';
// import ProductosNuevos from './pages/ProductoNuevos';
// import IngredienteCreado from './pages/IngredienteCreado';
// import IngredienteNuevo from './pages/IngredienteNuevo';
// import MenuDelDia from './pages/MenuDelDia';
// import ForgotPassword from './pages/ForgotPassword';

// const router = createBrowserRouter([
//   {
//     path: '/',
//     element: <Navigate to="/home" replace />
//   },
//   {
//     path: '/home',
//     element: <Home />
//   },
//   {
//     path: '/login',
//     element: <Login />
//   },
//   {
//     path: '/register',
//     element: <Register />
//   },
//   {
//     path: '/unauthorized',
//     element: <Unauthorized />
//   },
//   {path: '/forgotpassword',
//      element: <ForgotPassword/>
// },
//   // Rutas protegidas dentro del Layout principal
//   {
//     element:<Layout/>,
//     children:[
//     {
//   },
//   {
//     path: '/dashboard',
//     element: <Dashboard />
//   },
//   {
//     path: '/cocina',
//     element: <Cocina /> 
//   },
//   {
//     path: '/facturacion',
//     element: <Facturacion />
//   },
//   {
//     path: '/inventario',
//     element: <Inventario />
//   },
//   {
//     path: '/inventario/creandoingrediente',
//     element: <IngredienteCreado />
//   },
//   {
//     path: '/inventario/nuevoingrediente',
//     element: <IngredienteNuevo />
//   },
//   {
//     path: '/pedidos',
//     element: <Pedidos />
//   },
//   {
//     path: '/cocina/menudia',
//     element: <MenuDelDia />
//   },
//   {
//     path: '/mesas',
//     element: <Mesas />
//   },
//   {
//     path: '/configuracion',
//     element: <Configuracion />
//   },
//   {
//     path: '/configuracion/mesas',
//     element: <ConfiguracionMesas />
//   },
//   {
//     path: '/configuracion/usuarios',
//     element: <ConfiguracionUsuarios />
//   },
//   {
//     path: '/pedidos/form',
//     element: <PedidoForm />
//   },
//   {
//     path: '/pedidos/resumen',
//     element: <PedidoResumen />
//   },
//   {
//     path: '/productos',
//     element: <Productos />
//   },
//   {
//     path: '/productos/nuevos',
//     element: <ProductosNuevos />
//   },
//   {
//     path: '/productos/creados',
//     element: <ProductosCreados />
//   }

//   ]
//  }
// ]);

// export default router;