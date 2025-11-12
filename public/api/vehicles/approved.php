<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

require_once __DIR__ . '/../../../app/Config/database.php';
require_once __DIR__ . '/../../../app/Models/Vehicle.php';

$database = new Database();
$db = $database->getConnection();
$vehicleModel = new Vehicle($db);

$vehicles = $vehicleModel->getApprovedByUserId($_SESSION['user_id']);

echo json_encode(['success' => true, 'vehicles' => $vehicles]);
?>