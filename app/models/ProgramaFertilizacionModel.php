<?php
/**
 * ProgramaFertilizacionModel — Abono Track
 * Gestiona los programas de fertilización de temporada.
 */
class ProgramaFertilizacionModel {

    private $db;
    private $usuario_id;

    public function __construct($db, $usuario_id) {
        $this->db         = $db;
        $this->usuario_id = $usuario_id;
    }

    /**
     * Lista un resumen por predio+temporada (para el index).
     */
    public function listarResumen() {
        $sql = "SELECT
                    pf.predio_id,
                    p.nombre          AS predio,
                    pf.temporada,
                    COUNT(*)          AS total_semanas,
                    MIN(pf.fecha_estimada) AS inicio,
                    MAX(pf.fecha_estimada) AS fin,
                    SUM(pf.n_objetivo) AS total_n,
                    SUM(pf.p_objetivo) AS total_p,
                    SUM(pf.k_objetivo) AS total_k
                FROM programa_fertilizacion pf
                JOIN predios p ON pf.predio_id = p.id
                WHERE pf.usuario_id = :usuario_id
                GROUP BY pf.predio_id, pf.temporada
                ORDER BY pf.temporada DESC, p.nombre ASC";
        $this->db->query($sql);
        $this->db->bind(':usuario_id', $this->usuario_id);
        return $this->db->resultSet();
    }

    /**
     * Inserta múltiples semanas de un programa en una sola transacción.
     * Usa la API nativa de Database (beginTransaction / commit / rollBack).
     */
    public function crearMasivo(array $filas): bool {
        $sql = "INSERT INTO programa_fertilizacion
                    (usuario_id, predio_id, cultivo_id, temporada, semana,
                     fecha_estimada, n_objetivo, p_objetivo, k_objetivo,
                     micronutrientes_objetivo, observaciones)
                VALUES
                    (:usuario_id, :predio_id, :cultivo_id, :temporada, :semana,
                     :fecha_estimada, :n_objetivo, :p_objetivo, :k_objetivo,
                     :micronutrientes_objetivo, :observaciones)";

        $this->db->beginTransaction();
        try {
            foreach ($filas as $f) {
                $this->db->query($sql);
                $this->db->bind(':usuario_id',               $this->usuario_id);
                $this->db->bind(':predio_id',                $f['predio_id']);
                $this->db->bind(':cultivo_id',               $f['cultivo_id']);
                $this->db->bind(':temporada',                $f['temporada']);
                $this->db->bind(':semana',                   $f['semana']);
                $this->db->bind(':fecha_estimada',           $f['fecha_estimada']);
                $this->db->bind(':n_objetivo',               $f['n_objetivo']);
                $this->db->bind(':p_objetivo',               $f['p_objetivo']);
                $this->db->bind(':k_objetivo',               $f['k_objetivo']);
                $this->db->bind(':micronutrientes_objetivo', $f['micronutrientes_objetivo']);
                $this->db->bind(':observaciones',            $f['observaciones']);
                $this->db->execute();
            }
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Obtiene las semanas de un programa específico para exportar.
     */
    public function obtenerPorPredioTemporada($predioId, $temporada) {
        $sql = "SELECT pf.*, p.nombre AS predio
                FROM programa_fertilizacion pf
                JOIN predios p ON pf.predio_id = p.id
                WHERE pf.predio_id  = :predio_id
                  AND pf.temporada  = :temporada
                  AND pf.usuario_id = :usuario_id
                ORDER BY pf.semana ASC";
        $this->db->query($sql);
        $this->db->bind(':predio_id',  $predioId);
        $this->db->bind(':temporada',  $temporada);
        $this->db->bind(':usuario_id', $this->usuario_id);
        return $this->db->resultSet();
    }

    /**
     * Lista las temporadas disponibles para un predio.
     */
    public function listarTemporadas($predioId) {
        $sql = "SELECT DISTINCT temporada
                FROM programa_fertilizacion
                WHERE predio_id  = :predio_id
                  AND usuario_id = :usuario_id
                ORDER BY temporada DESC";
        $this->db->query($sql);
        $this->db->bind(':predio_id',  $predioId);
        $this->db->bind(':usuario_id', $this->usuario_id);
        return $this->db->resultSet();
    }

    /**
     * Elimina todas las semanas de un programa predio+temporada.
     */
    public function eliminarPrograma($predioId, $temporada): bool {
        $sql = "DELETE FROM programa_fertilizacion
                WHERE predio_id  = :predio_id
                  AND temporada  = :temporada
                  AND usuario_id = :usuario_id";
        $this->db->query($sql);
        $this->db->bind(':predio_id',  $predioId);
        $this->db->bind(':temporada',  $temporada);
        $this->db->bind(':usuario_id', $this->usuario_id);
        return $this->db->execute();
    }

    /**
     * Obtiene objetivos N/P/K acumulados por predio para una temporada.
     * Solo semanas con fecha_estimada <= HOY (lo ya "esperado" hasta hoy).
     * Usado por el reporte nutricional para mostrar alertas de atraso.
     */
    public function obtenerObjetivosAcumuladosPorPredio(string $temporada): array {
        $sql = "SELECT
                    predio_id,
                    SUM(n_objetivo) AS n_objetivo,
                    SUM(p_objetivo) AS p_objetivo,
                    SUM(k_objetivo) AS k_objetivo
                FROM programa_fertilizacion
                WHERE usuario_id     = :usuario_id
                  AND temporada      = :temporada
                  AND fecha_estimada <= CURDATE()
                GROUP BY predio_id";
        $this->db->query($sql);
        $this->db->bind(':usuario_id', $this->usuario_id);
        $this->db->bind(':temporada',  $temporada);
        return $this->db->resultSet() ?: [];
    }
}
?>
