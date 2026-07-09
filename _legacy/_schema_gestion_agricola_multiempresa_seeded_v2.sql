-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 15, 2025 at 06:44 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `gestion_agricola_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `calendar_weeks`
--

CREATE TABLE `calendar_weeks` (
  `week_start` date NOT NULL,
  `week_end` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Tabla de utilidad para reportes semanales';

-- --------------------------------------------------------

--
-- Table structure for table `certificaciones_tipos`
--

CREATE TABLE `certificaciones_tipos` (
  `id` int(11) NOT NULL COMMENT 'PK: ID del Tipo de Certificación',
  `empresa_id` int(11) NOT NULL COMMENT 'FK a empresas.id',
  `nombre` varchar(100) NOT NULL COMMENT 'Ej: Licencia Clase D, Carnet Aplicador SAG',
  `descripcion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Catálogo de certificaciones o licencias, por empresa';

-- --------------------------------------------------------

--
-- Table structure for table `certificaciones_trabajadores`
--

CREATE TABLE `certificaciones_trabajadores` (
  `id` int(11) NOT NULL COMMENT 'PK: ID del Registro de Certificación',
  `empresa_id` int(11) NOT NULL COMMENT 'FK a empresas.id (para facilitar consultas)',
  `trabajador_id` int(11) NOT NULL COMMENT 'FK a trabajadores.id',
  `certificacion_tipo_id` int(11) NOT NULL COMMENT 'FK a certificaciones_tipos.id',
  `fecha_emision` date DEFAULT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  `observaciones` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Registra las certificaciones/licencias de cada trabajador';

-- --------------------------------------------------------

--
-- Table structure for table `cosechas_registros`
--

CREATE TABLE `cosechas_registros` (
  `id` int(11) NOT NULL COMMENT 'PK: ID del Registro de Cosecha',
  `empresa_id` int(11) NOT NULL COMMENT 'FK a empresas.id',
  `predio_id` int(11) NOT NULL COMMENT 'FK a predios.id',
  `sector_id` int(11) NOT NULL COMMENT 'FK a sectores.id',
  `usuario_id` int(11) DEFAULT NULL COMMENT 'FK a usuarios.id (quién registró)',
  `fecha_cosecha` date NOT NULL,
  `folio_guia_despacho` varchar(50) DEFAULT NULL,
  `calidad` enum('Exportación','Mercado Nacional') NOT NULL,
  `destino` enum('Propal','Otro') NOT NULL,
  `codigo_productor` enum('41','42','44') NOT NULL COMMENT 'Esto podría ser una FK a otra tabla',
  `numero_bins` int(11) DEFAULT NULL,
  `kgs_recepcionados` decimal(10,2) NOT NULL,
  `fecha_registro_sistema` timestamp NOT NULL DEFAULT current_timestamp(),
  `notas` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Registro de cosechas detallado, por empresa';

-- --------------------------------------------------------

--
-- Table structure for table `empresas`
--

CREATE TABLE `empresas` (
  `id` int(11) NOT NULL COMMENT 'PK: ID de la Empresa',
  `nombre` varchar(255) NOT NULL,
  `mes_inicio_temporada` int(2) NOT NULL DEFAULT 7 COMMENT 'Mes (1-12) en que inicia la temporada (para reportes)',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `estado_suscripcion` enum('activa','inactiva','prueba') NOT NULL DEFAULT 'prueba',
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Tabla maestra de clientes (empresas) del SaaS';

--
-- Dumping data for table `empresas`
--

INSERT INTO `empresas` (`id`, `nombre`, `mes_inicio_temporada`, `fecha_creacion`, `estado_suscripcion`, `activo`) VALUES
(1, 'Empresa A', 7, '2025-11-10 12:28:08', 'prueba', 1),
(2, 'Empresa B', 7, '2025-11-10 12:28:08', 'prueba', 1),
(3, 'José Cristian Hargous Larraín', 7, '2025-11-10 12:28:08', 'prueba', 1);

-- --------------------------------------------------------

--
-- Table structure for table `instalaciones`
--

CREATE TABLE `instalaciones` (
  `id` int(11) NOT NULL COMMENT 'PK: ID de la Instalación',
  `empresa_id` int(11) NOT NULL COMMENT 'FK a empresas.id',
  `nombre` varchar(255) NOT NULL,
  `predio_id` int(11) DEFAULT NULL COMMENT 'FK a predios.id (Opcional)',
  `sector_id` int(11) DEFAULT NULL COMMENT 'FK a sectores.id (Opcional)',
  `latitud` decimal(10,8) DEFAULT NULL,
  `longitud` decimal(11,8) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `instalaciones`
--

INSERT INTO `instalaciones` (`id`, `empresa_id`, `nombre`, `predio_id`, `sector_id`, `latitud`, `longitud`, `activo`) VALUES
(1, 1, 'Caseta 1 Lote 4', 1, NULL, -32.82499972, -71.21561003, 1);

-- --------------------------------------------------------

--
-- Table structure for table `kpi_resumenes_semanales`
--

CREATE TABLE `kpi_resumenes_semanales` (
  `id` int(11) NOT NULL COMMENT 'PK: ID del Resumen KPI',
  `empresa_id` int(11) NOT NULL COMMENT 'FK a empresas.id',
  `predio_id` int(11) NOT NULL COMMENT 'FK a predios.id',
  `year` int(11) NOT NULL,
  `week_number` int(11) NOT NULL,
  `week_start` date DEFAULT NULL,
  `porcentaje_reposicion` decimal(5,1) DEFAULT NULL,
  `total_regado_mm` decimal(7,2) DEFAULT NULL,
  `total_bandeja_mm` decimal(7,2) DEFAULT NULL,
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `labores_registros`
--

CREATE TABLE `labores_registros` (
  `id` int(11) NOT NULL COMMENT 'PK: ID del Registro de Labor',
  `empresa_id` int(11) NOT NULL COMMENT 'FK a empresas.id',
  `labor_tipo_id` int(11) NOT NULL COMMENT 'FK a labores_tipos.id',
  `predio_id` int(11) NOT NULL COMMENT 'FK a predios.id',
  `sector_id` int(11) NOT NULL COMMENT 'FK a sectores.id',
  `usuario_id` int(11) DEFAULT NULL COMMENT 'FK a usuarios.id (quién registró)',
  `fecha` date NOT NULL,
  `porcentaje_avance` int(11) NOT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Registros de avance de labores de campo';

-- --------------------------------------------------------

--
-- Table structure for table `labores_tipos`
--

CREATE TABLE `labores_tipos` (
  `id` int(11) NOT NULL COMMENT 'PK: ID del Tipo de Labor',
  `empresa_id` int(11) NOT NULL COMMENT 'FK a empresas.id',
  `nombre` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Catálogo de tipos de labores de campo, por empresa';

-- --------------------------------------------------------

--
-- Table structure for table `lecturas_bandejas`
--

CREATE TABLE `lecturas_bandejas` (
  `id` int(11) NOT NULL COMMENT 'PK: ID de la Lectura',
  `empresa_id` int(11) NOT NULL COMMENT 'FK a empresas.id',
  `usuario_id` int(11) DEFAULT NULL COMMENT 'FK a usuarios.id (quién registró)',
  `fecha` date DEFAULT NULL,
  `lectura_mm` decimal(10,2) DEFAULT NULL,
  `semana` int(11) GENERATED ALWAYS AS (week(`fecha`,1)) STORED,
  `temporada` varchar(9) GENERATED ALWAYS AS (concat(year(`fecha`) - if(month(`fecha`) < 7,1,0),'-',year(`fecha`) + if(month(`fecha`) >= 7,1,0))) STORED
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Lecturas de bandejas de evaporación';

-- --------------------------------------------------------

--
-- Table structure for table `precipitaciones`
--

CREATE TABLE `precipitaciones` (
  `id` int(11) NOT NULL COMMENT 'PK: ID de la Precipitación',
  `empresa_id` int(11) NOT NULL COMMENT 'FK a empresas.id',
  `usuario_id` int(11) DEFAULT NULL COMMENT 'FK a usuarios.id (quién registró)',
  `mm_medidos` decimal(5,2) DEFAULT NULL,
  `fecha` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `predios`
--

CREATE TABLE `predios` (
  `id` int(11) NOT NULL COMMENT 'PK: ID del Predio',
  `empresa_id` int(11) NOT NULL COMMENT 'FK a empresas.id',
  `nombre` varchar(255) DEFAULT NULL,
  `año_plantacion` int(11) DEFAULT NULL,
  `plantas_por_hectarea` int(11) DEFAULT NULL,
  `superficie_total` decimal(10,2) DEFAULT NULL,
  `cantidad_plantas` int(11) DEFAULT NULL,
  `tipo_emisor` varchar(15) DEFAULT NULL,
  `caudal_lt_hora` decimal(10,2) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `predios`
--

INSERT INTO `predios` (`id`, `empresa_id`, `nombre`, `año_plantacion`, `plantas_por_hectarea`, `superficie_total`, `cantidad_plantas`, `tipo_emisor`, `caudal_lt_hora`, `activo`) VALUES
(1, 1, 'Predio de A', NULL, NULL, NULL, NULL, NULL, NULL, 1),
(2, 2, 'Predio de B', NULL, NULL, NULL, NULL, NULL, NULL, 1),
(3, 1, 'Predio de Prueba A', 2025, NULL, 5.00, NULL, 'gotero', 40.00, 1);

-- --------------------------------------------------------

--
-- Table structure for table `puntos`
--

CREATE TABLE `puntos` (
  `id` int(11) NOT NULL COMMENT 'PK: ID del Punto',
  `empresa_id` int(11) NOT NULL COMMENT 'FK a empresas.id',
  `punto_tipo_id` int(11) NOT NULL COMMENT 'FK a puntos_tipos.id',
  `predio_id` int(11) DEFAULT NULL COMMENT 'FK a predios.id',
  `sector_id` int(11) DEFAULT NULL COMMENT 'FK a sectores.id',
  `instalacion_id` int(11) DEFAULT NULL COMMENT 'FK a instalaciones.id',
  `usuario_registro_id` int(11) DEFAULT NULL COMMENT 'FK a usuarios.id (quién registró)',
  `usuario_modificacion_id` int(11) DEFAULT NULL COMMENT 'FK a usuarios.id (última modificación)',
  `latitud` decimal(10,8) NOT NULL,
  `longitud` decimal(11,8) NOT NULL,
  `precision_metros` float DEFAULT NULL,
  `timestamp_gps` timestamp NULL DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `nombre_archivo_foto` varchar(255) DEFAULT NULL,
  `fecha_registro_db` timestamp NULL DEFAULT current_timestamp(),
  `fecha_modificacion_db` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `puntos`
--

INSERT INTO `puntos` (`id`, `empresa_id`, `punto_tipo_id`, `predio_id`, `sector_id`, `instalacion_id`, `usuario_registro_id`, `usuario_modificacion_id`, `latitud`, `longitud`, `precision_metros`, `timestamp_gps`, `descripcion`, `nombre_archivo_foto`, `fecha_registro_db`, `fecha_modificacion_db`) VALUES
(1, 1, 1, NULL, NULL, NULL, NULL, NULL, -33.00000000, -71.00000000, NULL, NULL, NULL, NULL, '2025-11-10 12:35:53', NULL),
(2, 2, 2, NULL, NULL, NULL, NULL, NULL, -34.00000000, -70.00000000, NULL, NULL, NULL, NULL, '2025-11-10 12:35:53', NULL),
(3, 1, 4, 1, NULL, 1, 1, 1, -32.82499972, -71.21561003, NULL, NULL, 'prueba 1', '3_1762802813.jpg', '2025-11-10 19:26:53', '2025-11-10 16:26:53'),
(4, 1, 3, 1, NULL, NULL, 1, 1, -32.82217953, -71.21597377, NULL, NULL, 'prueba 2', '4_1762803300.png', '2025-11-10 19:35:00', '2025-11-10 16:35:00');

-- --------------------------------------------------------

--
-- Table structure for table `puntos_seguimientos`
--

CREATE TABLE `puntos_seguimientos` (
  `id` int(11) NOT NULL COMMENT 'PK: ID del Seguimiento',
  `empresa_id` int(11) NOT NULL COMMENT 'FK a empresas.id (para facilitar consultas)',
  `punto_id` int(11) NOT NULL COMMENT 'FK a puntos.id',
  `usuario_id` int(11) NOT NULL COMMENT 'FK a usuarios.id (quién actualizó)',
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Cuándo se registró este seguimiento',
  `estado_nuevo` enum('Resuelto','Pendiente') NOT NULL,
  `notas_seguimiento` text DEFAULT NULL COMMENT 'Comentarios sobre la actualización'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Historial de seguimiento y cambios de estado para puntos';

-- --------------------------------------------------------

--
-- Table structure for table `puntos_tipos`
--

CREATE TABLE `puntos_tipos` (
  `id` int(11) NOT NULL COMMENT 'PK: ID del Tipo de Punto',
  `empresa_id` int(11) NOT NULL COMMENT 'FK a empresas.id',
  `nombre` varchar(100) NOT NULL,
  `categoria` varchar(50) NOT NULL DEFAULT 'General' COMMENT 'Categoría para filtros (Ej: Plagas, Riego, Infraestructura, BPA)',
  `color_hex` varchar(7) NOT NULL DEFAULT '#FE7569' COMMENT 'Color en formato hexadecimal para el marcador',
  `icono_class` varchar(50) DEFAULT NULL COMMENT 'Clase del icono (ej. Bootstrap Icons)',
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Catálogo de tipos de puntos, específico por empresa';

--
-- Dumping data for table `puntos_tipos`
--

INSERT INTO `puntos_tipos` (`id`, `empresa_id`, `nombre`, `categoria`, `color_hex`, `icono_class`, `activo`) VALUES
(1, 1, 'Fuga de Riego (A)', 'Riego', '#FE7569', NULL, 1),
(2, 2, 'Plaga (B)', 'Plagas', '#FE7569', NULL, 1),
(3, 1, 'Daño estructural', 'Infraestructura', '#e67300', 'bi bi-geo-alt', 1),
(4, 1, 'Falla bomba', 'Riego', '#0000ff', 'bi bi-geo-alt', 1);

-- --------------------------------------------------------

--
-- Table structure for table `registros_riegos`
--

CREATE TABLE `registros_riegos` (
  `id` int(11) NOT NULL COMMENT 'PK: ID del Registro de Riego',
  `empresa_id` int(11) NOT NULL COMMENT 'FK a empresas.id',
  `predio_id` int(11) DEFAULT NULL COMMENT 'FK a predios.id',
  `usuario_id` int(11) DEFAULT NULL COMMENT 'FK a usuarios.id (quién registró)',
  `fecha` date DEFAULT NULL,
  `tiempo_riego` int(11) DEFAULT NULL COMMENT 'Tiempo en minutos u horas (a definir)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `riegos_exclusiones`
--

CREATE TABLE `riegos_exclusiones` (
  `id` int(11) NOT NULL COMMENT 'PK: ID de la Exclusión',
  `empresa_id` int(11) NOT NULL COMMENT 'FK a empresas.id (para facilitar consultas)',
  `registro_riego_id` int(11) NOT NULL COMMENT 'FK a registros_riegos.id',
  `unidad_riego_excluida` varchar(50) NOT NULL COMMENT 'Nombre o ID de la unidad/sector excluido',
  `fecha_registro_exclusion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL COMMENT 'PK: ID del Rol',
  `nombre` varchar(50) NOT NULL COMMENT 'Ej: Admin, Usuario_general, Terreno'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Catálogo global de roles de usuario';

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `nombre`) VALUES
(1, 'Admin'),
(4, 'Usuario_general'),
(5, 'Usuario_inventario'),
(3, 'Usuario_labores'),
(2, 'Usuario_riego');

-- --------------------------------------------------------

--
-- Table structure for table `sectores`
--

CREATE TABLE `sectores` (
  `id` int(11) NOT NULL COMMENT 'PK: ID del Sector',
  `empresa_id` int(11) NOT NULL COMMENT 'FK a empresas.id',
  `predio_id` int(11) NOT NULL COMMENT 'FK a predios.id',
  `nombre` varchar(10) NOT NULL,
  `unidad` varchar(5) NOT NULL,
  `superficie` decimal(10,2) NOT NULL,
  `cantidad_plantas` int(11) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `solicitudes`
--

CREATE TABLE `solicitudes` (
  `id` int(11) NOT NULL COMMENT 'PK',
  `empresa_id` int(11) NOT NULL COMMENT 'FK a empresas.id',
  `usuario_solicitante_id` int(11) NOT NULL COMMENT 'FK a usuarios.id (Quién la creó vía chat)',
  `usuario_asignado_id` int(11) DEFAULT NULL COMMENT 'FK a usuarios.id (Quién la debe gestionar)',
  `categoria_id` int(11) DEFAULT NULL COMMENT 'FK a solicitudes_categorias.id (Opcional)',
  `punto_id` int(11) DEFAULT NULL COMMENT 'FK a puntos.id (Opcional, si está ligada a un punto)',
  `descripcion` text NOT NULL COMMENT 'El texto íntegro de la solicitud del chat',
  `estado` enum('pendiente_revision','aprobada','rechazada','en_proceso','resuelta') NOT NULL DEFAULT 'pendiente_revision',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_ultima_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `solicitudes_categorias`
--

CREATE TABLE `solicitudes_categorias` (
  `id` int(11) NOT NULL COMMENT 'PK',
  `empresa_id` int(11) NOT NULL COMMENT 'FK a empresas.id',
  `nombre` varchar(100) NOT NULL COMMENT 'Ej: Materiales Riego, EPI, Combustible, Servicio Técnico',
  `activo` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Para borrado lógico (Soft Delete)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `trabajadores`
--

CREATE TABLE `trabajadores` (
  `id` int(11) NOT NULL COMMENT 'PK: ID del Trabajador',
  `empresa_id` int(11) NOT NULL COMMENT 'FK a empresas.id',
  `rut` varchar(12) NOT NULL COMMENT 'RUT con puntos y guión, ej: 12.345.678-9',
  `nombre_completo` varchar(255) NOT NULL,
  `cargo` varchar(100) DEFAULT NULL COMMENT 'Cargo o puesto del trabajador',
  `activo` tinyint(1) DEFAULT 1 COMMENT '1 = Activo, 0 = Inactivo',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_contrato` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabla maestra de trabajadores de cada empresa';

--
-- Dumping data for table `trabajadores`
--

INSERT INTO `trabajadores` (`id`, `empresa_id`, `rut`, `nombre_completo`, `cargo`, `activo`, `fecha_creacion`, `fecha_contrato`) VALUES
(1, 1, '1-1', 'Admin Empresa A', NULL, 1, '2025-11-10 12:32:51', NULL),
(2, 2, '2-2', 'Admin Empresa B', NULL, 1, '2025-11-10 12:32:51', NULL),
(3, 3, '17309117-6', 'Cristian Manzano', NULL, 1, '2025-11-10 12:32:51', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL COMMENT 'PK: ID del Usuario',
  `empresa_id` int(11) NOT NULL COMMENT 'FK a empresas.id',
  `trabajador_id` int(11) DEFAULT NULL COMMENT 'FK a trabajadores.id. NULO para usuarios Admin.',
  `username` varchar(50) NOT NULL COMMENT 'Nombre de usuario para el login (GLOBALMENTE ÚNICO)',
  `password_hash` varchar(255) NOT NULL COMMENT 'Hash de la contraseña',
  `rol_id` int(11) NOT NULL COMMENT 'FK a roles.id',
  `activo` tinyint(1) DEFAULT 1 COMMENT '1 = Usuario puede iniciar sesión, 0 = Deshabilitado',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `ultimo_login` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Credenciales y roles de los usuarios del sistema';

--
-- Dumping data for table `usuarios`
--

INSERT INTO `usuarios` (`id`, `empresa_id`, `trabajador_id`, `username`, `password_hash`, `rol_id`, `activo`, `fecha_creacion`, `ultimo_login`) VALUES
(1, 1, 1, 'admin_a', '$2y$10$9/mVFbICq4ZThGtlszLxJ.oDQlo7JrzZytgOLSrOPcpuIhbqD2iPy', 1, 1, '2025-11-10 12:32:51', NULL),
(2, 2, 2, 'admin_b', '$2y$10$9/mVFbICq4ZThGtlszLxJ.oDQlo7JrzZytgOLSrOPcpuIhbqD2iPy', 1, 1, '2025-11-10 12:32:51', NULL),
(3, 3, 3, 'cmanzano', '$2y$10$9/mVFbICq4ZThGtlszLxJ.oDQlo7JrzZytgOLSrOPcpuIhbqD2iPy', 1, 1, '2025-11-10 12:32:51', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `usuarios_whatsapp_links`
--

CREATE TABLE `usuarios_whatsapp_links` (
  `id` int(11) NOT NULL COMMENT 'PK',
  `empresa_id` int(11) NOT NULL COMMENT 'FK a empresas.id (Contexto Multi-Tenancy)',
  `usuario_id` int(11) NOT NULL COMMENT 'FK a usuarios.id (Quién es)',
  `numero_whatsapp` varchar(25) NOT NULL COMMENT 'Ej: +56912345678 (Clave de búsqueda)',
  `estado` enum('pendiente','verificado','desactivado') NOT NULL DEFAULT 'pendiente',
  `fecha_verificacion` timestamp NULL DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `calendar_weeks`
--
ALTER TABLE `calendar_weeks`
  ADD PRIMARY KEY (`week_start`,`week_end`),
  ADD KEY `idx_calendar_start_end` (`week_start`,`week_end`);

--
-- Indexes for table `certificaciones_tipos`
--
ALTER TABLE `certificaciones_tipos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_empresa_cert` (`empresa_id`,`nombre`);

--
-- Indexes for table `certificaciones_trabajadores`
--
ALTER TABLE `certificaciones_trabajadores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_cert_trabajador` (`trabajador_id`),
  ADD KEY `idx_cert_tipo` (`certificacion_tipo_id`),
  ADD KEY `idx_cert_vencimiento` (`fecha_vencimiento`),
  ADD KEY `fk_certtrab_empresa` (`empresa_id`);

--
-- Indexes for table `cosechas_registros`
--
ALTER TABLE `cosechas_registros`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_fecha_cosecha` (`fecha_cosecha`),
  ADD KEY `idx_folio_guia` (`folio_guia_despacho`),
  ADD KEY `idx_predio_cosecha` (`predio_id`),
  ADD KEY `idx_sector_cosecha` (`sector_id`),
  ADD KEY `idx_calidad` (`calidad`),
  ADD KEY `idx_destino` (`destino`),
  ADD KEY `idx_codigo_productor` (`codigo_productor`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `fk_cosecha_empresa` (`empresa_id`);

--
-- Indexes for table `empresas`
--
ALTER TABLE `empresas`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `instalaciones`
--
ALTER TABLE `instalaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_instalaciones_predios_idx` (`predio_id`),
  ADD KEY `fk_instalaciones_sectores_idx` (`sector_id`),
  ADD KEY `fk_instalacion_empresa` (`empresa_id`),
  ADD KEY `idx_empresa_predio` (`empresa_id`,`predio_id`);

--
-- Indexes for table `kpi_resumenes_semanales`
--
ALTER TABLE `kpi_resumenes_semanales`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_empresa_predio_semana` (`empresa_id`,`predio_id`,`year`,`week_number`),
  ADD KEY `fk_kpi_predio` (`predio_id`);

--
-- Indexes for table `labores_registros`
--
ALTER TABLE `labores_registros`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_fecha` (`fecha`),
  ADD KEY `idx_labor_tipo_id` (`labor_tipo_id`),
  ADD KEY `idx_predio_id` (`predio_id`),
  ADD KEY `idx_sector_id` (`sector_id`),
  ADD KEY `fk_labores_usuario` (`usuario_id`),
  ADD KEY `fk_reglabor_empresa` (`empresa_id`);

--
-- Indexes for table `labores_tipos`
--
ALTER TABLE `labores_tipos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_empresa_labor` (`empresa_id`,`nombre`),
  ADD KEY `idx_tipo_labor_nombre` (`nombre`);

--
-- Indexes for table `lecturas_bandejas`
--
ALTER TABLE `lecturas_bandejas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_empresa_fecha` (`empresa_id`,`fecha`),
  ADD KEY `fk_bandeja_usuario` (`usuario_id`);

--
-- Indexes for table `precipitaciones`
--
ALTER TABLE `precipitaciones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_empresa_fecha` (`empresa_id`,`fecha`),
  ADD KEY `fk_precipitacion_usuario` (`usuario_id`);

--
-- Indexes for table `predios`
--
ALTER TABLE `predios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_predio_empresa` (`empresa_id`);

--
-- Indexes for table `puntos`
--
ALTER TABLE `puntos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_puntos_tipos` (`punto_tipo_id`),
  ADD KEY `fk_puntos_predios` (`predio_id`),
  ADD KEY `fk_puntos_sectores` (`sector_id`),
  ADD KEY `fk_puntos_instalacion_idx` (`instalacion_id`),
  ADD KEY `fk_punto_empresa` (`empresa_id`),
  ADD KEY `fk_punto_usuario_reg` (`usuario_registro_id`),
  ADD KEY `fk_punto_usuario_mod` (`usuario_modificacion_id`),
  ADD KEY `idx_empresa_predio_fecha` (`empresa_id`,`predio_id`,`fecha_registro_db`);

--
-- Indexes for table `puntos_seguimientos`
--
ALTER TABLE `puntos_seguimientos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_seguimiento_punto` (`punto_id`),
  ADD KEY `idx_seguimiento_usuario` (`usuario_id`),
  ADD KEY `idx_seguimiento_fecha` (`fecha_actualizacion`),
  ADD KEY `fk_seguimiento_empresa` (`empresa_id`);

--
-- Indexes for table `puntos_tipos`
--
ALTER TABLE `puntos_tipos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_puntotipo_empresa` (`empresa_id`);

--
-- Indexes for table `registros_riegos`
--
ALTER TABLE `registros_riegos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_empresa_fecha_predio` (`empresa_id`,`fecha`,`predio_id`),
  ADD KEY `predio_id` (`predio_id`),
  ADD KEY `fk_riego_usuario` (`usuario_id`),
  ADD KEY `idx_riegos_fecha` (`fecha`),
  ADD KEY `idx_riegos_fecha_predio` (`fecha`,`predio_id`);

--
-- Indexes for table `riegos_exclusiones`
--
ALTER TABLE `riegos_exclusiones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_riego_id` (`registro_riego_id`),
  ADD KEY `fk_riegoex_empresa` (`empresa_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre_rol` (`nombre`);

--
-- Indexes for table `sectores`
--
ALTER TABLE `sectores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_sectores_predios` (`predio_id`),
  ADD KEY `fk_sector_empresa` (`empresa_id`);

--
-- Indexes for table `solicitudes`
--
ALTER TABLE `solicitudes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_solicitud_empresa` (`empresa_id`),
  ADD KEY `fk_solicitud_usuario_sol` (`usuario_solicitante_id`),
  ADD KEY `fk_solicitud_usuario_asig` (`usuario_asignado_id`),
  ADD KEY `fk_solicitud_categoria` (`categoria_id`),
  ADD KEY `fk_solicitud_punto` (`punto_id`);

--
-- Indexes for table `solicitudes_categorias`
--
ALTER TABLE `solicitudes_categorias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_solcat_empresa` (`empresa_id`);

--
-- Indexes for table `trabajadores`
--
ALTER TABLE `trabajadores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_empresa_rut` (`empresa_id`,`rut`),
  ADD KEY `idx_nombre_completo` (`nombre_completo`),
  ADD KEY `idx_activo` (`activo`),
  ADD KEY `fk_trabajador_empresa` (`empresa_id`);

--
-- Indexes for table `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`) COMMENT 'Username debe ser único en toda la plataforma',
  ADD KEY `idx_usuario_rol` (`rol_id`),
  ADD KEY `idx_usuario_activo` (`activo`),
  ADD KEY `trabajador_id` (`trabajador_id`),
  ADD KEY `fk_usuario_empresa` (`empresa_id`);

--
-- Indexes for table `usuarios_whatsapp_links`
--
ALTER TABLE `usuarios_whatsapp_links`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_numero_whatsapp` (`numero_whatsapp`),
  ADD KEY `fk_whatsapp_usuario` (`usuario_id`),
  ADD KEY `fk_whatsapp_empresa` (`empresa_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `certificaciones_tipos`
--
ALTER TABLE `certificaciones_tipos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'PK: ID del Tipo de Certificación';

--
-- AUTO_INCREMENT for table `certificaciones_trabajadores`
--
ALTER TABLE `certificaciones_trabajadores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'PK: ID del Registro de Certificación';

--
-- AUTO_INCREMENT for table `cosechas_registros`
--
ALTER TABLE `cosechas_registros`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'PK: ID del Registro de Cosecha';

--
-- AUTO_INCREMENT for table `empresas`
--
ALTER TABLE `empresas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'PK: ID de la Empresa', AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `instalaciones`
--
ALTER TABLE `instalaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'PK: ID de la Instalación', AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `kpi_resumenes_semanales`
--
ALTER TABLE `kpi_resumenes_semanales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'PK: ID del Resumen KPI';

--
-- AUTO_INCREMENT for table `labores_registros`
--
ALTER TABLE `labores_registros`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'PK: ID del Registro de Labor';

--
-- AUTO_INCREMENT for table `labores_tipos`
--
ALTER TABLE `labores_tipos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'PK: ID del Tipo de Labor';

--
-- AUTO_INCREMENT for table `lecturas_bandejas`
--
ALTER TABLE `lecturas_bandejas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'PK: ID de la Lectura';

--
-- AUTO_INCREMENT for table `precipitaciones`
--
ALTER TABLE `precipitaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'PK: ID de la Precipitación';

--
-- AUTO_INCREMENT for table `predios`
--
ALTER TABLE `predios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'PK: ID del Predio', AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `puntos`
--
ALTER TABLE `puntos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'PK: ID del Punto', AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `puntos_seguimientos`
--
ALTER TABLE `puntos_seguimientos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'PK: ID del Seguimiento';

--
-- AUTO_INCREMENT for table `puntos_tipos`
--
ALTER TABLE `puntos_tipos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'PK: ID del Tipo de Punto', AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `registros_riegos`
--
ALTER TABLE `registros_riegos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'PK: ID del Registro de Riego';

--
-- AUTO_INCREMENT for table `riegos_exclusiones`
--
ALTER TABLE `riegos_exclusiones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'PK: ID de la Exclusión';

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'PK: ID del Rol', AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `sectores`
--
ALTER TABLE `sectores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'PK: ID del Sector';

--
-- AUTO_INCREMENT for table `solicitudes`
--
ALTER TABLE `solicitudes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'PK';

--
-- AUTO_INCREMENT for table `solicitudes_categorias`
--
ALTER TABLE `solicitudes_categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'PK';

--
-- AUTO_INCREMENT for table `trabajadores`
--
ALTER TABLE `trabajadores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'PK: ID del Trabajador', AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'PK: ID del Usuario', AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `usuarios_whatsapp_links`
--
ALTER TABLE `usuarios_whatsapp_links`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'PK';

--
-- Constraints for dumped tables
--

--
-- Constraints for table `certificaciones_tipos`
--
ALTER TABLE `certificaciones_tipos`
  ADD CONSTRAINT `fk_tipocert_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `certificaciones_trabajadores`
--
ALTER TABLE `certificaciones_trabajadores`
  ADD CONSTRAINT `fk_certtrab_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_certtrab_tipocert` FOREIGN KEY (`certificacion_tipo_id`) REFERENCES `certificaciones_tipos` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_certtrab_trabajador` FOREIGN KEY (`trabajador_id`) REFERENCES `trabajadores` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `cosechas_registros`
--
ALTER TABLE `cosechas_registros`
  ADD CONSTRAINT `fk_cosecha_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_cosecha_predio` FOREIGN KEY (`predio_id`) REFERENCES `predios` (`id`),
  ADD CONSTRAINT `fk_cosecha_sector` FOREIGN KEY (`sector_id`) REFERENCES `sectores` (`id`),
  ADD CONSTRAINT `fk_cosecha_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `instalaciones`
--
ALTER TABLE `instalaciones`
  ADD CONSTRAINT `fk_instalacion_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_instalacion_predio` FOREIGN KEY (`predio_id`) REFERENCES `predios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_instalacion_sector` FOREIGN KEY (`sector_id`) REFERENCES `sectores` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `kpi_resumenes_semanales`
--
ALTER TABLE `kpi_resumenes_semanales`
  ADD CONSTRAINT `fk_kpi_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_kpi_predio` FOREIGN KEY (`predio_id`) REFERENCES `predios` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `labores_registros`
--
ALTER TABLE `labores_registros`
  ADD CONSTRAINT `fk_labreg_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_labreg_labortipo` FOREIGN KEY (`labor_tipo_id`) REFERENCES `labores_tipos` (`id`),
  ADD CONSTRAINT `fk_labreg_predio` FOREIGN KEY (`predio_id`) REFERENCES `predios` (`id`),
  ADD CONSTRAINT `fk_labreg_sector` FOREIGN KEY (`sector_id`) REFERENCES `sectores` (`id`),
  ADD CONSTRAINT `fk_labreg_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `labores_tipos`
--
ALTER TABLE `labores_tipos`
  ADD CONSTRAINT `fk_labor_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `lecturas_bandejas`
--
ALTER TABLE `lecturas_bandejas`
  ADD CONSTRAINT `fk_bandeja_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_bandeja_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `precipitaciones`
--
ALTER TABLE `precipitaciones`
  ADD CONSTRAINT `fk_precip_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_precipitacion_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `predios`
--
ALTER TABLE `predios`
  ADD CONSTRAINT `fk_predio_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `puntos`
--
ALTER TABLE `puntos`
  ADD CONSTRAINT `fk_punto_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_punto_instalacion` FOREIGN KEY (`instalacion_id`) REFERENCES `instalaciones` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_punto_predio` FOREIGN KEY (`predio_id`) REFERENCES `predios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_punto_sector` FOREIGN KEY (`sector_id`) REFERENCES `sectores` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_punto_tipo` FOREIGN KEY (`punto_tipo_id`) REFERENCES `puntos_tipos` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_punto_usuario_mod` FOREIGN KEY (`usuario_modificacion_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_punto_usuario_reg` FOREIGN KEY (`usuario_registro_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `puntos_seguimientos`
--
ALTER TABLE `puntos_seguimientos`
  ADD CONSTRAINT `fk_seguimiento_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_seguimiento_punto` FOREIGN KEY (`punto_id`) REFERENCES `puntos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_seguimiento_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `puntos_tipos`
--
ALTER TABLE `puntos_tipos`
  ADD CONSTRAINT `fk_puntotipo_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `registros_riegos`
--
ALTER TABLE `registros_riegos`
  ADD CONSTRAINT `fk_riego_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_riego_predio` FOREIGN KEY (`predio_id`) REFERENCES `predios` (`id`),
  ADD CONSTRAINT `fk_riego_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `riegos_exclusiones`
--
ALTER TABLE `riegos_exclusiones`
  ADD CONSTRAINT `fk_riegoex_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_riegoex_registro` FOREIGN KEY (`registro_riego_id`) REFERENCES `registros_riegos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `sectores`
--
ALTER TABLE `sectores`
  ADD CONSTRAINT `fk_sector_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_sector_predio` FOREIGN KEY (`predio_id`) REFERENCES `predios` (`id`);

--
-- Constraints for table `solicitudes`
--
ALTER TABLE `solicitudes`
  ADD CONSTRAINT `fk_solicitud_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `solicitudes_categorias` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_solicitud_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_solicitud_punto` FOREIGN KEY (`punto_id`) REFERENCES `puntos` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_solicitud_usuario_asig` FOREIGN KEY (`usuario_asignado_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_solicitud_usuario_sol` FOREIGN KEY (`usuario_solicitante_id`) REFERENCES `usuarios` (`id`);

--
-- Constraints for table `solicitudes_categorias`
--
ALTER TABLE `solicitudes_categorias`
  ADD CONSTRAINT `fk_solcat_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `trabajadores`
--
ALTER TABLE `trabajadores`
  ADD CONSTRAINT `fk_trabajador_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `fk_usuario_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_usuario_rol` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_usuario_trabajador` FOREIGN KEY (`trabajador_id`) REFERENCES `trabajadores` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `usuarios_whatsapp_links`
--
ALTER TABLE `usuarios_whatsapp_links`
  ADD CONSTRAINT `fk_whatsapp_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_whatsapp_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
