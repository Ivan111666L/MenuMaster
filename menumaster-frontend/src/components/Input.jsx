import React from 'react';
import PropTypes from 'prop-types';

/**
 * Componente de input con etiqueta para formularios.
 * @param {object} props
 * @param {string} props.label Texto de la etiqueta.
 * @param {string} props.id ID único para conectar la etiqueta y el input.
 * @param {string} [props.type='text'] Tipo de input (text, password, email, etc.).
 * @param {string} props.value Valor del input (para componentes controlados).
 * @param {function} props.onChange Función que maneja los cambios en el input.
 * @param {string} [props.className] Clases CSS adicionales.
 */
function Input({ label, id, type = 'text', value, onChange, className = '', ...props }) {
  const finalClassName = `form-input ${className}`;
  
  return (
    <div className="form-group">
      <label htmlFor={id} className="form-label">{label}</label>
      <input
        id={id}
        type={type}
        value={value}
        onChange={onChange}
        className={finalClassName}
        {...props}
      />
    </div>
  );
}

Input.propTypes = {
  label: PropTypes.string.isRequired,
  id: PropTypes.string.isRequired,
  type: PropTypes.string,
  value: PropTypes.any.isRequired,
  onChange: PropTypes.func.isRequired,
  className: PropTypes.string,
};

export default Input;