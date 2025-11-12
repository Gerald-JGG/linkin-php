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
    
    $data = [
        'passenger_id' => $_SESSION['user_id'],
        'ride_id' => $_POST['ride_id'] ?? '',
        'seats_requested' => $_POST['seats_requested'] ?? 1,
        'booking_date' => $_POST['booking_date'] ?? ''
    ];
    
    // Verificar que haya asientos disponibles
    $ride = $rideModel->findById($data['ride_id']);
    
    if ($ride['available_seats'] < $data['seats_requested']) {
        echo json_encode(['success' => false, 'message' => 'No hay suficientes asientos disponibles']);
        exit;
    }
    
    $bookingId = $bookingModel->create($data);
    
    if ($bookingId) {
        echo json_encode(['success' => true, 'message' => 'Reserva creada. Pendiente de aprobación del chofer', 'booking_id' => $bookingId]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al crear reserva']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
?>