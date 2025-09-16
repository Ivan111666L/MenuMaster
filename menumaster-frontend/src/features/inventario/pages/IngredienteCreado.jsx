import React, { useState, useEffect } from 'react';

import '@/styles/global.css'; // Asegúrate de que los estilos globales estén importados

const IngredienteCreado = () => {
    const API_URL = '/api/ingredientes'; // Endpoint GET para obtener ingredientes

    const [ingredientes, setIngredientes] = useState({});
    const [seleccionado, setSeleccionado] = useState(null);
    const [categoriaActiva, setCategoriaActiva] = useState(null);
    const [isLoading, setIsLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        const fetchIngredientes = async () => {
            try {
                const response = await fetch(API_URL);
                if (!response.ok) {
                    throw new Error('No se pudo conectar con el servidor.');
                }
                const data = await response.json();
                
                // Agrupar ingredientes por categoría
                const agrupados = data.reduce((acc, ingrediente) => {
                    const { categoria } = ingrediente;
                    if (!acc[categoria]) {
                        acc[categoria] = [];
                    }
                    acc[categoria].push(ingrediente);
                    return acc;
                }, {});

                setIngredientes(agrupados);
            } catch (err) {
                setError(err.message);
            } finally {
                setIsLoading(false);
            }
        };

        fetchIngredientes();
    }, []);

    const toggleCategoria = (categoria) => {
        setCategoriaActiva(categoriaActiva === categoria ? null : categoria);
    };

    if (isLoading) return <div className="loader">Cargando ingredientes...</div>;
    if (error) return <div className="error-msg">Error: {error}</div>;

    return (
        <>
            <style>{styles}</style>
            <div className="visor-container">
                <aside className="panel-categorias">
                    <h2 className="titulo-panel">Categorías</h2>
                    {Object.keys(ingredientes).length > 0 ? (
                        Object.keys(ingredientes).map(categoria => (
                            <div key={categoria} className="categoria-acordeon">
                                <button onClick={() => toggleCategoria(categoria)}>
                                    {categoria} ({ingredientes[categoria].length})
                                </button>
                                <ul className={`lista-ingredientes ${categoriaActiva === categoria ? 'abierto' : ''}`}>
                                    {ingredientes[categoria].map(ing => (
                                        <li 
                                            key={ing.id} 
                                            onClick={() => setSeleccionado(ing)}
                                            className={seleccionado?.id === ing.id ? 'seleccionado' : ''}
                                        >
                                            {ing.nombre}
                                        </li>
                                    ))}
                                </ul>
                            </div>
                        ))
                    ) : (
                        <p>No hay ingredientes registrados.</p>
                    )}
                </aside>
                
                <main className="panel-detalles">
                    {seleccionado ? (
                        <div className="tarjeta-detalle">
                            <h2>{seleccionado.nombre}</h2>
                            <p className="descripcion">{seleccionado.descripcion || 'Sin descripción.'}</p>
                            <div className="detalle-grid">
                                <div className="detalle-item"><strong>Stock Actual</strong> {seleccionado.stock_actual} {seleccionado.unidad_medida}</div>
                                <div className="detalle-item"><strong>Stock Mínimo</strong> {seleccionado.stock_minimo} {seleccionado.unidad_medida}</div>
                                <div className="detalle-item"><strong>Proveedor</strong> {seleccionado.proveedor || 'No especificado'}</div>
                                <div className="detalle-item"><strong>Precio Compra</strong> ${parseFloat(seleccionado.precio_compra || 0).toFixed(2)}</div>
                            </div>
                        </div>
                    ) : (
                        <div className="tarjeta-detalle tarjeta-placeholder">
                            <h3>Selecciona un ingrediente para ver sus detalles</h3>
                        </div>
                    )}
                </main>
            </div>
        </>
    );
};

export default IngredienteCreado;
