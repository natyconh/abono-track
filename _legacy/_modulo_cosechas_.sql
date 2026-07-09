-- ============================================================
-- MÓDULO DE COSECHAS - RYZOMA AGRO v5.1
-- ============================================================

-- 1. ENTIDADES LEGALES (Resolución de Multi-RUT)
-- Permite que una misma cuenta gestione predios de distintos dueños legales.
CREATE TABLE `entidades_legales` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `empresa_id` int(11) NOT NULL,
  `rut` varchar(20) NOT NULL COMMENT 'RUT de la razón social dueña de la fruta',
  `razon_social` varchar(255) NOT NULL,
  `nombre_fantasia` varchar(255) DEFAULT NULL,
  `codigo_sag` varchar(50) DEFAULT NULL COMMENT 'Código Productor SAG (CSG) para exportación',
  `direccion` varchar(255) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  
  PRIMARY KEY (`id`),
  KEY `fk_entidad_empresa` (`empresa_id`),
  CONSTRAINT `fk_entidad_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. DESTINOS DE COSECHA (Catálogo de Clientes)
CREATE TABLE `cosechas_destinos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `empresa_id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL COMMENT 'Ej: Agrícola Propal, Mercado Lo Valledor',
  `rut` varchar(20) DEFAULT NULL,
  `tipo` enum('Exportadora','Mercado Interno','Descarte','Procesadora','Otro') NOT NULL DEFAULT 'Exportadora',
  `activo` tinyint(1) DEFAULT 1,
  
  PRIMARY KEY (`id`),
  KEY `fk_destino_empresa` (`empresa_id`),
  CONSTRAINT `fk_destino_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. CONFIGURACIÓN OPERATIVA (Respuestas del Wizard)
-- Define cómo se comporta la UI para el operario (Javier vs Ana).
CREATE TABLE `cosechas_configuracion` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `empresa_id` int(11) NOT NULL,
  
  -- Configuración de Pesaje
  `modo_pesaje` enum('NETO','BRUTO_TARA') NOT NULL DEFAULT 'NETO' 
  COMMENT 'NETO: Ingresa kg finales. BRUTO_TARA: Ingresa Kg Bruto y Envases, sistema resta tara.',
  
  -- Configuración Legal
  `nivel_trazabilidad_legal` enum('EMPRESA','PREDIO','SECTOR') NOT NULL DEFAULT 'EMPRESA'
  COMMENT 'Define de dónde saca el sistema el dueño legal de la fruta automáticamente.',
  
  -- Configuración de Detalle UI
  `requiere_calidad` tinyint(1) DEFAULT 1 COMMENT 'Si es 0, oculta el selector de calidad',
  `requiere_tipo_cosecha` tinyint(1) DEFAULT 0 COMMENT 'Si es 0, asume Manual por defecto',
  
  `fecha_configuracion` timestamp DEFAULT current_timestamp(),
  
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_conf_empresa` (`empresa_id`),
  CONSTRAINT `fk_conf_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. ACTUALIZACIÓN DE SECTORES (Asociación Legal)
-- Agregamos la columna para definir el dueño a nivel de Sector (tu requerimiento).
-- NOTA: Si la tabla ya tiene datos, esto solo agrega la columna vacía.
ALTER TABLE `sectores`
ADD COLUMN `entidad_legal_id` int(11) NULL COMMENT 'Dueño legal de la fruta de este sector (Opcional)',
ADD CONSTRAINT `fk_sector_entidad` FOREIGN KEY (`entidad_legal_id`) REFERENCES `entidades_legales` (`id`) ON DELETE SET NULL;

-- 5. REGISTRO DE COSECHAS (Tabla Transaccional Flexible)
-- Diseñada para aceptar NULOS en campos que el "Wizard" decida ocultar.
CREATE TABLE `cosechas_registros` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `empresa_id` int(11) NOT NULL,
  
  -- Origen
  `predio_id` int(11) NOT NULL,
  `sector_id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  
  -- Trazabilidad (Se llenan automáticos según config si el user no los ve)
  `entidad_legal_id` int(11) DEFAULT NULL COMMENT 'El dueño legal de esta carga específica',
  `destino_id` int(11) NOT NULL,
  `folio_guia_despacho` varchar(50) DEFAULT NULL,
  
  -- Detalles Productivos
  `variedad` varchar(100) DEFAULT NULL COMMENT 'Snapshot de la variedad al momento de cosecha',
  `tipo_cosecha` enum('Manual','Mecanizada') DEFAULT 'Manual',
  `calidad_declarada` enum('Exportación','Nacional','Descarte') DEFAULT 'Exportación',
  
  -- Datos de Peso (Flexible según modo_pesaje)
  `tipo_envase` varchar(50) DEFAULT NULL COMMENT 'Ej: Bin Plástico, Caja Madera',
  `cantidad_envases` int(11) DEFAULT 0,
  `kilos_brutos` decimal(10,2) DEFAULT NULL COMMENT 'Peso total báscula',
  `tara_promedio` decimal(10,2) DEFAULT NULL COMMENT 'Tara unitaria del envase',
  `kilos_netos` decimal(10,2) NOT NULL COMMENT 'Dato final real (Calculado o Ingresado)',
  
  -- Auditoría
  `usuario_id` int(11) DEFAULT NULL COMMENT 'Quién registró (Javier o Ana)',
  `fecha_registro` timestamp DEFAULT current_timestamp(),
  `notas` text DEFAULT NULL,
  
  PRIMARY KEY (`id`),
  KEY `idx_cosecha_fecha` (`fecha`),
  KEY `fk_cosecha_empresa` (`empresa_id`),
  KEY `fk_cosecha_predio` (`predio_id`),
  KEY `fk_cosecha_sector` (`sector_id`),
  
  CONSTRAINT `fk_cr_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cr_predio` FOREIGN KEY (`predio_id`) REFERENCES `predios` (`id`),
  CONSTRAINT `fk_cr_sector` FOREIGN KEY (`sector_id`) REFERENCES `sectores` (`id`),
  CONSTRAINT `fk_cr_destino` FOREIGN KEY (`destino_id`) REFERENCES `cosechas_destinos` (`id`),
  CONSTRAINT `fk_cr_entidad` FOREIGN KEY (`entidad_legal_id`) REFERENCES `entidades_legales` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;