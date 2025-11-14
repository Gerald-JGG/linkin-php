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
        echo json_encode(['success' => false, 'message' => 'No autorizado para eliminar este viaje']);
        exit;
    }
    
    // Verificar si hay reservas activas
    // (Opcional: podrías agregar esta validación)
    
    if ($rideModel->delete($rideId)) {
        echo json_encode(['success' => true, 'message' => 'Viaje eliminado exitosamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar viaje']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
?>