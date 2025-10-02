import { Outlet, Link, useLocation } from "react-router-dom";
import React, { useState, useEffect } from "react";
import { useAuth } from "@/context/AuthContext"; // <-- Importa nuestro hook personalizado
import permisosService from "@/services/permisosService.js";
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
    const [userPermissions, setUserPermissions] = useState({});
    const [loading, setLoading] = useState(true);
    const location = useLocation();

    // Obtenemos el usuario y la función de logout directamente del contexto
    const { user, logout } = useAuth();

    // BUG CORREGIDO: Usamos 'user.rol' en lugar de 'user.role' para coincidir con la API
    const rol = user?.rol;

    // Cargar permisos del usuario al montar el componente
    useEffect(() => {
        const loadUserPermissions = async () => {
            if (user) {
                try {
                    const permissions = await permisosService.getPermissionsByModule();
                    setUserPermissions(permissions);
                } catch (error) {
                    console.error('Error al cargar permisos:', error);
                    // En caso de error, usar permisos básicos basados en el rol
                    const basicPermissions = getBasicPermissionsByRole(user.rol);
                    setUserPermissions(basicPermissions);
                } finally {
                    setLoading(false);
                }
            } else {
                setLoading(false);
            }
        };

        loadUserPermissions();
    }, [user]);

    // Función para obtener permisos básicos por rol como fallback
    const getBasicPermissionsByRole = (rol) => {
        const rolePermissions = {
            'administrador': {
                dashboard: [{ accion: 'ver' }],
                productos: [{ accion: 'ver' }],
                inventario: [{ accion: 'ver' }],
                facturacion: [{ accion: 'ver' }],
                pedidos: [{ accion: 'ver' }],
                mesas: [{ accion: 'ver' }],
                cocina: [{ accion: 'ver' }],
                reportes: [{ accion: 'ver' }],
                configuracion: [{ accion: 'ver' }],
                usuarios: [{ accion: 'ver' }]
            },
            'cajero': {
                dashboard: [{ accion: 'ver' }],
                facturacion: [{ accion: 'ver' }],
                pedidos: [{ accion: 'ver' }]
            },
            'cocinero': {
                dashboard: [{ accion: 'ver' }],
                productos: [{ accion: 'ver' }],
                inventario: [{ accion: 'ver' }],
                pedidos: [{ accion: 'ver' }],
                cocina: [{ accion: 'ver' }]
            },
            'mesero': {
                dashboard: [{ accion: 'ver' }],
                facturacion: [{ accion: 'ver' }],
                pedidos: [{ accion: 'ver' }],
                mesas: [{ accion: 'ver' }]
            }
        };
        
        return rolePermissions[rol?.toLowerCase()] || { dashboard: [{ accion: 'ver' }] };
    };

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

    // Función para verificar si el usuario tiene permisos para un módulo
    const hasModulePermission = (modulo, accion = 'ver') => {
        if (!userPermissions[modulo]) return false;
        return userPermissions[modulo].some(p => p.accion === accion);
    };

    // --- LÓGICA DE MENÚS MEJORADA Y MÁS CLARA ---
    const getMenuItems = () => {
        // Definición completa de menús con permisos requeridos
        const allMenuItems = [
            { 
                path: "/dashboard", 
                label: "Dashboard", 
                icon: <FaChartBar />, 
                module: "dashboard", 
                action: "ver",
                roles: ["administrador", "mesero", "cocinero", "cajero"]
            },
            { 
                path: "/productos", 
                label: "Productos", 
                icon: <FaUtensils />, 
                module: "productos", 
                action: "ver",
                roles: ["administrador", "cocinero"]
            },
            { 
                path: "/inventario", 
                label: "Inventario", 
                icon: <FaBoxes />, 
                module: "inventario", 
                action: "ver",
                roles: ["administrador", "cocinero"]
            },
            { 
                path: "/facturacion", 
                label: "Facturación", 
                icon: <FaFileInvoiceDollar />, 
                module: "facturacion", 
                action: "ver",
                roles: ["administrador", "mesero", "cajero"]
            },
            { 
                path: "/pedidos", 
                label: "Pedidos", 
                icon: <FaClipboardList />, 
                module: "pedidos", 
                action: "ver",
                roles: ["administrador", "mesero", "cocinero"]
            },
            { 
                path: "/mesas", 
                label: "Mesas", 
                icon: <FaTable />, 
                module: "mesas", 
                action: "ver",
                roles: ["administrador", "mesero"]
            },
            { 
                path: "/cocina", 
                label: "Cocina", 
                icon: <FaUtensils />, 
                module: "cocina", 
                action: "ver",
                roles: ["administrador", "cocinero"]
            },
            { 
                path: "/analisis", 
                label: "Análisis Avanzado", 
                icon: <FaChartBar />, 
                module: "reportes", 
                action: "ver",
                roles: ["administrador"]
            },
            { 
                path: "/configuracion", 
                label: "Configuración", 
                icon: <FaCog />, 
                module: "configuracion", 
                action: "ver",
                roles: ["administrador"]
            },
            { 
                path: "/usuarios", 
                label: "Usuarios", 
                icon: <FaUsers />, 
                module: "usuarios", 
                action: "ver",
                roles: ["administrador"]
            },
            { 
                path: "/cocina/menudia", 
                label: "Menú del Día", 
                icon: <FaHome />, 
                module: "cocina", 
                action: "gestionar",
                roles: ["cocinero"]
            }
        ];

        // Filtrar menús basado en rol y permisos
        return allMenuItems.filter(item => {
            // Verificar si el rol tiene acceso básico
            if (!item.roles.includes(rol?.toLowerCase())) return false;
            
            // Verificar permisos específicos del usuario
            return hasModulePermission(item.module, item.action);
        });
    };

    const menuItems = getMenuItems();

    // Mostrar loading mientras se cargan los permisos
    if (loading) {
        return (
            <div className="dashboard-container">
                <div className="loading-container">
                    <div className="loading-spinner"></div>
                    <p>Cargando permisos...</p>
                </div>
            </div>
        );
    }

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