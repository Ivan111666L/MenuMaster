import { Outlet, Link } from "react-router-dom";
import React, { useState } from "react";
import { useAuth } from "@/context/AuthContext"; // <-- Importa nuestro hook personalizado
import {
  FaHome, FaClipboardList, FaBoxes, FaFileInvoiceDollar,
  FaChartBar, FaCog, FaSignOutAlt,
} from "react-icons/fa";
import "@/styles/global.css";

const Layout = () => {
    // Estado para mostrar/ocultar el menú en móvil
    const [sidebarOpen, setSidebarOpen] = useState(false);

    // Detecta si es móvil
    const isMobile = window.innerWidth <= 768;

    // Cierra el menú al navegar (solo móvil)
    const handleMenuClick = () => {
        if (isMobile) setSidebarOpen(false);
    };
    // Obtenemos el usuario y la función de logout directamente del contexto
    const { user, logout } = useAuth();

    // BUG CORREGIDO: Usamos 'user.rol' en lugar de 'user.role' para coincidir con la API
    const rol = user?.rol;

    // --- LÓGICA DE MENÚS MEJORADA Y MÁS CLARA ---
    const getMenuItems = () => {
        // Menús base para cada rol. Así es más fácil de mantener.
        const baseMenus = {
            administrador: [
                { path: "/dashboard", label: "Dashboard", icon: <FaChartBar /> },
                { path: "/productos", label: "Productos", icon: <FaHome /> },
                { path: "/inventario", label: "Inventario", icon: <FaClipboardList /> },
                { path: "/facturacion", label: "Facturación", icon: <FaFileInvoiceDollar /> },
                { path: "/pedidos", label: "Pedidos", icon: <FaClipboardList /> },
                { path: "/mesas", label: "Mesas", icon: <FaFileInvoiceDollar /> },
                { path: "/analisis", label: "Análisis Avanzado", icon: <FaChartBar /> },
                { path: "/configuracion", label: "Configuracion", icon: <FaCog /> }, // Ruta para gestionar usuarios
            ],
            gerente: [
                { path: "/dashboard", label: "Dashboard", icon: <FaChartBar /> },
                { path: "/productos", label: "Productos", icon: <FaHome /> },
                { path: "/inventario", label: "Inventario", icon: <FaClipboardList /> },
                { path: "/pedidos", label: "Pedidos", icon: <FaClipboardList /> },
                { path: "/facturacion", label: "Facturación", icon: <FaFileInvoiceDollar /> },
                { path: "/mesas", label: "Mesas", icon: <FaFileInvoiceDollar /> },
                { path: "/analisis", label: "Análisis Avanzado", icon: <FaChartBar /> }
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
            {/* Botón hamburguesa solo en móvil */}
            {isMobile && (
                <button className="sidebar-toggle" onClick={() => setSidebarOpen(!sidebarOpen)}>
                    ☰
                </button>
            )}
            <div className={`sidebar${isMobile ? (sidebarOpen ? ' open' : ' closed') : ''}`}>
                <div className="sidebar-header">
                    <h2>MenuMaster</h2>
                </div>
                <ul className="sidebar-menu">
                    {menuItems.map((item, index) => (
                        <li key={index}>
                            <Link to={item.path} onClick={handleMenuClick}>
                                <span className="sidebar-icon">{item.icon}</span> {item.label}
                            </Link>
                        </li>
                    ))}
                </ul>
            </div>

            <div className="content-wrapper">
                <header className="top-bar">
                    {user && (
                        <div className="user-info">
                            <div className="user-details">
                                <span className="user-name">{user.nombre}</span>
                                <span className="user-role">{user.rol}</span>
                            </div>
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