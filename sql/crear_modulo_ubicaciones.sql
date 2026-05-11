-- =====================================================
-- SCRIPT: Módulo de Gestión de Ubicaciones
-- Sistema: Archivo Judicial - Centralización Palo Negro
-- Fecha: 20/04/2026
-- =====================================================

-- PASO 1: Crear tabla de Sedes de Depósito
CREATE TABLE IF NOT EXISTS sedes_deposito (
    id_sede INT AUTO_INCREMENT PRIMARY KEY,
    nombre_sede VARCHAR(100) NOT NULL UNIQUE,
    direccion TEXT,
    descripcion TEXT,
    activo TINYINT(1) DEFAULT 1 COMMENT '1=Activo, 0=Inactivo',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- PASO 2: Insertar sedes iniciales (ajusta según tus necesidades)
INSERT INTO sedes_deposito (nombre_sede, direccion, descripcion) VALUES
('Galpón Palo Negro', 'Zona Industrial Palo Negro, Aragua', 'Depósito principal de centralización'),
('Archivo Maracay Centro', 'Edificio Tribunales, Maracay', 'Archivo histórico del centro'),
('Depósito Temporal La Victoria', 'Calle Principal, La Victoria', 'Almacenamiento temporal');

-- PASO 3: Agregar columnas de ubicación a la tabla maestro
ALTER TABLE maestro 
ADD COLUMN id_sede INT DEFAULT NULL COMMENT 'FK a sedes_deposito',
ADD COLUMN ubicacion_area VARCHAR(100) DEFAULT NULL COMMENT 'Ej: Piso 3, Sección A',
ADD COLUMN ubicacion_detalle VARCHAR(255) DEFAULT NULL COMMENT 'Ej: Estante B / Caja 4',
ADD COLUMN fecha_ultima_ubicacion TIMESTAMP NULL DEFAULT NULL COMMENT 'Última vez que se actualizó la ubicación',
ADD INDEX idx_sede (id_sede),
ADD FOREIGN KEY (id_sede) REFERENCES sedes_deposito(id_sede) ON DELETE SET NULL;

-- PASO 4: Verificar estructura
DESCRIBE maestro;
DESCRIBE sedes_deposito;

-- PASO 5: Consulta de prueba
SELECT 
    m.n_expediente,
    m.demandante,
    s.nombre_sede,
    m.ubicacion_area,
    m.ubicacion_detalle,
    m.fecha_ultima_ubicacion
FROM maestro m
LEFT JOIN sedes_deposito s ON m.id_sede = s.id_sede
LIMIT 10;

-- =====================================================
-- FIN DEL SCRIPT
-- =====================================================
