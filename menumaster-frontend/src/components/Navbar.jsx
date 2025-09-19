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

    // CORRECCIÓN: Lógica de enrutamiento simplificada.
    // Mapeamos cada rol a su ruta de dashboard principal.
    const dashboardLinks = {
        administrador: "/dashboard", // O la ruta principal de admin
        mesero: "/mesas",          // O la ruta principal de mesero
        cocinero: "/cocina"        // O la ruta principal de cocinero
    };

    const userDashboardPath = rol ? dashboardLinks[rol] : null;

    return (
        <nav className="navbar">
            <div className="navbar-brand">
                <Link to="/" className="navbar-brand">
                    <img src={MenuMasterIcon} alt="MenuMaster" width="32" height="32" />
                    <span>MenuMaster</span>
                </Link>
            </div>
            
            <ul className="navbar-links">
                {/* Si el usuario está logueado y tiene una ruta de dashboard, se muestra el enlace. */}
                {isAuthenticated && userDashboardPath && (
                    <li>
                        <Link to={userDashboardPath} className="nav-link">Dashboard</Link>
                    </li>
                )}
                {/* Puedes añadir más enlaces públicos o protegidos aquí */}
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