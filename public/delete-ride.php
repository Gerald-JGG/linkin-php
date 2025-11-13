<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId   = $_SESSION['user_id'];
$roles    = $_SESSION['roles'] ?? [];
$roleIds  = array_column($roles, 'role_id');
$isAdmin  = in_array(1, $roleIds);
$isDriver = in_array(3, $roleIds);

require_once __DIR__ . '/../app/Config/database.php';
require_once __DIR__ . '/../app/Models/Ride.php';

$db = (new Database())->getConnection();
$rideModel = new Ride($db);

$rideId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$ride   = $rideModel->findById($rideId);

if (!$ride) {
    die("Ride no encontrado.");
}

// Solo admin o dueño del ride puede borrar
if (!$isAdmin && $ride['driver_id'] != $userId) {
    die("No tienes permiso para eliminar este ride.");
}

// ELIMINAR
if (isset($_POST['confirm']) && $_POST['confirm'] === "yes") {
    $rideModel->delete($rideId);
    header("Location: rides.php?deleted=1");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Eliminar Ride</title>
<link rel="stylesheet" href="css/styles.css">

<style>
.modal-bg {
    position: fixed;
    top:0; left:0; right:0; bottom:0;
    background: rgba(0,0,0,0.55);
    display:flex;
    justify-content:center;
    align-items:center;
    padding:20px;
}
.modal-box {
    background:white;
    padding:30px;
    border-radius:18px;
    max-width:420px;
    text-align:center;
    box-shadow:0 8px 30px rgba(0,0,0,0.25);
}
body.dark-mode .modal-box {
    background:#0f172a;
    color:#e2e8f0;
}
.btn {
    padding:10px 22px;
    border-radius:8px;
    font-weight:600;
    text-decoration:none;
    display:inline-block;
    margin-top:12px;
}
.btn-danger {
    background:#dc2626;
    color:white;
}
.btn-secondary {
    background:#e5e7eb;
    color:#1e293b;
}
body.dark-mode .btn-secondary {
    background:#1f2937;
    color:white;
}
</style>

</head>

<body>

<div class="modal-bg">
    <div class="modal-box">
        <h2>¿Eliminar este Ride?</h2>
        <p style="margin-top:10px; font-size:15px;">
            Esta acción no se puede deshacer.<br>
            Ride: <strong><?php echo htmlspecialchars($ride['ride_name']); ?></strong>
        </p>

        <form method="POST" style="margin-top:20px;">
            <input type="hidden" name="confirm" value="yes">
            <button class="btn btn-danger" type="submit">Sí, eliminar</button>
            <a href="rides.php" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</div>

</body>
</html>
