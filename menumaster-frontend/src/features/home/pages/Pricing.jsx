import '@/styles/public.css';

const PLANS = [
  {
    name: 'Básico',
    price: 'Gratis',
    features: ['Hasta 1 usuario', 'Catálogo de productos', 'Pedidos locales'],
    cta: { label: 'Crear cuenta', href: '/register' }
  },
  {
    name: 'Pro',
    price: '$19/mes',
    features: ['Hasta 5 usuarios', 'Inventario y reportes', 'Soporte prioritario'],
    cta: { label: 'Empezar prueba', href: '/register' }
  },
  {
    name: 'Enterprise',
    price: 'Contactar',
    features: ['Usuarios ilimitados', 'Integraciones avanzadas', 'Soporte dedicado'],
    cta: { label: 'Contactar ventas', href: '/contact' }
  }
];

export default function Pricing() {
  return (
    <div className="public-page">
      <div className="public-container">
        <header className="public-header">
          <h1 className="public-title">Planes y Precios</h1>
          <p className="public-subtitle">Elige el plan que mejor se adapta a tu restaurante.</p>
        </header>

        <section className="cards-grid">
          {PLANS.map((plan, idx) => (
            <div key={idx} className="card">
              <h3>{plan.name}</h3>
              <p><strong>{plan.price}</strong></p>
              <ul className="list">
                {plan.features.map((f, i) => (
                  <li key={i}>• {f}</li>
                ))}
              </ul>
              <a className="cta" href={plan.cta.href}>{plan.cta.label}</a>
            </div>
          ))}
        </section>
      </div>
    </div>
  );
}