import { Routes, Route } from 'react-router-dom';
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


export default function AppRouter() {
	   return (
		   <Routes>
			   <Route path="/pedidos" element={<Pedidos />} />
			   <Route path="/mesas" element={<Mesas />} />
			   <Route path="/dashboard" element={<Dashboard />} />
			   <Route path="/inventario" element={<InventarioLayout />} />
			   <Route path="/pagos" element={<Pagos />} />
			   <Route path="/facturacion" element={<Facturacion />} />
			   <Route path="/notificaciones" element={<Notificaciones />} />
			   <Route path="/productos" element={<ProductosLayout />} />
			   <Route path="/productos/creados" element={<ProductosCreados />} />
			   <Route path="/productos/nuevo" element={<ProductoNuevo />} />
			   <Route path="/configuracion/usuarios" element={<ConfiguracionUsuarios />} />
			   <Route path="/configuracion/mesas" element={<ConfiguracionMesas />} />
		   </Routes>
	   );
}