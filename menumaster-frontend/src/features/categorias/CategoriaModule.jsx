import React, { useState } from 'react';
import CategoriasLista from './pages/CategoriasLista';
import CategoriaCrear from './pages/CategoriaCrear';
import CategoriaEditar from './pages/CategoriaEditar';

function CategoriaModule() {
  const [editingId, setEditingId] = useState(null);
  const [refresh, setRefresh] = useState(false);

  const handleEdit = (id) => setEditingId(id);
  const handleSuccess = () => {
    setEditingId(null);
    setRefresh(r => !r);
  };

  return (
    <div>
      <h1>Gestión de Categorías</h1>
      {!editingId && <CategoriaCrear onSuccess={handleSuccess} />}
      {editingId && <CategoriaEditar categoriaId={editingId} onSuccess={handleSuccess} />}
      <CategoriasLista key={refresh} onEdit={handleEdit} />
    </div>
  );
}

export default CategoriaModule;
