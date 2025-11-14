<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$roles = $_SESSION['roles'] ?? [];
$roleIds = array_column($roles, 'role_id');

$isAdmin     = in_array(1, $roleIds);
$isPassenger = in_array(2, $roleIds);
$isDriver    = in_array(3, $roleIds);

require_once __DIR__ . '/../app/Config/database.php';
require_once __DIR__ . '/../app/Models/Ride.php';

$db = (new Database())->getConnection();
$rideModel = new Ride($db);

// todos los roles pueden ver rides disponibles
$rides = $rideModel->getAvailableForSearch();

// navbar user info
$firstName = $_SESSION['first_name'] ?? "Usuario";
$photo = $_SESSION['photo'] ?? null;
$initial = strtoupper(substr($firstName, 0, 1));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Buscar viajes - Aventones</title>
    <link rel="stylesheet" href="css/styles.css">

    <style>
        .ride-card {
            padding: 18px;
            border-radius: 12px;
            background:white;
            box-shadow:0 2px 8px rgba(0,0,0,0.1);
            margin-bottom:15px;
        }
        body.dark-mode .ride-card {
            background:#0f172a;
            color:white;
        }
        .btn-book {
            display:inline-block;
            padding:8px 14px;
            border-radius:8px;
            background:#10b981;
            color:white;
            text-decoration:none;
            font-weight:600;
        }
        .btn-back {
            padding:8px 14px;
            display:inline-block;
            border-radius:8px;
            background:#e5e7eb;
            text-decoration:none;
            margin-bottom:16px;
        }
    </style>
</head>

<body>

<nav class="navbar-custom" style="padding:12px 24px; display:flex; justify-content:space-between;">
    <div style="font-size:20px; font-weight:bold; color:white;">Aventones</div>

    <div class="user-menu-container">
        <button class="user-avatar-button" id="userMenuButton">
            <?php if ($photo): ?>
                <img src="<?php echo htmlspecialchars($photo); ?>" class="user-avatar">
            <?php else: ?>
                <div class="user-avatar-placeholder"><?php echo $initial; ?></div>
            <?php endif; ?>
            <span class="user-name-label"><?php echo htmlspecialchars($firstName); ?></span>
            <span class="user-chevron">▾</span>
        </button>

        <div class="user-menu" id="userMenu">
            <a href="dashboard.php">Inicio</a>
            <a href="my-bookings.php">Mis reservaciones</a>
            <hr>
            <a href="api/logout.php">Salir</a>
        </div>
    </div>
</nav>

<div style="max-width:900px; margin:24px auto; padding:0 16px;">

    <a href="dashboard.php" class="btn-back">← Volver al panel</a>

    <h2>Viajes disponibles</h2>
    <p style="color:gray; margin-bottom:20px;">Todos los viajes con espacios libres.</p>

    <?php if (empty($rides)): ?>
        <p style="color:gray;">No hay viajes disponibles por ahora.</p>

    <?php else: ?>
        <?php foreach ($rides as $r): ?>
            <div class="ride-card">
                <h3><?php echo htmlspecialchars($r['ride_name']); ?></h3>

                <p>
                    <strong>Conductor:</strong>
                    <?php echo htmlspecialchars($r['driver_first_name']." ".$r['driver_last_name']); ?>
                </p>

                <p>
                    <strong>Vehículo:</strong>
                    <?php echo htmlspecialchars($r['brand']." ".$r['model']." (".$r['plate'].")"); ?>
                </p>

                <p>
                    <strong>Ruta:</strong>
                    <?= htmlspecialchars($r['departure_location']); ?> →
                    <?= htmlspecialchars($r['arrival_location']); ?>
                </p>

                <p>
                    <strong>Horario:</strong>
                    <?= $r['departure_time']; ?> → <?= $r['arrival_time']; ?>
                </p>

                <p>
                    <strong>Días:</strong> <?= htmlspecialchars($r['weekdays']); ?>
                </p>

                <p>
                    <strong>Asientos disponibles:</strong>
                    <?= $r['available_seats']; ?>
                </p>

                <p>
                    <strong>Precio:</strong> ₡<?= $r['price_per_seat']; ?>
                </p>

                <a href="request-booking.php?ride_id=<?= $r['id']; ?>" class="btn-book">
                    Reservar asiento
                </a>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

</div>

<script src="js/theme.js"></script>
<script src="js/user-menu.js"></script>

</body>
</html>
