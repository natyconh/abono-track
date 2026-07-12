<?php
/**
 * Modelo para el Catálogo de Cultivos (Tabla 'cultivos')
 * ADAPTADO para Abono Track: sin multi-empresa, filtro por usuario_id.
 */
class CultivoModel {
    private $db;
    private $usuario_id;

    public function __construct($db, $usuario_id) {
        $this->db         = $db;
        $this->usuario_id = $usuario_id;
    }

    // --- Listados ---

    public function obtenerTodos() {
        $sql = "SELECT * FROM cultivos 
                WHERE usuario_id = :usuario_id 
                ORDER BY nombre ASC, variedad ASC";
        $this->db->query($sql);
        $this->db->bind(':usuario_id', $this->usuario_id);
        return $this->db->resultSet();
    }

    public function obtenerActivos() {
        $sql = "SELECT id, nombre, variedad FROM cultivos 
                WHERE usuario_id = :usuario_id AND activo = 1
                ORDER BY nombre ASC, variedad ASC";
        $this->db->query($sql);
        $this->db->bind(':usuario_id', $this->usuario_id);
        return $this->db->resultSet();
    }

    public function obtenerPorId($id) {
        $sql = "SELECT * FROM cultivos WHERE id = :id AND usuario_id = :usuario_id";
        $this->db->query($sql);
        $this->db->bind(':id',         $id);
        $this->db->bind(':usuario_id', $this->usuario_id);
        return $this->db->single();
    }

    // --- Acciones ---

    public function crear($datos) {
        $sql = "INSERT INTO cultivos (usuario_id, nombre, variedad, activo) 
                VALUES (:usuario, :nombre, :variedad, 1)";
        $this->db->query($sql);
        $this->db->bind(':usuario',  $this->usuario_id);
        $this->db->bind(':nombre',   $datos['nombre']);
        $this->db->bind(':variedad', $datos['variedad']);
        return $this->db->execute();
    }

    public function actualizar($id, $datos) {
        $sql = "UPDATE cultivos SET 
                    nombre   = :nombre, 
                    variedad = :variedad 
                WHERE id = :id AND usuario_id = :usuario";
        $this->db->query($sql);
        $this->db->bind(':id',       $id);
        $this->db->bind(':usuario',  $this->usuario_id);
        $this->db->bind(':nombre',   $datos['nombre']);
        $this->db->bind(':variedad', $datos['variedad']);
        return $this->db->execute();
    }

    public function eliminar($id) {
        $sql = "UPDATE cultivos SET activo = 0 WHERE id = :id AND usuario_id = :usuario";
        $this->db->query($sql);
        $this->db->bind(':id',      $id);
        $this->db->bind(':usuario', $this->usuario_id);
        return $this->db->execute();
    }
}
?>
