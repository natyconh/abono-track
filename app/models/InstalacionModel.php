<?php
/**
 * Modelo para la gestión de Instalaciones (tabla 'instalaciones')
 * MODIFICADO: Adaptado a Schema v2 y DIP/Multi-Tenancy
 */
class InstalacionModel {
    private $db;
    private $empresa_id;
    private $usuario_id;

    public function __construct($db, $empresa_id, $usuario_id) {
        $this->db = $db;
        $this->empresa_id = $empresa_id;
        $this->usuario_id = $usuario_id;
    }

    /**
     * MODIFICADO: Query adaptado a Schema v2 y Multi-Tenancy.
     * Obtiene todas las instalaciones activas de la empresa.
     */
    public function obtenerInstalacionesActivas() {
        $sql = "SELECT
                    i.*, 
                    p.nombre AS nombre_predio, 
                    s.nombre AS nombre_sector
                FROM instalaciones i
                LEFT JOIN predios p ON i.predio_id = p.id
                LEFT JOIN sectores s ON i.sector_id = s.id
                WHERE i.activo = 1 AND i.empresa_id = :empresa_id
                ORDER BY i.nombre ASC";
        
        $this->db->query($sql);
        $this->db->bind(':empresa_id', $this->empresa_id);
        return $this->db->resultSet();
    }

    /**
     * MODIFICADO: Query adaptado a Schema v2 y Multi-Tenancy.
     * Obtiene TODAS (activas e inactivas) para el CRUD.
     */
    public function obtenerTodasLasInstalaciones() {
        $sql = "SELECT
                    i.*, 
                    p.nombre AS nombre_predio, 
                    s.nombre AS nombre_sector
                FROM instalaciones i
                LEFT JOIN predios p ON i.predio_id = p.id
                LEFT JOIN sectores s ON i.sector_id = s.id
                WHERE i.empresa_id = :empresa_id
                ORDER BY i.nombre ASC";
        
        $this->db->query($sql);
        $this->db->bind(':empresa_id', $this->empresa_id);
        return $this->db->resultSet();
    }


    /**
     * MODIFICADO: Query adaptado a Schema v2 y Multi-Tenancy.
     */
    public function obtenerInstalacionPorId($id) {
        $sql = "SELECT * FROM instalaciones WHERE id = :id AND empresa_id = :empresa_id";
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        $this->db->bind(':empresa_id', $this->empresa_id);
        return $this->db->single();
    }

    /**
     * MODIFICADO: Query adaptado a Schema v2 y Multi-Tenancy.
     */
    public function crearInstalacion($datos) {
        $sql = "INSERT INTO instalaciones (empresa_id, nombre, predio_id, sector_id, latitud, longitud) 
                VALUES (:empresa_id, :nombre, :predio_id, :sector_id, :latitud, :longitud)";
        
        $this->db->query($sql);
        $this->db->bind(':empresa_id', $this->empresa_id);
        $this->db->bind(':nombre', $datos['nombre']);
        $this->db->bind(':predio_id', $datos['predio_id']);
        $this->db->bind(':sector_id', $datos['sector_id']);
        $this->db->bind(':latitud', $datos['latitud']);
        $this->db->bind(':longitud', $datos['longitud']);

        return $this->db->execute();
    }

    /**
     * MODIFICADO: Query adaptado a Schema v2 y Multi-Tenancy.
     */
    public function actualizarInstalacion($id, $datos) {
        $sql = "UPDATE instalaciones SET 
                    nombre = :nombre, 
                    predio_id = :predio_id, 
                    sector_id = :sector_id, 
                    latitud = :latitud, 
                    longitud = :longitud 
                WHERE id = :id AND empresa_id = :empresa_id";
        
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        $this->db->bind(':empresa_id', $this->empresa_id);
        $this->db->bind(':nombre', $datos['nombre']);
        $this->db->bind(':predio_id', $datos['predio_id']);
        $this->db->bind(':sector_id', $datos['sector_id']);
        $this->db->bind(':latitud', $datos['latitud']);
        $this->db->bind(':longitud', $datos['longitud']);

        return $this->db->execute();
    }

    /**
     * MODIFICADO: Query adaptado a Schema v2 y Multi-Tenancy.
     * (Soft Delete)
     */
    public function eliminarInstalacion($id) {
        $sql = "UPDATE instalaciones SET activo = 0 WHERE id = :id AND empresa_id = :empresa_id";
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        $this->db->bind(':empresa_id', $this->empresa_id);
        return $this->db->execute();
    }
}
?>