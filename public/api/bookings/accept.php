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
    
    $booking = $bookingModel->findById($bookingId);
    $ride = $rideModel->findById($booking['ride_id']);
    
    // Verificar que el viaje pertenece al chofer
    if ($ride['driver_id'] != $_SESSION['user_id']) {
        echo json_encode(['success' => false, 'message' => 'No autorizado']);
        exit;
    }
    
    // Verificar que hay asientos disponibles
    if ($ride['available_seats'] < $booking['seats_requested']) {
        echo json_encode(['success' => false, 'message' => 'No hay suficientes asientos disponibles']);
        exit;
    }
    
    // Actualizar estado de reserva
    if ($bookingModel->accept($bookingId)) {
        // Reducir asientos disponibles
        $newAvailableSeats = $ride['available_seats'] - $booking['seats_requested'];
        $rideModel->updateAvailableSeats($booking['ride_id'], $newAvailableSeats);
        
        echo json_encode(['success' => true, 'message' => 'Reserva aceptada']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al aceptar reserva']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
?>