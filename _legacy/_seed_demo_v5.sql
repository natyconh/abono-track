-- ============================================================
-- SEED DATA: EMPRESA 4 "AGRÍCOLA SANTA ELENA" (VERSIÓN FINAL)
-- ============================================================

-- 0. LIMPIEZA DE SEGURIDAD
-- Borramos la empresa 4 si existe para evitar conflictos de datos parciales.
-- Gracias al ON DELETE CASCADE, esto borrará predios, usuarios, sectores, etc. vinculados.
DELETE FROM `empresas` WHERE id = 4;

-- Reiniciamos la transacción
START TRANSACTION;

-- 1. CREAR EMPRESA
INSERT INTO `empresas` (`id`, `nombre`, `mes_inicio_temporada`, `estado_suscripcion`, `activo`) 
VALUES (4, 'Agrícola Santa Elena SpA', 5, 'activa', 1);

SET @empresa_id = 4;

-- ------------------------------------------------------------
-- 2. CREAR PERSONAL
-- ------------------------------------------------------------

-- Trabajadores
INSERT INTO `trabajadores` (`empresa_id`, `rut`, `nombre_completo`, `cargo`, `activo`) VALUES
(@empresa_id, '10.111.222-3', 'Ana Valdés', 'Administradora General', 1),
(@empresa_id, '12.333.444-5', 'Javier Muñoz', 'Jefe de Campo', 1),
(@empresa_id, '14.555.666-7', 'Ricardo Tapia', 'Operario de Riego', 1);

SET @id_ana = LAST_INSERT_ID();
SET @id_javier = @id_ana + 1;
SET @id_ricardo = @id_javier + 1;

-- Usuarios
INSERT INTO `usuarios` (`empresa_id`, `trabajador_id`, `username`, `password_hash`, `rol_id`, `activo`) VALUES
(@empresa_id, @id_ana, 'ana_admin', '$2y$10$9/mVFbICq4ZThGtlszLxJ.oDQlo7JrzZytgOLSrOPcpuIhbqD2iPy', 1, 1),
(@empresa_id, @id_javier, 'javier_campo', '$2y$10$9/mVFbICq4ZThGtlszLxJ.oDQlo7JrzZytgOLSrOPcpuIhbqD2iPy', 4, 1),
(@empresa_id, @id_ricardo, 'ricardo_riego', '$2y$10$9/mVFbICq4ZThGtlszLxJ.oDQlo7JrzZytgOLSrOPcpuIhbqD2iPy', 2, 1);

SET @uid_ana = LAST_INSERT_ID();
SET @uid_javier = @uid_ana + 1;
SET @uid_ricardo = @uid_javier + 1;

-- ------------------------------------------------------------
-- 3. CONFIGURACIÓN TÉCNICA
-- ------------------------------------------------------------

INSERT INTO `cultivos` (`empresa_id`, `nombre`, `variedad`) VALUES
(@empresa_id, 'Palto', 'Hass'),
(@empresa_id, 'Limonero', 'Eureka'),
(@empresa_id, 'Naranjo', 'Fukumoto');

SET @c_palto = LAST_INSERT_ID();
SET @c_limon = @c_palto + 1;
SET @c_naranja = @c_limon + 1;

INSERT INTO `fertilizantes` (`empresa_id`, `nombre_comercial`, `tipo_producto`, `tipo_unidad`, `porcentaje_n`, `porcentaje_k`) VALUES
(@empresa_id, 'Nitrato de Potasio', 'fertilizante', 'kg', 13.00, 44.00),
(@empresa_id, 'Urea', 'fertilizante', 'kg', 46.00, 0.00),
(@empresa_id, 'Ácido Fosfórico', 'fertilizante', 'lt', 0.00, 0.00);

INSERT INTO `puntos_tipos` (`empresa_id`, `nombre`, `categoria`, `color_hex`, `icono_class`) VALUES
(@empresa_id, 'Fuga Riego', 'Riego', '#0dcaf0', 'bi bi-droplet-half'),
(@empresa_id, 'Plaga: Arañita', 'Plagas', '#dc3545', 'bi bi-bug-fill'),
(@empresa_id, 'Falla Estructural', 'Infraestructura', '#ffc107', 'bi bi-cone-striped');

-- ------------------------------------------------------------
-- 4. CONFIGURACIÓN COSECHA
-- ------------------------------------------------------------

INSERT INTO `entidades_legales` (`empresa_id`, `rut`, `razon_social`, `nombre_fantasia`, `codigo_sag`) VALUES
(@empresa_id, '76.111.111-1', 'Agrícola Santa Elena SpA', 'Santa Elena', '12345'),
(@empresa_id, '12.888.999-0', 'Sucesión Pérez Ltda', 'Lote Arrendado', '67890');

SET @entidad_main = LAST_INSERT_ID();
SET @entidad_sucesion = @entidad_main + 1;

INSERT INTO `cosechas_destinos` (`empresa_id`, `nombre`, `tipo`) VALUES
(@empresa_id, 'Packing Exportadora del Valle', 'Exportadora'),
(@empresa_id, 'Feria Lo Valledor', 'Mercado Interno'),
(@empresa_id, 'Jugos del Sur', 'Procesadora');

INSERT INTO `cosechas_configuracion` (`empresa_id`, `modo_pesaje`, `nivel_trazabilidad_legal`, `requiere_calidad`) 
VALUES (@empresa_id, 'BRUTO_TARA', 'SECTOR', 1);

