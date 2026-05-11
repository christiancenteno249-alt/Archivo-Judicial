-- =====================================================
-- AMPLIAR CAMPO nombre_sede EN sedes_deposito
-- De VARCHAR(100) a VARCHAR(255)
-- =====================================================

-- PASO 1: Verificar longitud actual
SELECT 
    COLUMN_NAME,
    DATA_TYPE,
    CHARACTER_MAXIMUM_LENGTH,
    COLUMN_TYPE
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME = 'sedes_deposito'
AND COLUMN_NAME = 'nombre_sede';

-- PASO 2: Ampliar el campo a 255 caracteres
ALTER TABLE sedes_deposito 
MODIFY COLUMN nombre_sede VARCHAR(255) NOT NULL UNIQUE;

-- PASO 3: Verificar que se aplicó el cambio
SELECT 
    COLUMN_NAME,
    DATA_TYPE,
    CHARACTER_MAXIMUM_LENGTH,
    COLUMN_TYPE
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME = 'sedes_deposito'
AND COLUMN_NAME = 'nombre_sede';

-- PASO 4: Ver las sedes actuales y sus longitudes
SELECT 
    id_sede,
    nombre_sede,
    LENGTH(nombre_sede) as longitud_actual,
    direccion
FROM sedes_deposito
ORDER BY id_sede;

-- =====================================================
-- RESULTADO ESPERADO:
-- CHARACTER_MAXIMUM_LENGTH debe ser 255
-- =====================================================
