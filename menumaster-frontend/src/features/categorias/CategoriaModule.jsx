import React, { useState } from 'react';
import CategoriasLista from './pages/CategoriasLista';
import CategoriaCrear from './pages/CategoriaCrear';
import CategoriaEditar from './pages/CategoriaEditar';
import '@/styles/categorias.css';

function CategoriaModule() {
  const [editingId, setEditingId] = useState(null);
  const [refresh, setRefresh] = useState(false);

  const handleEdit = (id) => setEditingId(id);
  const handleSuccess = () => {
    setEditingId(null);
    setRefresh(r => !r);
  };

  return (
    <div className="categorias-app main-content">
      <div className="section-header">
        <h1>Gestión de Categorías</h1>
        <p className="section-subtitle">Crea, edita y organiza tus categorías.</p>
      </div>
      <div className="grid-two">
        <div className="panel">
          {!editingId && <CategoriaCrear onSuccess={handleSuccess} />}
          {editingId && <CategoriaEditar categoriaId={editingId} onSuccess={handleSuccess} />}
        </div>
        <div className="panel">
          <CategoriasLista key={refresh} onEdit={handleEdit} />
        </div>
      </div>
    </div>
  );
}

export default CategoriaModule;
