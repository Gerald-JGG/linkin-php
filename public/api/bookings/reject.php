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
    
    if ($bookingModel->reject($bookingId)) {
        echo json_encode(['success' => true, 'message' => 'Reserva rechazada']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al rechazar reserva']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
?>