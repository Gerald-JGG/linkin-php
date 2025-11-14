<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../app/Config/database.php';
require_once __DIR__ . '/../app/Models/Ride.php';

$roles = $_SESSION['roles'] ?? [];
$roleIds = array_column($roles, 'role_id');

$isAdmin = in_array(1, $roleIds);
$isDriver = in_array(3, $roleIds);

$db = (new Database())->getConnection();
$rideModel = new Ride($db);

$id = $_GET['id'] ?? 0;
$ride = $rideModel->find($id);

if (!$ride) die("Ride no encontrado");

// Los choferes solo pueden borrar los suyos
if ($isDriver && $ride["driver_id"] != $_SESSION["user_id"])
    die("No puedes borrar rides ajenos.");

$rideModel->delete($id);

header("Location: rides.php?deleted=1");
exit;
