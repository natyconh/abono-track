<?php
/**
 * Modelo para la gestión de Razones Sociales (Multi-RUT)
 * Permite abstraer la propiedad legal de la gestión operativa.
 */
class EntidadLegalModel {
    private $db;
    private $empresa_id;

    public function __construct($db, $empresa_id) {
        $this->db = $db;
        $this->empresa_id = $empresa_id;
    }

    public function obtenerTodas() {
        $sql = "SELECT * FROM entidades_legales WHERE empresa_id = :empresa_id AND activo = 1 ORDER BY razon_social ASC";
        $this->db->query($sql);
        $this->db->bind(':empresa_id', $this->empresa_id);
        return $this->db->resultSet();
    }

    /**
     * Obtiene la entidad "Principal" o por defecto.
     * Usado cuando la configuración es 'EMPRESA' (Un solo dueño).
     */
    public function obtenerPrincipal() {
        // Si hay una sola, esa es. Si hay varias, tomamos la primera creada.
        $sql = "SELECT id FROM entidades_legales WHERE empresa_id = :empresa_id AND activo = 1 ORDER BY id ASC LIMIT 1";
        $this->db->query($sql);
        $this->db->bind(':empresa_id', $this->empresa_id);
        $res = $this->db->single();
        return $res ? $res->id : null;
    }

    /**
     * Busca el dueño legal asociado a un Sector específico.
     * Si el sector no tiene dueño definido, busca en el Predio (cascada).
     */
    public function obtenerPorSector($sector_id) {
        // Lógica de cascada: Sector -> Entidad
        // (Nota: En tu schema v5.1 añadiste entidad_legal_id a sectores)
        $sql = "SELECT entidad_legal_id FROM sectores WHERE id = :id AND empresa_id = :empresa_id";
        $this->db->query($sql);
        $this->db->bind(':id', $sector_id);
        $this->db->bind(':empresa_id', $this->empresa_id);
        $res = $this->db->single();
        
        return $res ? $res->entidad_legal_id : null;
    }

    // CRUD Básico para gestión administrativa
    public function crear($datos) {
        $sql = "INSERT INTO entidades_legales (empresa_id, rut, razon_social, codigo_sag) 
                VALUES (:empresa, :rut, :razon, :sag)";
        $this->db->query($sql);
        $this->db->bind(':empresa', $this->empresa_id);
        $this->db->bind(':rut', $datos['rut']);
        $this->db->bind(':razon', $datos['razon_social']);
        $this->db->bind(':sag', $datos['codigo_sag']);
        return $this->db->execute();
    }
}
?>