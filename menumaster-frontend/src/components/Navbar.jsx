import React from 'react';
import { Link } from 'react-router-dom';
// CORRECCIÓN: Se importa el hook useAuth desde el contexto que creamos.
import { useAuth } from '@/context/AuthContext';
// Importamos el logo
import MenuMasterIcon from '@/assets/menumaster-icon.svg';

/**
 * Navbar principal de la aplicación.
 * No necesita props, ya que obtiene toda la información del AuthContext.
 */
function Navbar() {
    // Obtenemos todo lo necesario del contexto de autenticación.
    const { isAuthenticated, user, rol, logout } = useAuth();

    // Enlaces por rol para navegar por los módulos principales
    const navLinksByRole = {
        administrador: [
            { to: '/dashboard', label: 'Dashboard' },
            { to: '/mesas', label: 'Mesas' },
            { to: '/pedidos', label: 'Pedidos' },
            { to: '/cocina', label: 'Cocina' },
            { to: '/inventario', label: 'Inventario' },
            { to: '/productos', label: 'Productos' },
            { to: '/facturacion', label: 'Facturación' },
            { to: '/analisis', label: 'Análisis' },
            { to: '/configuracion', label: 'Configuración' }
        ],
        mesero: [
            { to: '/dashboard', label: 'Dashboard' },
            { to: '/mesas', label: 'Mesas' },
            { to: '/pedidos', label: 'Pedidos' },
            { to: '/facturacion', label: 'Facturación' }
        ],
        cocinero: [
            { to: '/dashboard', label: 'Dashboard' },
            { to: '/cocina', label: 'Cocina' },
            { to: '/inventario', label: 'Inventario' },
            { to: '/productos', label: 'Productos' }
        ],
        cajero: [
            { to: '/dashboard', label: 'Dashboard' },
            { to: '/facturacion', label: 'Facturación' },
            { to: '/pedidos', label: 'Pedidos' }
        ]
    };
    const roleKey = rol ? rol.toLowerCase() : null;
    const roleLinks = roleKey ? (navLinksByRole[roleKey] || []) : [];

    return (
        <nav className="navbar">
            <div className="navbar-brand">
                <Link to="/" className="navbar-brand">
                    <img src={MenuMasterIcon} alt="MenuMaster" width="32" height="32" />
                    <span>MenuMaster</span>
                </Link>
            </div>
            
            <ul className="navbar-links">
                {isAuthenticated && roleLinks.length > 0 ? (
                    roleLinks.map((item) => (
                        <li key={item.to}>
                            <Link to={item.to} className="nav-link">{item.label}</Link>
                        </li>
                    ))
                ) : (
                    // Enlaces públicos básicos
                    <>
                        <li>
                            <Link to="/home" className="nav-link">Inicio</Link>
                        </li>
                    </>
                )}
            </ul>

            {/* CORRECCIÓN: Lógica de usuario unificada y más limpia. */}
            <div className="navbar-user-section">
                {isAuthenticated && user ? (
                    <div className="user-info">
                        <span className="user-greeting">Hola, {user.nombre}</span>
                        <button onClick={logout} className="btn btn-outline logout-button">Cerrar Sesión</button>
                    </div>
                ) : (
                    <Link to="/login" className="nav-link login-link">Iniciar Sesión</Link>
                )}
            </div>
        </nav>
    );
}

// CORRECCIÓN: Se eliminan los PropTypes porque el componente ya no recibe props.
export default Navbar;