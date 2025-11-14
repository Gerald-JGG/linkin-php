<?php

class Booking
{
    private $conn;
    private $table = 'bookings';

    public function __construct($db)
    {
        $this->conn = $db;
    }

    /**
     * Crea una reserva (1 espacio) para un usuario en un ride.
     * Maneja:
     * - No duplicar reservas activas del mismo usuario en el mismo ride.
     * - Disminuir available_seats en rides.
     */
    public function create(int $rideId, int $userId): array
    {
        try {
            $this->conn->beginTransaction();

            // 1) Bloquear el ride y revisar espacios
            $stmt = $this->conn->prepare(
                "SELECT id, available_seats 
                 FROM rides
                 WHERE id = :id
                 FOR UPDATE"
            );
            $stmt->bindValue(':id', $rideId, PDO::PARAM_INT);
            $stmt->execute();
            $ride = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$ride) {
                $this->conn->rollBack();
                return ['success' => false, 'message' => 'Viaje no encontrado.'];
            }

            if ((int)$ride['available_seats'] <= 0) {
                $this->conn->rollBack();
                return ['success' => false, 'message' => 'No hay espacios disponibles.'];
            }

            // 2) Verificar si ya tiene reserva activa en este ride
            $stmt = $this->conn->prepare(
                "SELECT id FROM {$this->table}
                 WHERE ride_id = :ride_id
                   AND passenger_id = :user_id
                   AND status IN ('pending','accepted')"
            );
            $stmt->bindValue(':ride_id', $rideId, PDO::PARAM_INT);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->fetch()) {
                $this->conn->rollBack();
                return ['success' => false, 'message' => 'Ya tienes una reservación para este viaje.'];
            }

            // 3) Crear booking
            $stmt = $this->conn->prepare(
                "INSERT INTO {$this->table}
                    (ride_id, passenger_id, status, created_at)
                 VALUES
                    (:ride_id, :user_id, 'pending', NOW())"
            );
            $stmt->bindValue(':ride_id', $rideId, PDO::PARAM_INT);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();

            // 4) Descontar asiento
            $stmt = $this->conn->prepare(
                "UPDATE rides
                 SET available_seats = available_seats - 1
                 WHERE id = :id"
            );
            $stmt->bindValue(':id', $rideId, PDO::PARAM_INT);
            $stmt->execute();

            $this->conn->commit();
            return ['success' => true, 'message' => 'Reservación creada correctamente.'];

        } catch (Throwable $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            error_log('BOOKING CREATE ERROR: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error al crear la reservación.'];
        }
    }

    /**
     * Listar reservas de un pasajero con info del ride y chofer.
     */
    public function getByPassenger(int $userId): array
    {
        $sql = "SELECT 
                    b.id,
                    b.status,
                    b.created_at,
                    r.ride_name,
                    r.departure_location,
                    r.departure_time,
                    r.arrival_location,
                    r.arrival_time,
                    r.price_per_seat,
                    u.first_name AS driver_first_name,
                    u.last_name  AS driver_last_name
                FROM {$this->table} b
                JOIN rides r     ON b.ride_id = r.id
                JOIN users u     ON r.driver_id = u.id
                WHERE b.passenger_id = :uid
                ORDER BY b.created_at DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Cancelar reserva (solo el dueño) y liberar asiento.
     */
    public function cancel(int $bookingId, int $userId): array
    {
        try {
            $this->conn->beginTransaction();

            // 1) Cargar booking
            $stmt = $this->conn->prepare(
                "SELECT id, ride_id, passenger_id, status
                 FROM {$this->table}
                 WHERE id = :id
                 FOR UPDATE"
            );
            $stmt->bindValue(':id', $bookingId, PDO::PARAM_INT);
            $stmt->execute();
            $booking = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$booking) {
                $this->conn->rollBack();
                return ['success' => false, 'message' => 'Reservación no encontrada.'];
            }

            if ((int)$booking['passenger_id'] !== $userId) {
                $this->conn->rollBack();
                return ['success' => false, 'message' => 'No puedes cancelar esta reservación.'];
            }

            if (in_array($booking['status'], ['cancelled', 'rejected'], true)) {
                $this->conn->rollBack();
                return ['success' => false, 'message' => 'Esta reservación ya está cancelada.'];
            }

            // 2) Marcar como cancelada
            $stmt = $this->conn->prepare(
                "UPDATE {$this->table}
                 SET status = 'cancelled'
                 WHERE id = :id"
            );
            $stmt->bindValue(':id', $bookingId, PDO::PARAM_INT);
            $stmt->execute();

            // 3) Devolver asiento
            $stmt = $this->conn->prepare(
                "UPDATE rides
                 SET available_seats = available_seats + 1
                 WHERE id = :ride_id"
            );
            $stmt->bindValue(':ride_id', $booking['ride_id'], PDO::PARAM_INT);
            $stmt->execute();

            $this->conn->commit();
            return ['success' => true, 'message' => 'Reservación cancelada.'];

        } catch (Throwable $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            error_log('BOOKING CANCEL ERROR: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error al cancelar la reservación.'];
        }
    }
}
