-- =====================================================
-- AGREGAR COLUMNAS DE UBICACIÓN - MÉTODO DIRECTO
-- =====================================================

-- IMPORTANTE: Si alguna columna ya existe, simplemente ignora ese error y continúa

-- 1. Crear tabla sedes_deposito
CREATE TABLE IF NOT EXISTS sedes_deposito (
    id_sede INT AUTO_INCREMENT PRIMARY KEY,
    nombre_sede VARCHAR(100) NOT NULL UNIQUE,
    direccion TEXT,
    descripcion TEXT,
    activo TINYINT(1) DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Insertar sedes
INSERT IGNORE INTO sedes_deposito (nombre_sede, direccion, descripcion) VALUES
('Galpón Palo Negro - Depósito Central', 
 'Zona Industrial Palo Negro, Aragua',
 'Depósito principal de centralización'),
('Archivo Judicial Maracay Centro',
 'Edificio Tribunales, Maracay',
 'Archivo histórico del centro'),
('Depósito Temporal La Victoria',
 'Centro Comercial Dorado, La Victoria',
 'Almacenamiento temporal');

-- 3. Agregar columnas a maestro (UNA POR UNA)
-- Si da error "Duplicate column name", es porque ya existe - ignóralo

ALTER TABLE maestro ADD COLUMN id_sede INT DEFAULT NULL;

ALTER TABLE maestro ADD COLUMN ubicacion_area VARCHAR(100) DEFAULT NULL;

ALTER TABLE maestro ADD COLUMN ubicacion_detalle VARCHAR(255) DEFAULT NULL;

ALTER TABLE maestro ADD COLUMN fecha_ultima_ubicacion TIMESTAMP NULL DEFAULT NULL;

-- 4. Agregar índice
ALTER TABLE maestro ADD INDEX idx_sede (id_sede);

-- 5. Agregar Foreign Key
ALTER TABLE maestro 
ADD CONSTRAINT fk_maestro_sede 
FOREIGN KEY (id_sede) 
REFERENCES sedes_deposito(id_sede) 
ON DELETE SET NULL;

-- 6. VERIFICAR
DESCRIBE maestro;
SELECT * FROM sedes_deposito;
