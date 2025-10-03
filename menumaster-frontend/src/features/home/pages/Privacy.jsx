import '@/styles/public.css';

export default function Privacy() {
  return (
    <div className="public-page">
      <div className="public-container">
        <header className="public-header">
          <h1 className="public-title">Privacidad</h1>
          <p className="public-subtitle">Resumen de nuestra política de privacidad y tratamiento de datos.</p>
        </header>

        <section className="cards-grid">
          <div className="card">
            <h3>Datos Recopilados</h3>
            <p>Información de cuenta, actividad de la plataforma y configuración del negocio.</p>
          </div>
          <div className="card">
            <h3>Uso de Datos</h3>
            <p>Prestación del servicio, mejoras del producto y soporte.</p>
          </div>
          <div className="card">
            <h3>Compartición</h3>
            <p>No vendemos datos. Solo compartimos con servicios necesarios (p. ej., pagos).</p>
          </div>
          <div className="card">
            <h3>Tus Derechos</h3>
            <p>Acceso, rectificación, eliminación y portabilidad de tus datos.</p>
          </div>
        </section>
      </div>
    </div>
  );
}