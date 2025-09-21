import React from 'react';
import PropTypes from 'prop-types';
import { IoMdClose } from 'react-icons/io';
import './OrderDetailsPopup.css';

const OrderDetailsPopup = ({ order, isOpen, onClose }) => {
  if (!isOpen) return null;

  return (
    <div className="order-details-popup-overlay">
      <div className="order-details-popup">
        <div className="order-details-header">
          <h2>Detalles del Pedido #{order.id}</h2>
          <button onClick={onClose} className="close-button">
            <IoMdClose />
          </button>
        </div>
        <div className="order-details-content">
          <div className="order-info">
            <p><strong>Mesa:</strong> {order.mesa}</p>
            <p><strong>Estado:</strong> {order.estado}</p>
            <p><strong>Fecha:</strong> {new Date(order.fecha_creacion).toLocaleString()}</p>
          </div>
          <div className="order-items">
            <h3>Productos</h3>
            <ul>
              {order.items.map((item, index) => (
                <li key={index} className="order-item">
                  <span className="quantity">{item.cantidad}x</span>
                  <span className="name">{item.producto}</span>
                  <span className="price">${item.precio.toFixed(2)}</span>
                </li>
              ))}
            </ul>
          </div>
          <div className="order-total">
            <h3>Total</h3>
            <p className="total-amount">${order.total.toFixed(2)}</p>
          </div>
          {order.notas && (
            <div className="order-notes">
              <h3>Notas</h3>
              <p>{order.notas}</p>
            </div>
          )}
        </div>
      </div>
    </div>
  );
};

OrderDetailsPopup.propTypes = {
  order: PropTypes.shape({
    id: PropTypes.number.isRequired,
    mesa: PropTypes.string.isRequired,
    estado: PropTypes.string.isRequired,
    fecha_creacion: PropTypes.string.isRequired,
    items: PropTypes.arrayOf(PropTypes.shape({
      cantidad: PropTypes.number.isRequired,
      producto: PropTypes.string.isRequired,
      precio: PropTypes.number.isRequired
    })).isRequired,
    total: PropTypes.number.isRequired,
    notas: PropTypes.string
  }).isRequired,
  isOpen: PropTypes.bool.isRequired,
  onClose: PropTypes.func.isRequired
};

export default OrderDetailsPopup;