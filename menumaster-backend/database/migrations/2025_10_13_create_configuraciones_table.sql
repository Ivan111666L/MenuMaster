-- Crear tabla de configuraciones para persistencia global
CREATE TABLE IF NOT EXISTS configuraciones (
  id INT PRIMARY KEY,
  data TEXT NOT NULL,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insertar configuración por defecto (id=1) si no existe
INSERT INTO configuraciones (id, data)
SELECT 1, '{"sistema":{"horizonte_pronostico_default":7}}'
WHERE NOT EXISTS (SELECT 1 FROM configuraciones WHERE id = 1);