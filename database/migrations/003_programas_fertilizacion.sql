-- ============================================================
-- Migración 003: Módulo Programas de Fertilización
-- Abono Track — ejecutar una sola vez sobre la BD de producción
-- ============================================================

CREATE TABLE IF NOT EXISTS `programas_fertilizacion` (
    `id`                         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `predio_id`                  INT UNSIGNED NOT NULL,
    `cultivo_id`                 INT UNSIGNED DEFAULT NULL,
    `temporada`                  VARCHAR(20)  NOT NULL COMMENT 'Año inicio de temporada, ej: 2026',
    `semana`                     TINYINT UNSIGNED NOT NULL COMMENT 'Número de semana 1-52 dentro del programa',
    `fecha_estimada`             DATE         NOT NULL COMMENT 'Fecha prevista de aplicación',
    `n_objetivo`                 DECIMAL(10,4) NOT NULL DEFAULT 0 COMMENT 'Unidades N objetivo (kg o lt según acuerdo)',
    `p_objetivo`                 DECIMAL(10,4) NOT NULL DEFAULT 0,
    `k_objetivo`                 DECIMAL(10,4) NOT NULL DEFAULT 0,
    `micronutrientes_objetivo`   JSON          DEFAULT NULL COMMENT 'JSON: {"Ca": 1.5, "Mg": 0.8}',
    `observaciones`              TEXT          DEFAULT NULL,
    `created_at`                 TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`                 TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_predio_temporada` (`predio_id`, `temporada`),
    KEY `idx_semana` (`semana`),
    CONSTRAINT `fk_prog_predio`  FOREIGN KEY (`predio_id`)  REFERENCES `predios`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_prog_cultivo` FOREIGN KEY (`cultivo_id`) REFERENCES `cultivos`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Programa de fertilización semanal planificado por predio y temporada';

-- Fix datos históricos: limpiar micronutrientes vacíos que impiden visualizarlos en el reporte
UPDATE fertilizaciones_reales
   SET unidades_micronutrientes = NULL
 WHERE unidades_micronutrientes IS NOT NULL
   AND TRIM(unidades_micronutrientes) IN ('', '{}', '[]', 'null');
