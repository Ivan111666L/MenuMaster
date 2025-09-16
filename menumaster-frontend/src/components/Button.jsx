import React from 'react';
import PropTypes from 'prop-types';

/**
 * Botón reutilizable con diferentes estilos y estados.
 * @param {object} props
 * @param {React.ReactNode} props.children Contenido del botón.
 * @param {function} props.onClick Función a ejecutar al hacer clic.
 * @param {('button'|'submit'|'reset')} [props.type='button'] Tipo de botón.
 * @param {('primary'|'secondary'|'danger')} [props.variant='primary'] Estilo visual del botón.
 * @param {boolean} [props.disabled=false] Si el botón está deshabilitado.
 * @param {string} [props.className] Clases CSS adicionales.
 */
function Button({ children, onClick, type = 'button', variant = 'primary', disabled = false, className = '', ...props }) {
  // Clases base y clases específicas de la variante
  const baseClasses = 'btn';
  const variantClasses = {
    primary: 'btn-primary',
    secondary: 'btn-secondary',
    danger: 'btn-danger',
  };

  const finalClassName = `${baseClasses} ${variantClasses[variant]} ${className}`;

  return (
    <button
      type={type}
      onClick={onClick}
      disabled={disabled}
      className={finalClassName}
      {...props}
    >
      {children}
    </button>
  );
}

Button.propTypes = {
  children: PropTypes.node.isRequired,
  onClick: PropTypes.func,
  type: PropTypes.oneOf(['button', 'submit', 'reset']),
  variant: PropTypes.oneOf(['primary', 'secondary', 'danger']),
  disabled: PropTypes.bool,
  className: PropTypes.string,
};

export default Button;