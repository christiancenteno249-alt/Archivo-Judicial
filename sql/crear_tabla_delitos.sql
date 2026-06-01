-- =========================================================================
-- SCRIPT DE CREACIÓN DE LA TABLA DELITOS Y POBLADO INICIAL
-- Archivo: sql/crear_tabla_delitos.sql
-- =========================================================================

-- 1. Crear la tabla de catálogo 'delitos' si no existe
CREATE TABLE IF NOT EXISTS `delitos` (
  `id_delito` INT NOT NULL AUTO_INCREMENT,
  `nombre_delito` VARCHAR(255) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id_delito`),
  UNIQUE KEY `nombre_delito` (`nombre_delito`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 2. Poblar la tabla de forma automática utilizando los delitos ya cargados en 'maestro'
INSERT IGNORE INTO `delitos` (`nombre_delito`)
SELECT DISTINCT TRIM(UPPER(motivo_delito)) 
FROM `maestro` 
WHERE motivo_delito IS NOT NULL AND TRIM(motivo_delito) != '';
