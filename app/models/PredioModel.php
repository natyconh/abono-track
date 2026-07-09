<?php
/**
 * Modelo para obtener datos de Predios (tabla 'predios')
 * BASE: Tu versión más reciente (v3.3).
 * AGREGADO: Soporte para Umbrales de Riego (v2.1).
 */
class PredioModel {
    private $db;
    private $empresa_id;
    private $usuario_id;

    public function __construct($db, $empresa_id, $usuario_id) {
        $this->db = $db;
        $this->empresa_id = $empresa_id;
        $this->usuario_id = $usuario_id;
    }

    /**
     * Obtiene SOLO los predios físicos de cultivo.
     */
    public function obtenerPrediosAgricolas() {
        $sql = "SELECT p.*, c.nombre AS nombre_cultivo 
                FROM predios p
                LEFT JOIN cultivos c ON p.cultivo_id = c.id
                WHERE p.empresa_id = :empresa_id 
                  AND p.activo = 1 
                  AND p.tipo_superficie = 'cultivo'
                ORDER BY p.nombre ASC";
        $this->db->query($sql);
        $this->db->bind(':empresa_id', $this->empresa_id);
        return $this->db->resultSet();
    }

    /**
     * Obtiene los puntos de inyección de riego (Cabezales).
     */
    public function obtenerPuntosInyeccion() {
        $sql = "SELECT * FROM predios 
                WHERE empresa_id = :empresa_id 
                  AND activo = 1 
                  AND (
                      tipo_superficie = 'cabezal_virtual' 
                      OR (
                          tipo_superficie = 'cultivo' 
                          AND id NOT IN (
                              SELECT predio_destino_id 
                              FROM config_distribucion_riego 
                              WHERE empresa_id = :empresa_id
                          )
                      )
                  )
                ORDER BY tipo_superficie DESC, nombre ASC";
        
        $this->db->query($sql);
        $this->db->bind(':empresa_id', $this->empresa_id);
        return $this->db->resultSet();
    }

    public function obtenerTodosLosPredios() {
        $sql = "SELECT p.*, 
                       c.nombre AS nombre_cultivo, 
                       c.variedad AS variedad_cultivo
                FROM predios p
                LEFT JOIN cultivos c ON p.cultivo_id = c.id
                WHERE p.empresa_id = :empresa_id 
                ORDER BY p.nombre ASC";
        $this->db->query($sql);
        $this->db->bind(':empresa_id', $this->empresa_id);
        return $this->db->resultSet();
    }
    
    public function obtenerPredioPorId($id) {
        $sql = "SELECT * FROM predios WHERE id = :id AND empresa_id = :empresa_id";
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        $this->db->bind(':empresa_id', $this->empresa_id);
        return $this->db->single();
    }

    /**
     * Crear Predio (Full Atributos + Umbrales)
     */
    public function crearPredio($datos) {
        $sql = "INSERT INTO predios (
                    empresa_id, nombre, cultivo_id, tipo_superficie, 
                    superficie_total, año_plantacion, 
                    tipo_emisor, caudal_lt_hora, plantas_por_hectarea, cantidad_plantas,
                    umbral_bajo, umbral_optimo_min, umbral_optimo_max, umbral_exceso
                ) VALUES (
                    :empresa_id, :nombre, :cultivo, :tipo_sup,
                    :superficie, :ano, 
                    :emisor, :caudal, :densidad, :total_plantas,
                    :u_bajo, :u_opt_min, :u_opt_max, :u_exceso
                )";
        
        $this->db->query($sql);
        $this->bindParams($datos); // Usamos helper para no repetir código
        return $this->db->execute();
    }

    /**
     * Actualizar Predio (Full Atributos + Umbrales)
     */
    public function actualizarPredio($id, $datos) {
        $sql = "UPDATE predios SET 
                    nombre = :nombre, 
                    cultivo_id = :cultivo, 
                    tipo_superficie = :tipo_sup,
                    superficie_total = :superficie, 
                    año_plantacion = :ano, 
                    tipo_emisor = :emisor, 
                    caudal_lt_hora = :caudal,
                    plantas_por_hectarea = :densidad,
                    cantidad_plantas = :total_plantas,
                    umbral_bajo = :u_bajo,
                    umbral_optimo_min = :u_opt_min,
                    umbral_optimo_max = :u_opt_max,
                    umbral_exceso = :u_exceso
                WHERE id = :id AND empresa_id = :empresa_id";
        
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        $this->bindParams($datos);
        return $this->db->execute();
    }

    /**
     * Helper privado para binding de parámetros comunes
     */
    private function bindParams($datos) {
        $this->db->bind(':empresa_id', $this->empresa_id);
        $this->db->bind(':nombre', $datos['nombre']);
        $this->db->bind(':cultivo', $datos['cultivo_id']);
        $this->db->bind(':tipo_sup', $datos['tipo_superficie']);
        $this->db->bind(':superficie', $datos['superficie_total']);
        $this->db->bind(':ano', $datos['año_plantacion']);
        $this->db->bind(':emisor', $datos['tipo_emisor']);
        $this->db->bind(':caudal', $datos['caudal_lt_hora']);
        $this->db->bind(':densidad', $datos['plantas_por_hectarea']);
        $this->db->bind(':total_plantas', $datos['cantidad_plantas']);
        
        // Umbrales v2.1 (Con defaults de seguridad si vienen vacíos)
        $this->db->bind(':u_bajo', $datos['umbral_bajo'] ?? 75);
        $this->db->bind(':u_opt_min', $datos['umbral_optimo_min'] ?? 90);
        $this->db->bind(':u_opt_max', $datos['umbral_optimo_max'] ?? 110);
        $this->db->bind(':u_exceso', $datos['umbral_exceso'] ?? 130);
    }

    /**
     * Desactivación Lógica (Soft Delete).
     */
    public function eliminarPredio($id) {
        $sql = "UPDATE predios SET activo = 0 WHERE id = :id AND empresa_id = :empresa_id";
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        $this->db->bind(':empresa_id', $this->empresa_id);
        return $this->db->execute();
    }

    /**
     * Eliminación Física (Hard Delete) - Desde tu versión actual.
     */
    public function eliminarFisicamente($id) {
        $sql = "DELETE FROM predios WHERE id = :id AND empresa_id = :empresa_id";
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        $this->db->bind(':empresa_id', $this->empresa_id);
        return $this->db->execute();
    }
}
?>