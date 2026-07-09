<?php
/**
 * Modelo para la gestión de Trabajadores (tabla 'trabajadores')
 * Estándar v2.0 (Multi-Tenancy, DIP)
 */
class TrabajadorModel {
    private $db;
    private $empresa_id;
    private $usuario_id;

    public function __construct($db, $empresa_id, $usuario_id) {
        $this->db = $db;
        $this->empresa_id = $empresa_id;
        $this->usuario_id = $usuario_id;
    }

    /**
     * Obtiene todos los trabajadores (activos e inactivos) para el CRUD.
     */
    public function obtenerTodosLosTrabajadores() {
        $sql = "SELECT * FROM trabajadores 
                WHERE empresa_id = :empresa_id 
                ORDER BY nombre_completo ASC";
        $this->db->query($sql);
        $this->db->bind(':empresa_id', $this->empresa_id);
        return $this->db->resultSet();
    }

    /**
     * Obtiene un trabajador por ID verificando pertenencia a la empresa.
     */
    public function obtenerTrabajadorPorId($id) {
        $sql = "SELECT * FROM trabajadores WHERE id = :id AND empresa_id = :empresa_id";
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        $this->db->bind(':empresa_id', $this->empresa_id);
        return $this->db->single();
    }

    /**
     * Verifica si un RUT ya existe en la empresa (excluyendo un ID si es edición).
     */
    public function existeRut($rut, $excludeId = null) {
        $sql = "SELECT id FROM trabajadores WHERE rut = :rut AND empresa_id = :empresa_id";
        if ($excludeId) {
            $sql .= " AND id != :exclude_id";
        }
        $this->db->query($sql);
        $this->db->bind(':rut', $rut);
        $this->db->bind(':empresa_id', $this->empresa_id);
        if ($excludeId) {
            $this->db->bind(':exclude_id', $excludeId);
        }
        return (bool) $this->db->single();
    }

    public function crearTrabajador($datos) {
        $sql = "INSERT INTO trabajadores (empresa_id, rut, nombre_completo, cargo, activo, fecha_creacion) 
                VALUES (:empresa_id, :rut, :nombre, :cargo, :activo, NOW())";
        
        $this->db->query($sql);
        $this->db->bind(':empresa_id', $this->empresa_id);
        $this->db->bind(':rut', $datos['rut']);
        $this->db->bind(':nombre', $datos['nombre_completo']);
        $this->db->bind(':cargo', $datos['cargo']);
        $this->db->bind(':activo', $datos['activo']);
        return $this->db->execute();
    }

    public function actualizarTrabajador($id, $datos) {
        $sql = "UPDATE trabajadores SET 
                    rut = :rut, 
                    nombre_completo = :nombre, 
                    cargo = :cargo,
                    activo = :activo
                WHERE id = :id AND empresa_id = :empresa_id";
        
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        $this->db->bind(':empresa_id', $this->empresa_id);
        $this->db->bind(':rut', $datos['rut']);
        $this->db->bind(':nombre', $datos['nombre_completo']);
        $this->db->bind(':cargo', $datos['cargo']);
        $this->db->bind(':activo', $datos['activo']);
        return $this->db->execute();
    }

    /**
     * Soft delete (Desactivar).
     * Nota: No eliminamos físicamente para mantener integridad referencial con Usuarios/Labores.
     */
    public function desactivarTrabajador($id) {
        $sql = "UPDATE trabajadores SET activo = 0 WHERE id = :id AND empresa_id = :empresa_id";
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        $this->db->bind(':empresa_id', $this->empresa_id);
        return $this->db->execute();
    }
}
?>