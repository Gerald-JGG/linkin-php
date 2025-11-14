<?php
class Vehicle
{
    private $conn;
    private $table = 'vehicles';

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Crear vehículo (con control de placa duplicada)
    public function create($data)
    {
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

    public function findById($id)
    {
        $query = "SELECT v.*, u.first_name, u.last_name 
                  FROM vehicles v
                  INNER JOIN users u ON u.id = v.user_id
                  WHERE v.id = :id LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function getByUserId($userId)
    {
        $query = "SELECT * FROM vehicles 
                  WHERE user_id = :uid 
                  ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':uid', $userId);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getPending()
    {
        $query = "SELECT v.*, u.first_name, u.last_name
                  FROM vehicles v
                  INNER JOIN users u ON u.id = v.user_id
                  WHERE v.status = 'pending'
                  ORDER BY v.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getApprovedByUser($userId)
    {
        $query = "SELECT * FROM vehicles 
                  WHERE user_id = :uid 
                  AND status = 'approved'
                  ORDER BY created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':uid', $userId);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getAllApproved()
    {
        $query = "SELECT * FROM vehicles 
                  WHERE status = 'approved'
                  ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function approve($vehicleId, $adminId)
    {
        try {
            $this->conn->beginTransaction();

            // 1. Obtener user_id del vehículo
            $stmt = $this->conn->prepare("SELECT user_id FROM {$this->table} WHERE id = :id FOR UPDATE");
            $stmt->bindParam(':id', $vehicleId, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                $this->conn->rollBack();
                return false;
            }

            $userId = (int) $row['user_id'];

            // 2. Aprobar el vehículo
            $query = "UPDATE {$this->table}
                  SET status = 'approved',
                      approved_by = :admin_id,
                      approved_at = NOW()
                  WHERE id = :id";
            $stmt2 = $this->conn->prepare($query);
            $stmt2->bindParam(':id', $vehicleId, PDO::PARAM_INT);
            $stmt2->bindParam(':admin_id', $adminId, PDO::PARAM_INT);
            $stmt2->execute();

            // 3. Dar rol de Chofer (3) si no lo tiene aún
            $stmt3 = $this->conn->prepare("
            INSERT IGNORE INTO user_roles (user_id, role_id)
            VALUES (:user_id, 3)
        ");
            $stmt3->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt3->execute();

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            return false;
        }
    }


    public function reject($vehicleId, $adminId, $reason)
    {
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

    public function update($id, $data)
    {
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

    public function delete($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM vehicles WHERE id = :id");
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
?>