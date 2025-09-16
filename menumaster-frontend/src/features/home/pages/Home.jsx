import React from 'react';
import { Link } from 'react-router-dom';
import { useAuth } from '@/context/AuthContext'; // Para saber si el usuario está logueado
import Button from '@/components/Button'; // Nuestro botón reutilizable
import '@/styles/home.css'; // Importamos los nuevos estilos

function Home() {
    // Obtenemos el estado de autenticación desde el contexto
    const { isAuthenticated } = useAuth();

    return (
        <div className="home-page">
            {/* === Encabezado Principal (Hero Section) === */}
            <header className="home-header">
                <h1 className="home-title">Bienvenido a MenuMaster</h1>
                <p className="home-tagline">
                    La solución digital para optimizar la gestión de tu restaurante.
                </p>
                <div className="cta-buttons">
                    {/* CORRECCIÓN: Mostramos botones diferentes si el usuario ya inició sesión */}
                    {isAuthenticated ? (
                        <Link to="/dashboard">
                            <Button variant="primary">Ir al Dashboard</Button>
                        </Link>
                    ) : (
                        <>
                            <Link to="/register">
                                <Button variant="primary">Crear Cuenta Gratis</Button>
                            </Link>
                            <Link to="/login">
                                <Button variant="secondary">Iniciar Sesión</Button>
                            </Link>
                        </>
                    )}
                </div>
            </header>

            {/* === Sección de Funcionalidades (sin cambios en la estructura) === */}
            <main>
                <section className="features-section">
                    <h2 className="section-title">Funcionalidades Principales</h2>
                    <div className="features-grid">
                        <div className="feature-card">
                            <div className="feature-icon">🍔</div>
                            <h3>Gestión de Menús Digitales</h3>
                            <p>Crea, actualiza y organiza tus menús de forma fácil e intuitiva. Añade platos, precios y descripciones en tiempo real.</p>
                        </div>
                        <div className="feature-card">
                            <div className="feature-icon">📱</div>
                            <h3>Toma de Pedidos Eficiente</h3>
                            <p>El personal puede tomar pedidos directamente desde cualquier dispositivo, enviándolos al instante a la cocina.</p>
                        </div>
                        <div className="feature-card">
                            <div className="feature-icon">🍽️</div>
                            <h3>Administración de Mesas</h3>
                            <p>Visualiza el estado de todas tus mesas en un plano digital. Gestiona ocupación, reservas y asigna pedidos fácilmente.</p>
                        </div>
                        <div className="feature-card">
                            <div className="feature-icon">📊</div>
                            <h3>Reportes y Analíticas</h3>
                            <p>Obtén información valiosa sobre tus ventas, los platos más populares y el rendimiento del personal.</p>
                        </div>
                    </div>
                </section>

                <section className="cta-section">
                    <h2 className="section-title">¿Listo para transformar tu restaurante?</h2>
                    <p>Únete a cientos de restaurantes que ya están optimizando su servicio con MenuMaster.</p>
                    {/* CORRECCIÓN: Este botón también cambia si el usuario está logueado */}
                    {isAuthenticated ? (
                         <Link to="/dashboard">
                            <Button variant="primary">Ir a mi Panel</Button>
                        </Link>
                    ) : (
                        <Link to="/register">
                            <Button variant="primary">Comienza Ahora</Button>
                        </Link>
                    )}
                </section>
            </main>

            {/* === Pie de Página === */}
            <footer className="home-footer">
                <p>&copy; {new Date().getFullYear()} MenuMaster. Todos los derechos reservados.</p>
            </footer>
        </div>
    );
}

export default Home;