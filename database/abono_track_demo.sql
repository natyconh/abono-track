-- =============================================================
-- ABONO TRACK — Datos de Demostración (Seed)
-- Listo para importar en XAMPP (phpMyAdmin)
-- Usuario: demo
-- Clave:   123456
-- =============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- Limpiar tablas en caso de recarga
TRUNCATE TABLE `fertilizaciones_reales`;
TRUNCATE TABLE `fertilizaciones_cabezal`;
TRUNCATE TABLE `registros_riegos`;
TRUNCATE TABLE `config_distribucion_riego`;
TRUNCATE TABLE `predios`;
TRUNCATE TABLE `fertilizantes`;
TRUNCATE TABLE `cultivos`;
TRUNCATE TABLE `usuarios`;

SET FOREIGN_KEY_CHECKS = 1;

-- -------------------------------------------------------------
-- 1. USUARIO DE DEMOSTRACIÓN (Clave: 123456)
-- -------------------------------------------------------------
INSERT INTO `usuarios` (`id`, `nombre`, `username`, `password_hash`, `activo`) VALUES
(1, 'Administrador Agrícola', 'demo', '$2y$10$CdsXGZK5TCJ5F7SODYdlMus/5i8xm1K1XBDcX29FQRcZQkE.i0.J2', 1);

-- -------------------------------------------------------------
-- 2. CULTIVOS
-- -------------------------------------------------------------
INSERT INTO `cultivos` (`id`, `usuario_id`, `nombre`, `variedad`) VALUES
(1, 1, 'Palto', 'Hass'),
(2, 1, 'Cerezo', 'Regina');

-- -------------------------------------------------------------
-- 3. FERTILIZANTES (Con NPK y Densidades reales)
-- -------------------------------------------------------------
INSERT INTO `fertilizantes` (`id`, `usuario_id`, `nombre_comercial`, `tipo_producto`, `tipo_unidad`, `porcentaje_n`, `porcentaje_p`, `porcentaje_k`, `densidad`) VALUES
(1, 1, 'Urea Granulada', 'fertilizante', 'kg', 46.00, 0.00, 0.00, 1.000),
(2, 1, 'Fosfato Monoamónico (MAP)', 'fertilizante', 'kg', 11.00, 52.00, 0.00, 1.000),
(3, 1, 'Nitrato de Potasio', 'fertilizante', 'kg', 13.00, 0.00, 46.00, 1.000),
(4, 1, 'Ácido Fosfórico 85%', 'fertilizante', 'lt', 0.00, 61.00, 0.00, 1.685),
(5, 1, 'Extracto Húmico', 'biostimulante', 'lt', 0.00, 0.00, 5.00, 1.120);

-- -------------------------------------------------------------
-- 4. PREDIOS Y CABEZAL
-- -------------------------------------------------------------
INSERT INTO `predios` (`id`, `usuario_id`, `cultivo_id`, `nombre`, `tipo_superficie`, `superficie_total`, `plantas_por_hectarea`, `cantidad_plantas`, `tipo_emisor`, `caudal_lt_hora`, `umbral_bajo`, `umbral_optimo_min`, `umbral_optimo_max`, `umbral_exceso`) VALUES
(1, 1, NULL, 'Cabezal Central Riego', 'cabezal_virtual', NULL, NULL, NULL, NULL, NULL, 75, 90, 110, 130),
(2, 1, 1, 'Lote 1 - Paltos Norte', 'cultivo', 5.50, 400, 2200, 'gotero', 2.400, 75, 90, 110, 130),
(3, 1, 2, 'Lote 2 - Cerezos Sur', 'cultivo', 3.20, 800, 2560, 'gotero', 2.000, 75, 90, 110, 130);

