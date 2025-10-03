import '@/styles/public.css';

const FAQS = [
  { q: '¿Cómo creo un producto?', a: 'Ve a Catálogo > Productos y pulsa “Nuevo”. Completa los campos y guarda.' },
  { q: '¿Cómo registro una venta?', a: 'Desde el módulo de Facturación, selecciona cliente, productos y confirma la operación.' },
  { q: '¿Puedo importar datos?', a: 'Sí, desde Configuración > Importar puedes subir CSV de productos y clientes.' },
];

export default function Help() {
  return (
    <div className="public-page">
      <div className="public-container">
        <header className="public-header">
          <h1 className="public-title">Centro de Ayuda</h1>
          <p className="public-subtitle">Guías y respuestas a preguntas frecuentes.</p>
        </header>

        <section className="cards-grid">
          {FAQS.map((item, idx) => (
            <div key={idx} className="card">
              <h3>{item.q}</h3>
              <p>{item.a}</p>
            </div>
          ))}
        </section>
      </div>
    </div>
  );
}