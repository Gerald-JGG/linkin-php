<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: my-bookings.php");
    exit;
}

require_once __DIR__ . '/../app/Config/database.php';
require_once __DIR__ . '/../app/Models/Booking.php';

$bookingId = isset($_POST['booking_id']) ? (int)$_POST['booking_id'] : 0;
$userId    = (int)$_SESSION['user_id'];

if ($bookingId <= 0) {
    header("Location: my-bookings.php?err=" . urlencode('Reservación inválida.'));
    exit;
}

$db = (new Database())->getConnection();
$bookingModel = new Booking($db);

$result = $bookingModel->cancel($bookingId, $userId);

if ($result['success']) {
    header("Location: my-bookings.php?msg=" . urlencode($result['message']));
} else {
    header("Location: my-bookings.php?err=" . urlencode($result['message']));
}
exit;
