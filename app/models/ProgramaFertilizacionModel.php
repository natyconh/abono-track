<?php
// app/models/ProgramaFertilizacionModel.php — Abono Track
// CRUD para la tabla programas_fertilizacion.

class ProgramaFertilizacionModel {

    private $db;
    private $usuario_id;

    public function __construct($db, $usuario_id) {
        $this->db         = $db;
        $this->usuario_id = $usuario_id;
    }

    // -----------------------------------------------------------------------
    // LECTURA
    // -----------------------------------------------------------------------

    /**
     * Lista todos los programas del usuario, agrupando por predio y temporada.
     */
    public function listarResumen() {
        $sql = "SELECT
                    pf.predio_id,
                    p.nombre        AS predio,
                    pf.temporada,
                    COUNT(*)        AS total_semanas,
                    MIN(pf.fecha_estimada) AS inicio,
                    MAX(pf.fecha_estimada) AS fin,
                    SUM(pf.n_objetivo) AS total_n,
                    SUM(pf.p_objetivo) AS total_p,
                    SUM(pf.k_objetivo) AS total_k
                FROM programas_fertilizacion pf
                JOIN predios p ON pf.predio_id = p.id
                WHERE p.usuario_id = :uid
                GROUP BY pf.predio_id, pf.temporada
                ORDER BY pf.temporada DESC, p.nombre ASC";
        $this->db->query($sql);
        $this->db->bind(':uid', $this->usuario_id);
        return $this->db->resultSet();
    }

    /**
     * Obtiene todas las filas de un programa (por predio + temporada).
     */
    public function obtenerPorPredioTemporada($predioId, $temporada) {
        $sql = "SELECT pf.*, p.nombre AS predio, c.nombre AS cultivo
                FROM programas_fertilizacion pf
                JOIN predios p ON pf.predio_id = p.id
                LEFT JOIN cultivos c ON pf.cultivo_id = c.id
                WHERE pf.predio_id = :predio AND pf.temporada = :temporada
                  AND p.usuario_id = :uid
                ORDER BY pf.semana ASC";
        $this->db->query($sql);
        $this->db->bind(':predio',    $predioId);
        $this->db->bind(':temporada', $temporada);
        $this->db->bind(':uid',       $this->usuario_id);
        return $this->db->resultSet();
    }

    /**
     * Obtiene una fila individual por ID.
     */
    public function obtenerPorId($id) {
        $sql = "SELECT pf.* FROM programas_fertilizacion pf
                JOIN predios p ON pf.predio_id = p.id
                WHERE pf.id = :id AND p.usuario_id = :uid";
        $this->db->query($sql);
        $this->db->bind(':id',  $id);
        $this->db->bind(':uid', $this->usuario_id);
        return $this->db->single();
    }

    /**
     * Lista temporadas disponibles para un predio.
     */
    public function listarTemporadas($predioId) {
        $sql = "SELECT DISTINCT pf.temporada
                FROM programas_fertilizacion pf
                JOIN predios p ON pf.predio_id = p.id
                WHERE pf.predio_id = :predio AND p.usuario_id = :uid
                ORDER BY pf.temporada DESC";
        $this->db->query($sql);
        $this->db->bind(':predio', $predioId);
        $this->db->bind(':uid',    $this->usuario_id);
        return $this->db->resultSet();
    }

    // -----------------------------------------------------------------------
    // ESCRITURA
    // -----------------------------------------------------------------------

    /**
     * Inserta una fila del programa (una semana).
     */
    public function crear($datos) {
        $sql = "INSERT INTO programas_fertilizacion
                    (predio_id, cultivo_id, temporada, semana, fecha_estimada,
                     n_objetivo, p_objetivo, k_objetivo, micronutrientes_objetivo, observaciones)
                VALUES
                    (:predio, :cultivo, :temporada, :semana, :fecha,
                     :n, :p, :k, :micros, :obs)";
        $this->db->query($sql);
        $this->db->bind(':predio',    $datos['predio_id']);
        $this->db->bind(':cultivo',   $datos['cultivo_id'] ?: null);
        $this->db->bind(':temporada', $datos['temporada']);
        $this->db->bind(':semana',    (int)$datos['semana']);
        $this->db->bind(':fecha',     $datos['fecha_estimada']);
        $this->db->bind(':n',         (float)($datos['n_objetivo'] ?? 0));
        $this->db->bind(':p',         (float)($datos['p_objetivo'] ?? 0));
        $this->db->bind(':k',         (float)($datos['k_objetivo'] ?? 0));
        $this->db->bind(':micros',    $datos['micronutrientes_objetivo'] ?? null);
        $this->db->bind(':obs',       $datos['observaciones'] ?? null);
        return $this->db->execute();
    }

    /**
     * Inserta múltiples filas en una transacción (importación masiva).
     */
    public function crearMasivo(array $filas) {
        $this->db->beginTransaction();
        try {
            foreach ($filas as $datos) {
                if (!$this->crear($datos)) {
                    $this->db->rollBack();
                    return false;
                }
            }
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Actualiza una fila del programa.
     */
    public function actualizar($id, $datos) {
        $sql = "UPDATE programas_fertilizacion SET
                    semana              = :semana,
                    fecha_estimada      = :fecha,
                    n_objetivo          = :n,
                    p_objetivo          = :p,
                    k_objetivo          = :k,
                    micronutrientes_objetivo = :micros,
                    observaciones       = :obs
                WHERE id = :id
                  AND predio_id IN (
                      SELECT id FROM predios WHERE usuario_id = :uid
                  )";
        $this->db->query($sql);
        $this->db->bind(':id',     $id);
        $this->db->bind(':semana', (int)$datos['semana']);
        $this->db->bind(':fecha',  $datos['fecha_estimada']);
        $this->db->bind(':n',      (float)($datos['n_objetivo'] ?? 0));
        $this->db->bind(':p',      (float)($datos['p_objetivo'] ?? 0));
        $this->db->bind(':k',      (float)($datos['k_objetivo'] ?? 0));
        $this->db->bind(':micros', $datos['micronutrientes_objetivo'] ?? null);
        $this->db->bind(':obs',    $datos['observaciones'] ?? null);
        $this->db->bind(':uid',    $this->usuario_id);
        return $this->db->execute();
    }

    /**
     * Elimina todas las filas de un programa (predio + temporada).
     */
    public function eliminarPrograma($predioId, $temporada) {
        $sql = "DELETE pf FROM programas_fertilizacion pf
                JOIN predios p ON pf.predio_id = p.id
                WHERE pf.predio_id = :predio AND pf.temporada = :temporada
                  AND p.usuario_id = :uid";
        $this->db->query($sql);
        $this->db->bind(':predio',    $predioId);
        $this->db->bind(':temporada', $temporada);
        $this->db->bind(':uid',       $this->usuario_id);
        return $this->db->execute();
    }

    /**
     * Elimina una fila individual del programa.
     */
    public function eliminarFila($id) {
        $sql = "DELETE pf FROM programas_fertilizacion pf
                JOIN predios p ON pf.predio_id = p.id
                WHERE pf.id = :id AND p.usuario_id = :uid";
        $this->db->query($sql);
        $this->db->bind(':id',  $id);
        $this->db->bind(':uid', $this->usuario_id);
        return $this->db->execute();
    }
}
?>
