<?php

require_once __DIR__ . '/../Config/database.php';

class Ride {
    private $conn;
    private $table = 'rides';

    public function __construct($db) {
        $this->conn = $db;
    }

    /** Obtener todos los rides (solo para admin) */
    public function getAllRidesAdmin(): array {
        $sql = "SELECT r.*, 
                CONCAT(u.first_name, ' ', u.last_name) AS driver_name,
                CONCAT(v.brand, ' ', v.model, ' - ', v.plate) AS vehicle_info
                FROM rides r 
                JOIN users u ON r.driver_id = u.id
                JOIN vehicles v ON r.vehicle_id = v.id
                ORDER BY r.id DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Rides de un chofer */
    public function getByDriver($driverId): array {
        $sql = "SELECT r.*, 
                CONCAT(v.brand,' ',v.model,' - ',v.plate) AS vehicle_info
                FROM rides r
                JOIN vehicles v ON r.vehicle_id = v.id
                WHERE r.driver_id = :id
                ORDER BY r.id DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $driverId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Rides disponibles para búsqueda */
    public function getAvailableForSearch(): array {
        $sql = "SELECT r.*, 
                u.first_name AS driver_first_name, 
                u.last_name AS driver_last_name,
                v.brand, v.model, v.color, v.plate
                FROM rides r
                JOIN users u ON r.driver_id = u.id
                JOIN vehicles v ON r.vehicle_id = v.id
                WHERE r.available_seats > 0
                ORDER BY r.departure_time ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Crear ride */
    public function create($data) {
        $sql = "INSERT INTO rides 
                (driver_id, vehicle_id, ride_name, departure_location, departure_time, 
                 arrival_location, arrival_time, weekdays, price_per_seat, 
                 total_seats, available_seats)
                VALUES 
                (:driver_id, :vehicle_id, :ride_name, :departure_location, :departure_time,
                 :arrival_location, :arrival_time, :weekdays, :price_per_seat,
                 :total_seats, :available_seats)";

        $stmt = $this->conn->prepare($sql);

        $stmt->execute([
            ':driver_id' => $data['driver_id'],
            ':vehicle_id' => $data['vehicle_id'],
            ':ride_name' => $data['ride_name'],
            ':departure_location' => $data['departure_location'],
            ':departure_time' => $data['departure_time'],
            ':arrival_location' => $data['arrival_location'],
            ':arrival_time' => $data['arrival_time'],
            ':weekdays' => $data['weekdays'],
            ':price_per_seat' => $data['price_per_seat'],
            ':total_seats' => $data['total_seats'],
            ':available_seats' => $data['available_seats']
        ]);

        return $this->conn->lastInsertId();
    }

    /** Update */
    public function update($id, $data) {
        $sql = "UPDATE rides SET
                ride_name = :ride_name,
                departure_location = :departure_location,
                departure_time = :departure_time,
                arrival_location = :arrival_location,
                arrival_time = :arrival_time,
                weekdays = :weekdays,
                price_per_seat = :price_per_seat,
                total_seats = :total_seats,
                available_seats = :available_seats
                WHERE id = :id";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            ':id' => $id,
            ':ride_name' => $data['ride_name'],
            ':departure_location' => $data['departure_location'],
            ':departure_time' => $data['departure_time'],
            ':arrival_location' => $data['arrival_location'],
            ':arrival_time' => $data['arrival_time'],
            ':weekdays' => $data['weekdays'],
            ':price_per_seat' => $data['price_per_seat'],
            ':total_seats' => $data['total_seats'],
            ':available_seats' => $data['available_seats']
        ]);
    }

    /** Eliminar */
    public function delete($id) {
        $sql = "DELETE FROM rides WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
}
