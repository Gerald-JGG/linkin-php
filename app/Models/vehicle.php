<?php
class Vehicle {
    private $conn;
    private $table = 'vehicles';
    
    public function __construct($db) {
        $this->conn = $db;
    }

    // Crear vehículo (con control de placa duplicada)
    public function create($data) {
        try {
            $query = "INSERT INTO vehicles 
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

        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                return "duplicate_plate";
            }
            throw $e;
        }
    }

    public function findById($id) {
        $query = "SELECT v.*, u.first_name, u.last_name 
                  FROM vehicles v
                  INNER JOIN users u ON u.id = v.user_id
                  WHERE v.id = :id LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function getByUserId($userId) {
        $query = "SELECT * FROM vehicles 
                  WHERE user_id = :uid 
                  ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':uid', $userId);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getPending() {
        $query = "SELECT v.*, u.first_name, u.last_name
                  FROM vehicles v
                  INNER JOIN users u ON u.id = v.user_id
                  WHERE v.status = 'pending'
                  ORDER BY v.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getApprovedByUser($userId) {
        $query = "SELECT * FROM vehicles 
                  WHERE user_id = :uid 
                  AND status = 'approved'
                  ORDER BY created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':uid', $userId);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getAllApproved() {
        $query = "SELECT * FROM vehicles 
                  WHERE status = 'approved'
                  ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function approve($vehicleId, $adminId) {
        $query = "UPDATE vehicles
                  SET status = 'approved',
                      approved_by = :aid,
                      approved_at = NOW()
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':aid', $adminId);
        $stmt->bindParam(':id', $vehicleId);
        return $stmt->execute();
    }

    public function reject($vehicleId, $adminId, $reason) {
        $query = "UPDATE vehicles
                  SET status = 'rejected',
                      approved_by = :aid,
                      rejection_reason = :reason,
                      approved_at = NOW()
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':aid', $adminId);
        $stmt->bindParam(':reason', $reason);
        $stmt->bindParam(':id', $vehicleId);
        return $stmt->execute();
    }

    public function update($id, $data) {
        $query = "UPDATE vehicles
                  SET brand = :brand,
                      model = :model,
                      year = :year,
                      color = :color,
                      plate = :plate
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
        $stmt = $this->conn->prepare("DELETE FROM vehicles WHERE id = :id");
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
?>
