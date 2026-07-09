<?php
/**
 * Modelo para obtener datos de Sectores (tabla 'sectores')
 * MODIFICADO: Corrección crítica para Importador (incluir predio_id)
 * MODIFICADO: Se incluyó superficie y plantas para el módulo de Avance de Labores.
 */
class SectorModel {
    private $db;
    private $empresa_id;
    private $usuario_id;

    public function __construct($db, $empresa_id, $usuario_id) {
        $this->db = $db;
        $this->empresa_id = $empresa_id;
        $this->usuario_id = $usuario_id;
    }

    /**
     * CORREGIDO: Se agrega s.predio_id al SELECT.
     * Es vital para que el Importador de Excel sepa asignar el predio automáticamente.
     */
    public function obtenerSectoresActivos() {
        $sql = "SELECT s.id, s.predio_id, s.nombre, p.nombre AS nombre_predio 
                FROM sectores s
                LEFT JOIN predios p ON s.predio_id = p.id
                WHERE s.activo = 1 AND s.empresa_id = :empresa_id 
                ORDER BY p.nombre, s.nombre ASC";
        
        $this->db->query($sql);
        $this->db->bind(':empresa_id', $this->empresa_id);
        return $this->db->resultSet();
    }

    /**
     * ADAPTACIÓN SEGURA: Se añaden las columnas 'superficie' y 'cantidad_plantas' 
     * para que la calculadora del módulo Avance de Labores funcione. Los módulos
     * anteriores que usen esto solo ignorarán los campos extra, garantizando retrocompatibilidad.
     */
    public function obtenerSectoresPorPredio($predio_id) {
        $sql = "SELECT id, nombre, superficie, cantidad_plantas AS plantas 
                FROM sectores 
                WHERE predio_id = :predio_id 
                AND activo = 1 
                AND empresa_id = :empresa_id 
                ORDER BY nombre ASC";
        
        $this->db->query($sql);
        $this->db->bind(':predio_id', $predio_id);
        $this->db->bind(':empresa_id', $this->empresa_id);
        return $this->db->resultSet();
    } 
    
    public function obtenerTodosLosSectores() {
        $sql = "SELECT s.*, p.nombre AS nombre_predio, e.razon_social AS nombre_entidad
                FROM sectores s
                LEFT JOIN predios p ON s.predio_id = p.id
                LEFT JOIN entidades_legales e ON s.entidad_legal_id = e.id
                WHERE s.empresa_id = :empresa_id
                ORDER BY p.nombre, s.nombre ASC";
        
        $this->db->query($sql);
        $this->db->bind(':empresa_id', $this->empresa_id);
        return $this->db->resultSet();
    }
   
    public function obtenerSectorPorId($id) {
        $sql = "SELECT * FROM sectores WHERE id = :id AND empresa_id = :empresa_id";
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        $this->db->bind(':empresa_id', $this->empresa_id);
        return $this->db->single();
    }
   
    public function crearSector($datos) {
        $sql = "INSERT INTO sectores (empresa_id, predio_id, nombre, unidad, superficie, cantidad_plantas, entidad_legal_id) 
                VALUES (:empresa_id, :predio_id, :nombre, :unidad, :superficie, :cantidad_plantas, :entidad_id)";
       
        $this->db->query($sql);
        $this->db->bind(':empresa_id', $this->empresa_id);
        $this->db->bind(':predio_id', $datos['predio_id']);
        $this->db->bind(':nombre', $datos['nombre']);
        $this->db->bind(':unidad', $datos['unidad']);
        $this->db->bind(':superficie', $datos['superficie']);
        $this->db->bind(':cantidad_plantas', $datos['cantidad_plantas']);
        $this->db->bind(':entidad_id', !empty($datos['entidad_legal_id']) ? $datos['entidad_legal_id'] : null);
        
        return $this->db->execute();
    }

    public function actualizarSector($id, $datos) {
        $sql = "UPDATE sectores SET 
                    predio_id = :predio_id,
                    nombre = :nombre, 
                    unidad = :unidad, 
                    superficie = :superficie, 
                    cantidad_plantas = :cantidad_plantas,
                    entidad_legal_id = :entidad_id
                WHERE id = :id AND empresa_id = :empresa_id";
       
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        $this->db->bind(':empresa_id', $this->empresa_id);
        $this->db->bind(':predio_id', $datos['predio_id']);
        $this->db->bind(':nombre', $datos['nombre']);
        $this->db->bind(':unidad', $datos['unidad']);
        $this->db->bind(':superficie', $datos['superficie']);
        $this->db->bind(':cantidad_plantas', $datos['cantidad_plantas']);
        $this->db->bind(':entidad_id', !empty($datos['entidad_legal_id']) ? $datos['entidad_legal_id'] : null);
        
        return $this->db->execute();
    }

    public function eliminarSector($id) {
        $sql = "UPDATE sectores SET activo = 0 WHERE id = :id AND empresa_id = :empresa_id";
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        $this->db->bind(':empresa_id', $this->empresa_id);
        return $this->db->execute();
    }
}
?>