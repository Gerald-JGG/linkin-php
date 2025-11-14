<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: rides-available.php");
    exit;
}

require_once __DIR__ . '/../app/Config/database.php';
require_once __DIR__ . '/../app/Models/Booking.php';

$rideId = isset($_POST['ride_id']) ? (int)$_POST['ride_id'] : 0;
$userId = (int)$_SESSION['user_id'];

if ($rideId <= 0) {
    header("Location: rides-available.php?err=" . urlencode('Viaje inválido.'));
    exit;
}

$db = (new Database())->getConnection();
$bookingModel = new Booking($db);

$result = $bookingModel->create($rideId, $userId);

if ($result['success']) {
    header("Location: rides-available.php?msg=" . urlencode($result['message']));
} else {
    header("Location: rides-available.php?err=" . urlencode($result['message']));
}
exit;
