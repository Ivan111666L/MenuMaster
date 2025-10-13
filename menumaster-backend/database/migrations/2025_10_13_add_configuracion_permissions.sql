-- Permisos granulares para el módulo de configuración

-- Crear permiso 'configuracion_editar' si no existe
INSERT INTO permisos (nombre, descripcion, modulo, accion, estado_id)
SELECT 'configuracion_editar', 'Editar configuración global del sistema', 'configuracion', 'editar', 1
WHERE NOT EXISTS (
  SELECT 1 FROM permisos WHERE nombre = 'configuracion_editar'
);

-- Asignar permiso al rol Administrador (rol_id = 1) si no existe la asociación
INSERT INTO rol_permisos (rol_id, permiso_id)
SELECT 1, p.id FROM permisos p
WHERE p.nombre = 'configuracion_editar'
AND NOT EXISTS (
  SELECT 1 FROM rol_permisos rp WHERE rp.rol_id = 1 AND rp.permiso_id = p.id
);