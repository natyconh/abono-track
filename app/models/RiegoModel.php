<?php
// app/models/RiegoModel.php — Abono Track
// Modelo limpio para registros de riego directos al Predio.

class RiegoModel {
    private $db;
    private $usuario_id;

    public function __construct($db, $usuario_id) {
        $this->db         = $db;
        $this->usuario_id = $usuario_id;
    }

    public function obtenerHistorialRiego($filtros = [], $limit = 30, $offset = 0) {
        $sql = "SELECT 
                    rr.id, rr.fecha, rr.tiempo_riego,
                    p.nombre AS nombre_predio,
                    u.username AS nombre_usuario
                FROM registros_riegos rr
                LEFT JOIN predios  p ON rr.predio_id  = p.id
                LEFT JOIN usuarios u ON rr.usuario_id = u.id
                WHERE rr.usuario_id = :usuario_id";

        $params = [':usuario_id' => $this->usuario_id];

        if (!empty($filtros['fecha_inicio'])) {
            $sql .= " AND rr.fecha >= :fecha_inicio";
            $params[':fecha_inicio'] = $filtros['fecha_inicio'];
        }
        if (!empty($filtros['fecha_fin'])) {
            $sql .= " AND rr.fecha <= :fecha_fin";
            $params[':fecha_fin'] = $filtros['fecha_fin'];
        }
        if (!empty($filtros['predio_id'])) {
            $sql .= " AND rr.predio_id = :predio_id";
            $params[':predio_id'] = $filtros['predio_id'];
        }

        $sql .= " ORDER BY rr.fecha DESC, p.nombre ASC LIMIT :limit OFFSET :offset";

        $this->db->query($sql);
        foreach ($params as $key => $val) $this->db->bind($key, $val);
        $this->db->bind(':limit',  (int)$limit);
        $this->db->bind(':offset', (int)$offset);

        return $this->db->resultSet();
    }

    public function contarHistorialRiego($filtros = []) {
        $sql = "SELECT COUNT(*) as total FROM registros_riegos rr WHERE rr.usuario_id = :usuario_id";
        $params = [':usuario_id' => $this->usuario_id];

        if (!empty($filtros['fecha_inicio'])) {
            $sql .= " AND rr.fecha >= :fecha_inicio";
            $params[':fecha_inicio'] = $filtros['fecha_inicio'];
        }
        if (!empty($filtros['fecha_fin'])) {
            $sql .= " AND rr.fecha <= :fecha_fin";
            $params[':fecha_fin'] = $filtros['fecha_fin'];
        }
        if (!empty($filtros['predio_id'])) {
            $sql .= " AND rr.predio_id = :predio_id";
            $params[':predio_id'] = $filtros['predio_id'];
        }

        $this->db->query($sql);
        foreach ($params as $key => $val) $this->db->bind($key, $val);
        $fila = $this->db->single();
        return $fila ? $fila->total : 0;
    }

    public function obtenerRegistrosPorFecha($fecha) {
        $sql = "SELECT rr.*, u.username as nombre_usuario 
                FROM registros_riegos rr 
                LEFT JOIN usuarios u ON rr.usuario_id = u.id 
                WHERE rr.fecha = :fecha AND rr.usuario_id = :usuario_id";
        $this->db->query($sql);
        $this->db->bind(':fecha',      $fecha);
        $this->db->bind(':usuario_id', $this->usuario_id);
        $resultados = $this->db->resultSet();
        $mapa = [];
        foreach ($resultados as $row) {
            $mapa[$row->predio_id] = $row;
        }
        return $mapa;
    }

    public function crearRegistroRiego($datos) {
        $sql = "INSERT INTO registros_riegos (usuario_id, predio_id, fecha, tiempo_riego) 
                VALUES (:usuario_id, :predio_id, :fecha, :tiempo_riego)";
        $this->db->query($sql);
        $this->db->bind(':usuario_id',   $this->usuario_id);
        $this->db->bind(':predio_id',    $datos['predio_id']);
        $this->db->bind(':fecha',        $datos['fecha']);
        $this->db->bind(':tiempo_riego', $datos['tiempo_riego']);
        if ($this->db->execute()) return $this->db->lastInsertId();
        return false;
    }

    public function actualizarRegistroRiego($id, $datos) {
        $sql = "UPDATE registros_riegos SET 
                    predio_id   = :predio_id, 
                    fecha       = :fecha, 
                    tiempo_riego = :tiempo_riego 
                WHERE id = :id AND usuario_id = :usuario_id";
        $this->db->query($sql);
        $this->db->bind(':predio_id',    $datos['predio_id']);
        $this->db->bind(':fecha',        $datos['fecha']);
        $this->db->bind(':tiempo_riego', $datos['tiempo_riego']);
        $this->db->bind(':id',           $id);
        $this->db->bind(':usuario_id',   $this->usuario_id);
        return $this->db->execute();
    }

    public function obtenerRegistroPorFechaYPredio($fecha, $predio_id) {
        $sql = "SELECT rr.*, u.username as nombre_usuario 
                FROM registros_riegos rr 
                LEFT JOIN usuarios u ON rr.usuario_id = u.id 
                WHERE rr.fecha = :fecha AND rr.predio_id = :predio_id AND rr.usuario_id = :usuario_id";
        $this->db->query($sql);
        $this->db->bind(':fecha',      $fecha);
        $this->db->bind(':predio_id',  $predio_id);
        $this->db->bind(':usuario_id', $this->usuario_id);
        return $this->db->single();
    }

