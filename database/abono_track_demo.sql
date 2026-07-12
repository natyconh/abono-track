-- =============================================================
-- ABONO TRACK — Datos de Demostración (Seed MVP Final)
-- =============================================================

SET FOREIGN_KEY_CHECKS = 0;

TRUNCATE TABLE `fertilizaciones_reales`;
TRUNCATE TABLE `fertilizaciones_cabezal`;
TRUNCATE TABLE `programa_fertilizacion`;
TRUNCATE TABLE `config_distribucion_riego`;
TRUNCATE TABLE `predios`;
TRUNCATE TABLE `fertilizantes`;
TRUNCATE TABLE `cultivos`;
TRUNCATE TABLE `usuarios`;

SET FOREIGN_KEY_CHECKS = 1;

INSERT INTO `usuarios` (`id`, `nombre`, `username`, `password_hash`, `activo`) VALUES
(1, 'Administrador Agrícola', 'demo', '$2y$10$CdsXGZK5TCJ5F7SODYdlMus/5i8xm1K1XBDcX29FQRcZQkE.i0.J2', 1);

INSERT INTO `cultivos` (`id`, `usuario_id`, `nombre`, `variedad`) VALUES
(1, 1, 'Palto', 'Hass'),
(2, 1, 'Cerezo', 'Regina');

INSERT INTO `fertilizantes` (`id`, `usuario_id`, `nombre_comercial`, `tipo_producto`, `tipo_unidad`, `porcentaje_n`, `porcentaje_p`, `porcentaje_k`, `densidad`) VALUES
(1, 1, 'Urea Granulada', 'fertilizante', 'kg', 46.00, 0.00, 0.00, 1.000),
(2, 1, 'Fosfato Monoamónico (MAP)', 'fertilizante', 'kg', 11.00, 52.00, 0.00, 1.000),
(3, 1, 'Nitrato de Potasio', 'fertilizante', 'kg', 13.00, 0.00, 46.00, 1.000),
(4, 1, 'Ácido Fosfórico 85%', 'fertilizante', 'lt', 0.00, 61.00, 0.00, 1.685),
(5, 1, 'Extracto Húmico', 'biostimulante', 'lt', 0.00, 0.00, 5.00, 1.120);

INSERT INTO `predios` (`id`, `usuario_id`, `cultivo_id`, `nombre`, `tipo_superficie`, `superficie_total`, `plantas_por_hectarea`, `cantidad_plantas`, `tipo_emisor`, `caudal_lt_hora`, `umbral_bajo`, `umbral_optimo_min`, `umbral_optimo_max`, `umbral_exceso`) VALUES
(1, 1, NULL, 'Cabezal Central Riego', 'cabezal_virtual', NULL, NULL, NULL, NULL, NULL, 75, 90, 110, 130),
(2, 1, 1, 'Lote 1 - Paltos Norte', 'cultivo', 5.50, 400, 2200, 'gotero', 2.400, 75, 90, 110, 130),
(3, 1, 2, 'Lote 2 - Cerezos Sur', 'cultivo', 3.20, 800, 2560, 'gotero', 2.000, 75, 90, 110, 130);

INSERT INTO `config_distribucion_riego` (`id`, `usuario_id`, `predio_origen_id`, `predio_destino_id`, `porcentaje_flujo`) VALUES
(1, 1, 1, 2, 60.00),
(2, 1, 1, 3, 40.00);

INSERT INTO `fertilizaciones_cabezal` (`id`, `predio_cabezal_id`, `usuario_id`, `fertilizante_id`, `fecha`, `cantidad_aplicada`) VALUES
(1, 1, 1, 1, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 50.00),
(2, 1, 1, 4, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 20.00),
(3, 1, 1, 3, CURDATE(), 100.00),
(4, 1, 1, 5, CURDATE(), 10.00);

INSERT INTO `fertilizaciones_reales` (`fertilizacion_cabezal_id`, `predio_destino_id`, `cantidad_recibida`, `unidades_n`, `unidades_p`, `unidades_k`) VALUES
(1, 2, 30.00, 13.8000, 0.0000, 0.0000),
(1, 3, 20.00, 9.2000, 0.0000, 0.0000),
(2, 2, 12.00, 0.0000, 12.3342, 0.0000),
(2, 3, 8.00, 0.0000, 8.2228, 0.0000),
(3, 2, 60.00, 7.8000, 0.0000, 27.6000),
(3, 3, 40.00, 5.2000, 0.0000, 18.4000),
(4, 2, 6.00, 0.0000, 0.0000, 0.3360),
(4, 3, 4.00, 0.0000, 0.0000, 0.2240);

INSERT INTO `programa_fertilizacion` (`usuario_id`, `predio_id`, `cultivo_id`, `temporada`, `semana`, `fecha_estimada`, `n_objetivo`, `p_objetivo`, `k_objetivo`, `micronutrientes_objetivo`, `observaciones`) VALUES
(1, 2, 1, '2025', 1, '2025-09-01', 2.500, 1.200, 3.000, NULL, 'Inicio temporada'),
(1, 2, 1, '2025', 2, '2025-09-08', 2.500, 1.200, 3.000, NULL, NULL),
(1, 2, 1, '2025', 3, '2025-09-15', 3.000, 1.500, 3.500, NULL, 'Crecimiento activo'),
(1, 2, 1, '2025', 4, '2025-09-22', 3.000, 1.500, 3.500, NULL, NULL),
(1, 2, 1, '2025', 40, '2026-06-01', 1.500, 0.800, 2.000, NULL, 'Pre-cosecha'),
(1, 2, 1, '2025', 41, '2026-06-08', 1.500, 0.800, 2.000, NULL, NULL),
(1, 3, 2, '2025', 1, '2025-09-01', 1.800, 0.900, 2.200, NULL, 'Inicio temporada'),
(1, 3, 2, '2025', 2, '2025-09-08', 1.800, 0.900, 2.200, NULL, NULL),
(1, 3, 2, '2025', 3, '2025-09-15', 2.000, 1.000, 2.500, NULL, 'Crecimiento activo'),
(1, 3, 2, '2025', 4, '2025-09-22', 2.000, 1.000, 2.500, NULL, NULL),
(1, 3, 2, '2025', 40, '2026-06-01', 1.200, 0.600, 1.500, NULL, 'Pre-cosecha'),
(1, 3, 2, '2025', 41, '2026-06-08', 1.200, 0.600, 1.500, NULL, NULL);
