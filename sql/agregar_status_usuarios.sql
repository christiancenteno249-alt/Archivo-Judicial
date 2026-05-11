-- =====================================================
-- SCRIPT: Agregar columna STATUS para Borrado Lógico
-- Tabla: usuarios_sistema
-- Fecha: 13/04/2026
-- =====================================================

-- Agregar columna status (1 = activo, 0 = inactivo/eliminado)
ALTER TABLE usuarios_sistema 
ADD COLUMN status INT DEFAULT 1 NOT NULL 
COMMENT '1=Activo, 0=Inactivo/Eliminado';

-- Actualizar todos los usuarios existentes a status = 1 (activos)
UPDATE usuarios_sistema SET status = 1;

-- Verificar que la columna se agregó correctamente
SELECT id_usuario, nombre_full, usuario_nick, rol, status, fecha_registro 
FROM usuarios_sistema;
