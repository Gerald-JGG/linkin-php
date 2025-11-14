<?php

require_once __DIR__ . '/../Config/database.php';

class User {
    private $conn;
    private $table = "users";

    public function __construct($db) {
        $this->conn = $db;
    }

    /** Obtener TODOS los usuarios con roles */
    public function getAll() {
        $sql = "SELECT u.*, 
                GROUP_CONCAT(r.name SEPARATOR ', ') AS roles
                FROM users u
                LEFT JOIN user_roles ur ON ur.user_id = u.id
                LEFT JOIN roles r ON r.id = ur.role_id
                GROUP BY u.id
                ORDER BY u.id DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById($id) {
        $sql = "SELECT * FROM users WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getUserRoles($id) {
        $sql = "SELECT role_id FROM user_roles WHERE user_id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update($id, $data) {
        $sql = "UPDATE users SET 
                first_name = :first_name,
                last_name = :last_name,
                cedula = :cedula,
                birth_date = :birth_date,
                email = :email,
                phone = :phone
                WHERE id = :id";

        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':id' => $id,
            ':first_name' => $data['first_name'],
            ':last_name' => $data['last_name'],
            ':cedula' => $data['cedula'],
            ':birth_date' => $data['birth_date'],
            ':email' => $data['email'],
            ':phone' => $data['phone']
        ]);
    }
}