-- ------------------------------------------------------------
-- 5. INFRAESTRUCTURA
-- ------------------------------------------------------------

INSERT INTO `predios` (`empresa_id`, `nombre`, `cultivo_id`, `tipo_superficie`, `superficie_total`, `tipo_emisor`, `caudal_lt_hora`, `plantas_por_hectarea`) VALUES
(@empresa_id, 'Predio El Bajo', @c_palto, 'cultivo', 15.0, 'microaspersor', 35.0, 400),
(@empresa_id, 'Predio Loma Alta', NULL, 'cultivo', 10.0, 'gotero', 4.0, 550);

SET @p_bajo = LAST_INSERT_ID();
SET @p_loma = @p_bajo + 1; 

-- Insertamos Sectores y CAPTURAMOS LOS IDs para usarlos abajo
INSERT INTO `sectores` (`empresa_id`, `predio_id`, `nombre`, `unidad`, `superficie`, `cantidad_plantas`, `entidad_legal_id`) VALUES
(@empresa_id, @p_bajo, 'Sector 1 - Plano', 'S-01', 7.5, 3000, @entidad_main),        -- ID base
(@empresa_id, @p_bajo, 'Sector 2 - Río', 'S-02', 7.5, 3000, @entidad_main),          -- ID base + 1
(@empresa_id, @p_loma, 'Sector 3 - Limones', 'S-03', 5.0, 2750, @entidad_main),      -- ID base + 2
(@empresa_id, @p_loma, 'Sector 4 - Naranjas (Arr)', 'S-04', 5.0, 2750, @entidad_sucesion); -- ID base + 3

-- Variables de Sector (Crucial para evitar el error NULL)
SET @s_plano = LAST_INSERT_ID();
SET @s_naranjas = @s_plano + 3;

-- ------------------------------------------------------------
-- 6. DATOS OPERATIVOS
-- ------------------------------------------------------------

INSERT INTO `registros_riegos` (`empresa_id`, `predio_id`, `usuario_id`, `fecha`, `tiempo_riego`) VALUES
(@empresa_id, @p_bajo, @uid_ricardo, CURDATE() - INTERVAL 2 DAY, 120),
(@empresa_id, @p_loma, @uid_ricardo, CURDATE() - INTERVAL 2 DAY, 180),
(@empresa_id, @p_bajo, @uid_ricardo, CURDATE() - INTERVAL 1 DAY, 100),
(@empresa_id, @p_loma, @uid_ricardo, CURDATE() - INTERVAL 1 DAY, 150),
(@empresa_id, @p_bajo, @uid_ricardo, CURDATE(), 120);

INSERT INTO `lecturas_bandejas` (`empresa_id`, `usuario_id`, `fecha`, `lectura_mm`) VALUES
(@empresa_id, @uid_ricardo, CURDATE() - INTERVAL 2 DAY, 4.5),
(@empresa_id, @uid_ricardo, CURDATE() - INTERVAL 1 DAY, 5.2),
(@empresa_id, @uid_ricardo, CURDATE(), 6.1);

INSERT INTO `puntos` (`empresa_id`, `punto_tipo_id`, `predio_id`, `usuario_registro_id`, `latitud`, `longitud`, `descripcion`, `fecha_registro_db`) VALUES
(@empresa_id, (SELECT id FROM puntos_tipos WHERE nombre='Fuga Riego' AND empresa_id=@empresa_id), @p_bajo, @uid_javier, -32.8855, -71.2455, 'Matriz rota sector 2', NOW() - INTERVAL 2 DAY);

-- Cosecha 1: Paltos (Usando variable @s_plano)
INSERT INTO `cosechas_registros` (
    `empresa_id`, `predio_id`, `sector_id`, `fecha`, `usuario_id`,
    `entidad_legal_id`, `destino_id`, `calidad_declarada`, `tipo_cosecha`,
    `tipo_envase`, `cantidad_envases`, `kilos_brutos`, `tara_promedio`, `kilos_netos`
) VALUES (
    @empresa_id, @p_bajo, @s_plano, CURDATE() - INTERVAL 1 DAY, @uid_javier,
    @entidad_main, (SELECT id FROM cosechas_destinos WHERE tipo='Exportadora' AND empresa_id=@empresa_id LIMIT 1), 'Exportación', 'Manual',
    'Bin Plástico', 10, 4180.00, 38.00, 3800.00
);

-- Cosecha 2: Naranjas (Usando variable @s_naranjas)
INSERT INTO `cosechas_registros` (
    `empresa_id`, `predio_id`, `sector_id`, `fecha`, `usuario_id`,
    `entidad_legal_id`, `destino_id`, `calidad_declarada`, `tipo_cosecha`,
    `tipo_envase`, `cantidad_envases`, `kilos_brutos`, `tara_promedio`, `kilos_netos`
) VALUES (
    @empresa_id, @p_loma, @s_naranjas, CURDATE(), @uid_javier,
    @entidad_sucesion, (SELECT id FROM cosechas_destinos WHERE tipo='Mercado Interno' AND empresa_id=@empresa_id LIMIT 1), 'Nacional', 'Manual',
    'Bin Madera', 5, 2100.00, 45.00, 1875.00
);

COMMIT;