    public function obtenerRegistroPorId($id) {
        $sql = "SELECT * FROM registros_riegos WHERE id = :id AND usuario_id = :usuario_id";
        $this->db->query($sql);
        $this->db->bind(':id',         $id);
        $this->db->bind(':usuario_id', $this->usuario_id);
        return $this->db->single();
    }

    public function eliminarRegistroRiego($id) {
        $sql = "DELETE FROM registros_riegos WHERE id = :id AND usuario_id = :usuario_id";
        $this->db->query($sql);
        $this->db->bind(':id',         $id);
        $this->db->bind(':usuario_id', $this->usuario_id);
        return $this->db->execute();
    }

    public function obtenerMinutosAgrupadosPorFecha($fecha_inicio, $fecha_fin) {
        $sql = "SELECT fecha, SUM(tiempo_riego) as total_minutos
                FROM registros_riegos
                WHERE usuario_id = :usuario_id
                  AND fecha BETWEEN :fecha_inicio AND :fecha_fin
                GROUP BY fecha
                ORDER BY fecha ASC";
        $this->db->query($sql);
        $this->db->bind(':usuario_id',   $this->usuario_id);
        $this->db->bind(':fecha_inicio', $fecha_inicio);
        $this->db->bind(':fecha_fin',    $fecha_fin);
        return $this->db->resultSet();
    }

    public function obtenerRiegoDiarioPorPredio($predio_id, $fecha_inicio, $fecha_fin) {
        $sql = "SELECT fecha, tiempo_riego
                FROM registros_riegos
                WHERE usuario_id = :usuario_id
                  AND predio_id  = :predio_id
                  AND fecha BETWEEN :fecha_inicio AND :fecha_fin
                ORDER BY fecha ASC";
        $this->db->query($sql);
        $this->db->bind(':usuario_id',   $this->usuario_id);
        $this->db->bind(':predio_id',    $predio_id);
        $this->db->bind(':fecha_inicio', $fecha_inicio);
        $this->db->bind(':fecha_fin',    $fecha_fin);
        return $this->db->resultSet();
    }

    public function obtenerResumenMensual($mes, $year) {
        $startDate = "$year-$mes-01";
        $endDate   = date("Y-m-t", strtotime($startDate));
        $sql = "SELECT fecha, COUNT(DISTINCT predio_id) as predios_regados, SUM(tiempo_riego) as total_minutos
                FROM registros_riegos
                WHERE usuario_id = :usuario_id
                  AND fecha BETWEEN :startDate AND :endDate
                GROUP BY fecha";
        $this->db->query($sql);
        $this->db->bind(':usuario_id', $this->usuario_id);
        $this->db->bind(':startDate',  $startDate);
        $this->db->bind(':endDate',    $endDate);
        return $this->db->resultSet();
    }

    public function obtenerConsolidadoRiegoPorPeriodo($fecha_inicio, $fecha_fin) {
        $sql = "SELECT 
                    rr.predio_id,
                    p.nombre as nombre_predio,
                    p.caudal_lt_hora,
                    p.plantas_por_hectarea,
                    p.umbral_bajo, p.umbral_optimo_min, p.umbral_optimo_max, p.umbral_exceso,
                    SUM(rr.tiempo_riego) as total_minutos,
                    COUNT(DISTINCT rr.fecha) as dias_regados
                FROM registros_riegos rr
                JOIN predios p ON rr.predio_id = p.id
                WHERE rr.usuario_id = :usuario_id
                  AND rr.fecha BETWEEN :fecha_inicio AND :fecha_fin
                GROUP BY rr.predio_id";
        $this->db->query($sql);
        $this->db->bind(':usuario_id',   $this->usuario_id);
        $this->db->bind(':fecha_inicio', $fecha_inicio);
        $this->db->bind(':fecha_fin',    $fecha_fin);
        return $this->db->resultSet();
    }

    public function obtenerEventosDetallePredio($predio_id, $fecha_inicio, $fecha_fin) {
        $sql = "SELECT 
                    rr.id, rr.fecha, rr.tiempo_riego,
                    p.nombre as nombre_predio,
                    p.caudal_lt_hora, p.plantas_por_hectarea,
                    u.username as nombre_usuario
                FROM registros_riegos rr
                JOIN  predios  p ON rr.predio_id  = p.id
                LEFT JOIN usuarios u ON rr.usuario_id = u.id
                WHERE rr.usuario_id = :usuario_id
                  AND rr.predio_id  = :predio_id
                  AND rr.fecha BETWEEN :fecha_inicio AND :fecha_fin
                ORDER BY rr.fecha ASC";
        $this->db->query($sql);
        $this->db->bind(':usuario_id',   $this->usuario_id);
        $this->db->bind(':predio_id',    $predio_id);
        $this->db->bind(':fecha_inicio', $fecha_inicio);
        $this->db->bind(':fecha_fin',    $fecha_fin);
        return $this->db->resultSet();
    }
}
?>
