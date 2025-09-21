import React, { useState } from 'react';
import MesasLista from './pages/MesasLista';
import MesaCrear from './pages/MesaCrear';
import MesaEditar from './pages/MesaEditar';

function MesaModule() {
  const [editingId, setEditingId] = useState(null);
  const [refresh, setRefresh] = useState(false);

  const handleEdit = (id) => setEditingId(id);
  const handleSuccess = () => {
    setEditingId(null);
    setRefresh(r => !r);
  };

  return (
    <div>
      <h1>Gestión de Mesas</h1>
      {!editingId && <MesaCrear onSuccess={handleSuccess} />}
      {editingId && <MesaEditar mesaId={editingId} onSuccess={handleSuccess} />}
      <MesasLista key={refresh} onEdit={handleEdit} />
    </div>
  );
}

export default MesaModule;
