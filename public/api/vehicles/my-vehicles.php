<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

// Verificar sesión
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

try {
    require_once __DIR__ . '/../../../app/Controllers/VehicleController.php';
    
    $vehicleController = new VehicleController();
    $result = $vehicleController->getMyVehicles();
    
    echo json_encode($result);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error del servidor: ' . $e->getMessage()]);
}
?>