import { Routes, Route } from 'react-router-dom';
import ConfiguracionMesas from '@/features/configuracion/pages/ConfiguracionMesas.jsx';
import ConfiguracionUsuarios from '@/features/configuracion/pages/ConfiguracionUsuarios.jsx';
import CategoriaModule from '@/features/categorias';
import MesaModule from '@/features/mesas';
import DashboardModule from '@/features/dashboard';
import InventarioModule from '@/features/inventario';
import PagoModule from '@/features/pagos';
import FacturaModule from '@/features/facturacion';
import NotificacionesModule from '@/features/notificaciones';

export default function AppRouter() {
	   return (
		   <Routes>
			   <Route path="/categorias" element={<CategoriaModule />} />
			   <Route path="/mesas" element={<MesaModule />} />
			   <Route path="/dashboard" element={<DashboardModule />} />
			   <Route path="/inventario" element={<InventarioModule />} />
			   <Route path="/pagos" element={<PagoModule />} />
			   <Route path="/facturacion" element={<FacturaModule />} />
			   <Route path="/notificaciones" element={<NotificacionesModule />} />
			   <Route path="/configuracion/usuarios" element={<ConfiguracionUsuarios />} />
			   <Route path="/configuracion/mesas" element={<ConfiguracionMesas />} />
		   </Routes>
	   );
}