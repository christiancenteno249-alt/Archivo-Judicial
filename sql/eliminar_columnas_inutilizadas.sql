-- Script para eliminar campos sin uso de la tabla maestro

ALTER TABLE maestro
DROP COLUMN fecha_sentencia,
DROP COLUMN desicion;
