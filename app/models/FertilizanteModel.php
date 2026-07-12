<?php
/**
 * Modelo para el Catálogo de Fertilizantes e Insumos
 * (NPK + Micronutrientes JSON)
 * ADAPTADO para Abono Track: sin multi-empresa, filtro por usuario_id.
 */
class FertilizanteModel {
    private $db;
    private $usuario_id;

    public function __construct($db, $usuario_id) {
        $this->db       = $db;
        $this->usuario_id = $usuario_id;
    }

    // --- CRUD Básico ---

    public function obtenerTodos($limit = null, $offset = 0) {
        $sql = "SELECT * FROM fertilizantes 
                WHERE usuario_id = :usuario_id 
                ORDER BY nombre_comercial ASC";

        if ($limit !== null) {
            $sql .= " LIMIT :limit OFFSET :offset";
        }

        $this->db->query($sql);
        $this->db->bind(':usuario_id', $this->usuario_id);

        if ($limit !== null) {
            $this->db->bind(':limit',  (int)$limit);
            $this->db->bind(':offset', (int)$offset);
        }

        return $this->db->resultSet();
    }

    public function obtenerActivos() {
        $sql = "SELECT * FROM fertilizantes WHERE usuario_id = :usuario_id AND activo = 1 ORDER BY nombre_comercial ASC";
        $this->db->query($sql);
        $this->db->bind(':usuario_id', $this->usuario_id);
        return $this->db->resultSet();
    }

    public function obtenerPorId($id) {
        $sql = "SELECT * FROM fertilizantes WHERE id = :id AND usuario_id = :usuario_id";
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        $this->db->bind(':usuario_id', $this->usuario_id);
        $row = $this->db->single();
        if ($row && !empty($row->micronutrientes)) {
            $row->micronutrientes_array = json_decode($row->micronutrientes, true) ?? [];
        } else {
            $row->micronutrientes_array = [];
        }
        return $row;
    }

    public function crear($datos) {
        $sql = "INSERT INTO fertilizantes (
                    usuario_id, nombre_comercial, tipo_producto, tipo_unidad, densidad,
                    porcentaje_n, porcentaje_p, porcentaje_k,
                    micronutrientes, activo
                ) VALUES (
                    :usuario, :nombre, :tipo, :unidad, :densidad,
                    :n, :p, :k,
                    :micros, 1
                )";

        $this->db->query($sql);
        $this->bindParams($datos);
        return $this->db->execute();
    }

    public function actualizar($id, $datos) {
        $sql = "UPDATE fertilizantes SET
                    nombre_comercial = :nombre,
                    tipo_producto    = :tipo,
                    tipo_unidad      = :unidad,
                    densidad         = :densidad,
                    porcentaje_n     = :n,
                    porcentaje_p     = :p,
                    porcentaje_k     = :k,
                    micronutrientes  = :micros
                WHERE id = :id AND usuario_id = :usuario";

        $this->db->query($sql);
        $this->db->bind(':id', $id);
        $this->bindParams($datos);
        return $this->db->execute();
    }

    public function desactivar($id) {
        $sql = "UPDATE fertilizantes SET activo = 0 WHERE id = :id AND usuario_id = :usuario";
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        $this->db->bind(':usuario', $this->usuario_id);
        return $this->db->execute();
    }

    private function bindParams($datos) {
        $this->db->bind(':usuario',   $this->usuario_id);
        $this->db->bind(':nombre',    $datos['nombre_comercial']);
        $this->db->bind(':tipo',      $datos['tipo_producto']);
        $this->db->bind(':unidad',    $datos['tipo_unidad']);
        $this->db->bind(':densidad',  !empty($datos['densidad'])      ? $datos['densidad']      : 1.00);
        $this->db->bind(':n',         !empty($datos['porcentaje_n'])  ? $datos['porcentaje_n']  : 0.00);
        $this->db->bind(':p',         !empty($datos['porcentaje_p'])  ? $datos['porcentaje_p']  : 0.00);
        $this->db->bind(':k',         !empty($datos['porcentaje_k'])  ? $datos['porcentaje_k']  : 0.00);
        $this->db->bind(':micros',    $datos['micronutrientes'] ?? null);
    }
}
?>
