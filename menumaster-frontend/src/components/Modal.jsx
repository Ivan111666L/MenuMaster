import React from 'react';
import ReactDOM from 'react-dom';
import PropTypes from 'prop-types';
import { FaTimes } from 'react-icons/fa';

/**
 * Ventana modal que se renderiza fuera del DOM principal usando un Portal.
 * @param {object} props
 * @param {boolean} props.isOpen Si el modal está visible o no.
 * @param {function} props.onClose Función para cerrar el modal.
 * @param {string} props.title Título del modal.
 * @param {React.ReactNode} props.children Contenido principal del modal.
 */
function Modal({ isOpen, onClose, title, children }) {
  if (!isOpen) {
    return null;
  }

  // Usamos un Portal para renderizar el modal en el body, fuera del div #root
  return ReactDOM.createPortal(
    <div className="modal-overlay" onClick={onClose}>
      <div className="modal-content" onClick={(e) => e.stopPropagation()}>
        <div className="modal-header">
          <h2 className="modal-title">{title}</h2>
          <button onClick={onClose} className="modal-close-btn">
            <FaTimes />
          </button>
        </div>
        <div className="modal-body">
          {children}
        </div>
      </div>
    </div>,
    document.getElementById('modal-root') // El elemento del DOM donde se montará el modal
  );
}

Modal.propTypes = {
  isOpen: PropTypes.bool.isRequired,
  onClose: PropTypes.func.isRequired,
  title: PropTypes.string.isRequired,
  children: PropTypes.node.isRequired,
};

export default Modal;