<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

require_once __DIR__ . '/../../../app/Controllers/RideController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'vehicle_id' => $_POST['vehicle_id'] ?? '',
        'ride_name' => $_POST['ride_name'] ?? '',
        'departure_location' => $_POST['departure_location'] ?? '',
        'departure_time' => $_POST['departure_time'] ?? '',
        'arrival_location' => $_POST['arrival_location'] ?? '',
        'arrival_time' => $_POST['arrival_time'] ?? '',
        'weekdays' => $_POST['weekdays'] ?? '',
        'price_per_seat' => $_POST['price_per_seat'] ?? '',
        'total_seats' => $_POST['total_seats'] ?? ''
    ];
    
    $rideController = new RideController();
    $result = $rideController->create($data);
    
    echo json_encode($result);
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
?>