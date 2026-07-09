<?php
/**
 * Modelo para el Catálogo de Cultivos (Tabla 'cultivos')
 * Parte de la arquitectura v3.6 para Programas de Fertilización
 */
class CultivoModel {
    private $db;
    private $empresa_id;

    public function __construct($db, $empresa_id) {
        $this->db = $db;
        $this->empresa_id = $empresa_id;
    }

    // --- Listados ---

    public function obtenerTodos() {
        $sql = "SELECT * FROM cultivos 
                WHERE empresa_id = :empresa_id 
                ORDER BY nombre ASC, variedad ASC";
        $this->db->query($sql);
        $this->db->bind(':empresa_id', $this->empresa_id);
        return $this->db->resultSet();
    }

    /**
     * Para llenar el <select> en el formulario de Predios
     */
    public function obtenerActivos() {
        $sql = "SELECT id, nombre, variedad FROM cultivos 
                WHERE empresa_id = :empresa_id AND activo = 1
                ORDER BY nombre ASC, variedad ASC";
        $this->db->query($sql);
        $this->db->bind(':empresa_id', $this->empresa_id);
        return $this->db->resultSet();
    }

    public function obtenerPorId($id) {
        $sql = "SELECT * FROM cultivos WHERE id = :id AND empresa_id = :empresa_id";
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        $this->db->bind(':empresa_id', $this->empresa_id);
        return $this->db->single();
    }

    // --- Acciones ---

    public function crear($datos) {
        $sql = "INSERT INTO cultivos (empresa_id, nombre, variedad, activo) 
                VALUES (:empresa, :nombre, :variedad, 1)";
        
        $this->db->query($sql);
        $this->db->bind(':empresa', $this->empresa_id);
        $this->db->bind(':nombre', $datos['nombre']);
        $this->db->bind(':variedad', $datos['variedad']);
        return $this->db->execute();
    }

    public function actualizar($id, $datos) {
        $sql = "UPDATE cultivos SET 
                    nombre = :nombre, 
                    variedad = :variedad 
                WHERE id = :id AND empresa_id = :empresa";
        
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        $this->db->bind(':empresa', $this->empresa_id);
        $this->db->bind(':nombre', $datos['nombre']);
        $this->db->bind(':variedad', $datos['variedad']);
        return $this->db->execute();
    }

    public function eliminar($id) {
        // Borrado lógico para mantener integridad histórica
        $sql = "UPDATE cultivos SET activo = 0 WHERE id = :id AND empresa_id = :empresa";
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        $this->db->bind(':empresa', $this->empresa_id);
        return $this->db->execute();
    }
}
?>