<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$roles = $_SESSION['roles'] ?? [];
$roleIds = array_column($roles, 'role_id');

$isAdmin  = in_array(1, $roleIds);
$isDriver = in_array(3, $roleIds);

if (!$isAdmin && !$isDriver) {
    die("No tienes permisos.");
}

require_once __DIR__ . '/../app/Config/database.php';
require_once __DIR__ . '/../app/Models/Ride.php';

$db = (new Database())->getConnection();
$rideModel = new Ride($db);

$id = $_GET['id'] ?? 0;
$ride = $rideModel->find($id);

if (!$ride) {
    die("Ride no encontrado");
}

// Los choferes no pueden editar rides ajenos
if ($isDriver && $ride["driver_id"] != $_SESSION["user_id"]) {
    die("No puedes editar rides que no son tuyos.");
}

$message = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $weekdays = implode(",", $_POST['weekdays'] ?? []);

    $data = [
        "ride_name"         => $_POST["ride_name"],
        "departure_location"=> $_POST["departure_location"],
        "departure_time"    => $_POST["departure_time"],
        "arrival_location"  => $_POST["arrival_location"],
        "arrival_time"      => $_POST["arrival_time"],
        "weekdays"          => $weekdays,
        "price_per_seat"    => $_POST["price_per_seat"],
        "total_seats"       => $_POST["total_seats"],
        "available_seats"   => $_POST["available_seats"],
    ];

    $ok = $rideModel->update($id, $data);

    if ($ok) {
        header("Location: rides.php?updated=1");
        exit;
    } else {
        $error = "No se pudo actualizar.";
    }
}

// NAVBAR INFO
$firstName = $_SESSION['first_name'];
$photo     = $_SESSION['photo'] ?? null;
$initial   = strtoupper(substr($firstName, 0, 1));
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Editar Viaje</title>
<link rel="stylesheet" href="css/styles.css">
<style>
.form-card {padding:20px;background:white;border-radius:12px;max-width:900px;margin:auto;}
</style>
</head>
<body>

<nav class="navbar-custom" style="padding:14px 24px;display:flex;justify-content:space-between;">
    <div style="color:white;font-size:20px;font-weight:bold;">Editar Viaje</div>

    <div class="user-menu-container">
        <button id="userMenuButton" class="user-avatar-button">
            <?php if ($photo): ?>
                <img src="<?= $photo ?>" class="user-avatar">
            <?php else: ?>
                <div class="user-avatar-placeholder"><?= $initial ?></div>
            <?php endif; ?>
            <span class="user-name-label"><?= $firstName ?></span>
            <span class="user-chevron">▾</span>
        </button>

        <div class="user-menu" id="userMenu">
            <a href="dashboard.php">Panel</a>
            <a href="profile.php">Mi perfil</a>
            <a href="settings.php">Configuración</a>
            <hr>
            <a href="api/logout.php">Salir</a>
        </div>
    </div>
</nav>


<div class="form-card">

    <a href="rides.php" class="btn-back">← Volver</a>

    <h2>Editar viaje</h2>

    <?php if ($error): ?>
        <div class="alert-error"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">

        <label>Nombre del viaje</label>
        <input type="text" name="ride_name" value="<?= $ride["ride_name"] ?>" required>

        <label>Lugar de salida</label>
        <input type="text" name="departure_location" value="<?= $ride["departure_location"] ?>" required>

        <label>Hora de salida</label>
        <input type="time" name="departure_time" value="<?= $ride["departure_time"] ?>" required>

        <label>Lugar de llegada</label>
        <input type="text" name="arrival_location" value="<?= $ride["arrival_location"] ?>" required>

        <label>Hora de llegada</label>
        <input type="time" name="arrival_time" value="<?= $ride["arrival_time"] ?>" required>

        <label>Días de la semana</label>
        <?php
            $days = ["monday","tuesday","wednesday","thursday","friday","saturday","sunday"];
            $selected = explode(",", $ride["weekdays"]);
        ?>
        <div style="display:flex;flex-wrap:wrap;gap:10px;">
        <?php foreach ($days as $d): ?>
            <label>
                <input type="checkbox" name="weekdays[]" value="<?= $d ?>"
                    <?= in_array($d,$selected) ? "checked" : "" ?>> <?= ucfirst($d) ?>
            </label>
        <?php endforeach; ?>
        </div>

        <label>Precio por asiento</label>
        <input type="number" name="price_per_seat" value="<?= $ride["price_per_seat"] ?>" required>

        <label>Total de asientos</label>
        <input type="number" name="total_seats" value="<?= $ride["total_seats"] ?>" required>

        <label>Asientos disponibles</label>
        <input type="number" name="available_seats" value="<?= $ride["available_seats"] ?>" required>

        <br><br>
        <button class="btn-primary-custom" type="submit">Guardar cambios</button>

    </form>

</div>

<script src="js/user-menu.js"></script>
<script src="js/theme.js"></script>

</body>
</html>
