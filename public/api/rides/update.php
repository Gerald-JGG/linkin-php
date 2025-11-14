<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

require_once __DIR__ . '/../../../app/Config/database.php';
require_once __DIR__ . '/../../../app/Models/Ride.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $database = new Database();
    $db = $database->getConnection();
    $rideModel = new Ride($db);
    
    $rideId = $_POST['ride_id'] ?? '';
    
    if (empty($rideId)) {
        echo json_encode(['success' => false, 'message' => 'ID de viaje no proporcionado']);
        exit;
    }
    
    // Verificar que el viaje existe y pertenece al usuario
    $existingRide = $rideModel->findById($rideId);
    
    if (!$existingRide) {
        echo json_encode(['success' => false, 'message' => 'Viaje no encontrado']);
        exit;
    }
    
    if ($existingRide['driver_id'] != $_SESSION['user_id']) {
        echo json_encode(['success' => false, 'message' => 'No autorizado para editar este viaje']);
        exit;
    }
    
    $data = [
        'ride_name' => $_POST['ride_name'] ?? '',
        'vehicle_id' => $_POST['vehicle_id'] ?? '',
        'departure_location' => $_POST['departure_location'] ?? '',
        'departure_time' => $_POST['departure_time'] ?? '',
        'arrival_location' => $_POST['arrival_location'] ?? '',
        'arrival_time' => $_POST['arrival_time'] ?? '',
        'weekdays' => $_POST['weekdays'] ?? '',
        'price_per_seat' => $_POST['price_per_seat'] ?? '',
        'total_seats' => $_POST['total_seats'] ?? ''
    ];
    
    if ($rideModel->update($rideId, $data)) {
        echo json_encode(['success' => true, 'message' => 'Viaje actualizado exitosamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar viaje']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
?>