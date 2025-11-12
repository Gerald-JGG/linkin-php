<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

require_once __DIR__ . '/../../../app/Config/database.php';
require_once __DIR__ . '/../../../app/Models/Booking.php';
require_once __DIR__ . '/../../../app/Models/Ride.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $database = new Database();
    $db = $database->getConnection();
    $bookingModel = new Booking($db);
    $rideModel = new Ride($db);
    
    $bookingId = $_POST['booking_id'] ?? '';
    
    // Verificar que la reserva pertenece al usuario
    $booking = $bookingModel->findById($bookingId);
    
    if ($booking['passenger_id'] != $_SESSION['user_id']) {
        echo json_encode(['success' => false, 'message' => 'No autorizado']);
        exit;
    }
    
    // Si la reserva estaba aceptada, liberar los asientos
    if ($booking['status'] === 'accepted') {
        $ride = $rideModel->findById($booking['ride_id']);
        $newAvailableSeats = $ride['available_seats'] + $booking['seats_requested'];
        $rideModel->updateAvailableSeats($booking['ride_id'], $newAvailableSeats);
    }
    
    if ($bookingModel->cancel($bookingId)) {
        echo json_encode(['success' => true, 'message' => 'Reserva cancelada']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al cancelar reserva']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
?>