-- ==========================================================
-- Módulo: Control de Avance de Labores y Rendimiento
-- Plataforma: Ryzoma Agro
-- ==========================================================

-- 1. Catálogo estandarizado de labores
CREATE TABLE IF NOT EXISTS labores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    categoria VARCHAR(50) NULL, -- Ej: Fitosanitario, Cultural, Cosecha
    estado TINYINT(1) DEFAULT 1
);

-- Inserción de datos básicos de ejemplo
INSERT INTO labores (nombre, categoria) VALUES 
('Poda de Producción', 'Cultural'),
('Aplicación Herbicida', 'Fitosanitario'),
('Aplicación Cobre', 'Fitosanitario'),
('Amarra / Conducción', 'Cultural');

-- 2. Órdenes de Trabajo (El contenedor temporal/cíclico)
CREATE TABLE IF NOT EXISTS ordenes_trabajo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    predio_id INT NOT NULL,
    labor_id INT NOT NULL,
    temporada VARCHAR(20) NOT NULL, -- Ej: '2025-2026'
    correlativo_nombre VARCHAR(100) NOT NULL, -- Ej: '1ra Aplicación Cobre'
    estado ENUM('Abierta', 'Pausada', 'Cerrada') DEFAULT 'Abierta',
    fecha_inicio DATE NOT NULL,
    fecha_termino DATE NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (predio_id) REFERENCES predios(id),
    FOREIGN KEY (labor_id) REFERENCES labores(id)
);

-- 3. Cabecera del Reporte Semanal (Vinculado a la OT)
CREATE TABLE IF NOT EXISTS reporte_labor_semanal (
    id INT AUTO_INCREMENT PRIMARY KEY,
    orden_trabajo_id INT NOT NULL,
    semana_anio VARCHAR(10) NOT NULL, -- Formato ISO: '2026-W11'
    observaciones TEXT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    -- Asegura que no se ingrese la misma semana dos veces para la misma OT
    UNIQUE KEY unq_semana_ot (orden_trabajo_id, semana_anio),
    FOREIGN KEY (orden_trabajo_id) REFERENCES ordenes_trabajo(id) ON DELETE CASCADE
);

-- 4. Detalle de Esfuerzo (Jornadas por Día)
CREATE TABLE IF NOT EXISTS reporte_labor_jornadas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reporte_semanal_id INT NOT NULL,
    fecha DATE NOT NULL,
    jornadas_planta DECIMAL(8,2) DEFAULT 0.00,
    jornadas_contratista DECIMAL(8,2) DEFAULT 0.00,
    FOREIGN KEY (reporte_semanal_id) REFERENCES reporte_labor_semanal(id) ON DELETE CASCADE
);

-- 5. Detalle de Avance Físico (Por Sector)
CREATE TABLE IF NOT EXISTS reporte_labor_avance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reporte_semanal_id INT NOT NULL,
    sector_id INT NOT NULL,
    avance_hectareas DECIMAL(10,2) DEFAULT 0.00,
    avance_plantas INT DEFAULT 0,
    avance_porcentaje DECIMAL(5,2) DEFAULT 0.00,
    FOREIGN KEY (reporte_semanal_id) REFERENCES reporte_labor_semanal(id) ON DELETE CASCADE,
    FOREIGN KEY (sector_id) REFERENCES sectores(id)
);