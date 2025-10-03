import '@/styles/public.css';

export default function Cookies() {
  return (
    <div className="public-page">
      <div className="public-container">
        <header className="public-header">
          <h1 className="public-title">Cookies</h1>
          <p className="public-subtitle">Cómo usamos cookies y tecnologías similares.</p>
        </header>

        <section className="cards-grid">
          <div className="card">
            <h3>Esenciales</h3>
            <p>Necesarias para el funcionamiento básico del sitio.</p>
          </div>
          <div className="card">
            <h3>Preferencias</h3>
            <p>Guardan configuraciones y opciones del usuario.</p>
          </div>
          <div className="card">
            <h3>Analíticas</h3>
            <p>Nos ayudan a mejorar el producto con estadísticas de uso.</p>
          </div>
          <div className="card">
            <h3>Marketing</h3>
            <p>Opcionales, para campañas y comunicación personalizada.</p>
          </div>
        </section>
      </div>
    </div>
  );
}