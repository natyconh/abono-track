-- Tabla para gestionar accesos públicos temporales a reportes
CREATE TABLE `reportes_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `empresa_id` int(11) NOT NULL,
  `usuario_creador_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL COMMENT 'Hash único de acceso',
  `tipo_reporte` varchar(50) NOT NULL DEFAULT 'nutricional_temporada',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_expiracion` datetime DEFAULT NULL COMMENT 'NULL = Indefinido',
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_token` (`token`),
  KEY `fk_token_empresa` (`empresa_id`),
  CONSTRAINT `fk_token_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;