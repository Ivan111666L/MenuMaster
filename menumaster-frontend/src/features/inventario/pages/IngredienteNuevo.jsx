import React, { useState } from 'react';
import { crearIngrediente } from '@/services/ingredienteService';
import '@/styles/global.css';

const IngredienteNuevo = () => {
    const initialState = {
        nombre: '',
        descripcion: '',
        unidad_medida: 'gramos', // Valor por defecto
        stock_actual: '',
        stock_minimo: '',
        precio_compra: '',
        proveedor_nombre: ''
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
            // Copia el objeto y elimina proveedor_nombre si está vacío
            const payload = { ...ingrediente };
            if (!payload.proveedor_nombre || payload.proveedor_nombre.trim() === '') {
                delete payload.proveedor_nombre;
            }
            const nuevoIngrediente = await crearIngrediente(payload);
            setMensaje({ 
                tipo: 'exito', 
                texto: `Ingrediente "${nuevoIngrediente.nombre}" creado exitosamente.` 
            });
            setIngrediente(initialState); // Limpiar el formulario

        } catch (error) {
            console.error('Error al crear ingrediente:', error);
            const errorMessage = error.response?.data?.error || error.message || 'Error al crear el ingrediente';
            setMensaje({ tipo: 'error', texto: errorMessage });
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
                                <label htmlFor="proveedor_nombre">Proveedor</label>
                                <input type="text" id="proveedor_nombre" name="proveedor_nombre" value={ingrediente.proveedor_nombre} onChange={handleInputChange} />
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