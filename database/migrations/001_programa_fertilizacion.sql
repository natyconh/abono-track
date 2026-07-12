-- =============================================================
-- MIGRACIÓN 001: Tabla programa_fertilizacion
-- Abono Track — Programación NPK por predio y temporada
-- Ejecutar UNA SOLA VEZ sobre la base de datos abono_track
-- =============================================================

CREATE TABLE IF NOT EXISTS `programa_fertilizacion` (
    `id`                         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `usuario_id`                 INT UNSIGNED NOT NULL,
    `predio_id`                  INT UNSIGNED NOT NULL,
    `cultivo_id`                 INT UNSIGNED DEFAULT NULL,

    -- Temporada: año de inicio (ej. 2025 = temporada 2025/2026)
    `temporada`                  VARCHAR(9)   NOT NULL COMMENT 'Ej: 2025 o 2025/2026',

    -- Semana del año agronómico (1-52)
    `semana`                     TINYINT UNSIGNED NOT NULL DEFAULT 1,

    -- Fecha estimada de aplicación de esa semana
    `fecha_estimada`             DATE NOT NULL,

    -- Objetivos NPK en kg/ha para ESA semana
    `n_objetivo`                 DECIMAL(8,3) UNSIGNED NOT NULL DEFAULT 0,
    `p_objetivo`                 DECIMAL(8,3) UNSIGNED NOT NULL DEFAULT 0,
    `k_objetivo`                 DECIMAL(8,3) UNSIGNED NOT NULL DEFAULT 0,

    -- Micronutrientes objetivo como JSON (igual que fertilizantes)
    `micronutrientes_objetivo`   JSON DEFAULT NULL,

    `observaciones`              TEXT DEFAULT NULL,
    `created_at`                 TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`                 TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    -- Un predio no puede tener dos programas para la misma semana/temporada
    UNIQUE KEY `uq_programa_predio_temporada_semana` (`predio_id`, `temporada`, `semana`),

    INDEX `idx_pf_usuario`    (`usuario_id`),
    INDEX `idx_pf_predio`     (`predio_id`),
    INDEX `idx_pf_temporada`  (`temporada`),
    INDEX `idx_pf_fecha`      (`fecha_estimada`),

    CONSTRAINT `fk_pf_usuario`  FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`)  ON DELETE CASCADE,
    CONSTRAINT `fk_pf_predio`   FOREIGN KEY (`predio_id`)  REFERENCES `predios`(`id`)   ON DELETE CASCADE,
    CONSTRAINT `fk_pf_cultivo`  FOREIGN KEY (`cultivo_id`) REFERENCES `cultivos`(`id`)  ON DELETE SET NULL

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Programa semanal de fertilización NPK por predio y temporada';

-- -------------------------------------------------------------
-- DATOS DE EJEMPLO (comentados — descomenta para demo)
-- Requiere que existan usuario_id=1, predio_id=1, cultivo_id=1
-- -------------------------------------------------------------
/*
INSERT INTO `programa_fertilizacion`
    (usuario_id, predio_id, cultivo_id, temporada, semana, fecha_estimada,
     n_objetivo, p_objetivo, k_objetivo, observaciones)
VALUES
    (1, 1, 1, '2025', 1,  '2025-09-01', 2.500, 1.200, 3.000, 'Inicio temporada'),
    (1, 1, 1, '2025', 2,  '2025-09-08', 2.500, 1.200, 3.000, NULL),
    (1, 1, 1, '2025', 3,  '2025-09-15', 3.000, 1.500, 3.500, 'Crecimiento activo'),
    (1, 1, 1, '2025', 4,  '2025-09-22', 3.000, 1.500, 3.500, NULL),
    (1, 1, 1, '2025', 40, '2026-06-01', 1.500, 0.800, 2.000, 'Pre-cosecha'),
    (1, 1, 1, '2025', 41, '2026-06-08', 1.500, 0.800, 2.000, NULL);
*/
