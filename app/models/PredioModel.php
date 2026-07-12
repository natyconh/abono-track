<?php
/**
 * Modelo para obtener datos de Predios (tabla 'predios')
 * ADAPTADO para Abono Track: sin multi-empresa, filtro por usuario_id.
 * Soporte para Umbrales de Riego.
 */
class PredioModel {
    private $db;
    private $usuario_id;

    public function __construct($db, $usuario_id) {
        $this->db         = $db;
        $this->usuario_id = $usuario_id;
    }

    /**
     * Obtiene SOLO los predios físicos de cultivo.
     */
    public function obtenerPrediosAgricolas() {
        $sql = "SELECT p.*, c.nombre AS nombre_cultivo 
                FROM predios p
                LEFT JOIN cultivos c ON p.cultivo_id = c.id
                WHERE p.usuario_id = :usuario_id 
                  AND p.activo = 1 
                  AND p.tipo_superficie = 'cultivo'
                ORDER BY p.nombre ASC";
        $this->db->query($sql);
        $this->db->bind(':usuario_id', $this->usuario_id);
        return $this->db->resultSet();
    }

    /**
     * Obtiene los puntos de inyección de riego (Cabezales).
     */
    public function obtenerPuntosInyeccion() {
        $sql = "SELECT * FROM predios 
                WHERE usuario_id = :usuario_id 
                  AND activo = 1 
                  AND (
                      tipo_superficie = 'cabezal_virtual' 
                      OR (
                          tipo_superficie = 'cultivo' 
                          AND id NOT IN (
                              SELECT predio_destino_id 
                              FROM config_distribucion_riego 
                              WHERE usuario_id = :usuario_id
                          )
                      )
                  )
                ORDER BY tipo_superficie DESC, nombre ASC";
        $this->db->query($sql);
        $this->db->bind(':usuario_id', $this->usuario_id);
        return $this->db->resultSet();
    }

    public function obtenerTodosLosPredios() {
        $sql = "SELECT p.*, 
                       c.nombre   AS nombre_cultivo, 
                       c.variedad AS variedad_cultivo
                FROM predios p
                LEFT JOIN cultivos c ON p.cultivo_id = c.id
                WHERE p.usuario_id = :usuario_id 
                ORDER BY p.nombre ASC";
        $this->db->query($sql);
        $this->db->bind(':usuario_id', $this->usuario_id);
        return $this->db->resultSet();
    }

    /**
     * Obtiene todos los predios activos del usuario junto con sus
     * distribuciones hidráulicas configuradas (para Config. Hidráulica).
     * Cada predio devuelto tiene una propiedad ->distribuciones (array).
     */
    public function obtenerTodosConDistribuciones() {
        // 1. Todos los predios activos del usuario
        $sql = "SELECT p.*, c.nombre AS nombre_cultivo
                FROM predios p
                LEFT JOIN cultivos c ON p.cultivo_id = c.id
                WHERE p.usuario_id = :usuario_id
                  AND p.activo = 1
                ORDER BY p.tipo_superficie DESC, p.nombre ASC";
        $this->db->query($sql);
        $this->db->bind(':usuario_id', $this->usuario_id);
        $predios = $this->db->resultSet();

        if (empty($predios)) return [];

        // 2. Todas las distribuciones del usuario en una sola consulta
        $sql = "SELECT cdr.id,
                       cdr.predio_origen_id,
                       cdr.predio_destino_id,
                       cdr.porcentaje_flujo,
                       pd.nombre AS nombre_destino
                FROM config_distribucion_riego cdr
                JOIN predios pd ON cdr.predio_destino_id = pd.id
                WHERE cdr.usuario_id = :usuario_id
                ORDER BY cdr.predio_origen_id ASC, cdr.porcentaje_flujo DESC";
        $this->db->query($sql);
        $this->db->bind(':usuario_id', $this->usuario_id);
        $distribuciones = $this->db->resultSet();

        // 3. Indexar distribuciones por predio_origen_id
        $distPorOrigen = [];
        foreach ($distribuciones as $d) {
            $distPorOrigen[$d->predio_origen_id][] = $d;
        }

        // 4. Inyectar distribuciones en cada predio
        foreach ($predios as $predio) {
            $predio->distribuciones = $distPorOrigen[$predio->id] ?? [];
        }

        return $predios;
    }

