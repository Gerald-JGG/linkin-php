<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../../app/Config/database.php';
require_once __DIR__ . '/../../../app/Models/Ride.php';

$database = new Database();
$db = $database->getConnection();
$rideModel = new Ride($db);

$rides = $rideModel->getAvailableRides();

echo json_encode(['success' => true, 'rides' => $rides]);
?>