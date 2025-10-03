import React, { useEffect, useState, useCallback, useContext } from 'react';
import mesaService from '@/features/mesas/services/mesaService';
import api from '@/services/api';
import { generarTicketHTML } from '@/features/pedidos/services/imprimirPedidoService';
import Button from '@/components/Button';
import Spinner from '@/components/Spinner';
import { AuthContext } from '@/context/AuthContext';
import { Link, useNavigate } from 'react-router-dom';
import { FaEdit, FaPrint, FaFilter, FaSearch } from 'react-icons/fa';
import '@/styles/mesas.css';

function Mesas() {
    const navigate = useNavigate();
    const { user } = useContext(AuthContext);
    const [mesas, setMesas] = useState([]);
    const [isLoading, setIsLoading] = useState(true);
    const [error, setError] = useState(null);
    const [filtro, setFiltro] = useState('todas'); // todas, misMesas, ocupadas, disponibles
    const [busqueda, setBusqueda] = useState('');
    const [pedidoSeleccionado, setPedidoSeleccionado] = useState(null);
    const [mostrarDetallePedido, setMostrarDetallePedido] = useState(false);

    const cargarMesas = useCallback(async () => {
        try {
            // No seteamos isLoading aquí para que el refresco sea en segundo plano
            const data = await mesaService.getMesas();
            setMesas(Array.isArray(data) ? data : []);
        } catch (err) {
            setError('Error al cargar las mesas. Por favor, recarga la página.');
        } finally {
            setIsLoading(false); // Solo se ejecuta la primera vez
        }
    }, []);

    useEffect(() => {
        cargarMesas(); // Carga inicial
        // Polling: refrescar las mesas cada 3 segundos para estado casi en tiempo real
        const intervalId = setInterval(cargarMesas, 3000);

        // Escuchar evento global para refrescar inmediatamente tras acciones (ej. crear pedido)
        const handleMesasUpdate = () => cargarMesas();
        window.addEventListener('mesas:update', handleMesasUpdate);

        // Limpieza al desmontar el componente
        return () => {
            clearInterval(intervalId);
            window.removeEventListener('mesas:update', handleMesasUpdate);
        };
    }, [cargarMesas]);

    const cambiarEstado = async (id, estadoActual) => {
        const estadosCiclo = {
            'disponible': 'ocupada',
            'ocupada': 'reservada',
            'reservada': 'disponible'
        };
        const nuevoEstado = estadosCiclo[estadoActual];

        // Actualización optimista: cambiamos el estado en la UI al instante
        const mesasOriginales = [...mesas];
        setMesas(prev => prev.map(m => m.id === id ? { ...m, estado: nuevoEstado } : m));

        try {
            await mesaService.updateMesa(id, { estado_nombre: nuevoEstado });
        } catch (err) {
            alert('Error al actualizar la mesa. Revirtiendo cambio.');
            setMesas(mesasOriginales); // Si falla, revertimos al estado anterior
        }
    };

    const resetearMesas = async () => {
        if (window.confirm('¿Estás seguro de que quieres poner todas las mesas como disponibles?')) {
            try {
                await mesaService.resetAllMesas();
                cargarMesas(); // Recargamos la lista
            } catch (err) {
                alert('No se pudo resetear las mesas.');
            }
        }
    };
    
    const resetMesa = async (mesaId) => {
        try {
            await mesaService.resetMesa(mesaId);
            // Actualización optimista de la UI
            setMesas(prevMesas => 
                prevMesas.map(mesa => 
                    mesa.id === mesaId ? { ...mesa, estado: 'disponible', pedido_id: null } : mesa
                )
            );
        } catch (error) {
            console.error('Error al resetear la mesa:', error);
            setError('No se pudo resetear la mesa. Intente de nuevo.');
        }
    };
    
    // Función para filtrar mesas según el criterio seleccionado
    const mesasFiltradas = () => {
        let mesasFiltradas = [...mesas];
        
        // Aplicar filtro por estado o mesero
        switch(filtro) {
            case 'misMesas':
                mesasFiltradas = mesasFiltradas.filter(mesa => 
                    mesa.pedido && mesa.pedido.usuario_id === user.id);
                break;
            case 'ocupadas':
                mesasFiltradas = mesasFiltradas.filter(mesa => mesa.estado === 'ocupada');
                break;
            case 'disponibles':
                mesasFiltradas = mesasFiltradas.filter(mesa => mesa.estado === 'disponible');
                break;
            default:
                // Mostrar todas las mesas
                break;
        }
        
        // Aplicar búsqueda por número de mesa o nombre de cliente
        if (busqueda.trim() !== '') {
            const terminoBusqueda = busqueda.toLowerCase().trim();
            mesasFiltradas = mesasFiltradas.filter(mesa => 
                mesa.numero.toString().includes(terminoBusqueda) || 
                (mesa.pedido && mesa.pedido.cliente && 
                 mesa.pedido.cliente.toLowerCase().includes(terminoBusqueda))
            );
        }
        
        return mesasFiltradas;
    };
    
    // Función para ver detalles del pedido
    const verDetallePedido = async (pedidoId) => {
        // Buscar el pedido en las mesas
        const mesa = mesas.find(m => m.pedido_id === pedidoId);
        if (mesa && mesa.pedido) {
            setPedidoSeleccionado(mesa.pedido);
            setMostrarDetallePedido(true);
            return;
        }

        try {
            const res = await api.get(`/pedidos/${pedidoId}`);
            const pedido = res?.data?.data ?? res?.data;
            setPedidoSeleccionado(pedido);
            setMostrarDetallePedido(true);
        } catch (error) {
            console.error('Error al cargar el pedido:', error);
            setError('No se pudo cargar el detalle del pedido');
        }
    };
    
    // Función para imprimir pedido
    const imprimirPedido = async (pedidoId) => {
        try {
            if (pedidoId === undefined || pedidoId === null || pedidoId === '') {
                setError('No se encontró el ID del pedido para imprimir.');
                return;
            }
            const html = await generarTicketHTML(pedidoId);
            const win = window.open('', '_blank');
            win.document.write(html);
            win.document.close();
            win.focus();
            win.print();
            win.close();
        } catch (error) {
            console.error('Error al imprimir:', error);
            setError('No se pudo imprimir el pedido. Verifique la configuración de la impresora.');
        }
    };
    
    if (isLoading) {
        return <div className="loader-container"><Spinner /></div>;
    }

    if (error) {
        return <div className="error-message">{error}</div>;
    }

    // Obtener las mesas filtradas
    const mesasMostradas = mesasFiltradas();

    return (
        <div className="mesas-container">
            <h1>Gestión de Mesas</h1>
            
            {/* Barra de búsqueda y filtros */}
            <div className="mesas-controls">
                <div className="search-bar">
                    <FaSearch />
                    {/* Etiqueta accesible y atributos id/name para el campo de búsqueda */}
                    <label htmlFor="busqueda" className="visually-hidden">Buscar</label>
                    <input 
                        id="busqueda"
                        name="busqueda"
                        type="text" 
                        placeholder="Buscar por número o cliente..." 
                        aria-label="Buscar por número o cliente"
                        value={busqueda}
                        onChange={(e) => setBusqueda(e.target.value)}
                    />
                </div>
                
                <div className="filter-buttons">
                    <Button 
                        onClick={() => setFiltro('todas')}
                        variant={filtro === 'todas' ? 'primary' : 'secondary'}
                    >
                        <FaFilter /> Todas
                    </Button>
                    <Button 
                        onClick={() => setFiltro('misMesas')}
                        variant={filtro === 'misMesas' ? 'primary' : 'secondary'}
                    >
                        <FaFilter /> Mis Mesas
                    </Button>
                    <Button 
                        onClick={() => setFiltro('ocupadas')}
                        variant={filtro === 'ocupadas' ? 'primary' : 'secondary'}
                    >
                        <FaFilter /> Ocupadas
                    </Button>
                    <Button 
                        onClick={() => setFiltro('disponibles')}
                        variant={filtro === 'disponibles' ? 'primary' : 'secondary'}
                    >
                        <FaFilter /> Disponibles
                    </Button>
                </div>
            </div>
            
            {/* Grid de mesas */}
            <div className="mesas-grid">
                {mesasMostradas.map(mesa => (
                    <div 
                        key={mesa.id} 
                        className={`mesa-card ${mesa.estado}`}
                    >
                        <h2>Mesa {mesa.numero}</h2>
                        <p>Estado: {mesa.estado}</p>
                        {/* Control para cambiar el estado de la mesa */}
                        <div className="estado-actions">
                            <Button 
                                onClick={() => cambiarEstado(mesa.id, mesa.estado)}
                                variant="secondary"
                                title="Cambiar estado de la mesa"
                            >
                                Cambiar Estado
                            </Button>
                        </div>

                        {/* Acción para crear pedido cuando la mesa está disponible */}
                        {mesa.estado === 'disponible' && (
                            <div className="mesa-actions">
                                <div className="action-buttons">
                                    <Button 
                                        variant="primary"
                                        title="Tomar pedido en esta mesa"
                                        onClick={() => {
                                            try {
                                                navigate('/pedidos', { state: { mesaId: mesa.id } });
                                            } catch (e) {
                                                console.error('No se pudo navegar a Pedidos:', e);
                                            }
                                        }}
                                    >
                                        Crear Pedido
                                    </Button>
                                </div>
                            </div>
                        )}
                        
                        {mesa.estado === 'ocupada' && (
                            <div className="mesa-actions">
                                {/* Botones para mesas ocupadas */}
                                <div className="action-buttons">
                                    <Link to={`/pedidos/editar/${mesa.pedido_id}`}>
                                        <Button variant="warning">
                                            <FaEdit /> Editar
                                        </Button>
                                    </Link>
                                    
                                    <Button 
                                        onClick={() => verDetallePedido(mesa.pedido_id)}
                                        variant="info"
                                    >
                                        Ver Detalles
                                    </Button>
                                    
                                    <Button 
                                        onClick={() => imprimirPedido(mesa.pedido_id)}
                                        variant="secondary"
                                        disabled={!mesa.pedido_id}
                                        title={!mesa.pedido_id ? 'Esta mesa no tiene pedido activo' : ''}
                                    >
                                        <FaPrint /> Imprimir
                                    </Button>
                                    
                                    <Button 
                                        onClick={() => resetMesa(mesa.id)}
                                        variant="danger"
                                    >
                                        Liberar Mesa
                                    </Button>
                                </div>
                                
                                {/* Información del pedido */}
                                {mesa.pedido && (
                                    <div className="pedido-info">
                                        <p>Cliente: {mesa.pedido.cliente || 'Sin nombre'}</p>
                                        <p>Mesero: {mesa.pedido.usuario_nombre || user.nombre}</p>
                                        <p>Total: ${mesa.pedido.total || '0.00'}</p>
                                    </div>
                                )}
                            </div>
                        )}
                    </div>
                ))}
            </div>
            
            {/* Modal para ver detalles del pedido */}
            {mostrarDetallePedido && pedidoSeleccionado && (
                <div className="modal-overlay" onClick={() => setMostrarDetallePedido(false)}>
                    <div className="modal-content" onClick={e => e.stopPropagation()}>
                        <h2>Detalles del Pedido #{pedidoSeleccionado.id}</h2>
                        <div className="pedido-detalles">
                            <p><strong>Cliente:</strong> {pedidoSeleccionado.cliente || 'Sin nombre'}</p>
                            <p><strong>Mesero:</strong> {pedidoSeleccionado.usuario_nombre}</p>
                            <p><strong>Fecha:</strong> {new Date(pedidoSeleccionado.fecha_creacion).toLocaleString()}</p>
                            <p><strong>Estado:</strong> {pedidoSeleccionado.estado_nombre}</p>
                            
                            <h3>Productos:</h3>
                            <ul className="productos-lista">
                                {pedidoSeleccionado.detalles && pedidoSeleccionado.detalles.map(detalle => (
                                    <li key={detalle.id}>
                                        <div className="producto-detalle">
                                            <span className="producto-nombre">
                                                {detalle.es_combo ? 'COMBO: ' : ''}{detalle.producto_nombre}
                                            </span>
                                            <span className="producto-cantidad">x{detalle.cantidad}</span>
                                            <span className="producto-precio">${detalle.precio_unitario}</span>
                                        </div>
                                        {detalle.comentario && (
                                            <div className="producto-comentario">
                                                Nota: {detalle.comentario}
                                            </div>
                                        )}
                                        {detalle.es_combo && detalle.elementos_combo && (
                                            <ul className="elementos-combo">
                                                {detalle.elementos_combo.map(elemento => (
                                                    <li key={elemento.id}>
                                                        {elemento.producto_nombre} x{elemento.cantidad}
                                                        {elemento.cancelado && <span className="cancelado"> (Cancelado)</span>}
                                                    </li>
                                                ))}
                                            </ul>
                                        )}
                                    </li>
                                ))}
                            </ul>
                            
                            <div className="pedido-total">
                                <p><strong>Total:</strong> ${pedidoSeleccionado.total}</p>
                            </div>
                        </div>
                        
                        <div className="modal-actions">
                            <Button onClick={() => setMostrarDetallePedido(false)} variant="secondary">
                                Cerrar
                            </Button>
                            <Link to={`/pedidos/editar/${pedidoSeleccionado.id}`}>
                                <Button variant="warning">
                                    <FaEdit /> Editar Pedido
                                </Button>
                            </Link>
                            <Button onClick={() => imprimirPedido(pedidoSeleccionado?.id)} variant="primary" disabled={!pedidoSeleccionado?.id} title={!pedidoSeleccionado?.id ? 'Pedido sin ID válido' : ''}>
                                <FaPrint /> Imprimir
                            </Button>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
};

export default Mesas;
