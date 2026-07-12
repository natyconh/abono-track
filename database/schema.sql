-- =============================================================
-- ABONO TRACK — Schema SQL MVP (Ajustado)
-- Versión: 0.1.1
-- Sin multitenancy, sin roles, sin sectores.
-- =============================================================

SET NAMES 'utf8mb4';
SET time_zone = '-04:00';

-- -------------------------------------------------------------
-- 1. USUARIOS (Simplificado, sin roles)
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `usuarios` (
    `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nombre`        VARCHAR(100) NOT NULL,
    `username`      VARCHAR(60)  NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `activo`        TINYINT(1) NOT NULL DEFAULT 1,
    `ultimo_login`  DATETIME DEFAULT NULL,
    `fecha_creacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- 2. CULTIVOS
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `cultivos` (
    `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `usuario_id`    INT UNSIGNED NOT NULL,
    `nombre`        VARCHAR(100) NOT NULL,
    `variedad`      VARCHAR(100) NOT NULL DEFAULT '',
    `activo`        TINYINT(1) NOT NULL DEFAULT 1,
    `created_at`    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_cultivos_usuario` (`usuario_id`),
    CONSTRAINT `fk_cultivos_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- 3. FERTILIZANTES / PRODUCTOS
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `fertilizantes` (
    `id`                INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `usuario_id`        INT UNSIGNED NOT NULL,
    `nombre_comercial`  VARCHAR(150) NOT NULL,
    `tipo_producto`     VARCHAR(100),
    `tipo_unidad`       ENUM('kg', 'lt') NOT NULL DEFAULT 'kg',
    `porcentaje_n`      DECIMAL(6,2) UNSIGNED DEFAULT 0,
    `porcentaje_p`      DECIMAL(6,2) UNSIGNED DEFAULT 0,
    `porcentaje_k`      DECIMAL(6,2) UNSIGNED DEFAULT 0,
    `micronutrientes`   JSON DEFAULT NULL,
    `densidad`          DECIMAL(6,3) UNSIGNED DEFAULT 1.000,
    `activo`            TINYINT(1) NOT NULL DEFAULT 1,
    `created_at`        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_fertilizantes_usuario` (`usuario_id`),
    CONSTRAINT `fk_fertilizantes_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- 4. PREDIOS (Lotes/Cuarteles/Cabezales)
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `predios` (
    `id`                  INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `usuario_id`          INT UNSIGNED NOT NULL,
    `cultivo_id`          INT UNSIGNED DEFAULT NULL,
    `nombre`              VARCHAR(150) NOT NULL,
    `tipo_superficie`     ENUM('cultivo','cabezal_virtual','infraestructura') NOT NULL DEFAULT 'cultivo',
    `superficie_total`    DECIMAL(10,2) UNSIGNED DEFAULT NULL COMMENT 'Hectáreas',
    `plantas_por_hectarea` INT UNSIGNED DEFAULT NULL,
    `cantidad_plantas`    INT UNSIGNED DEFAULT NULL,
    `tipo_emisor`         ENUM('gotero','microaspersor','cinta','pivote','surco') DEFAULT NULL,
    `caudal_lt_hora`      DECIMAL(8,3) UNSIGNED DEFAULT NULL COMMENT 'L/h por planta',
    `año_plantacion`      SMALLINT UNSIGNED DEFAULT NULL,
    `umbral_bajo`         TINYINT UNSIGNED NOT NULL DEFAULT 75,
    `umbral_optimo_min`   TINYINT UNSIGNED NOT NULL DEFAULT 90,
    `umbral_optimo_max`   TINYINT UNSIGNED NOT NULL DEFAULT 110,
    `umbral_exceso`       TINYINT UNSIGNED NOT NULL DEFAULT 130,
    `activo`              TINYINT(1) NOT NULL DEFAULT 1,
    `created_at`          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_predios_usuario` (`usuario_id`),
    INDEX `idx_predios_cultivo` (`cultivo_id`),
    CONSTRAINT `fk_predios_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_predios_cultivo` FOREIGN KEY (`cultivo_id`) REFERENCES `cultivos`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- 5. CONFIGURACIÓN DE DISTRIBUCIÓN DE RIEGO (Corregido para NPK)
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `config_distribucion_riego` (
    `id`                 INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `usuario_id`         INT UNSIGNED NOT NULL,
    `predio_origen_id`   INT UNSIGNED NOT NULL COMMENT 'Cabezal',
    `predio_destino_id`  INT UNSIGNED NOT NULL COMMENT 'Lote/Cuartel',
    `porcentaje_flujo`   DECIMAL(5,2) UNSIGNED NOT NULL DEFAULT 100.00,
    PRIMARY KEY (`id`),
    INDEX `idx_cdr_usuario` (`usuario_id`),
    CONSTRAINT `fk_cdr_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_cdr_origen`  FOREIGN KEY (`predio_origen_id`) REFERENCES `predios`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_cdr_destino` FOREIGN KEY (`predio_destino_id`) REFERENCES `predios`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- 6. REGISTROS DE RIEGO DIARIO (Sin exclusiones)
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `registros_riegos` (
    `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `usuario_id`      INT UNSIGNED NOT NULL,
    `predio_id`       INT UNSIGNED NOT NULL,
    `fecha`           DATE         NOT NULL,
    `tiempo_riego`    SMALLINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Minutos',
    `created_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_riego_predio_fecha` (`predio_id`, `fecha`),
    CONSTRAINT `fk_rr_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_rr_predio`  FOREIGN KEY (`predio_id`)  REFERENCES `predios`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- 7. FERTILIZACIÓN (Cabezal y Reales)
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `fertilizaciones_cabezal` (
    `id`                INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `predio_cabezal_id` INT UNSIGNED NOT NULL,
    `usuario_id`        INT UNSIGNED NOT NULL,
    `fertilizante_id`   INT UNSIGNED NOT NULL,
    `fecha`             DATE NOT NULL,
    `cantidad_aplicada` DECIMAL(10,2) NOT NULL,
    `fecha_registro`    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_fert_cab_predio` FOREIGN KEY (`predio_cabezal_id`) REFERENCES `predios`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_fert_cab_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_fert_cab_fert` FOREIGN KEY (`fertilizante_id`) REFERENCES `fertilizantes`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `fertilizaciones_reales` (
    `id`                       INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `fertilizacion_cabezal_id` INT UNSIGNED NOT NULL,
    `predio_destino_id`        INT UNSIGNED NOT NULL,
    `cantidad_recibida`        DECIMAL(10,2) NOT NULL,
    `unidades_n`               DECIMAL(10,4) NOT NULL DEFAULT 0,
    `unidades_p`               DECIMAL(10,4) NOT NULL DEFAULT 0,
    `unidades_k`               DECIMAL(10,4) NOT NULL DEFAULT 0,
    `unidades_micronutrientes` JSON DEFAULT NULL,
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_fert_real_cab` FOREIGN KEY (`fertilizacion_cabezal_id`) REFERENCES `fertilizaciones_cabezal`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_fert_real_predio` FOREIGN KEY (`predio_destino_id`) REFERENCES `predios`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
