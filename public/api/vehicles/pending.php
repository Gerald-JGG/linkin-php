<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

require_once __DIR__ . '/../../../app/Controllers/VehicleController.php';

$vehicleController = new VehicleController();
$result = $vehicleController->getPending();

echo json_encode($result);
?>