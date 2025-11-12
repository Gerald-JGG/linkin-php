<?php
class Booking {
    private $conn;
    private $table = 'bookings';
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    public function create($data) {
        $query = "INSERT INTO " . $this->table . " 
                  (passenger_id, ride_id, seats_requested, booking_date, status) 
                  VALUES (:passenger_id, :ride_id, :seats_requested, :booking_date, 'pending')";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':passenger_id', $data['passenger_id']);
        $stmt->bindParam(':ride_id', $data['ride_id']);
        $stmt->bindParam(':seats_requested', $data['seats_requested']);
        $stmt->bindParam(':booking_date', $data['booking_date']);
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }
    
    public function findById($id) {
        $query = "SELECT b.*, r.ride_name, r.departure_location, r.arrival_location,
                         r.departure_time, r.price_per_seat,
                         u.first_name, u.last_name, u.email, u.phone
                  FROM " . $this->table . " b
                  JOIN rides r ON b.ride_id = r.id
                  JOIN users u ON b.passenger_id = u.id
                  WHERE b.id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    public function getByPassengerId($passengerId) {
        $query = "SELECT b.*, r.ride_name, r.departure_location, r.arrival_location,
                         r.departure_time, r.arrival_time, r.price_per_seat, r.weekdays,
                         v.brand, v.model, v.plate,
                         u.first_name as driver_first_name, u.last_name as driver_last_name
                  FROM " . $this->table . " b
                  JOIN rides r ON b.ride_id = r.id
                  JOIN vehicles v ON r.vehicle_id = v.id
                  JOIN users u ON r.driver_id = u.id
                  WHERE b.passenger_id = :passenger_id
                  ORDER BY b.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':passenger_id', $passengerId);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getByRideId($rideId) {
        $query = "SELECT b.*, u.first_name, u.last_name, u.email, u.phone, u.photo
                  FROM " . $this->table . " b
                  JOIN users u ON b.passenger_id = u.id
                  WHERE b.ride_id = :ride_id
                  ORDER BY b.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':ride_id', $rideId);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getPendingByDriverId($driverId) {
        $query = "SELECT b.*, r.ride_name, u.first_name, u.last_name, u.email, u.phone
                  FROM " . $this->table . " b
                  JOIN rides r ON b.ride_id = r.id
                  JOIN users u ON b.passenger_id = u.id
                  WHERE r.driver_id = :driver_id AND b.status = 'pending'
                  ORDER BY b.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':driver_id', $driverId);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function updateStatus($id, $status) {
        $query = "UPDATE " . $this->table . " SET status = :status WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':status', $status);
        return $stmt->execute();
    }
    
    public function cancel($id) {
        return $this->updateStatus($id, 'cancelled');
    }
    
    public function accept($id) {
        return $this->updateStatus($id, 'accepted');
    }
    
    public function reject($id) {
        return $this->updateStatus($id, 'rejected');
    }
}
?>