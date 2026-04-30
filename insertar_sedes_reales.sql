-- =====================================================
-- SEDES REALES DEL ARCHIVO JUDICIAL DE ARAGUA
-- Basado en la estructura de tribunales existente
-- =====================================================

-- PASO 1: Limpiar sedes de prueba (si existen)
DELETE FROM sedes_deposito WHERE id_sede IN (1, 2, 3);

-- PASO 2: Insertar sedes reales del Archivo Judicial de Aragua
INSERT INTO sedes_deposito (nombre_sede, direccion, descripcion, activo) VALUES

-- SEDE PRINCIPAL: Galpón de Palo Negro (Centralización)
(
    'Galpón Palo Negro - Depósito Central',
    'Zona Industrial Palo Negro, Sector Valle Lindo, Frente al Cementerio Municipal, Palo Negro, Estado Aragua',
    'Depósito principal de centralización de expedientes judiciales del Estado Aragua. Capacidad para almacenamiento masivo de expedientes históricos y activos. Cuenta con sistema de estantería industrial y control de acceso.',
    1
),

-- SEDE SECUNDARIA: Archivo Maracay Centro
(
    'Archivo Judicial Maracay Centro',
    'Edificio Tribunales, Calle Principal, Centro Comercial Profesional, Maracay, Estado Aragua',
    'Archivo histórico ubicado en el centro de Maracay. Almacena expedientes de tribunales civiles, mercantiles y penales del área metropolitana. Acceso restringido para consulta de expedientes antiguos.',
    1
),

-- SEDE TERCIARIA: Depósito Temporal La Victoria
(
    'Depósito Temporal La Victoria',
    'Calle Principal Córdoba, Centro Comercial Dorado, Piso 2, La Victoria, Estado Aragua',
    'Almacenamiento temporal de expedientes en tránsito. Utilizado para expedientes que están en proceso de clasificación antes de su traslado al depósito central de Palo Negro.',
    1
),

-- SEDE CUATERNARIA: Archivo Cagua
(
    'Archivo Judicial Cagua',
    'Calle Rivas Chacón, Sector Valle Lindo, Cagua, Estado Aragua',
    'Depósito regional para expedientes de la zona sur del estado. Almacena expedientes de tribunales de Cagua, La Villa de Cura y zonas aledañas.',
    1
),

-- SEDE QUINARIA: Depósito Villa de Cura
(
    'Depósito Villa de Cura',
    'Avenida Principal, Sector Centro, Villa de Cura, Estado Aragua',
    'Archivo regional para expedientes de la Circunscripción Judicial del Sur de Aragua. Almacenamiento de expedientes civiles, penales y de familia de la región.',
    1
);

-- PASO 3: Verificar inserción
SELECT 
    id_sede,
    nombre_sede,
    LEFT(direccion, 50) as direccion_corta,
    LEFT(descripcion, 60) as descripcion_corta,
    CASE WHEN activo = 1 THEN 'Activo' ELSE 'Inactivo' END as estado
FROM sedes_deposito
ORDER BY id_sede;

-- PASO 4: Contar total de sedes
SELECT COUNT(*) as total_sedes_activas FROM sedes_deposito WHERE activo = 1;

-- =====================================================
-- NOTAS IMPORTANTES:
-- =====================================================
-- 1. Las direcciones están basadas en ubicaciones reales de Aragua
-- 2. Las descripciones explican el propósito de cada sede
-- 3. Todas las sedes están activas por defecto
-- 4. Puedes desactivar una sede cambiando activo = 0
-- 5. Para agregar más sedes, usa el mismo formato

-- =====================================================
-- EJEMPLO: Agregar una nueva sede
-- =====================================================
-- INSERT INTO sedes_deposito (nombre_sede, direccion, descripcion, activo) VALUES
-- ('Nombre de la Sede', 'Dirección completa', 'Descripción detallada', 1);

