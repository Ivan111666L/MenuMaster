import { Outlet, Link, useLocation } from "react-router-dom";
import React, { useState, useEffect } from "react";
import { useAuth } from "@/context/AuthContext"; // <-- Importa nuestro hook personalizado
import {
  FaHome, FaClipboardList, FaBoxes, FaFileInvoiceDollar,
  FaChartBar, FaCog, FaSignOutAlt, FaUtensils, FaTable,
  FaBars, FaTimes, FaUsers, FaTruck
} from "react-icons/fa";
import "@/styles/global.css";
import "@/styles/Layout.css";
import MenuMasterLogo from '@/assets/logo.png';

const Layout = () => {
    // Estado para mostrar/ocultar el menú en móvil
    const [sidebarOpen, setSidebarOpen] = useState(false);
    const [isMobile, setIsMobile] = useState(window.innerWidth <= 768);
    const location = useLocation();

    // Detectar cambios en el tamaño de pantalla
    useEffect(() => {
        const handleResize = () => {
            const mobile = window.innerWidth <= 768;
            setIsMobile(mobile);
            if (!mobile) {
                setSidebarOpen(false);
            }
        };

        window.addEventListener('resize', handleResize);
        return () => window.removeEventListener('resize', handleResize);
    }, []);

    // Cierra el menú al navegar (solo móvil)
    const handleMenuClick = () => {
        if (isMobile) setSidebarOpen(false);
    };

    // Cierra el menú al hacer clic fuera
    const handleOverlayClick = () => {
        if (isMobile && sidebarOpen) {
            setSidebarOpen(false);
        }
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
                { path: "/productos", label: "Productos", icon: <FaUtensils /> },
                { path: "/inventario", label: "Inventario", icon: <FaBoxes /> },
                { path: "/facturacion", label: "Facturación", icon: <FaFileInvoiceDollar /> },
                { path: "/pedidos", label: "Pedidos", icon: <FaClipboardList /> },
                { path: "/mesas", label: "Mesas", icon: <FaTable /> },
                { path: "/cocina", label: "Cocina", icon: <FaUtensils /> },
                { path: "/analisis", label: "Análisis Avanzado", icon: <FaChartBar /> },
                { path: "/configuracion", label: "Configuración", icon: <FaCog /> },
            ],
            gerente: [
                { path: "/dashboard", label: "Dashboard", icon: <FaChartBar /> },
                { path: "/productos", label: "Productos", icon: <FaUtensils /> },
                { path: "/inventario", label: "Inventario", icon: <FaBoxes /> },
                { path: "/pedidos", label: "Pedidos", icon: <FaClipboardList /> },
                { path: "/facturacion", label: "Facturación", icon: <FaFileInvoiceDollar /> },
                { path: "/mesas", label: "Mesas", icon: <FaTable /> },
                { path: "/cocina", label: "Cocina", icon: <FaUtensils /> },
                { path: "/analisis", label: "Análisis Avanzado", icon: <FaChartBar /> }
            ],
            mesero: [
                { path: "/mesas", label: "Mesas", icon: <FaTable /> },
                { path: "/pedidos", label: "Pedidos", icon: <FaClipboardList /> },
                { path: "/facturacion", label: "Facturación", icon: <FaFileInvoiceDollar /> },
            ],
            cocinero: [
                { path: "/cocina", label: "Cocina", icon: <FaUtensils /> },
                { path: "/inventario", label: "Inventario", icon: <FaBoxes /> },
                { path: "/cocina/menudia", label: "Menú del Día", icon: <FaHome /> }
            ],
        };
        // Devolvemos el menú correspondiente al rol, o un array vacío si no hay rol.
        return baseMenus[rol] || [];
    };

    const menuItems = getMenuItems();

    return (
        <div className="dashboard-container">
            {/* Overlay para cerrar menú en móvil */}
            {isMobile && sidebarOpen && (
                <div className="sidebar-overlay" onClick={handleOverlayClick}></div>
            )}
            
            {/* Botón hamburguesa solo en móvil */}
            {isMobile && (
                <button 
                    className="sidebar-toggle" 
                    onClick={() => setSidebarOpen(!sidebarOpen)}
                    aria-label="Toggle menu"
                >
                    {sidebarOpen ? <FaTimes /> : <FaBars />}
                </button>
            )}
            
            <div className={`sidebar ${isMobile ? (sidebarOpen ? 'sidebar-open' : 'sidebar-closed') : ''}`}>
                <div className="sidebar-header">
                    <img src={MenuMasterLogo} alt="MenuMaster Logo" className="sidebar-logo" />
                    
                </div>
                
                <nav className="sidebar-nav">
                    <ul className="sidebar-menu">
                        {menuItems.map((item, index) => (
                            <li key={index} className="sidebar-menu-item">
                                <Link 
                                    to={item.path} 
                                    onClick={handleMenuClick}
                                    className={`sidebar-link ${location.pathname === item.path ? 'active' : ''}`}
                                >
                                    <span className="sidebar-icon">{item.icon}</span> 
                                    <span className="sidebar-label">{item.label}</span>
                                </Link>
                            </li>
                        ))}
                    </ul>
                </nav>
            </div>

            <div className="content-wrapper">
                <header className="top-bar">
                    <div className="top-bar-left">
                        <h1 className="page-title">
                            {menuItems.find(item => item.path === location.pathname)?.label || 'MenuMaster'}
                        </h1>
                    </div>
                    
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