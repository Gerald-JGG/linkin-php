<?php
session_start();

// ---------------- VALIDAR LOGIN ----------------
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId   = $_SESSION['user_id'];
$roles    = $_SESSION['roles'] ?? [];
$roleIds  = array_column($roles, 'role_id');

$isAdmin  = in_array(1, $roleIds);
$isDriver = in_array(3, $roleIds);

if (!$isAdmin && !$isDriver) {
    header("Location: dashboard.php");
    exit;
}

require_once __DIR__ . '/../app/Config/database.php';
require_once __DIR__ . '/../app/Models/Vehicle.php';
require_once __DIR__ . '/../app/Models/Ride.php';

$db = (new Database())->getConnection();
$vehicleModel = new Vehicle($db);
$rideModel    = new Ride($db);

// ---------------- OBTENER ID DEL RIDE ----------------
$rideId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$ride = $rideModel->findById($rideId);

if (!$ride) {
    die("Ride no encontrado.");
}

// Solo admin o dueño del ride
if (!$isAdmin && $ride['driver_id'] != $userId) {
    die("No tienes permiso para editar este ride.");
}

// ---------------- VEHÍCULOS DISPONIBLES ----------------
$vehicles = $isAdmin 
    ? $vehicleModel->getAllApproved()
    : $vehicleModel->getApprovedByUser($userId);

// ---------------- PROCESAR FORMULARIO ----------------
$message = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $data = [
        "ride_name"          => $_POST["ride_name"] ?? "",
        "departure_location" => $_POST["departure_location"] ?? "",
        "departure_time"     => $_POST["departure_time"] ?? "",
        "arrival_location"   => $_POST["arrival_location"] ?? "",
        "arrival_time"       => $_POST["arrival_time"] ?? "",
        "weekdays"           => isset($_POST["weekdays"]) ? implode(",", $_POST["weekdays"]) : "",
        "price_per_seat"     => $_POST["price_per_seat"] ?? 0,
        "total_seats"        => $_POST["total_seats"] ?? 1,
        "vehicle_id"         => $_POST["vehicle_id"] ?? null,
    ];

    if ($rideModel->update($rideId, $data)) {
        $message = "Ride actualizado correctamente.";
        $ride = $rideModel->findById($rideId);  // refrescar
    } else {
        $error = "Error al actualizar el ride.";
    }
}

// ---------------- DATOS PARA NAVBAR ----------------
$firstName = $_SESSION['first_name'] ?? "Usuario";
$photoPath = $_SESSION['photo'] ?? null;
$initial   = strtoupper(substr($firstName, 0, 1));
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Editar Ride - Aventones</title>
<link rel="stylesheet" href="css/styles.css">

<style>
.form-container {
    max-width: 800px;
    margin: 24px auto;
    padding: 20px;
}
.form-row {
    display: flex; 
    gap: 16px;
    flex-wrap: wrap;
}
.form-row > div {
    flex: 1;
    min-width: 250px;
}
.form-group {
    margin-bottom: 12px;
}
.weekdays-box {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}
</style>

</head>
<body>

<!-- NAVBAR -->
<nav class="navbar-custom" style="padding: 12px 24px; display:flex; justify-content:space-between; align-items:center;">
    <div style="font-size:20px; font-weight:700; color:white;">Aventones</div>

    <div class="user-menu-container">
        <button class="user-avatar-button" id="userMenuButton">
            <?php if ($photoPath): ?>
                <img src="<?php echo $photoPath; ?>" class="user-avatar">
            <?php else: ?>
                <div class="user-avatar-placeholder"><?php echo $initial; ?></div>
            <?php endif; ?>
            <span class="user-name-label"><?php echo htmlspecialchars($firstName); ?></span>
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

<div class="form-container card-custom">

    <a href="rides.php" class="btn-back">← Volver a mis rides</a>

    <h2 style="margin-bottom:12px;">Editar Ride</h2>

    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo $message; ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST">

        <div class="form-group">
            <label class="form-label">Nombre del viaje</label>
            <input type="text" class="form-control" name="ride_name" value="<?php echo htmlspecialchars($ride['ride_name']); ?>" required>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Lugar de salida</label>
                <input type="text" class="form-control" name="departure_location" value="<?php echo htmlspecialchars($ride['departure_location']); ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label">Hora de salida</label>
                <input type="time" class="form-control" name="departure_time" value="<?php echo htmlspecialchars($ride['departure_time']); ?>" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Lugar de llegada</label>
                <input type="text" class="form-control" name="arrival_location" value="<?php echo htmlspecialchars($ride['arrival_location']); ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label">Hora de llegada</label>
                <input type="time" class="form-control" name="arrival_time" value="<?php echo htmlspecialchars($ride['arrival_time']); ?>" required>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Días de la semana</label>
            <div class="weekdays-box">
                <?php
                $days = ["monday"=>"Lun", "tuesday"=>"Mar", "wednesday"=>"Mié", "thursday"=>"Jue", "friday"=>"Vie", "saturday"=>"Sáb", "sunday"=>"Dom"];
                $selected = explode(",", $ride["weekdays"]);
                foreach ($days as $value => $label):
                ?>
                    <label>
                        <input type="checkbox" name="weekdays[]" value="<?php echo $value; ?>"
                            <?php echo in_array($value, $selected) ? "checked" : ""; ?>>
                        <?php echo $label; ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Precio por asiento</label>
                <input type="number" class="form-control" name="price_per_seat" value="<?php echo htmlspecialchars($ride['price_per_seat']); ?>" min="0" required>
            </div>

            <div class="form-group">
                <label class="form-label">Cantidad de espacios</label>
                <input type="number" class="form-control" name="total_seats" value="<?php echo htmlspecialchars($ride['total_seats']); ?>" min="1" required>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Vehículo</label>
            <select name="vehicle_id" class="form-control" required>
                <?php foreach ($vehicles as $v): ?>
                    <option value="<?php echo $v['id']; ?>"
                        <?php echo ($v['id'] == $ride['vehicle_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($v['brand'].' '.$v['model'].' ('.$v['plate'].')'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <button class="btn-primary-custom">Guardar cambios</button>

    </form>
</div>

<script src="js/theme.js"></script>
<script src="js/user-menu.js"></script>
</body>
</html>
