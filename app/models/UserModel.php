<?php
// app/models/UserModel.php
// MODIFICADO: Adaptado a Schema v2 y DIP/Multi-Tenancy

class UserModel {
    private $db; 
    private $empresa_id; 
    private $usuario_id; 

    public function __construct($db = null, $empresa_id = null, $usuario_id = null) {
        $this->db = $db ?? Database::getInstance(); 
        $this->empresa_id = $empresa_id;
        $this->usuario_id = $usuario_id;
    }

    public function findUserByUsername($username) {
        $sql = "SELECT 
                    u.id, u.empresa_id, u.trabajador_id, u.username, 
                    u.password_hash, u.activo, u.rol_id, r.nombre AS nombre_rol,
                    t.nombre_completo AS nombre_completo_trabajador 
                FROM usuarios u
                JOIN roles r ON u.rol_id = r.id
                LEFT JOIN trabajadores t ON u.trabajador_id = t.id AND u.empresa_id = t.empresa_id
                WHERE u.username = :username";
        
        $this->db->query($sql);
        $this->db->bind(':username', $username);
        return $this->db->single();
    }

    public function getAllUsersWithDetails() {
        $sql = "SELECT 
                    u.id, u.username, u.activo, u.ultimo_login,
                    t.nombre_completo AS nombre_trabajador,
                    r.nombre AS nombre_rol
                FROM usuarios u
                LEFT JOIN trabajadores t ON u.trabajador_id = t.id
                LEFT JOIN roles r ON u.rol_id = r.id
                WHERE u.empresa_id = :empresa_id
                ORDER BY u.username";
        
        $this->db->query($sql);
        $this->db->bind(':empresa_id', $this->empresa_id);
        return $this->db->resultSet();
    }
    
    /**
     * MODIFICADO: Ahora devuelve el ID insertado si es exitoso.
     */
    public function register($data) {
        $sql = "INSERT INTO usuarios (empresa_id, trabajador_id, username, password_hash, rol_id, activo, fecha_creacion) 
                VALUES (:empresa_id, :trabajador_id, :username, :password_hash, :rol_id, :activo, NOW())";
        
        $this->db->query($sql);
        $this->db->bind(':empresa_id', $data['empresa_id']); 
        $this->db->bind(':trabajador_id', $data['trabajador_id']);
        $this->db->bind(':username', $data['username']);
        $this->db->bind(':password_hash', $data['password_hash']);
        $this->db->bind(':rol_id', $data['rol_id']);
        $this->db->bind(':activo', $data['activo']);
        
        if ($this->db->execute()) {
            return $this->db->lastInsertId(); // ¡Retorna el ID!
        }
        return false;
    }
    
    public function update($data) {
        $sql = "UPDATE usuarios SET 
                    username = :username, 
                    rol_id = :rol_id, 
                    trabajador_id = :trabajador_id, 
                    activo = :activo";
        
        if (!empty($data['password_hash'])) {
            $sql .= ", password_hash = :password_hash";
        }
        
        $sql .= " WHERE id = :id AND empresa_id = :empresa_id";

        $this->db->query($sql);
        $this->db->bind(':username', $data['username']);
        $this->db->bind(':rol_id', $data['rol_id']);
        $this->db->bind(':trabajador_id', $data['trabajador_id']);
        $this->db->bind(':activo', $data['activo']);
        $this->db->bind(':id', $data['id']);
        $this->db->bind(':empresa_id', $this->empresa_id);
        
        if (!empty($data['password_hash'])) {
            $this->db->bind(':password_hash', $data['password_hash']);
        }
        
        return $this->db->execute();
    }
    
    public function delete($id) {
        $sql = "DELETE FROM usuarios WHERE id = :id AND empresa_id = :empresa_id";
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        $this->db->bind(':empresa_id', $this->empresa_id);
        return $this->db->execute();
    }

    public function findUserById($id) {
        $sql = "SELECT * FROM usuarios WHERE id = :id AND empresa_id = :empresa_id";
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        $this->db->bind(':empresa_id', $this->empresa_id);
        return $this->db->single();
    }

    public function findUserByTrabajadorId($id, $excludeUserId = 0) {
        $sql = "SELECT id FROM usuarios 
                WHERE trabajador_id = :id 
                AND empresa_id = :empresa_id 
                AND id != :exclude_id";
        
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        $this->db->bind(':empresa_id', $this->empresa_id);
        $this->db->bind(':exclude_id', $excludeUserId);
        return $this->db->single();
    }

    public function getRoles() {
        $this->db->query("SELECT * FROM roles ORDER BY nombre");
        return $this->db->resultSet();
    }

    public function getTrabajadoresSinUsuario() {
        $sql = "SELECT t.id, t.nombre_completo 
                FROM trabajadores t
                WHERE t.empresa_id = :empresa_id
                AND t.activo = 1
                ORDER BY t.nombre_completo";
        
        $this->db->query($sql);
        $this->db->bind(':empresa_id', $this->empresa_id);
        return $this->db->resultSet();
    }
    /**
     * Actualiza la fecha del último inicio de sesión al momento actual.
     */
    public function updateLoginDate($id) {
        $sql = "UPDATE usuarios SET ultimo_login = NOW() WHERE id = :id";
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }
}
?>