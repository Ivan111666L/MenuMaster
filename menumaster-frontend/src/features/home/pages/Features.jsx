import '@/styles/public.css';
import { FaChartLine, FaTable, FaBox, FaShoppingCart, FaWarehouse } from 'react-icons/fa';

const FEATURES = [
  { icon: <FaChartLine />, title: 'Dashboard Inteligente', desc: 'Métricas y análisis en tiempo real.' },
  { icon: <FaTable />, title: 'Gestión de Mesas', desc: 'Reserva, estado y distribución del restaurante.' },
  { icon: <FaBox />, title: 'Catálogo de Productos', desc: 'Categorías, precios, ingredientes y disponibilidad.' },
  { icon: <FaShoppingCart />, title: 'Sistema de Pedidos', desc: 'Toma de órdenes y seguimiento del estado.' },
  { icon: <FaWarehouse />, title: 'Control de Inventario', desc: 'Stock, mermas, alertas y rotación.' },
];

export default function Features() {
  return (
    <div className="public-page">
      <div className="public-container">
        <header className="public-header">
          <h1 className="public-title">Funcionalidades</h1>
          <p className="public-subtitle">Conoce todo lo que puedes hacer con MenuMaster.</p>
        </header>

        <section className="section">
          <div className="cards-grid">
            {FEATURES.map((f, i) => (
              <div key={i} className="card">
                <div style={{ fontSize: '1.5rem', color: 'var(--primary-teal)' }}>{f.icon}</div>
                <h3>{f.title}</h3>
                <p>{f.desc}</p>
              </div>
            ))}
          </div>
        </section>

        <section className="section">
          <a className="cta" href="/register">Empieza Gratis</a>
        </section>
      </div>
    </div>
  );
}