-- -------------------------------------------------------------
-- 5. CONFIGURACIÓN DISTRIBUCIÓN DE RIEGO (100% de la mezcla se reparte)
--    (60% para los paltos, 40% para los cerezos)
-- -------------------------------------------------------------
INSERT INTO `config_distribucion_riego` (`id`, `usuario_id`, `predio_origen_id`, `predio_destino_id`, `porcentaje_flujo`) VALUES
(1, 1, 1, 2, 60.00),
(2, 1, 1, 3, 40.00);

-- -------------------------------------------------------------
-- 6. REGISTROS DE RIEGO DIARIO (Simulando los últimos 3 días)
-- -------------------------------------------------------------
INSERT INTO `registros_riegos` (`usuario_id`, `predio_id`, `fecha`, `tiempo_riego`) VALUES
(1, 2, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 180),
(1, 3, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 120),

(1, 2, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 210),
(1, 3, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 150),

(1, 2, CURDATE(), 240),
(1, 3, CURDATE(), 180);

-- -------------------------------------------------------------
-- 7. FERTILIZACIÓN (Aplicaciones recientes en el cabezal)
-- -------------------------------------------------------------
-- Aplicación Hace 2 Días: Urea (50kg) y Ácido Fosfórico (20Lts)
INSERT INTO `fertilizaciones_cabezal` (`id`, `predio_cabezal_id`, `usuario_id`, `fertilizante_id`, `fecha`, `cantidad_aplicada`) VALUES
(1, 1, 1, 1, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 50.00),
(2, 1, 1, 4, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 20.00),

-- Aplicación Hoy: Nitrato de Potasio (100kg) y Extracto Húmico (10Lts)
(3, 1, 1, 3, CURDATE(), 100.00),
(4, 1, 1, 5, CURDATE(), 10.00);

-- -------------------------------------------------------------
-- 8. FERTILIZACIONES REALES (Distribución automática según config: 60/40)
-- -------------------------------------------------------------
-- Urea (50kg): 46% N. Total N=23kg. (Lote 1: 60% = 13.8kg N | Lote 2: 40% = 9.2kg N)
INSERT INTO `fertilizaciones_reales` (`fertilizacion_cabezal_id`, `predio_destino_id`, `cantidad_recibida`, `unidades_n`, `unidades_p`, `unidades_k`) VALUES
(1, 2, 30.00, 13.8000, 0.0000, 0.0000),
(1, 3, 20.00, 9.2000,  0.0000, 0.0000);

-- Ácido Fosfórico (20Lt x 1.685 = 33.7kg): 61% P. Total P=20.557kg (Lote 1: 12.3342kg P | Lote 2: 8.2228kg P)
INSERT INTO `fertilizaciones_reales` (`fertilizacion_cabezal_id`, `predio_destino_id`, `cantidad_recibida`, `unidades_n`, `unidades_p`, `unidades_k`) VALUES
(2, 2, 12.00, 0.0000, 12.3342, 0.0000),
(2, 3, 8.00,  0.0000, 8.2228,  0.0000);

-- Nitrato K (100kg): 13%N, 46%K. Total N=13kg, K=46kg (Lote1: N=7.8, K=27.6 | Lote2: N=5.2, K=18.4)
INSERT INTO `fertilizaciones_reales` (`fertilizacion_cabezal_id`, `predio_destino_id`, `cantidad_recibida`, `unidades_n`, `unidades_p`, `unidades_k`) VALUES
(3, 2, 60.00, 7.8000, 0.0000, 27.6000),
(3, 3, 40.00, 5.2000, 0.0000, 18.4000);

-- Extracto Húmico (10Lts x 1.12 = 11.2kg): 5% K. Total K=0.56kg (Lote1: K=0.336 | Lote2: K=0.224)
INSERT INTO `fertilizaciones_reales` (`fertilizacion_cabezal_id`, `predio_destino_id`, `cantidad_recibida`, `unidades_n`, `unidades_p`, `unidades_k`) VALUES
(4, 2, 6.00, 0.0000, 0.0000, 0.3360),
(4, 3, 4.00, 0.0000, 0.0000, 0.2240);