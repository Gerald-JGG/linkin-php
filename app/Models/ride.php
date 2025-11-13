<?php

class Ride
{
    private $conn;
    private $table = 'rides';

    public function __construct($db)
    {
        $this->conn = $db;
    }

    /**
     * Obtener todos los rides (para admin),
     * con info de chofer y vehículo.
     */
    public function getAllWithDetails()
    {
        $sql = "
            SELECT 
                r.*,
                u.first_name AS driver_first_name,
                u.last_name  AS driver_last_name,
                u.username   AS driver_username,
                v.brand      AS vehicle_brand,
                v.model      AS vehicle_model,
                v.plate      AS vehicle_plate
            FROM {$this->table} r
            INNER JOIN users u    ON u.id = r.driver_id
            INNER JOIN vehicles v ON v.id = r.vehicle_id
            ORDER BY r.id DESC
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener rides por chofer (para rol Chofer).
     */
    public function getByDriver(int $driverId)
    {
        $sql = "
            SELECT r.*, v.brand, v.model, v.plate
            FROM {$this->table} r
            INNER JOIN vehicles v ON v.id = r.vehicle_id
            WHERE r.driver_id = :driver_id
            ORDER BY r.id DESC
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':driver_id', $driverId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * (Lo dejaremos preparado para el futuro)
     * Crear un ride nuevo.
     */
    public function create(array $data): bool
    {
        $sql = "
            INSERT INTO {$this->table} (
                driver_id,
                vehicle_id,
                ride_name,
                departure_location,
                departure_time,
                arrival_location,
                arrival_time,
                weekdays,
                price_per_seat,
                total_seats,
                available_seats
            ) VALUES (
                :driver_id,
                :vehicle_id,
                :ride_name,
                :departure_location,
                :departure_time,
                :arrival_location,
                :arrival_time,
                :weekdays,
                :price_per_seat,
                :total_seats,
                :available_seats
            )
        ";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(':driver_id',          $data['driver_id'],          PDO::PARAM_INT);
        $stmt->bindValue(':vehicle_id',         $data['vehicle_id'],         PDO::PARAM_INT);
        $stmt->bindValue(':ride_name',          $data['ride_name']);
        $stmt->bindValue(':departure_location', $data['departure_location']);
        $stmt->bindValue(':departure_time',     $data['departure_time']); // formato HH:MM:SS
        $stmt->bindValue(':arrival_location',   $data['arrival_location']);
        $stmt->bindValue(':arrival_time',       $data['arrival_time']);   // formato HH:MM:SS
        $stmt->bindValue(':weekdays',           $data['weekdays']);       // ej: monday,tuesday
        $stmt->bindValue(':price_per_seat',     $data['price_per_seat']);
        $stmt->bindValue(':total_seats',        $data['total_seats'],        PDO::PARAM_INT);
        $stmt->bindValue(':available_seats',    $data['available_seats'],    PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Actualizar un ride existente.
     */
    public function update(int $id, array $data): bool
    {
        $sql = "
            UPDATE {$this->table}
            SET
                vehicle_id         = :vehicle_id,
                ride_name          = :ride_name,
                departure_location = :departure_location,
                departure_time     = :departure_time,
                arrival_location   = :arrival_location,
                arrival_time       = :arrival_time,
                weekdays           = :weekdays,
                price_per_seat     = :price_per_seat,
                total_seats        = :total_seats,
                available_seats    = :available_seats
            WHERE id = :id
        ";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(':vehicle_id',         $data['vehicle_id'],         PDO::PARAM_INT);
        $stmt->bindValue(':ride_name',          $data['ride_name']);
        $stmt->bindValue(':departure_location', $data['departure_location']);
        $stmt->bindValue(':departure_time',     $data['departure_time']);
        $stmt->bindValue(':arrival_location',   $data['arrival_location']);
        $stmt->bindValue(':arrival_time',       $data['arrival_time']);
        $stmt->bindValue(':weekdays',           $data['weekdays']);
        $stmt->bindValue(':price_per_seat',     $data['price_per_seat']);
        $stmt->bindValue(':total_seats',        $data['total_seats'],     PDO::PARAM_INT);
        $stmt->bindValue(':available_seats',    $data['available_seats'], PDO::PARAM_INT);
        $stmt->bindValue(':id',                 $id,                       PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Eliminar un ride (lo usará admin y chofer).
     */
    public function delete(int $id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function findById(int $id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
