<?php
class User {
    private $conn;
    private $table = 'users';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function findById($id) {
        $query = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
        $stmt  = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function getAll() {
        $query = "SELECT u.*, 
                     GROUP_CONCAT(r.name SEPARATOR ', ') AS roles
                  FROM users u
                  LEFT JOIN user_roles ur ON ur.user_id = u.id
                  LEFT JOIN roles r ON ur.role_id = r.id
                  GROUP BY u.id
                  ORDER BY u.id DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getUserRoles($userId) {
        $query = "SELECT role_id FROM user_roles WHERE user_id = :uid";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":uid", $userId);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function update($id, $data) {
        $query = "UPDATE {$this->table}
                  SET first_name = :first_name,
                      last_name  = :last_name,
                      cedula     = :cedula,
                      birth_date = :birth_date,
                      email      = :email,
                      phone      = :phone
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':id',         $id);
        $stmt->bindParam(':first_name', $data['first_name']);
        $stmt->bindParam(':last_name',  $data['last_name']);
        $stmt->bindParam(':cedula',     $data['cedula']);
        $stmt->bindParam(':birth_date', $data['birth_date']);
        $stmt->bindParam(':email',      $data['email']);
        $stmt->bindParam(':phone',      $data['phone']);

        return $stmt->execute();
    }

    public function updateRole($userId, $roleId) {
        // borrar todos excepto rol actual admin si existiera
        $delete = $this->conn->prepare("
            DELETE FROM user_roles WHERE user_id = :uid
        ");
        $delete->bindParam(":uid", $userId);
        $delete->execute();

        // asignar nuevo rol
        $insert = $this->conn->prepare("
            INSERT INTO user_roles (user_id, role_id)
            VALUES (:uid, :rid)
        ");
        $insert->bindParam(":uid", $userId);
        $insert->bindParam(":rid", $roleId);
        return $insert->execute();
    }

    public function delete($userId) {
        $deleteRoles = $this->conn->prepare("DELETE FROM user_roles WHERE user_id = :uid");
        $deleteRoles->bindParam(":uid", $userId);
        $deleteRoles->execute();

        $deleteUser = $this->conn->prepare("DELETE FROM users WHERE id = :uid");
        $deleteUser->bindParam(":uid", $userId);
        return $deleteUser->execute();
    }
}
