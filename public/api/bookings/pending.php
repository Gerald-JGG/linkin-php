<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

require_once __DIR__ . '/../../../app/Config/database.php';
require_once __DIR__ . '/../../../app/Models/Booking.php';

$database = new Database();
$db = $database->getConnection();
$bookingModel = new Booking($db);

$bookings = $bookingModel->getPendingByDriverId($_SESSION['user_id']);

echo json_encode(['success' => true, 'bookings' => $bookings]);
?>