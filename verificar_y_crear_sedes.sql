-- =====================================================
-- VERIFICAR Y CREAR TABLA DE SEDES
-- =====================================================

-- PASO 1: Verificar si existe la tabla
SELECT 'Verificando tabla sedes_deposito...' as mensaje;

-- PASO 2: Crear tabla si no existe
CREATE TABLE IF NOT EXISTS sedes_deposito (
    id_sede INT AUTO_INCREMENT PRIMARY KEY,
    nombre_sede VARCHAR(100) NOT NULL UNIQUE,
    direccion TEXT,
    descripcion TEXT,
    activo TINYINT(1) DEFAULT 1 COMMENT '1=Activo, 0=Inactivo',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- PASO 3: Insertar sedes iniciales (solo si no existen)
INSERT IGNORE INTO sedes_deposito (nombre_sede, direccion, descripcion) VALUES
('Galpón Palo Negro', 'Zona Industrial Palo Negro, Aragua', 'Depósito principal de centralización de expedientes judiciales'),
('Archivo Maracay Centro', 'Edificio Tribunales, Maracay', 'Archivo histórico del centro de la ciudad'),
('Depósito Temporal La Victoria', 'Calle Principal, La Victoria', 'Almacenamiento temporal de expedientes en tránsito');

-- PASO 4: Verificar que se insertaron
SELECT * FROM sedes_deposito;

-- PASO 5: Verificar si las columnas existen en maestro
SHOW COLUMNS FROM maestro LIKE 'id_sede';
SHOW COLUMNS FROM maestro LIKE 'ubicacion_area';
SHOW COLUMNS FROM maestro LIKE 'ubicacion_detalle';

-- PASO 6: Agregar columnas si no existen
SET @exist := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
               WHERE TABLE_SCHEMA = DATABASE() 
               AND TABLE_NAME = 'maestro' 
               AND COLUMN_NAME = 'id_sede');

SET @sqlstmt := IF(@exist = 0, 
    'ALTER TABLE maestro 
     ADD COLUMN id_sede INT DEFAULT NULL COMMENT "FK a sedes_deposito",
     ADD COLUMN ubicacion_area VARCHAR(100) DEFAULT NULL COMMENT "Ej: Piso 3, Sección A",
     ADD COLUMN ubicacion_detalle VARCHAR(255) DEFAULT NULL COMMENT "Ej: Estante B / Caja 4",
     ADD COLUMN fecha_ultima_ubicacion TIMESTAMP NULL DEFAULT NULL COMMENT "Última vez que se actualizó la ubicación",
     ADD INDEX idx_sede (id_sede)',
    'SELECT "Las columnas ya existen" as mensaje');

PREPARE stmt FROM @sqlstmt;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- PASO 7: Agregar Foreign Key si no existe
SET @fk_exist := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                  WHERE TABLE_SCHEMA = DATABASE() 
                  AND TABLE_NAME = 'maestro' 
                  AND CONSTRAINT_NAME LIKE '%id_sede%');

SET @fk_stmt := IF(@fk_exist = 0,
    'ALTER TABLE maestro ADD FOREIGN KEY (id_sede) REFERENCES sedes_deposito(id_sede) ON DELETE SET NULL',
    'SELECT "Foreign Key ya existe" as mensaje');

PREPARE fk_stmt FROM @fk_stmt;
EXECUTE fk_stmt;
DEALLOCATE PREPARE fk_stmt;

-- PASO 8: Verificación final
SELECT 'VERIFICACIÓN FINAL:' as mensaje;
SELECT COUNT(*) as total_sedes FROM sedes_deposito;
DESCRIBE maestro;
