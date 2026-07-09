-- Actualización v2.1: Umbrales de Riego Configurables por Predio
-- Ejecutar este script en tu base de datos MySQL

ALTER TABLE `predios`
ADD COLUMN `umbral_bajo` INT DEFAULT 75 COMMENT 'Bajo esto es Crítico (Rojo)',
ADD COLUMN `umbral_optimo_min` INT DEFAULT 90 COMMENT 'Desde aquí es Óptimo (Verde)',
ADD COLUMN `umbral_optimo_max` INT DEFAULT 110 COMMENT 'Hasta aquí es Óptimo (Verde)',
ADD COLUMN `umbral_exceso` INT DEFAULT 130 COMMENT 'Sobre esto es Exceso Crítico (Azul)';

-- Explicación de Rangos resultantes por defecto:
-- < 75: Déficit Crítico (Rojo)
-- 75 - 90: Déficit Leve (Amarillo)
-- 90 - 110: Óptimo (Verde)
-- 110 - 130: Sobre-riego Leve (Celeste)
-- > 130: Exceso Crítico (Azul Oscuro)