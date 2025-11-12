<?php
class Ride {
    private $conn;
    private $table = 'rides';
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    public function create($data) {
        $query = "INSERT INTO " . $this->table . " 
                  (driver_id, vehicle_id, ride_name, departure_location, departure_time, 
                   arrival_location, arrival_time, weekdays, price_per_seat, total_seats, available_seats) 
                  VALUES (:driver_id, :vehicle_id, :ride_name, :departure_location, :departure_time,
                          :arrival_location, :arrival_time, :weekdays, :price_per_seat, :total_seats, :available_seats)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':driver_id', $data['driver_id']);
        $stmt->bindParam(':vehicle_id', $data['vehicle_id']);
        $stmt->bindParam(':ride_name', $data['ride_name']);
        $stmt->bindParam(':departure_location', $data['departure_location']);
        $stmt->bindParam(':departure_time', $data['departure_time']);
        $stmt->bindParam(':arrival_location', $data['arrival_location']);
        $stmt->bindParam(':arrival_time', $data['arrival_time']);
        $stmt->bindParam(':weekdays', $data['weekdays']);
        $stmt->bindParam(':price_per_seat', $data['price_per_seat']);
        $stmt->bindParam(':total_seats', $data['total_seats']);
        $stmt->bindParam(':available_seats', $data['total_seats']);
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }
    
    public function findById($id) {
        $query = "SELECT r.*, v.brand, v.model, v.plate, v.color,
                         u.first_name, u.last_name, u.photo as driver_photo
                  FROM " . $this->table . " r
                  JOIN vehicles v ON r.vehicle_id = v.id
                  JOIN users u ON r.driver_id = u.id
                  WHERE r.id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    public function getAvailableRides() {
        $query = "SELECT r.*, v.brand, v.model, v.plate, v.color,
                         u.first_name, u.last_name, u.photo as driver_photo
                  FROM " . $this->table . " r
                  JOIN vehicles v ON r.vehicle_id = v.id
                  JOIN users u ON r.driver_id = u.id
                  WHERE r.is_active = 1 AND r.available_seats > 0
                  ORDER BY r.departure_time ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getByDriverId($driverId) {
        $query = "SELECT r.*, v.brand, v.model, v.plate
                  FROM " . $this->table . " r
                  JOIN vehicles v ON r.vehicle_id = v.id
                  WHERE r.driver_id = :driver_id
                  ORDER BY r.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':driver_id', $driverId);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function update($id, $data) {
        $query = "UPDATE " . $this->table . " 
                  SET ride_name = :ride_name, 
                      departure_location = :departure_location,
                      departure_time = :departure_time,
                      arrival_location = :arrival_location,
                      arrival_time = :arrival_time,
                      weekdays = :weekdays,
                      price_per_seat = :price_per_seat,
                      total_seats = :total_seats
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':ride_name', $data['ride_name']);
        $stmt->bindParam(':departure_location', $data['departure_location']);
        $stmt->bindParam(':departure_time', $data['departure_time']);
        $stmt->bindParam(':arrival_location', $data['arrival_location']);
        $stmt->bindParam(':arrival_time', $data['arrival_time']);
        $stmt->bindParam(':weekdays', $data['weekdays']);
        $stmt->bindParam(':price_per_seat', $data['price_per_seat']);
        $stmt->bindParam(':total_seats', $data['total_seats']);
        
        return $stmt->execute();
    }
    
    public function updateAvailableSeats($rideId, $seats) {
        $query = "UPDATE " . $this->table . " SET available_seats = :seats WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $rideId);
        $stmt->bindParam(':seats', $seats);
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