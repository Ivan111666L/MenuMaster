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
