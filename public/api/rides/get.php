<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

require_once __DIR__ . '/../../../app/Config/database.php';
require_once __DIR__ . '/../../../app/Models/Ride.php';

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID de viaje no proporcionado']);
    exit;
}

$database = new Database();
$db = $database->getConnection();
$rideModel = new Ride($db);

$ride = $rideModel->findById($_GET['id']);

if ($ride) {
    // Verificar que el viaje pertenece al usuario
    if ($ride['driver_id'] != $_SESSION['user_id']) {
        echo json_encode(['success' => false, 'message' => 'No autorizado para ver este viaje']);
        exit;
    }
    
    echo json_encode(['success' => true, 'ride' => $ride]);
} else {
    echo json_encode(['success' => false, 'message' => 'Viaje no encontrado']);
}
?>