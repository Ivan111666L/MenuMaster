import React from 'react';
import Modal from '@/components/Modal';

const PedidoTicket = ({ html, open, onClose }) => {
  return (
    <Modal open={open} onClose={onClose}>
      <div dangerouslySetInnerHTML={{ __html: html }} />
      <button onClick={onClose} style={{marginTop: '1em'}}>Cerrar</button>
    </Modal>
  );
};

export default PedidoTicket;
