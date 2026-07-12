<?php
// app/core/FertilizacionService.php — Abono Track
// Lógica de negocio NPK: registra, actualiza y reporta aplicaciones de fertilizante.

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

        // Agregar micronutrientes en PHP — FIX: descarta strings vacíos y JSONs inválidos
        $sqlMicros = "SELECT fr.predio_destino_id, fr.unidades_micronutrientes
                      FROM fertilizaciones_reales fr
                      JOIN fertilizaciones_cabezal fc ON fr.fertilizacion_cabezal_id = fc.id
                      WHERE fc.fecha BETWEEN :inicio AND :fin
                        AND fr.unidades_micronutrientes IS NOT NULL
                        AND fr.unidades_micronutrientes != ''";
        $this->db->query($sqlMicros);
        $this->db->bind(':inicio', $fechaInicio);
        $this->db->bind(':fin',    $fechaFin);
        $microRows = $this->db->resultSet();

        $microsPorPredio = [];
        foreach ($microRows as $mr) {
            $raw     = trim($mr->unidades_micronutrientes);
            $decoded = json_decode($raw, true);
            if (!is_array($decoded) || count($decoded) === 0) continue;
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

    // -----------------------------------------------------------------------
    // COMPARACIÓN PROGRAMA vs APLICADO
    // -----------------------------------------------------------------------

    /**
     * Compara lo planificado (programas_fertilizacion) vs lo aplicado
     * (fertilizaciones_reales) para un predio y temporada dados.
     * Retorna array con filas por semana: objetivo N/P/K, aplicado N/P/K, desviación %.
     */
    public function compararProgramaVsAplicado($predioId, $temporada) {
        // 1. Obtener programa planificado por semana
        $sqlPrograma = "SELECT
                            semana,
                            fecha_estimada,
                            n_objetivo,
                            p_objetivo,
                            k_objetivo,
                            micronutrientes_objetivo,
                            observaciones
                        FROM programas_fertilizacion
                        WHERE predio_id = :predio AND temporada = :temporada
                        ORDER BY semana ASC";
        $this->db->query($sqlPrograma);
        $this->db->bind(':predio',    $predioId);
        $this->db->bind(':temporada', $temporada);
        $programa = $this->db->resultSet();

        if (empty($programa)) return [];

        // 2. Determinar rango de fechas del programa
        $fechaMin = $programa[0]->fecha_estimada;
        $fechaMax = end($programa)->fecha_estimada;

        // 3. Obtener aplicaciones reales agrupadas por semana ISO
        $sqlAplicado = "SELECT
                            WEEK(fc.fecha, 1)                           AS semana_iso,
                            SUM(fr.unidades_n)                          AS real_n,
                            SUM(fr.unidades_p)                          AS real_p,
                            SUM(fr.unidades_k)                          AS real_k,
                            GROUP_CONCAT(
                                fr.unidades_micronutrientes
                                ORDER BY fr.id SEPARATOR '|||'
                            )                                           AS micros_json_list
                        FROM fertilizaciones_reales fr
                        JOIN fertilizaciones_cabezal fc ON fr.fertilizacion_cabezal_id = fc.id
                        WHERE fr.predio_destino_id = :predio
                          AND fc.fecha BETWEEN :inicio AND :fin
                        GROUP BY semana_iso
                        ORDER BY semana_iso ASC";
        $this->db->query($sqlAplicado);
        $this->db->bind(':predio',  $predioId);
        $this->db->bind(':inicio',  $fechaMin);
        $this->db->bind(':fin',     $fechaMax);
        $aplicaciones = $this->db->resultSet();

        // Indexar aplicaciones por semana ISO
        $aplicadoPorSemana = [];
        foreach ($aplicaciones as $a) {
            $aplicadoPorSemana[(int)$a->semana_iso] = $a;
        }

        // 4. Cruzar programa con aplicado
        $resultado = [];
        foreach ($programa as $prog) {
            $semanaIso = (int)date('W', strtotime($prog->fecha_estimada));
            $aplicado  = $aplicadoPorSemana[$semanaIso] ?? null;

            $realN = $aplicado ? (float)$aplicado->real_n : 0;
            $realP = $aplicado ? (float)$aplicado->real_p : 0;
            $realK = $aplicado ? (float)$aplicado->real_k : 0;

            $objN = (float)$prog->n_objetivo;
            $objP = (float)$prog->p_objetivo;
            $objK = (float)$prog->k_objetivo;

            // Micronutrientes aplicados: sumar JSONs concatenados
            $realMicros = [];
            if ($aplicado && !empty($aplicado->micros_json_list)) {
                foreach (explode('|||', $aplicado->micros_json_list) as $jsonStr) {
                    $dec = json_decode(trim($jsonStr), true);
                    if (!is_array($dec)) continue;
                    foreach ($dec as $k => $v) {
                        $realMicros[$k] = ($realMicros[$k] ?? 0) + (float)$v;
                    }
                }
            }

            $resultado[] = [
                'semana'            => (int)$prog->semana,
                'fecha_estimada'    => $prog->fecha_estimada,
                'observaciones'     => $prog->observaciones,
                // Objetivos
                'obj_n'             => $objN,
                'obj_p'             => $objP,
                'obj_k'             => $objK,
                'obj_micros'        => $prog->micronutrientes_objetivo
                                         ? json_decode($prog->micronutrientes_objetivo, true)
                                         : [],
                // Aplicado
                'real_n'            => $realN,
                'real_p'            => $realP,
                'real_k'            => $realK,
                'real_micros'       => $realMicros,
                // Desviación % (negativo = déficit, positivo = exceso)
                'dev_n'             => $objN > 0 ? round((($realN - $objN) / $objN) * 100, 1) : null,
                'dev_p'             => $objP > 0 ? round((($realP - $objP) / $objP) * 100, 1) : null,
                'dev_k'             => $objK > 0 ? round((($realK - $objK) / $objK) * 100, 1) : null,
            ];
        }
        return $resultado;
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
    // GENERACIÓN DE TOKEN PÚBLICO
    // -----------------------------------------------------------------------

    public function generarTokenReporte($usuarioId) {
        $token = bin2hex(random_bytes(24));
        $expiry = date('Y-m-d H:i:s', strtotime('+7 days'));
        $this->db->query('INSERT INTO reportes_publicos (usuario_id, token, expiracion) VALUES (:uid, :tok, :exp)
                          ON DUPLICATE KEY UPDATE token = :tok, expiracion = :exp');
        $this->db->bind(':uid', $usuarioId);
        $this->db->bind(':tok', $token);
        $this->db->bind(':exp', $expiry);
        return $this->db->execute() ? $token : false;
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

    /**
     * Crea un registro en fertilizaciones_reales calculando unidades N/P/K
     * y micronutrientes desde la composición del fertilizante.
     *
     * FIX: Normaliza micronutrientes vacíos a NULL para evitar que json_decode
     * falle silenciosamente en el reporte nutricional.
     */
    private function crearRegistroReal($cabezalId, $predioDestinoId, $cantidadNominal, $infoFert) {
        $masaEfectiva = $cantidadNominal;
        if ($infoFert->tipo_unidad === 'lt') {
            $densidad     = ((float)($infoFert->densidad ?? 0) > 0) ? (float)$infoFert->densidad : 1.0;
            $masaEfectiva = $cantidadNominal * $densidad;
        }
        $n = ($masaEfectiva * ((float)($infoFert->porcentaje_n ?? 0))) / 100;
        $p = ($masaEfectiva * ((float)($infoFert->porcentaje_p ?? 0))) / 100;
        $k = ($masaEfectiva * ((float)($infoFert->porcentaje_k ?? 0))) / 100;

        // --- FIX micronutrientes ---
        // Normalizar campo: si es null, vacío o whitespace, no guardar JSON.
        $unidades_micros = null;
        $rawMicro = trim($infoFert->micronutrientes ?? '');
        if ($rawMicro !== '' && $rawMicro !== '{}' && $rawMicro !== '[]' && $rawMicro !== 'null') {
            $micros = json_decode($rawMicro, true);
            if (is_array($micros) && count($micros) > 0) {
                $resultado = [];
                foreach ($micros as $nombre => $pct) {
                    $valor = round(($masaEfectiva * (float)$pct) / 100, 4);
                    if ($valor > 0) $resultado[$nombre] = $valor;
                }
                if (count($resultado) > 0) {
                    $unidades_micros = json_encode($resultado, JSON_UNESCAPED_UNICODE);
                }
            }
        }
        // --- FIN FIX ---

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
