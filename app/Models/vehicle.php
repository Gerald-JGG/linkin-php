<?php
class Vehicle {
    private $conn;
    private $table = 'vehicles';
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    public function create($data) {
        $query = "INSERT INTO " . $this->table . " 
                  (user_id, brand, model, year, color, plate, photo, status) 
                  VALUES (:user_id, :brand, :model, :year, :color, :plate, :photo, 'pending')";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':user_id', $data['user_id']);
        $stmt->bindParam(':brand', $data['brand']);
        $stmt->bindParam(':model', $data['model']);
        $stmt->bindParam(':year', $data['year']);
        $stmt->bindParam(':color', $data['color']);
        $stmt->bindParam(':plate', $data['plate']);
        $stmt->bindParam(':photo', $data['photo']);
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }
    
    public function findById($id) {
        $query = "SELECT v.*, u.first_name, u.last_name 
                  FROM " . $this->table . " v
                  JOIN users u ON v.user_id = u.id
                  WHERE v.id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    public function getByUserId($userId) {
        $query = "SELECT * FROM " . $this->table . " WHERE user_id = :user_id ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getPending() {
        $query = "SELECT v.*, u.first_name, u.last_name, u.email 
                  FROM " . $this->table . " v
                  JOIN users u ON v.user_id = u.id
                  WHERE v.status = 'pending'
                  ORDER BY v.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getApprovedByUserId($userId) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE user_id = :user_id AND status = 'approved' 
                  ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function approve($vehicleId, $adminId) {
        $query = "UPDATE " . $this->table . " 
                  SET status = 'approved', approved_by = :admin_id, approved_at = NOW()
                  WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $vehicleId);
        $stmt->bindParam(':admin_id', $adminId);
        return $stmt->execute();
    }
    
    public function reject($vehicleId, $adminId, $reason) {
        $query = "UPDATE " . $this->table . " 
                  SET status = 'rejected', approved_by = :admin_id, rejection_reason = :reason, approved_at = NOW()
                  WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $vehicleId);
        $stmt->bindParam(':admin_id', $adminId);
        $stmt->bindParam(':reason', $reason);
        return $stmt->execute();
    }
    
    public function update($id, $data) {
        $query = "UPDATE " . $this->table . " 
                  SET brand = :brand, model = :model, year = :year, 
                      color = :color, plate = :plate
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':brand', $data['brand']);
        $stmt->bindParam(':model', $data['model']);
        $stmt->bindParam(':year', $data['year']);
        $stmt->bindParam(':color', $data['color']);
        $stmt->bindParam(':plate', $data['plate']);
        
        return $stmt->execute();
    }
    
    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
?>