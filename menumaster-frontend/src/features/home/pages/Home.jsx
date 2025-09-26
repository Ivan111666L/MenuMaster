import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { useAuth } from '@/context/AuthContext';
import Button from '@/components/Button';
import logo from '@/assets/logo.png';
import { 
  FaUtensils, 
  FaTable, 
  FaBox, 
  FaShoppingCart, 
  FaWarehouse, 
  FaConciergeBell, 
  FaCalculator, 
  FaCog, 
  FaUsers, 
  FaTruck,
  FaChartLine,
  FaShieldAlt,
  FaClock,
  FaCloud,
  FaMobile,
  FaArrowRight,
  FaStar,
  FaQuoteLeft,
  FaCheckCircle,
  FaRocket
} from 'react-icons/fa';
import MenuMasterLogo from '@/assets/logo.png';
import '@/styles/home.css';
const Home = () => {
  const { user } = useAuth();
  const [currentFeature, setCurrentFeature] = useState(0);

  const features = [
    {
      id: 'dashboard',
      icon: <FaChartLine />,
      title: 'Dashboard Inteligente',
      description: 'Panel de control completo con métricas en tiempo real, análisis de ventas y reportes detallados para tomar decisiones informadas.',
      details: [
        'Métricas de ventas en tiempo real',
        'Análisis de productos más vendidos',
        'Reportes de ingresos y gastos',
        'Gráficos interactivos y estadísticas',
        'Alertas de inventario bajo'
      ],
      link: '/dashboard'
    },
    {
      id: 'tables',
      icon: <FaTable />,
      title: 'Gestión de Mesas',
      description: 'Sistema avanzado para administrar mesas, reservas y distribución del restaurante con vista en tiempo real del estado de cada mesa.',
      details: [
        'Vista interactiva del layout del restaurante',
        'Estado en tiempo real de cada mesa',
        'Sistema de reservas integrado',
        'Asignación automática de meseros',
        'Historial de ocupación y rotación'
      ],
      link: '/tables'
    },
    {
      id: 'products',
      icon: <FaBox />,
      title: 'Catálogo de Productos',
      description: 'Gestión completa del menú con categorías, precios, ingredientes y disponibilidad en tiempo real.',
      details: [
        'Organización por categorías personalizables',
        'Control de precios y promociones',
        'Gestión de ingredientes y alérgenos',
        'Fotos y descripciones detalladas',
        'Control de disponibilidad por horarios'
      ],
      link: '/products'
    },
    {
      id: 'orders',
      icon: <FaShoppingCart />,
      title: 'Sistema de Pedidos',
      description: 'Procesamiento eficiente de órdenes desde la toma hasta la entrega, con seguimiento completo del estado.',
      details: [
        'Toma de pedidos intuitiva y rápida',
        'Seguimiento en tiempo real del estado',
        'Integración con cocina y servicio',
        'Modificaciones y cancelaciones',
        'Historial completo de pedidos'
      ],
      link: '/orders'
    },
    {
      id: 'inventory',
      icon: <FaWarehouse />,
      title: 'Control de Inventario',
      description: 'Gestión inteligente de stock con alertas automáticas, control de mermas y optimización de compras.',
      details: [
        'Seguimiento automático de stock',
        'Alertas de inventario bajo',
        'Control de fechas de vencimiento',
        'Gestión de mermas y desperdicios',
        'Reportes de rotación de productos'
      ],
      link: '/inventory'
    },
    {
      id: 'kitchen',
      icon: <FaConciergeBell />,
      title: 'Panel de Cocina',
      description: 'Interfaz especializada para la cocina con gestión de tiempos, prioridades y comunicación con el servicio.',
      details: [
        'Vista optimizada para pantallas de cocina',
        'Gestión de tiempos de preparación',
        'Priorización automática de pedidos',
        'Comunicación directa con meseros',
        'Control de calidad y terminados'
      ],
      link: '/kitchen'
    },
    {
      id: 'billing',
      icon: <FaCalculator />,
      title: 'Facturación Inteligente',
      description: 'Sistema completo de facturación con múltiples métodos de pago, propinas y reportes fiscales.',
      details: [
        'Múltiples métodos de pago',
        'Cálculo automático de propinas',
        'Generación de facturas y recibos',
        'Reportes fiscales automatizados',
        'Integración con sistemas contables'
      ],
      link: '/billing'
    },
    {
      id: 'settings',
      icon: <FaCog />,
      title: 'Configuración Avanzada',
      description: 'Personalización completa del sistema con configuraciones específicas para tu restaurante.',
      details: [
        'Configuración de horarios y turnos',
        'Personalización de la interfaz',
        'Gestión de impresoras y dispositivos',
        'Configuración de impuestos y monedas',
        'Backup automático de datos'
      ],
      link: '/settings'
    },
    {
      id: 'users',
      icon: <FaUsers />,
      title: 'Gestión de Personal',
      description: 'Administración completa del equipo con roles, permisos y seguimiento de desempeño.',
      details: [
        'Gestión de roles y permisos',
        'Seguimiento de ventas por empleado',
        'Sistema de comisiones y bonos',
        'Evaluación de desempeño'
      ],
      link: '/users'
    },
    {
      id: 'suppliers',
      icon: <FaTruck />,
      title: 'Proveedores',
      description: 'Gestión integral de proveedores con seguimiento de pedidos, precios y evaluación de servicios.',
      details: [
        'Base de datos completa de proveedores',
        'Comparación de precios y servicios',
        'Gestión de órdenes de compra',
        'Seguimiento de entregas',
        'Evaluación y calificación de proveedores'
      ],
      link: '/suppliers'
    }
  ];

  const benefits = [
    {
      icon: <FaMobile />,
      title: 'Diseño Responsive',
      description: 'Accede desde cualquier dispositivo - computadora, tablet o móvil - con una experiencia optimizada.'
    },
    {
      icon: <FaCloud />,
      title: 'Basado en la Nube',
      description: 'Tus datos seguros en la nube con acceso desde cualquier lugar y sincronización automática.'
    },
    {
      icon: <FaShieldAlt />,
      title: 'Seguridad Avanzada',
      description: 'Protección de datos con encriptación de nivel bancario y copias de seguridad automáticas.'
    },
    {
      icon: <FaClock />,
      title: 'Soporte 24/7',
      description: 'Asistencia técnica disponible las 24 horas para garantizar el funcionamiento continuo.'
    }
  ];

  const testimonials = [
    {
      name: 'María González',
      role: 'Propietaria de Restaurante La Cocina',
      content: 'MenuMaster transformó completamente la gestión de nuestro restaurante. Ahora tenemos control total sobre inventario, pedidos y facturación.',
      rating: 5
    },
    {
      name: 'Carlos Rodríguez',
      role: 'Gerente de Cadena de Pizzerías',
      content: 'La facilidad de uso y las funcionalidades avanzadas nos permitieron optimizar nuestras operaciones y aumentar las ventas en un 30%.',
      rating: 5
    },
    {
      name: 'Ana Martínez',
      role: 'Chef Ejecutiva',
      content: 'El panel de cocina es increíble. Nos ayuda a coordinar mejor los pedidos y mantener la calidad en tiempos de alta demanda.',
      rating: 5
    }
  ];

  useEffect(() => {
    const interval = setInterval(() => {
      setCurrentFeature((prev) => (prev + 1) % features.length);
    }, 5000);

    return () => clearInterval(interval);
  }, [features.length]);

  return (
    <div className="home-page">
      {/* Hero Section */}
      <section className="home-hero">
        <div className="container">
          <div className="hero-content">
            <div className="hero-badge">
              <FaRocket />
              <span>Sistema de Gestión Restaurantera #1</span>
            </div>
            
            <h1 className="hero-title">
              Gestiona tu restaurante con
            </h1>
            <img src={logo} alt="MenuMaster" className="hero-logo" />
            <p className="hero-description">
              La solución completa para restaurantes modernos. Controla inventario, 
              gestiona pedidos, administra personal y maximiza tus ganancias con 
              nuestra plataforma todo-en-uno.
            </p>

            <div className="hero-stats">
              <div className="stat">
                <span className="stat-number">2+</span>
                <span className="stat-label">Restaurantes</span>
              </div>
              <div className="stat">
                <span className="stat-number">99.9%</span>
                <span className="stat-label">Uptime</span>
              </div>
              <div className="stat">
                <span className="stat-number">24/7</span>
                <span className="stat-label">Soporte</span>
              </div>
            </div>

            <div className="hero-actions">
              {user ? (
                <Link to="/dashboard" className="hero-cta primary">
                  <span>Ir al Dashboard</span>
                  <FaArrowRight />
                </Link>
              ) : (
                <>
                  <Link to="/register" className="hero-cta primary">
                    <span>Comenzar Gratis</span>
                    <FaArrowRight />
                  </Link>
                  <Link to="/login" className="hero-cta secondary">
                    <span>Iniciar Sesión</span>
                  </Link>
                </>
              )}
            </div>
          </div>

          <div className="hero-visual">
            <div className="floating-cards">
              <div className="floating-card card-1">
                <FaChartLine />
                <span>Analytics</span>
              </div>
              <div className="floating-card card-2">
                <FaShoppingCart />
                <span>Pedidos</span>
              </div>
              <div className="floating-card card-3">
                <FaWarehouse />
                <span>Inventario</span>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* Main Content */}
      <main className="home-main">
        {/* Features Section */}
        <section className="features-section">
          <div className="container">
            <div className="section-header">
              <h2 className="section-title">Funcionalidades Completas</h2>
              <p className="section-subtitle">
                Todo lo que necesitas para gestionar tu restaurante de manera eficiente y profesional
              </p>
            </div>

            <div className="features-showcase">
              <div className="features-nav">
                {features.map((feature, index) => (
                  <button
                    key={feature.id}
                    className={`feature-nav-item ${index === currentFeature ? 'active' : ''}`}
                    onClick={() => setCurrentFeature(index)}
                  >
                    <span className="feature-nav-icon">{feature.icon}</span>
                    <div>
                      <div className="feature-nav-title">{feature.title}</div>
                    </div>
                  </button>
                ))}
              </div>

              <div className="feature-display">
                <div className="feature-content">
                  <div className="feature-icon-large">
                    {features[currentFeature].icon}
                  </div>
                  <h3 className="feature-title">{features[currentFeature].title}</h3>
                  <p className="feature-description">{features[currentFeature].description}</p>
                  
                  <div className="feature-details">
                    <h4>Características principales:</h4>
                    <ul className="feature-list">
                      {features[currentFeature].details.map((detail, index) => (
                        <li key={index}>{detail}</li>
                      ))}
                    </ul>
                  </div>

                  <Link to={features[currentFeature].link} className="feature-cta">
                    <span>Explorar Función</span>
                    <FaArrowRight />
                  </Link>
                </div>
              </div>
            </div>
          </div>
        </section>

        {/* Benefits Section */}
        <section className="benefits-section">
          <div className="container">
            <div className="section-header">
              <h2 className="section-title">¿Por qué elegir MenuMaster?</h2>
              <p className="section-subtitle">
                Ventajas que marcan la diferencia en la gestión de tu restaurante
              </p>
            </div>

            <div className="benefits-grid">
              {benefits.map((benefit, index) => (
                <div key={index} className="benefit-card">
                  <div className="benefit-icon">{benefit.icon}</div>
                  <h3>{benefit.title}</h3>
                  <p>{benefit.description}</p>
                </div>
              ))}
            </div>
          </div>
        </section>

        {/* Testimonials Section */}
        <section className="testimonials-section">
          <div className="container">
            <div className="section-header">
              <h2 className="section-title">Lo que dicen nuestros clientes</h2>
              <p className="section-subtitle">
                Testimonios reales de restaurantes que han transformado su negocio
              </p>
            </div>

            <div className="testimonials-grid">
              {testimonials.map((testimonial, index) => (
                <div key={index} className="testimonial-card">
                  <div className="quote-icon">
                    <FaQuoteLeft />
                  </div>
                  <p className="testimonial-content">"{testimonial.content}"</p>
                  <div className="testimonial-author">
                    <div>
                      <div className="author-name">{testimonial.name}</div>
                      <div className="author-role">{testimonial.role}</div>
                    </div>
                    <div className="rating">
                      {[...Array(testimonial.rating)].map((_, i) => (
                        <FaStar key={i} className="star" />
                      ))}
                    </div>
                  </div>
                </div>
              ))}
            </div>
          </div>
        </section>

        {/* Final CTA Section */}
        <section className="final-cta-section">
          <div className="container">
            <div className="cta-content">
              <h2 className="cta-title">¿Listo para transformar tu restaurante?</h2>
              <p className="cta-description">
                Únete a cientos de restaurantes que ya confían en MenuMaster para 
                gestionar sus operaciones de manera eficiente y profesional.
              </p>
              <div className="cta-actions">
                {user ? (
                  <Link to="/dashboard" className="cta-button primary">
                    <span>Acceder al Sistema</span>
                    <FaArrowRight />
                  </Link>
                ) : (
                  <>
                    <Link to="/register" className="cta-button primary">
                      <span>Comenzar Prueba Gratuita</span>
                      <FaRocket />
                    </Link>
                    <Link to="/login" className="cta-button secondary">
                      <span>Ya tengo cuenta</span>
                    </Link>
                  </>
                )}
              </div>
            </div>
          </div>
        </section>
      </main>

      {/* Footer */}
      <footer className="home-footer">
        <div className="container">
          <div className="footer-content">
            <div className="footer-brand">
              <h3>MenuMaster</h3>
              <p>La solución completa para la gestión de restaurantes modernos.</p>
            </div>
            <div className="footer-links">
              <div className="link-group">
                <h4>Producto</h4>
                <Link to="/features">Funcionalidades</Link>
                <Link to="/pricing">Precios</Link>
                <Link to="/demo">Demo</Link>
                <Link to="/updates">Actualizaciones</Link>
              </div>
              <div className="link-group">
                <h4>Empresa</h4>
                <Link to="/about">Acerca de</Link>
                <Link to="/careers">Carreras</Link>
                <Link to="/press">Prensa</Link>
                <Link to="/partners">Socios</Link>
              </div>
              <div className="link-group">
                <h4>Soporte</h4>
                <Link to="/help">Centro de Ayuda</Link>
                <Link to="/contact">Contacto</Link>
                <Link to="/status">Estado del Sistema</Link>
                <Link to="/security">Seguridad</Link>
              </div>
              <div className="link-group">
                <h4>Legal</h4>
                <Link to="/privacy">Privacidad</Link>
                <Link to="/terms">Términos</Link>
                <Link to="/cookies">Cookies</Link>
                <Link to="/licenses">Licencias</Link>
              </div>
            </div>
          </div>
          <div className="footer-bottom">
            <p>&copy; 2024 MenuMaster. Todos los derechos reservados.</p>
          </div>
        </div>
      </footer>
    </div>
  );
};

export default Home;