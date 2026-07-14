<?php
/**
 * Modelo para la Configuración de Distribución Hidráulica de Abono Track
 */
class ConfiguracionRiegoModel {
    private $db;
    private $usuario_id;

    public function __construct($db, $usuario_id) {
        $this->db         = $db;
        $this->usuario_id = $usuario_id;
    }

    public function obtenerPorOrigen($predio_origen_id) {
        $sql = "SELECT c.*, p.nombre as nombre_destino 
                FROM config_distribucion_riego c
                JOIN predios p ON c.predio_destino_id = p.id
                WHERE c.predio_origen_id = :origen AND c.usuario_id = :usuario";
        $this->db->query($sql);
        $this->db->bind(':origen',  $predio_origen_id);
        $this->db->bind(':usuario', $this->usuario_id);
        return $this->db->resultSet();
    }

    /**
     * Guarda (inserta o actualiza) una relación de distribución.
     */
    public function guardarRelacion($origen_id, $destino_id, $porcentaje) {
        $sqlCheck = "SELECT id FROM config_distribucion_riego 
                     WHERE predio_origen_id  = :origen 
                       AND predio_destino_id = :destino 
                       AND usuario_id        = :usuario";
        $this->db->query($sqlCheck);
        $this->db->bind(':origen',  $origen_id);
        $this->db->bind(':destino', $destino_id);
        $this->db->bind(':usuario', $this->usuario_id);
        $existente = $this->db->single();

        if ($existente) {
            $sqlUpdate = "UPDATE config_distribucion_riego 
                          SET porcentaje_flujo = :porcentaje
                          WHERE id = :id";
            $this->db->query($sqlUpdate);
            $this->db->bind(':porcentaje', $porcentaje);
            $this->db->bind(':id', $existente->id);
            return $this->db->execute();
        } else {
            $sqlInsert = "INSERT INTO config_distribucion_riego (usuario_id, predio_origen_id, predio_destino_id, porcentaje_flujo) 
                          VALUES (:usuario, :origen, :destino, :porcentaje)";
            $this->db->query($sqlInsert);
            $this->db->bind(':usuario',    $this->usuario_id);
            $this->db->bind(':origen',     $origen_id);
            $this->db->bind(':destino',    $destino_id);
            $this->db->bind(':porcentaje', $porcentaje);
            return $this->db->execute();
        }
    }

    public function eliminarRelacion($id) {
        $sql = "DELETE FROM config_distribucion_riego WHERE id = :id AND usuario_id = :usuario";
        $this->db->query($sql);
        $this->db->bind(':id',      $id);
        $this->db->bind(':usuario', $this->usuario_id);
        return $this->db->execute();
    }
}
?>
