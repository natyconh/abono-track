-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 04, 2025 at 03:12 PM
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
-- Database: `gestion_agricola_ensenada`
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
-- Table structure for table `config_distribucion_riego`
--

CREATE TABLE `config_distribucion_riego` (
  `id` int(11) NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `predio_origen_id` int(11) NOT NULL,
  `predio_destino_id` int(11) NOT NULL,
  `porcentaje_flujo` decimal(5,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cosechas_configuracion`
--

CREATE TABLE `cosechas_configuracion` (
  `id` int(11) NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `modo_pesaje` enum('NETO','BRUTO_TARA') NOT NULL DEFAULT 'NETO' COMMENT 'NETO: Ingresa kg finales. BRUTO_TARA: Ingresa Kg Bruto y Envases, sistema resta tara.',
  `nivel_trazabilidad_legal` enum('EMPRESA','PREDIO','SECTOR') NOT NULL DEFAULT 'EMPRESA' COMMENT 'Define de dónde saca el sistema el dueño legal de la fruta automáticamente.',
  `requiere_calidad` tinyint(1) DEFAULT 1 COMMENT 'Si es 0, oculta el selector de calidad',
  `requiere_tipo_cosecha` tinyint(1) DEFAULT 0 COMMENT 'Si es 0, asume Manual por defecto',
  `fecha_configuracion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cosechas_destinos`
--

CREATE TABLE `cosechas_destinos` (
  `id` int(11) NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL COMMENT 'Ej: Agrícola Propal, Mercado Lo Valledor',
  `rut` varchar(20) DEFAULT NULL,
  `tipo` enum('Exportadora','Mercado Interno','Descarte','Procesadora','Otro') NOT NULL DEFAULT 'Exportadora',
  `activo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cosechas_registros`
--

CREATE TABLE `cosechas_registros` (
  `id` int(11) NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `predio_id` int(11) NOT NULL,
  `sector_id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `entidad_legal_id` int(11) DEFAULT NULL COMMENT 'El dueño legal de esta carga específica',
  `destino_id` int(11) NOT NULL,
  `folio_guia_despacho` varchar(50) DEFAULT NULL,
  `variedad` varchar(100) DEFAULT NULL COMMENT 'Snapshot de la variedad al momento de cosecha',
  `tipo_cosecha` enum('Manual','Mecanizada') DEFAULT 'Manual',
  `calidad_declarada` enum('Exportación','Nacional','Descarte') DEFAULT 'Exportación',
  `tipo_envase` varchar(50) DEFAULT NULL COMMENT 'Ej: Bin Plástico, Caja Madera',
  `cantidad_envases` int(11) DEFAULT 0,
  `kilos_brutos` decimal(10,2) DEFAULT NULL COMMENT 'Peso total báscula',
  `tara_promedio` decimal(10,2) DEFAULT NULL COMMENT 'Tara unitaria del envase',
  `kilos_netos` decimal(10,2) NOT NULL COMMENT 'Dato final real (Calculado o Ingresado)',
  `usuario_id` int(11) DEFAULT NULL COMMENT 'Quién registró (Javier o Ana)',
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `notas` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cultivos`
--

CREATE TABLE `cultivos` (
  `id` int(11) NOT NULL,
  `empresa_id` int(11) NOT NULL COMMENT 'Multi-tenancy',
  `nombre` varchar(100) NOT NULL COMMENT 'Ej: Palto, Cítrico, Nogal',
  `variedad` varchar(100) DEFAULT NULL COMMENT 'Ej: Hass, Edranol, Serr',
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

-- --------------------------------------------------------

--
-- Table structure for table `entidades_legales`
--

CREATE TABLE `entidades_legales` (
  `id` int(11) NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `rut` varchar(20) NOT NULL COMMENT 'RUT de la razón social dueña de la fruta',
  `razon_social` varchar(255) NOT NULL,
  `nombre_fantasia` varchar(255) DEFAULT NULL,
  `codigo_sag` varchar(50) DEFAULT NULL COMMENT 'Código Productor SAG (CSG) para exportación',
  `direccion` varchar(255) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fertilizaciones_cabezal`
--

CREATE TABLE `fertilizaciones_cabezal` (
  `id` int(11) NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `predio_cabezal_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `fertilizante_id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `semana` int(11) GENERATED ALWAYS AS (week(`fecha`,1)) STORED,
  `cantidad_aplicada` decimal(10,2) NOT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fertilizaciones_reales`
--

CREATE TABLE `fertilizaciones_reales` (
  `id` int(11) NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `fertilizacion_cabezal_id` int(11) NOT NULL,
  `predio_destino_id` int(11) NOT NULL,
  `cantidad_recibida` decimal(10,2) NOT NULL,
  `unidades_n` decimal(10,2) DEFAULT 0.00,
  `unidades_p` decimal(10,2) DEFAULT 0.00,
  `unidades_k` decimal(10,2) DEFAULT 0.00,
  `unidades_extra` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fertilizantes`
--

CREATE TABLE `fertilizantes` (
  `id` int(11) NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `nombre_comercial` varchar(150) NOT NULL,
  `tipo_producto` enum('fertilizante','enmienda','biostimulante','otro') NOT NULL DEFAULT 'fertilizante',
  `tipo_unidad` enum('kg','lt') NOT NULL DEFAULT 'kg',
  `densidad` decimal(5,3) DEFAULT 1.000,
  `porcentaje_n` decimal(5,2) DEFAULT 0.00,
  `porcentaje_p` decimal(5,2) DEFAULT 0.00,
  `porcentaje_k` decimal(5,2) DEFAULT 0.00,
  `componente_extra_nombre` varchar(50) DEFAULT NULL,
  `componente_extra_porcentaje` decimal(5,2) DEFAULT 0.00,
  `activo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `cultivo_id` int(11) DEFAULT NULL,
  `tipo_superficie` enum('cultivo','cabezal_virtual','infraestructura') NOT NULL DEFAULT 'cultivo',
  `año_plantacion` int(11) DEFAULT NULL,
  `plantas_por_hectarea` int(11) DEFAULT NULL,
  `superficie_total` decimal(10,2) DEFAULT NULL,
  `cantidad_plantas` int(11) DEFAULT NULL,
  `tipo_emisor` varchar(15) DEFAULT NULL,
  `caudal_lt_hora` decimal(10,2) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `umbral_bajo` int(11) DEFAULT 75 COMMENT 'Bajo esto es Crítico (Rojo)',
  `umbral_optimo_min` int(11) DEFAULT 90 COMMENT 'Desde aquí es Óptimo (Verde)',
  `umbral_optimo_max` int(11) DEFAULT 110 COMMENT 'Hasta aquí es Óptimo (Verde)',
  `umbral_exceso` int(11) DEFAULT 130 COMMENT 'Sobre esto es Exceso Crítico (Azul)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `programas_detalles`
--

CREATE TABLE `programas_detalles` (
  `id` int(11) NOT NULL,
  `programa_id` int(11) NOT NULL,
  `mes` int(2) NOT NULL COMMENT '1=Enero, 12=Diciembre',
  `meta_n` decimal(10,2) DEFAULT 0.00,
  `meta_p` decimal(10,2) DEFAULT 0.00,
  `meta_k` decimal(10,2) DEFAULT 0.00,
  `meta_extra_nombre` varchar(50) DEFAULT NULL COMMENT 'Ej: Zinc, Húmico',
  `meta_extra_cantidad` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `programas_fertilizacion`
--

CREATE TABLE `programas_fertilizacion` (
  `id` int(11) NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL COMMENT 'Ej: Matriz Paltos 2025',
  `temporada` varchar(20) NOT NULL COMMENT 'Ej: 2025-2026',
  `cultivo_id` int(11) DEFAULT NULL COMMENT 'Si se llena, aplica a todos los predios de este cultivo',
  `predio_id` int(11) DEFAULT NULL COMMENT 'Si se llena, aplica SOLO a este predio (Excepción/Ajuste)',
  `tipo` enum('base','ajuste') NOT NULL DEFAULT 'base',
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
-- Table structure for table `reportes_grupos`
--

CREATE TABLE `reportes_grupos` (
  `id` int(11) NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL COMMENT 'Ej: Lote 4 Histórico, Zona Norte, Hass Total',
  `tipo` enum('predio','sector') NOT NULL DEFAULT 'predio',
  `activo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reportes_grupos_items`
--

CREATE TABLE `reportes_grupos_items` (
  `id` int(11) NOT NULL,
  `grupo_id` int(11) NOT NULL,
  `elemento_id` int(11) NOT NULL COMMENT 'ID del predio o sector según el tipo del grupo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `entidad_legal_id` int(11) DEFAULT NULL COMMENT 'Dueño legal de la fruta de este sector (Opcional)'
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
-- Indexes for table `config_distribucion_riego`
--
ALTER TABLE `config_distribucion_riego`
  ADD PRIMARY KEY (`id`),
  ADD KEY `empresa_id` (`empresa_id`),
  ADD KEY `predio_origen_id` (`predio_origen_id`),
  ADD KEY `predio_destino_id` (`predio_destino_id`);

--
-- Indexes for table `cosechas_configuracion`
--
ALTER TABLE `cosechas_configuracion`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_conf_empresa` (`empresa_id`);

--
-- Indexes for table `cosechas_destinos`
--
ALTER TABLE `cosechas_destinos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_destino_empresa` (`empresa_id`);

--
-- Indexes for table `cosechas_registros`
--
ALTER TABLE `cosechas_registros`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_cosecha_fecha` (`fecha`),
  ADD KEY `fk_cosecha_empresa` (`empresa_id`),
  ADD KEY `fk_cosecha_predio` (`predio_id`),
  ADD KEY `fk_cosecha_sector` (`sector_id`),
  ADD KEY `fk_cr_destino` (`destino_id`),
  ADD KEY `fk_cr_entidad` (`entidad_legal_id`);

--
-- Indexes for table `cultivos`
--
ALTER TABLE `cultivos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_cultivo_empresa` (`empresa_id`);

--
-- Indexes for table `empresas`
--
ALTER TABLE `empresas`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `entidades_legales`
--
ALTER TABLE `entidades_legales`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_entidad_empresa` (`empresa_id`);

--
-- Indexes for table `fertilizaciones_cabezal`
--
ALTER TABLE `fertilizaciones_cabezal`
  ADD PRIMARY KEY (`id`),
  ADD KEY `empresa_id` (`empresa_id`),
  ADD KEY `predio_cabezal_id` (`predio_cabezal_id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `fertilizante_id` (`fertilizante_id`);

--
-- Indexes for table `fertilizaciones_reales`
--
ALTER TABLE `fertilizaciones_reales`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fertilizacion_cabezal_id` (`fertilizacion_cabezal_id`),
  ADD KEY `predio_destino_id` (`predio_destino_id`);

--
-- Indexes for table `fertilizantes`
--
ALTER TABLE `fertilizantes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `empresa_id` (`empresa_id`);

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
  ADD KEY `fk_predio_empresa` (`empresa_id`),
  ADD KEY `fk_predio_cultivo_idx` (`cultivo_id`);

--
-- Indexes for table `programas_detalles`
--
ALTER TABLE `programas_detalles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_prog_mes` (`programa_id`,`mes`);

--
-- Indexes for table `programas_fertilizacion`
--
ALTER TABLE `programas_fertilizacion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_prog_empresa` (`empresa_id`),
  ADD KEY `fk_prog_cultivo` (`cultivo_id`),
  ADD KEY `fk_prog_predio` (`predio_id`);

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
-- Indexes for table `reportes_grupos`
--
ALTER TABLE `reportes_grupos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_grupos_empresa` (`empresa_id`);

--
-- Indexes for table `reportes_grupos_items`
--
ALTER TABLE `reportes_grupos_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_grupo_item` (`grupo_id`);

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
  ADD KEY `fk_sector_empresa` (`empresa_id`),
  ADD KEY `fk_sector_entidad` (`entidad_legal_id`);

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
-- AUTO_INCREMENT for table `config_distribucion_riego`
--
ALTER TABLE `config_distribucion_riego`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cosechas_configuracion`
--
ALTER TABLE `cosechas_configuracion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cosechas_destinos`
--
ALTER TABLE `cosechas_destinos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cosechas_registros`
--
ALTER TABLE `cosechas_registros`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cultivos`
--
ALTER TABLE `cultivos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `empresas`
--
ALTER TABLE `empresas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'PK: ID de la Empresa';

--
-- AUTO_INCREMENT for table `entidades_legales`
--
ALTER TABLE `entidades_legales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fertilizaciones_cabezal`
--
ALTER TABLE `fertilizaciones_cabezal`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fertilizaciones_reales`
--
ALTER TABLE `fertilizaciones_reales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fertilizantes`
--
ALTER TABLE `fertilizantes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `instalaciones`
--
ALTER TABLE `instalaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'PK: ID de la Instalación';

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'PK: ID del Predio';

--
-- AUTO_INCREMENT for table `programas_detalles`
--
ALTER TABLE `programas_detalles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `programas_fertilizacion`
--
ALTER TABLE `programas_fertilizacion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `puntos`
--
ALTER TABLE `puntos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'PK: ID del Punto';

--
-- AUTO_INCREMENT for table `puntos_seguimientos`
--
ALTER TABLE `puntos_seguimientos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'PK: ID del Seguimiento';

--
-- AUTO_INCREMENT for table `puntos_tipos`
--
ALTER TABLE `puntos_tipos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'PK: ID del Tipo de Punto';

--
-- AUTO_INCREMENT for table `registros_riegos`
--
ALTER TABLE `registros_riegos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'PK: ID del Registro de Riego';

--
-- AUTO_INCREMENT for table `reportes_grupos`
--
ALTER TABLE `reportes_grupos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reportes_grupos_items`
--
ALTER TABLE `reportes_grupos_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `riegos_exclusiones`
--
ALTER TABLE `riegos_exclusiones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'PK: ID de la Exclusión';

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'PK: ID del Rol';

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'PK: ID del Trabajador';

--
-- AUTO_INCREMENT for table `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'PK: ID del Usuario';

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
-- Constraints for table `config_distribucion_riego`
--
ALTER TABLE `config_distribucion_riego`
  ADD CONSTRAINT `config_distribucion_riego_ibfk_1` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `config_distribucion_riego_ibfk_2` FOREIGN KEY (`predio_origen_id`) REFERENCES `predios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `config_distribucion_riego_ibfk_3` FOREIGN KEY (`predio_destino_id`) REFERENCES `predios` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cosechas_configuracion`
--
ALTER TABLE `cosechas_configuracion`
  ADD CONSTRAINT `fk_conf_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cosechas_destinos`
--
ALTER TABLE `cosechas_destinos`
  ADD CONSTRAINT `fk_destino_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cosechas_registros`
--
ALTER TABLE `cosechas_registros`
  ADD CONSTRAINT `fk_cr_destino` FOREIGN KEY (`destino_id`) REFERENCES `cosechas_destinos` (`id`),
  ADD CONSTRAINT `fk_cr_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cr_entidad` FOREIGN KEY (`entidad_legal_id`) REFERENCES `entidades_legales` (`id`),
  ADD CONSTRAINT `fk_cr_predio` FOREIGN KEY (`predio_id`) REFERENCES `predios` (`id`),
  ADD CONSTRAINT `fk_cr_sector` FOREIGN KEY (`sector_id`) REFERENCES `sectores` (`id`);

--
-- Constraints for table `cultivos`
--
ALTER TABLE `cultivos`
  ADD CONSTRAINT `fk_cultivo_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `entidades_legales`
--
ALTER TABLE `entidades_legales`
  ADD CONSTRAINT `fk_entidad_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `fertilizaciones_cabezal`
--
ALTER TABLE `fertilizaciones_cabezal`
  ADD CONSTRAINT `fertilizaciones_cabezal_ibfk_1` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fertilizaciones_cabezal_ibfk_2` FOREIGN KEY (`predio_cabezal_id`) REFERENCES `predios` (`id`),
  ADD CONSTRAINT `fertilizaciones_cabezal_ibfk_3` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `fertilizaciones_cabezal_ibfk_4` FOREIGN KEY (`fertilizante_id`) REFERENCES `fertilizantes` (`id`);

--
-- Constraints for table `fertilizaciones_reales`
--
ALTER TABLE `fertilizaciones_reales`
  ADD CONSTRAINT `fertilizaciones_reales_ibfk_1` FOREIGN KEY (`fertilizacion_cabezal_id`) REFERENCES `fertilizaciones_cabezal` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fertilizaciones_reales_ibfk_2` FOREIGN KEY (`predio_destino_id`) REFERENCES `predios` (`id`);

--
-- Constraints for table `fertilizantes`
--
ALTER TABLE `fertilizantes`
  ADD CONSTRAINT `fertilizantes_ibfk_1` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE;

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
  ADD CONSTRAINT `fk_predio_cultivo` FOREIGN KEY (`cultivo_id`) REFERENCES `cultivos` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_predio_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `programas_detalles`
--
ALTER TABLE `programas_detalles`
  ADD CONSTRAINT `fk_detalle_programa` FOREIGN KEY (`programa_id`) REFERENCES `programas_fertilizacion` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `programas_fertilizacion`
--
ALTER TABLE `programas_fertilizacion`
  ADD CONSTRAINT `fk_prog_cultivo` FOREIGN KEY (`cultivo_id`) REFERENCES `cultivos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_prog_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_prog_predio` FOREIGN KEY (`predio_id`) REFERENCES `predios` (`id`) ON DELETE CASCADE;

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
-- Constraints for table `reportes_grupos`
--
ALTER TABLE `reportes_grupos`
  ADD CONSTRAINT `fk_grupos_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reportes_grupos_items`
--
ALTER TABLE `reportes_grupos_items`
  ADD CONSTRAINT `fk_items_grupo` FOREIGN KEY (`grupo_id`) REFERENCES `reportes_grupos` (`id`) ON DELETE CASCADE;

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
  ADD CONSTRAINT `fk_sector_entidad` FOREIGN KEY (`entidad_legal_id`) REFERENCES `entidades_legales` (`id`) ON DELETE SET NULL,
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
