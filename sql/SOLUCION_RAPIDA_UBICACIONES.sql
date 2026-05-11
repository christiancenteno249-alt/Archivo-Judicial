-- =====================================================
-- SOLUCIÓN RÁPIDA: Agregar columnas de ubicación
-- =====================================================

-- PASO 1: Verificar si las columnas ya existen
SELECT 
    COLUMN_NAME,
    DATA_TYPE,
    IS_NULLABLE,
    COLUMN_DEFAULT
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME = 'maestro'
AND COLUMN_NAME IN ('id_sede', 'ubicacion_area', 'ubicacion_detalle', 'fecha_ultima_ubicacion');

-- PASO 2: Agregar columnas si no existen
-- (Este script es seguro, no dará error si ya existen)

-- Agregar id_sede
SET @exist := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
               WHERE TABLE_SCHEMA = DATABASE() 
               AND TABLE_NAME = 'maestro' 
               AND COLUMN_NAME = 'id_sede');

SET @sqlstmt := IF(@exist = 0, 
    'ALTER TABLE maestro ADD COLUMN id_sede INT DEFAULT NULL COMMENT "FK a sedes_deposito"',
    'SELECT "Columna id_sede ya existe" as mensaje');

PREPARE stmt FROM @sqlstmt;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Agregar ubicacion_area
SET @exist := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
               WHERE TABLE_SCHEMA = DATABASE() 
               AND TABLE_NAME = 'maestro' 
               AND COLUMN_NAME = 'ubicacion_area');

SET @sqlstmt := IF(@exist = 0, 
    'ALTER TABLE maestro ADD COLUMN ubicacion_area VARCHAR(100) DEFAULT NULL COMMENT "Ej: Piso 3, Sección A"',
    'SELECT "Columna ubicacion_area ya existe" as mensaje');

PREPARE stmt FROM @sqlstmt;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Agregar ubicacion_detalle
SET @exist := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
               WHERE TABLE_SCHEMA = DATABASE() 
               AND TABLE_NAME = 'maestro' 
               AND COLUMN_NAME = 'ubicacion_detalle');

SET @sqlstmt := IF(@exist = 0, 
    'ALTER TABLE maestro ADD COLUMN ubicacion_detalle VARCHAR(255) DEFAULT NULL COMMENT "Ej: Estante B / Caja 4"',
    'SELECT "Columna ubicacion_detalle ya existe" as mensaje');

PREPARE stmt FROM @sqlstmt;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Agregar fecha_ultima_ubicacion
SET @exist := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
               WHERE TABLE_SCHEMA = DATABASE() 
               AND TABLE_NAME = 'maestro' 
               AND COLUMN_NAME = 'fecha_ultima_ubicacion');

SET @sqlstmt := IF(@exist = 0, 
    'ALTER TABLE maestro ADD COLUMN fecha_ultima_ubicacion TIMESTAMP NULL DEFAULT NULL COMMENT "Última actualización de ubicación"',
    'SELECT "Columna fecha_ultima_ubicacion ya existe" as mensaje');

PREPARE stmt FROM @sqlstmt;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- PASO 3: Agregar índice en id_sede
SET @exist := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
               WHERE TABLE_SCHEMA = DATABASE() 
               AND TABLE_NAME = 'maestro' 
               AND INDEX_NAME = 'idx_sede');

SET @sqlstmt := IF(@exist = 0, 
    'ALTER TABLE maestro ADD INDEX idx_sede (id_sede)',
    'SELECT "Índice idx_sede ya existe" as mensaje');

PREPARE stmt FROM @sqlstmt;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- PASO 4: Crear tabla sedes_deposito si no existe
CREATE TABLE IF NOT EXISTS sedes_deposito (
    id_sede INT AUTO_INCREMENT PRIMARY KEY,
    nombre_sede VARCHAR(100) NOT NULL UNIQUE,
    direccion TEXT,
    descripcion TEXT,
    activo TINYINT(1) DEFAULT 1 COMMENT '1=Activo, 0=Inactivo',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- PASO 5: Insertar sedes si la tabla está vacía
INSERT IGNORE INTO sedes_deposito (nombre_sede, direccion, descripcion, activo) VALUES
('Galpón Palo Negro - Depósito Central', 
 'Zona Industrial Palo Negro, Sector Valle Lindo, Frente al Cementerio Municipal, Palo Negro, Estado Aragua',
 'Depósito principal de centralización de expedientes judiciales del Estado Aragua. Capacidad para almacenamiento masivo de expedientes históricos y activos.',
 1),

('Archivo Judicial Maracay Centro',
 'Edificio Tribunales, Calle Principal, Centro Comercial Profesional, Maracay, Estado Aragua',
 'Archivo histórico ubicado en el centro de Maracay. Almacena expedientes de tribunales civiles, mercantiles y penales del área metropolitana.',
 1),

('Depósito Temporal La Victoria',
 'Calle Principal Córdoba, Centro Comercial Dorado, Piso 2, La Victoria, Estado Aragua',
 'Almacenamiento temporal de expedientes en tránsito. Utilizado para expedientes en proceso de clasificación.',
 1),

('Archivo Judicial Cagua',
 'Calle Rivas Chacón, Sector Valle Lindo, Cagua, Estado Aragua',
 'Depósito regional para expedientes de la zona sur del estado. Almacena expedientes de tribunales de Cagua y zonas aledañas.',
 1),

('Depósito Villa de Cura',
 'Avenida Principal, Sector Centro, Villa de Cura, Estado Aragua',
 'Archivo regional para expedientes de la Circunscripción Judicial del Sur de Aragua.',
 1);

-- PASO 6: Agregar Foreign Key si no existe
SET @fk_exist := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                  WHERE TABLE_SCHEMA = DATABASE() 
                  AND TABLE_NAME = 'maestro' 
                  AND COLUMN_NAME = 'id_sede'
                  AND REFERENCED_TABLE_NAME = 'sedes_deposito');

SET @fk_stmt := IF(@fk_exist = 0,
    'ALTER TABLE maestro ADD CONSTRAINT fk_maestro_sede FOREIGN KEY (id_sede) REFERENCES sedes_deposito(id_sede) ON DELETE SET NULL',
    'SELECT "Foreign Key ya existe" as mensaje');

PREPARE fk_stmt FROM @fk_stmt;
EXECUTE fk_stmt;
DEALLOCATE PREPARE fk_stmt;

-- PASO 7: VERIFICACIÓN FINAL
SELECT '========== VERIFICACIÓN FINAL ==========' as mensaje;

-- Verificar columnas en maestro
SELECT 
    COLUMN_NAME,
    DATA_TYPE,
    IS_NULLABLE,
    COLUMN_DEFAULT,
    COLUMN_COMMENT
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME = 'maestro'
AND COLUMN_NAME IN ('id_sede', 'ubicacion_area', 'ubicacion_detalle', 'fecha_ultima_ubicacion')
ORDER BY ORDINAL_POSITION;

-- Verificar sedes
SELECT '========== SEDES REGISTRADAS ==========' as mensaje;
SELECT id_sede, nombre_sede, activo FROM sedes_deposito ORDER BY id_sede;

-- Verificar Foreign Key
SELECT '========== FOREIGN KEYS ==========' as mensaje;
SELECT 
    CONSTRAINT_NAME,
    COLUMN_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME = 'maestro'
AND COLUMN_NAME = 'id_sede'
AND REFERENCED_TABLE_NAME IS NOT NULL;

SELECT '========== ✅ PROCESO COMPLETADO ==========' as mensaje;

