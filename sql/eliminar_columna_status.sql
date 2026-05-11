-- Script para eliminar la columna 'status' de la tabla maestro
-- IMPORTANTE: Hacer respaldo de la base de datos ANTES de ejecutar este script

-- Verificar que la columna existe antes de eliminarla
-- (Este comando mostrará la estructura de la tabla)
DESCRIBE maestro;

-- Eliminar la columna 'status' de la tabla maestro
ALTER TABLE maestro DROP COLUMN status;

-- Verificar que la columna fue eliminada correctamente
DESCRIBE maestro;

-- Mensaje de confirmación
SELECT 'Columna status eliminada exitosamente de la tabla maestro' AS resultado;
