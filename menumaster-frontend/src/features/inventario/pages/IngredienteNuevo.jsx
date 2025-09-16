import React, { useState } from 'react';
import '@/styles/global.css'; // Asegúrate de que los estilos globales estén importados
// Estilos CSS dentro del componente para simplicidad

const IngredienteNuevo = () => {
    const API_URL = '/api/ingredientes'; // Endpoint donde tu router escuchará

    const initialState = {
        nombre: '',
        descripcion: '',
        unidad_medida: 'gramos', // Valor por defecto
        stock_actual: '',
        stock_minimo: '',
        precio_compra: '',
        proveedor: ''
    };

    const [ingrediente, setIngrediente] = useState(initialState);
    const [mensaje, setMensaje] = useState(null); // { tipo: 'exito' | 'error', texto: '...' }
    const [isLoading, setIsLoading] = useState(false);

    const handleInputChange = (e) => {
        const { name, value } = e.target;
        setIngrediente({ ...ingrediente, [name]: value });
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setIsLoading(true);
        setMensaje(null);

        try {
            const response = await fetch(API_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(ingrediente),
            });
            
            const result = await response.json();

            if (!response.ok) {
                // Si la respuesta no es 2xx, lanzamos un error con el mensaje de la API
                throw new Error(result.error || 'Ocurrió un error desconocido.');
            }

            setMensaje({ tipo: 'exito', texto: result.mensaje });
            setIngrediente(initialState); // Limpiar el formulario

        } catch (error) {
            setMensaje({ tipo: 'error', texto: error.message });
        } finally {
            setIsLoading(false);
        }
    };

    return (
        <>
            <div className="app-container">
                <div className="form-wrapper">
                    <h1 className="form-title">Gestión de Inventario</h1>
                    <p className="form-description">
                        Añade un nuevo ingrediente al sistema. Los campos con * son obligatorios.
                    </p>
                    
                    {mensaje && (
                        <div className={`mensaje mensaje-${mensaje.tipo}`}>
                            {mensaje.texto}
                        </div>
                    )}
                    
                    <form onSubmit={handleSubmit}>
                        <div className="form-grid">
                            <div className="form-group full-width">
                                <label htmlFor="nombre">Nombre del Ingrediente *</label>
                                <input type="text" id="nombre" name="nombre" value={ingrediente.nombre} onChange={handleInputChange} required />
                            </div>

                            <div className="form-group full-width">
                                <label htmlFor="descripcion">Descripción</label>
                                <textarea id="descripcion" name="descripcion" rows="3" value={ingrediente.descripcion} onChange={handleInputChange}></textarea>
                            </div>

                            <div className="form-group">
                                <label htmlFor="unidad_medida">Unidad de Medida *</label>
                                <select id="unidad_medida" name="unidad_medida" value={ingrediente.unidad_medida} onChange={handleInputChange}>
                                    <option value="gramos">Gramos (gr)</option>
                                    <option value="kilogramos">Kilogramos (kg)</option>
                                    <option value="unidades">Unidades (u)</option>
                                    <option value="litros">Litros (l)</option>
                                    <option value="mililitros">Mililitros (ml)</option>
                                </select>
                            </div>

                            <div className="form-group">
                                <label htmlFor="proveedor">Proveedor</label>
                                <input type="text" id="proveedor" name="proveedor" value={ingrediente.proveedor} onChange={handleInputChange} />
                            </div>

                            <div className="form-group">
                                <label htmlFor="stock_actual">Stock Actual *</label>
                                <input type="number" id="stock_actual" name="stock_actual" value={ingrediente.stock_actual} onChange={handleInputChange} required step="0.01" />
                            </div>

                            <div className="form-group">
                                <label htmlFor="stock_minimo">Stock Mínimo *</label>
                                <input type="number" id="stock_minimo" name="stock_minimo" value={ingrediente.stock_minimo} onChange={handleInputChange} required step="0.01" />
                            </div>
                            
                            <div className="form-group full-width">
                                <label htmlFor="precio_compra">Precio de Compra (por unidad de medida)</label>
                                <input type="number" id="precio_compra" name="precio_compra" value={ingrediente.precio_compra} onChange={handleInputChange} step="0.01" />
                            </div>
                        </div>

                        <div className="form-actions">
                            <button type="submit" className="btn-crear" disabled={isLoading}>
                                {isLoading ? 'Guardando...' : 'Guardar Ingrediente'}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </>
    );
};

export default IngredienteNuevo;