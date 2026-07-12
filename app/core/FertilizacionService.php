<?php
// app/core/FertilizacionService.php — Abono Track
// Lógica de negocio NPK: registra, actualiza y reporta aplicaciones de fertilizante.
// ADAPTADO: eliminado empresa_id de todos los queries (single-tenant).

class FertilizacionService {

    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    // -----------------------------------------------------------------------
    // CREACIÓN
    // -----------------------------------------------------------------------

    public function registrarAplicacion($datos) {
        try {
            $this->db->beginTransaction();
            $cabezalId = $this->crearRegistroCabezal($datos);
            if (!$cabezalId) { $this->db->rollBack(); return false; }
            $this->procesarDistribucion($cabezalId, $datos);
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    // -----------------------------------------------------------------------
    // ACTUALIZACIÓN
    // -----------------------------------------------------------------------

    public function actualizarAplicacion($id, $datos) {
        try {
            $this->db->beginTransaction();

            $sql = "UPDATE fertilizaciones_cabezal SET
                        predio_cabezal_id  = :predio,
                        fertilizante_id    = :fert,
                        fecha              = :fecha,
                        cantidad_aplicada  = :cant,
                        usuario_id         = :user
                    WHERE id = :id";
            $this->db->query($sql);
            $this->db->bind(':id',    $id);
            $this->db->bind(':predio', $datos['predio_cabezal_id']);
            $this->db->bind(':fert',   $datos['fertilizante_id']);
            $this->db->bind(':fecha',  $datos['fecha']);
            $this->db->bind(':cant',   $datos['cantidad_aplicada']);
            $this->db->bind(':user',   $datos['usuario_id']);
            if (!$this->db->execute()) { $this->db->rollBack(); return false; }

            $this->db->query('DELETE FROM fertilizaciones_reales WHERE fertilizacion_cabezal_id = :id');
            $this->db->bind(':id', $id);
            $this->db->execute();

            $this->procesarDistribucion($id, $datos);
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    // -----------------------------------------------------------------------
    // CONSULTAS PÚBLICAS
    // -----------------------------------------------------------------------

    public function obtenerCabezalPorId($id) {
        $this->db->query('SELECT * FROM fertilizaciones_cabezal WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function verificarDuplicado($fecha, $cabezalId, $fertilizanteId, $excludeId = null) {
        $sql = "SELECT id, cantidad_aplicada FROM fertilizaciones_cabezal
                WHERE fecha = :fecha AND predio_cabezal_id = :cabezal AND fertilizante_id = :fert";
        if ($excludeId) $sql .= ' AND id != :excludeId';
        $this->db->query($sql);
        $this->db->bind(':fecha',   $fecha);
        $this->db->bind(':cabezal', $cabezalId);
        $this->db->bind(':fert',    $fertilizanteId);
        if ($excludeId) $this->db->bind(':excludeId', $excludeId);
        return $this->db->single();
    }

    public function obtenerHistorialCabezal($mes, $year, $orderBy = 'fecha', $orderDir = 'DESC') {
        $allowed  = ['fecha' => 'fc.fecha', 'cabezal' => 'p.nombre', 'producto' => 'f.nombre_comercial'];
        $sortCol  = $allowed[$orderBy] ?? 'fc.fecha';
        $sortDir  = strtoupper($orderDir) === 'ASC' ? 'ASC' : 'DESC';

        $sql = "SELECT
                    fc.id, fc.fecha, fc.cantidad_aplicada, fc.fecha_registro,
                    f.nombre_comercial, f.tipo_producto, f.tipo_unidad,
                    p.nombre AS nombre_cabezal,
                    u.username AS nombre_usuario
                FROM fertilizaciones_cabezal fc
                JOIN fertilizantes f  ON fc.fertilizante_id    = f.id
                JOIN predios p        ON fc.predio_cabezal_id  = p.id
                LEFT JOIN usuarios u  ON fc.usuario_id         = u.id
                WHERE MONTH(fc.fecha) = :mes AND YEAR(fc.fecha) = :year
                ORDER BY $sortCol $sortDir, fc.id DESC";
        $this->db->query($sql);
        $this->db->bind(':mes',  $mes);
        $this->db->bind(':year', $year);
        return $this->db->resultSet();
    }

    public function obtenerResumenMensual($mes, $year) {
        $sql = "SELECT
                    p.nombre AS nombre_cabezal,
                    f.nombre_comercial,
                    f.tipo_unidad,
                    SUM(fc.cantidad_aplicada) AS total_cantidad
                FROM fertilizaciones_cabezal fc
                JOIN predios p     ON fc.predio_cabezal_id = p.id
                JOIN fertilizantes f ON fc.fertilizante_id  = f.id
                WHERE MONTH(fc.fecha) = :mes AND YEAR(fc.fecha) = :year
                GROUP BY p.nombre, f.nombre_comercial, f.tipo_unidad
                ORDER BY p.nombre ASC, f.nombre_comercial ASC";
        $this->db->query($sql);
        $this->db->bind(':mes',  $mes);
        $this->db->bind(':year', $year);
        return $this->db->resultSet();
    }

    // -----------------------------------------------------------------------
    // REPORTE NPK TEMPORADA
    // -----------------------------------------------------------------------

    /**
     * Devuelve datos NPK/Ha por predio para un rango de fechas.
     * Incluye micronutrientes agregados en PHP (JSON).
     */
    public function obtenerReporteNutricionalTemporada($fechaInicio, $fechaFin) {
        $sql = "SELECT
                    p.id   AS predio_id,
                    p.nombre AS predio,
                    p.superficie_total AS hectareas,
                    c.nombre AS cultivo,
                    SUM(fr.unidades_n) AS total_n,
                    SUM(fr.unidades_p) AS total_p,
                    SUM(fr.unidades_k) AS total_k,
                    (SUM(fr.unidades_n) / NULLIF(p.superficie_total, 0)) AS n_ha,
                    (SUM(fr.unidades_p) / NULLIF(p.superficie_total, 0)) AS p_ha,
                    (SUM(fr.unidades_k) / NULLIF(p.superficie_total, 0)) AS k_ha
                FROM fertilizaciones_reales fr
                JOIN fertilizaciones_cabezal fc ON fr.fertilizacion_cabezal_id = fc.id
                JOIN predios p  ON fr.predio_destino_id = p.id
                LEFT JOIN cultivos c ON p.cultivo_id = c.id
                WHERE fc.fecha BETWEEN :inicio AND :fin
                GROUP BY p.id, p.nombre, p.superficie_total
                ORDER BY p.nombre ASC";
        $this->db->query($sql);
        $this->db->bind(':inicio', $fechaInicio);
        $this->db->bind(':fin',    $fechaFin);
        $rows = $this->db->resultSet();

        // Agregar micronutrientes en PHP
        $sqlMicros = "SELECT fr.predio_destino_id, fr.unidades_micronutrientes
                      FROM fertilizaciones_reales fr
                      JOIN fertilizaciones_cabezal fc ON fr.fertilizacion_cabezal_id = fc.id
                      WHERE fc.fecha BETWEEN :inicio AND :fin
                        AND fr.unidades_micronutrientes IS NOT NULL";
        $this->db->query($sqlMicros);
        $this->db->bind(':inicio', $fechaInicio);
        $this->db->bind(':fin',    $fechaFin);
        $microRows = $this->db->resultSet();

        $microsPorPredio = [];
        foreach ($microRows as $mr) {
            $decoded = json_decode($mr->unidades_micronutrientes, true);
            if (!is_array($decoded)) continue;
            $pid = $mr->predio_destino_id;
            if (!isset($microsPorPredio[$pid])) $microsPorPredio[$pid] = [];
            foreach ($decoded as $nombre => $valor) {
                $microsPorPredio[$pid][$nombre] = ($microsPorPredio[$pid][$nombre] ?? 0) + (float)$valor;
            }
        }
        foreach ($rows as $row) {
            $row->micronutrientes = $microsPorPredio[$row->predio_id] ?? [];
        }
        return $rows;
    }

    public function obtenerDetalleDistribucion($cabezalId) {
        $sql = "SELECT fr.*, p.nombre AS nombre_sector_destino
                FROM fertilizaciones_reales fr
                JOIN predios p ON fr.predio_destino_id = p.id
                WHERE fr.fertilizacion_cabezal_id = :cabezalId";
        $this->db->query($sql);
        $this->db->bind(':cabezalId', $cabezalId);
        $rows = $this->db->resultSet();
        foreach ($rows as $row) {
            $row->micronutrientes_decoded = !empty($row->unidades_micronutrientes)
                ? json_decode($row->unidades_micronutrientes, true) : [];
        }
        return $rows;
    }

    // -----------------------------------------------------------------------
    // HELPERS PRIVADOS
    // -----------------------------------------------------------------------

    private function procesarDistribucion($cabezalId, $datos) {
        $infoFert      = $this->obtenerInfoFertilizante($datos['fertilizante_id']);
        $distribuciones = $this->obtenerDistribuciones($datos['predio_cabezal_id']);
        $cantidadAplicada = (float)$datos['cantidad_aplicada'];
        $porcentajeSaliente = 0;
        $detalle = [];

        foreach ($distribuciones as $dist) {
            $cantidadParcial = $cantidadAplicada * ($dist->porcentaje_flujo / 100);
            $detalle[] = ['predio_destino_id' => $dist->predio_destino_id, 'cantidad_nominal' => $cantidadParcial];
            $porcentajeSaliente += $dist->porcentaje_flujo;
        }

        $remanente = 100 - $porcentajeSaliente;
        if ($remanente > 0.01) {
            $detalle[] = [
                'predio_destino_id' => $datos['predio_cabezal_id'],
                'cantidad_nominal'  => $cantidadAplicada * ($remanente / 100)
            ];
        }
        foreach ($detalle as $item) {
            $this->crearRegistroReal($cabezalId, $item['predio_destino_id'], $item['cantidad_nominal'], $infoFert);
        }
    }

    private function crearRegistroCabezal($datos) {
        $sql = "INSERT INTO fertilizaciones_cabezal
                    (predio_cabezal_id, usuario_id, fertilizante_id, fecha, cantidad_aplicada)
                VALUES (:predio, :user, :fert, :fecha, :cant)";
        $this->db->query($sql);
        $this->db->bind(':predio', $datos['predio_cabezal_id']);
        $this->db->bind(':user',   $datos['usuario_id']);
        $this->db->bind(':fert',   $datos['fertilizante_id']);
        $this->db->bind(':fecha',  $datos['fecha']);
        $this->db->bind(':cant',   $datos['cantidad_aplicada']);
        return $this->db->execute() ? $this->db->lastInsertId() : false;
    }

    private function obtenerDistribuciones($predioOrigenId) {
        $this->db->query('SELECT predio_destino_id, porcentaje_flujo FROM config_distribucion_riego WHERE predio_origen_id = :origen');
        $this->db->bind(':origen', $predioOrigenId);
        return $this->db->resultSet();
    }

    private function obtenerInfoFertilizante($id) {
        $this->db->query('SELECT porcentaje_n, porcentaje_p, porcentaje_k, micronutrientes, densidad, tipo_unidad FROM fertilizantes WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    private function crearRegistroReal($cabezalId, $predioDestinoId, $cantidadNominal, $infoFert) {
        $masaEfectiva = $cantidadNominal;
        if ($infoFert->tipo_unidad === 'lt') {
            $densidad     = ($infoFert->densidad > 0) ? (float)$infoFert->densidad : 1.0;
            $masaEfectiva = $cantidadNominal * $densidad;
        }
        $n = ($masaEfectiva * ((float)($infoFert->porcentaje_n ?? 0))) / 100;
        $p = ($masaEfectiva * ((float)($infoFert->porcentaje_p ?? 0))) / 100;
        $k = ($masaEfectiva * ((float)($infoFert->porcentaje_k ?? 0))) / 100;

        $unidades_micros = null;
        if (!empty($infoFert->micronutrientes)) {
            $micros = json_decode($infoFert->micronutrientes, true);
            if (is_array($micros) && count($micros) > 0) {
                $resultado = [];
                foreach ($micros as $nombre => $pct) {
                    $resultado[$nombre] = round(($masaEfectiva * (float)$pct) / 100, 4);
                }
                $unidades_micros = json_encode($resultado, JSON_UNESCAPED_UNICODE);
            }
        }

        $sql = "INSERT INTO fertilizaciones_reales
                    (fertilizacion_cabezal_id, predio_destino_id, cantidad_recibida,
                     unidades_n, unidades_p, unidades_k, unidades_micronutrientes)
                VALUES (:cabezal, :destino, :cant, :n, :p, :k, :micros)";
        $this->db->query($sql);
        $this->db->bind(':cabezal', $cabezalId);
        $this->db->bind(':destino', $predioDestinoId);
        $this->db->bind(':cant',    $cantidadNominal);
        $this->db->bind(':n',       $n);
        $this->db->bind(':p',       $p);
        $this->db->bind(':k',       $k);
        $this->db->bind(':micros',  $unidades_micros);
        $this->db->execute();
    }
}
?>
