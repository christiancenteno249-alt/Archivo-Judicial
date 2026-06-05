-- Script para agregar campos de apellidos a la tabla maestro

ALTER TABLE maestro
ADD COLUMN apellidos_demandante VARCHAR(255) NULL AFTER demandante,
ADD COLUMN apellidos_demandado VARCHAR(255) NULL AFTER demandado;
