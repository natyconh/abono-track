-- =============================================================
-- ABONO TRACK — Schema SQL MVP Final
-- Versión: 1.1.0
-- Alcance: gestión de fertilización, predios, programas y comparación
-- Cambios v1.1.0: columna `estado` en programa_fertilizacion
-- =============================================================

SET NAMES 'utf8mb4';
SET time_zone = '-04:00';

CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(100) NOT NULL,
  `username` VARCHAR(60) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `activo` TINYINT(1) NOT NULL DEFAULT 1,
  `ultimo_login` DATETIME DEFAULT NULL,
  `fecha_creacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cultivos` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `usuario_id` INT UNSIGNED NOT NULL,
  `nombre` VARCHAR(100) NOT NULL,
  `variedad` VARCHAR(100) NOT NULL DEFAULT '',
  `activo` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_cultivos_usuario` (`usuario_id`),
  CONSTRAINT `fk_cultivos_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `fertilizantes` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `usuario_id` INT UNSIGNED NOT NULL,
  `nombre_comercial` VARCHAR(150) NOT NULL,
  `tipo_producto` VARCHAR(100),
  `tipo_unidad` ENUM('kg', 'lt') NOT NULL DEFAULT 'kg',
  `porcentaje_n` DECIMAL(6,2) UNSIGNED DEFAULT 0,
  `porcentaje_p` DECIMAL(6,2) UNSIGNED DEFAULT 0,
  `porcentaje_k` DECIMAL(6,2) UNSIGNED DEFAULT 0,
  `micronutrientes` JSON DEFAULT NULL,
  `densidad` DECIMAL(6,3) UNSIGNED DEFAULT 1.000,
  `activo` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_fertilizantes_usuario` (`usuario_id`),
  CONSTRAINT `fk_fertilizantes_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `predios` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `usuario_id` INT UNSIGNED NOT NULL,
  `cultivo_id` INT UNSIGNED DEFAULT NULL,
  `nombre` VARCHAR(150) NOT NULL,
  `tipo_superficie` ENUM('cultivo','cabezal_virtual','infraestructura') NOT NULL DEFAULT 'cultivo',
  `superficie_total` DECIMAL(10,2) UNSIGNED DEFAULT NULL COMMENT 'Hectáreas',
  `plantas_por_hectarea` INT UNSIGNED DEFAULT NULL,
  `cantidad_plantas` INT UNSIGNED DEFAULT NULL,
  `tipo_emisor` ENUM('gotero','microaspersor','cinta','pivote','surco') DEFAULT NULL,
  `caudal_lt_hora` DECIMAL(8,3) UNSIGNED DEFAULT NULL COMMENT 'L/h por planta',
  `año_plantacion` SMALLINT UNSIGNED DEFAULT NULL,
  `umbral_bajo` TINYINT UNSIGNED NOT NULL DEFAULT 75,
  `umbral_optimo_min` TINYINT UNSIGNED NOT NULL DEFAULT 90,
  `umbral_optimo_max` TINYINT UNSIGNED NOT NULL DEFAULT 110,
  `umbral_exceso` TINYINT UNSIGNED NOT NULL DEFAULT 130,
  `activo` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_predios_usuario` (`usuario_id`),
  INDEX `idx_predios_cultivo` (`cultivo_id`),
  CONSTRAINT `fk_predios_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_predios_cultivo` FOREIGN KEY (`cultivo_id`) REFERENCES `cultivos`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `config_distribucion_riego` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `usuario_id` INT UNSIGNED NOT NULL,
  `predio_origen_id` INT UNSIGNED NOT NULL COMMENT 'Cabezal',
  `predio_destino_id` INT UNSIGNED NOT NULL COMMENT 'Lote/Cuartel',
  `porcentaje_flujo` DECIMAL(5,2) UNSIGNED NOT NULL DEFAULT 100.00,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_cdr_origen_destino` (`predio_origen_id`, `predio_destino_id`),
  INDEX `idx_cdr_usuario` (`usuario_id`),
  CONSTRAINT `fk_cdr_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cdr_origen` FOREIGN KEY (`predio_origen_id`) REFERENCES `predios`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cdr_destino` FOREIGN KEY (`predio_destino_id`) REFERENCES `predios`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `fertilizaciones_cabezal` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `predio_cabezal_id` INT UNSIGNED NOT NULL,
  `usuario_id` INT UNSIGNED NOT NULL,
  `fertilizante_id` INT UNSIGNED NOT NULL,
  `fecha` DATE NOT NULL,
  `cantidad_aplicada` DECIMAL(10,2) NOT NULL,
  `fecha_registro` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_fc_usuario_fecha` (`usuario_id`, `fecha`),
  INDEX `idx_fc_predio` (`predio_cabezal_id`),
  INDEX `idx_fc_fertilizante` (`fertilizante_id`),
  CONSTRAINT `fk_fert_cab_predio` FOREIGN KEY (`predio_cabezal_id`) REFERENCES `predios`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_fert_cab_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_fert_cab_fert` FOREIGN KEY (`fertilizante_id`) REFERENCES `fertilizantes`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `fertilizaciones_reales` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `fertilizacion_cabezal_id` INT UNSIGNED NOT NULL,
  `predio_destino_id` INT UNSIGNED NOT NULL,
  `cantidad_recibida` DECIMAL(10,2) NOT NULL,
  `unidades_n` DECIMAL(10,4) NOT NULL DEFAULT 0,
  `unidades_p` DECIMAL(10,4) NOT NULL DEFAULT 0,
  `unidades_k` DECIMAL(10,4) NOT NULL DEFAULT 0,
  `unidades_micronutrientes` JSON DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_fr_cabezal` (`fertilizacion_cabezal_id`),
  INDEX `idx_fr_predio` (`predio_destino_id`),
  CONSTRAINT `fk_fert_real_cab` FOREIGN KEY (`fertilizacion_cabezal_id`) REFERENCES `fertilizaciones_cabezal`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_fert_real_predio` FOREIGN KEY (`predio_destino_id`) REFERENCES `predios`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `programa_fertilizacion` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `usuario_id` INT UNSIGNED NOT NULL,
  `predio_id` INT UNSIGNED NOT NULL,
  `cultivo_id` INT UNSIGNED DEFAULT NULL,
  `temporada` VARCHAR(9) NOT NULL COMMENT 'Ej: 2025 o 2025/2026',
  -- Estado del programa: activo = se usa en reportes; archivado = cerrado, no influye en alertas;
  -- borrador = en preparación, tampoco influye en reportes.
  `estado` ENUM('activo','archivado','borrador') NOT NULL DEFAULT 'activo' COMMENT 'activo=visible en reportes, archivado=cerrado, borrador=en preparación',
  `semana` TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `fecha_estimada` DATE NOT NULL,
  `n_objetivo` DECIMAL(8,3) UNSIGNED NOT NULL DEFAULT 0,
  `p_objetivo` DECIMAL(8,3) UNSIGNED NOT NULL DEFAULT 0,
  `k_objetivo` DECIMAL(8,3) UNSIGNED NOT NULL DEFAULT 0,
  `micronutrientes_objetivo` JSON DEFAULT NULL,
  `observaciones` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_programa_predio_temporada_semana` (`predio_id`, `temporada`, `semana`),
  INDEX `idx_pf_usuario` (`usuario_id`),
  INDEX `idx_pf_predio` (`predio_id`),
  INDEX `idx_pf_temporada` (`temporada`),
  INDEX `idx_pf_fecha` (`fecha_estimada`),
  INDEX `idx_pf_estado` (`estado`),
  CONSTRAINT `fk_pf_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pf_predio` FOREIGN KEY (`predio_id`) REFERENCES `predios`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pf_cultivo` FOREIGN KEY (`cultivo_id`) REFERENCES `cultivos`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Programa semanal de fertilización NPK por predio y temporada';

-- ================================================================
-- SCRIPT DE MIGRACIÓN PARA INSTALACIONES EXISTENTES (v1.0 → v1.1)
-- Ejecutar manualmente si la tabla ya existe en producción:
-- ================================================================
-- ALTER TABLE `programa_fertilizacion`
--   ADD COLUMN `estado` ENUM('activo','archivado','borrador')
--     NOT NULL DEFAULT 'activo'
--     COMMENT 'activo=visible en reportes, archivado=cerrado, borrador=en preparación'
--     AFTER `temporada`,
--   ADD INDEX `idx_pf_estado` (`estado`);
