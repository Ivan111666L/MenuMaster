import '@/styles/public.css';

export default function About() {
  return (
    <div className="public-page">
      <div className="public-container">
        <header className="public-header">
          <h1 className="public-title">Acerca de</h1>
          <p className="public-subtitle">Nuestra misión es simplificar la gestión de restaurantes.</p>
        </header>

        <section className="cards-grid">
          <div className="card">
            <h3>Misión</h3>
            <p>Empoderar negocios gastronómicos con tecnología accesible y efectiva.</p>
          </div>
          <div className="card">
            <h3>Visión</h3>
            <p>Ser la plataforma preferida para la operación integral de restaurantes.</p>
          </div>
          <div className="card">
            <h3>Valores</h3>
            <p>Transparencia, seguridad, innovación y atención al cliente.</p>
          </div>
        </section>
      </div>
    </div>
  );
}