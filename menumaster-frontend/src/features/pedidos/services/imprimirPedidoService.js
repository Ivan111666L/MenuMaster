// Servicio de impresión: usar el endpoint público del backend sin el prefijo /api
// porque el archivo imprimir_pedido.php vive en public/ y no pasa por index.php/api
const BACKEND_PUBLIC_URL = import.meta.env.VITE_BACKEND_PUBLIC_URL || 'http://localhost/MenuMaster/menumaster-backend/public';

// Obtiene el ticket imprimible de un pedido
export const getPedidoTicket = async (pedidoId) => {
  if (pedidoId === undefined || pedidoId === null || pedidoId === '') {
    throw new Error('ID de pedido inválido: no se puede generar ticket sin ID');
  }
  const res = await fetch(`${BACKEND_PUBLIC_URL}/imprimir_pedido.php?id=${pedidoId}`, { method: 'GET' });
  const contentType = res.headers.get('content-type') || '';
  if (contentType.includes('application/json')) {
    return await res.json();
  }
  const html = await res.text();
  return { success: true, html };
};

// Genera un ticket HTML mínimo (solo datos necesarios) consultando JSON del backend
export const generarTicketHTMLMinimo = async (pedidoId) => {
  if (pedidoId === undefined || pedidoId === null || pedidoId === '') {
    throw new Error('ID de pedido inválido: no se puede imprimir sin ID');
  }
  // Intentar obtener JSON completo del pedido
  const res = await fetch(`${BACKEND_PUBLIC_URL}/imprimir_pedido.php?id=${pedidoId}`, { method: 'GET' });
  const contentType = res.headers.get('content-type') || '';
  let data;
  if (contentType.includes('application/json')) {
    const json = await res.json();
    data = json?.data;
  } else {
    // Si no hay JSON, como último recurso obtener HTML del backend
    const htmlFallback = await res.text();
    return htmlFallback;
  }
  if (!data) {
    throw new Error('No se pudieron obtener datos del pedido para imprimir');
  }

  const fecha = data.fecha_creacion ? new Date(data.fecha_creacion) : new Date();
  const horaStr = `${fecha.toLocaleDateString()} ${fecha.toLocaleTimeString()}`;

  // Construir HTML mínimo de cocina
  let html = '';
  html += "<div style='font-family: monospace; width: 260px; margin:0 auto;'>";
  html += "<div style='text-align:center;border-top:1px dashed #000;border-bottom:1px dashed #000;padding:6px 0;margin-bottom:8px;'>";
  html += "<strong>PEDIDO COCINA</strong>";
  html += "</div>";
  html += `<div><strong>Mesa:</strong> ${data.mesa_numero ?? data.mesa_id}</div>`;
  html += `<div><strong>Pedido:</strong> #${data.id}</div>`;
  html += `<div><strong>Hora:</strong> ${horaStr}</div>`;
  html += "<div style='border-bottom:1px dashed #000;margin:8px 0;'></div>";
  html += "<div>";
  (data.items || []).forEach(item => {
    const nombre = item.producto_nombre || item.nombre || `Producto ${item.producto_id}`;
    const cantidad = item.cantidad || 1;
    html += `<div>${cantidad} x ${nombre}</div>`;
  });
  html += "</div>";
  if (data.notas) {
    html += "<div style='border-top:1px dashed #000;margin:8px 0;'></div>";
    html += `<div><strong>Notas:</strong> ${String(data.notas).replace(/[<>]/g, '')}</div>`;
  }
  html += "<div style='border-top:1px dashed #000;margin-top:8px;'></div>";
  html += "</div>";
  return html;
};

// Genera el ticket HTML para impresión
export const generarTicketHTML = async (pedidoId) => {
  if (pedidoId === undefined || pedidoId === null || pedidoId === '') {
    throw new Error('ID de pedido inválido: no se puede imprimir sin ID');
  }
  const res = await fetch(`${BACKEND_PUBLIC_URL}/imprimir_pedido.php`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ id: pedidoId })
  });
  const contentType = res.headers.get('content-type') || '';
  if (contentType.includes('application/json')) {
    const data = await res.json();
    if (data?.html) return data.html;
  }
  // Fallback a HTML directo si el backend no devolvió JSON
  const resHtml = await fetch(`${BACKEND_PUBLIC_URL}/imprimir_pedido.php?id=${pedidoId}&format=html`);
  return await resHtml.text();
};