    public function obtenerPredioPorId($id) {
        $sql = "SELECT * FROM predios WHERE id = :id AND usuario_id = :usuario_id";
        $this->db->query($sql);
        $this->db->bind(':id',         $id);
        $this->db->bind(':usuario_id', $this->usuario_id);
        return $this->db->single();
    }

    public function crearPredio($datos) {
        $sql = "INSERT INTO predios (
                    usuario_id, nombre, cultivo_id, tipo_superficie, 
                    superficie_total, año_plantacion, 
                    tipo_emisor, caudal_lt_hora, plantas_por_hectarea, cantidad_plantas,
                    umbral_bajo, umbral_optimo_min, umbral_optimo_max, umbral_exceso
                ) VALUES (
                    :usuario_id, :nombre, :cultivo, :tipo_sup,
                    :superficie, :ano, 
                    :emisor, :caudal, :densidad, :total_plantas,
                    :u_bajo, :u_opt_min, :u_opt_max, :u_exceso
                )";
        $this->db->query($sql);
        $this->bindParams($datos);
        return $this->db->execute();
    }

    public function actualizarPredio($id, $datos) {
        $sql = "UPDATE predios SET 
                    nombre             = :nombre, 
                    cultivo_id         = :cultivo, 
                    tipo_superficie    = :tipo_sup,
                    superficie_total   = :superficie, 
                    año_plantacion     = :ano, 
                    tipo_emisor        = :emisor, 
                    caudal_lt_hora     = :caudal,
                    plantas_por_hectarea = :densidad,
                    cantidad_plantas   = :total_plantas,
                    umbral_bajo        = :u_bajo,
                    umbral_optimo_min  = :u_opt_min,
                    umbral_optimo_max  = :u_opt_max,
                    umbral_exceso      = :u_exceso
                WHERE id = :id AND usuario_id = :usuario_id";
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        $this->bindParams($datos);
        return $this->db->execute();
    }

    private function bindParams($datos) {
        $this->db->bind(':usuario_id',    $this->usuario_id);
        $this->db->bind(':nombre',        $datos['nombre']);
        $this->db->bind(':cultivo',       $datos['cultivo_id']);
        $this->db->bind(':tipo_sup',      $datos['tipo_superficie']);
        $this->db->bind(':superficie',    $datos['superficie_total']);
        $this->db->bind(':ano',           $datos['año_plantacion']);
        $this->db->bind(':emisor',        $datos['tipo_emisor']);
        $this->db->bind(':caudal',        $datos['caudal_lt_hora']);
        $this->db->bind(':densidad',      $datos['plantas_por_hectarea']);
        $this->db->bind(':total_plantas', $datos['cantidad_plantas']);
        $this->db->bind(':u_bajo',        $datos['umbral_bajo']        ?? 75);
        $this->db->bind(':u_opt_min',     $datos['umbral_optimo_min']  ?? 90);
        $this->db->bind(':u_opt_max',     $datos['umbral_optimo_max']  ?? 110);
        $this->db->bind(':u_exceso',      $datos['umbral_exceso']      ?? 130);
    }

    public function eliminarPredio($id) {
        $sql = "UPDATE predios SET activo = 0 WHERE id = :id AND usuario_id = :usuario_id";
        $this->db->query($sql);
        $this->db->bind(':id',         $id);
        $this->db->bind(':usuario_id', $this->usuario_id);
        return $this->db->execute();
    }

    public function eliminarFisicamente($id) {
        $sql = "DELETE FROM predios WHERE id = :id AND usuario_id = :usuario_id";
        $this->db->query($sql);
        $this->db->bind(':id',         $id);
        $this->db->bind(':usuario_id', $this->usuario_id);
        return $this->db->execute();
    }
}
?>
