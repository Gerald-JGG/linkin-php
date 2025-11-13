<?php
session_start();

// Debe estar logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId   = $_SESSION['user_id'];
$roles    = $_SESSION['roles'] ?? [];
$roleIds  = array_column($roles, 'role_id');

$isAdmin  = in_array(1, $roleIds);
$isDriver = in_array(3, $roleIds);

// Solo admin o chofer pueden crear rides
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

// Vehículos disponibles para asociar al ride
$vehicles = $isAdmin
    ? $vehicleModel->getAllApproved()
    : $vehicleModel->getApprovedByUser($userId);

$message = "";
$error   = "";

// Si no tiene vehículos aprobados y NO es admin → no puede crear rides
$hasVehicles = !empty($vehicles);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $hasVehicles) {

    $rideName          = $_POST['ride_name'] ?? '';
    $departureLocation = $_POST['departure_location'] ?? '';
    $departureTime     = $_POST['departure_time'] ?? '';
    $arrivalLocation   = $_POST['arrival_location'] ?? '';
    $arrivalTime       = $_POST['arrival_time'] ?? '';
    $weekdays          = isset($_POST['weekdays']) ? implode(',', $_POST['weekdays']) : '';
    $pricePerSeat      = (float)($_POST['price_per_seat'] ?? 0);
    $totalSeats        = (int)($_POST['total_seats'] ?? 1);
    $vehicleId         = (int)($_POST['vehicle_id'] ?? 0);

    if ($rideName && $departureLocation && $arrivalLocation && $weekdays && $vehicleId > 0) {

        $data = [
            'driver_id'         => $userId,
            'vehicle_id'        => $vehicleId,
            'ride_name'         => $rideName,
            'departure_location'=> $departureLocation,
            'departure_time'    => $departureTime,
            'arrival_location'  => $arrivalLocation,
            'arrival_time'      => $arrivalTime,
            'weekdays'          => $weekdays,
            'price_per_seat'    => $pricePerSeat,
            'total_seats'       => $totalSeats,
            // Podemos mandar available_seats explícitamente, pero igualmente
            // el modelo lo calcula si no viene:
            'available_seats'   => $totalSeats
        ];

        $newId = $rideModel->create($data);

        if ($newId) {
            header("Location: rides.php");
            exit;
        } else {
            $error = "No se pudo crear el ride. Intenta de nuevo.";
        }
    } else {
        $error = "Por favor completa todos los campos obligatorios.";
    }
}

// Navbar info
$firstName = $_SESSION['first_name'] ?? "Usuario";
$photoPath = $_SESSION['photo'] ?? null;
$initial   = strtoupper(substr($firstName, 0, 1));
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Crear Ride - Aventones</title>
<link rel="stylesheet" href="css/styles.css">
<style>
.form-container {
    max-width: 800px;
    margin: 24px auto;
    padding: 20px;
}
.form-row {
    display:flex;
    gap:16px;
    flex-wrap:wrap;
}
.form-row > div {
    flex:1;
    min-width:240px;
}
.form-group {
    margin-bottom:12px;
}
.weekdays-box {
    display:flex;
    gap:10px;
    flex-wrap:wrap;
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
                <img src="<?php echo htmlspecialchars($photoPath); ?>" class="user-avatar">
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

    <h2 style="margin-bottom:12px;">Crear nuevo ride</h2>

    <?php if (!$hasVehicles): ?>
        <div class="alert alert-error">
            No tienes vehículos aprobados. Registra un vehículo y espera la aprobación del administrador
            antes de poder crear rides.
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label class="form-label">Nombre del viaje</label>
            <input type="text" name="ride_name" class="form-control" required>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Lugar de salida</label>
                <input type="text" name="departure_location" class="form-control" required>
            </div>

            <div class="form-group">
                <label class="form-label">Hora de salida</label>
                <input type="time" name="departure_time" class="form-control" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Lugar de llegada</label>
                <input type="text" name="arrival_location" class="form-control" required>
            </div>

            <div class="form-group">
                <label class="form-label">Hora de llegada</label>
                <input type="time" name="arrival_time" class="form-control" required>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Días de la semana</label>
            <div class="weekdays-box">
                <label><input type="checkbox" name="weekdays[]" value="monday"> Lun</label>
                <label><input type="checkbox" name="weekdays[]" value="tuesday"> Mar</label>
                <label><input type="checkbox" name="weekdays[]" value="wednesday"> Mié</label>
                <label><input type="checkbox" name="weekdays[]" value="thursday"> Jue</label>
                <label><input type="checkbox" name="weekdays[]" value="friday"> Vie</label>
                <label><input type="checkbox" name="weekdays[]" value="saturday"> Sáb</label>
                <label><input type="checkbox" name="weekdays[]" value="sunday"> Dom</label>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Precio por asiento</label>
                <input type="number" name="price_per_seat" class="form-control" min="0" step="0.01" required>
            </div>

            <div class="form-group">
                <label class="form-label">Cantidad de asientos</label>
                <input type="number" name="total_seats" class="form-control" min="1" required>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Vehículo</label>
            <select name="vehicle_id" class="form-control" required <?php echo !$hasVehicles ? 'disabled' : ''; ?>>
                <?php foreach ($vehicles as $v): ?>
                    <option value="<?php echo (int)$v['id']; ?>">
                        <?php echo htmlspecialchars($v['brand'].' '.$v['model'].' ('.$v['plate'].')'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <button class="btn-primary-custom" type="submit" <?php echo !$hasVehicles ? 'disabled' : ''; ?>>
            Crear ride
        </button>
    </form>
</div>

<script src="js/theme.js"></script>
<script src="js/user-menu.js"></script>
</body>
</html>
