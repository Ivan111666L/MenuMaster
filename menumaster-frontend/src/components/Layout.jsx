import { Outlet, Link } from "react-router-dom";
import { useAuth } from "@/context/AuthContext"; // <-- Importa nuestro hook personalizado
import {
  FaHome, FaClipboardList, FaBoxes, FaFileInvoiceDollar,
  FaChartBar, FaCog, FaSignOutAlt,
} from "react-icons/fa";
import "@/styles/global.css";

const Layout = () => {
    // Obtenemos el usuario y la función de logout directamente del contexto
    const { user, logout } = useAuth();

    // BUG CORREGIDO: Usamos 'user.rol' en lugar de 'user.role' para coincidir con la API
    const rol = user?.rol;

    // --- LÓGICA DE MENÚS MEJORADA Y MÁS CLARA ---
    const getMenuItems = () => {
        // Menús base para cada rol. Así es más fácil de mantener.
        const baseMenus = {
            administrador: [
                { path: "/dashboard", label: "Dashboard", icon: <FaBoxes /> },
                { path: "/productos", label: "Productos", icon: <FaHome /> },
                { path: "/inventario", label: "Inventario", icon: <FaClipboardList /> },
                { path: "/facturacion", label: "Facturación", icon: <FaChartBar /> },
                { path: "/pedidos", label: "Pedidos", icon: <FaClipboardList /> },
                { path: "/mesas", label: "Mesas", icon: <FaFileInvoiceDollar /> },
                { path: "/configuracion", label: "Configuracion", icon: <FaCog /> }, // Ruta para gestionar usuarios
            ],
            mesero: [
                { path: "/mesas", label: "Mesas", icon: <FaFileInvoiceDollar /> },
                { path: "/pedidos", label: "Pedidos", icon: <FaClipboardList /> },
                
            ],
            cocinero: [
                { path: "/cocina", label: "Cocina", icon: <FaClipboardList /> },
                { path: "/inventario", label: "Inventario", icon: <FaClipboardList /> },
                { path: "/cocina/menudia", label: "Menú del Día", icon: <FaHome /> }
            ],
        };
        // Devolvemos el menú correspondiente al rol, o un array vacío si no hay rol.
        return baseMenus[rol] || [];
    };

    const menuItems = getMenuItems();

    return (
        <div className="dashboard-container">
            <div className="sidebar">
                <div className="sidebar-header">
                    <h2>MenuMaster</h2>
                </div>
                <ul className="sidebar-menu">
                    {menuItems.map((item, index) => (
                        <li key={index}>
                            <Link to={item.path}>
                                <span className="sidebar-icon">{item.icon}</span> {item.label}
                            </Link>
                        </li>
                    ))}
                </ul>
            </div>

            <div className="content-wrapper">
                <header className="top-bar">
                    {/* La información se muestra solo si hay un usuario en el contexto */}
                    {user && (
                        <div className="user-info">
                            <div className="user-details">
                                {/* BUG CORREGIDO: Usamos 'user.nombre' */}
                                <span className="user-name">{user.nombre}</span>
                                <span className="user-role">{user.rol}</span>
                            </div>
                            {/* La función logout ahora viene del contexto */}
                            <button onClick={logout} className="logout-button" title="Cerrar Sesión">
                                <FaSignOutAlt />
                            </button>
                        </div>
                    )}
                </header>

                <main className="main-content">
                    <Outlet />
                </main>
            </div>
        </div>
    );
};

export default Layout;