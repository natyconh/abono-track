<?php
// app/models/UserModel.php — Abono Track
// Modelo limpio para login y gestión básica del dueño de la cuenta.

class UserModel {
    private $db;

    public function __construct($db = null, $usuario_id = null) {
        $this->db = $db ?? Database::getInstance();
    }

    public function findUserByUsername($username) {
        $sql = "SELECT id, nombre, username, password_hash, activo 
                FROM usuarios 
                WHERE username = :username";
        $this->db->query($sql);
        $this->db->bind(':username', $username);
        return $this->db->single();
    }

    public function register($data) {
        $sql = "INSERT INTO usuarios (nombre, username, password_hash, activo) 
                VALUES (:nombre, :username, :password_hash, :activo)";
        $this->db->query($sql);
        $this->db->bind(':nombre',        $data['nombre']);
        $this->db->bind(':username',      $data['username']);
        $this->db->bind(':password_hash', $data['password_hash']);
        $this->db->bind(':activo',        $data['activo']);
        
        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    public function updateLoginDate($id) {
        $sql = "UPDATE usuarios SET ultimo_login = NOW() WHERE id = :id";
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }
}
?>
