<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../app/Config/database.php';
require_once __DIR__ . '/../app/Models/Vehicle.php';

$roles   = $_SESSION['roles'] ?? [];
$roleIds = array_column($roles, 'role_id');
$isAdmin = in_array(1, $roleIds);

if (!$isAdmin) {
    header("Location: dashboard.php");
    exit;
}

$vehicleId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($vehicleId <= 0) {
    die("Vehículo inválido.");
}

$reason = "Rechazado por el administrador"; // si quieres podemos hacer popup para escribir motivo

$database = new Database();
$db       = $database->getConnection();
$vehicleModel = new Vehicle($db);

$vehicleModel->reject($vehicleId, $_SESSION['user_id'], $reason);

header("Location: admin-vehicles.php?rejected=1");
exit;
