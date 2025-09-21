import React, { useEffect, useState } from 'react';
import { generarTicketHTML } from '../services/imprimirPedidoService';
import Button from '@/components/Button';

function PedidoImprimir({ pedidoId }) {
  const [ticketHtml, setTicketHtml] = useState('');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');

  useEffect(() => {
    if (pedidoId) {
      setLoading(true);
      generarTicketHTML(pedidoId)
        .then(html => setTicketHtml(html))
        .catch(() => setError('No se pudo generar el ticket'))
        .finally(() => setLoading(false));
    }
  }, [pedidoId]);

  const handlePrint = () => {
    const printWindow = window.open('', '_blank');
    printWindow.document.write(ticketHtml);
    printWindow.document.close();
    printWindow.focus();
    printWindow.print();
    printWindow.close();
  };

  if (loading) return <div>Cargando ticket...</div>;
  if (error) return <div>{error}</div>;
  if (!ticketHtml) return null;

  return (
    <div>
      <div dangerouslySetInnerHTML={{ __html: ticketHtml }} />
      <Button onClick={handlePrint} variant="primary">Imprimir Ticket</Button>
    </div>
  );
}

export default PedidoImprimir;
