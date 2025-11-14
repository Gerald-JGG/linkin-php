<?php
session_start();

// Si no está logueado → fuera
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Roles desde sesión
$roles   = $_SESSION['roles'] ?? [];
$roleIds = array_column($roles, 'role_id');

$isAdmin     = in_array(1, $roleIds);
$isPassenger = in_array(2, $roleIds);
$isDriver    = in_array(3, $roleIds);

require_once __DIR__ . '/../app/Config/database.php';
require_once __DIR__ . '/../app/Models/Ride.php';

$db = (new Database())->getConnection();
$rideModel = new Ride($db);

// Rides según el rol
if ($isAdmin) {
    $rides = $rideModel->getAllRidesAdmin();
} elseif ($isDriver) {
    $rides = $rideModel->getByDriver($_SESSION['user_id']);
} else {
    $rides = []; // Pasajero no gestiona rides aquí
}

// Navbar
$firstName = $_SESSION['first_name'] ?? $_SESSION['username'] ?? "Usuario";
$photo = $_SESSION['photo'] ?? null;
$initial = strtoupper(substr($firstName, 0, 1));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis viajes - Aventones</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>

        .page-container {
            max-width: 1100px;
            margin: 24px auto;
            padding: 0 16px;
        }

        .ride-card {
            padding:18px;
            background:white;
            border-radius:12px;
            margin-bottom:14px;
            box-shadow:0 2px 8px rgba(0,0,0,0.08);
        }
        body.dark-mode .ride-card {
            background:#0f172a;
            color:#f1f5f9;
        }

        .btn-create {
            display:inline-block;
            background:#2563eb;
            color:white;
            padding:10px 16px;
            border-radius:8px;
            text-decoration:none;
            font-weight:600;
        }
        .btn-edit, .btn-delete {
            display:inline-block;
            padding:8px 14px;
            border-radius:8px;
            font-weight:600;
            text-decoration:none;
            font-size:14px;
        }
        .btn-edit { background:#2563eb; color:white; }
        .btn-delete { background:#dc2626; color:white; }

        .btn-back {
            display:inline-block;
            padding:8px 16px;
            margin-bottom:18px;
            background:#e5e7eb;
            border-radius:8px;
            text-decoration:none;
            font-weight:600;
        }
        body.dark-mode .btn-back {
            background:#1f2937;
            color:#e5e7eb;
        }
    </style>
</head>

<body>

<!-- NAVBAR -->
<nav class="navbar-custom" style="padding: 12px 24px; display:flex; justify-content:space-between;">
    <div style="color:white; font-size:20px; font-weight:bold;">
        Aventones
    </div>

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
            <a href="dashboard.php">Panel</a>
            <a href="profile.php">Mi perfil</a>
            <a href="settings.php">Configuración</a>
            <hr>
            <a href="api/logout.php">Salir</a>
        </div>
    </div>
</nav>

<div class="page-container">

    <a href="dashboard.php" class="btn-back">← Volver al panel</a>

    <div style="display:flex; justify-content:space-between; align-items:center;">
        <h2>Mis viajes</h2>

        <?php if ($isAdmin || $isDriver): ?>
            <a href="create-ride.php" class="btn-create">+ Crear viaje</a>
        <?php endif; ?>
    </div>

    <?php if (empty($rides)): ?>
        <p style="color:gray;">No tienes viajes registrados.</p>
    <?php else: ?>
        <?php foreach ($rides as $r): ?>
            <div class="ride-card">
                <h3><?php echo htmlspecialchars($r['ride_name']); ?></h3>

                <p>
                    <strong>Salida:</strong> <?= htmlspecialchars($r['departure_location']); ?>
                    (<?= $r['departure_time']; ?>)<br>

                    <strong>Llegada:</strong> <?= htmlspecialchars($r['arrival_location']); ?>
                    (<?= $r['arrival_time']; ?>)
                </p>

                <p style="font-size:14px; color:gray;">
                    <strong>Días:</strong> <?= htmlspecialchars($r['weekdays']); ?><br>
                    <strong>Asientos:</strong> <?= $r['available_seats']; ?>/<?= $r['total_seats']; ?>
                </p>

                <?php if ($isAdmin || ($isDriver && $r['driver_id'] == $_SESSION['user_id'])): ?>
                    <div style="margin-top:10px;">
                        <a class="btn-edit" href="edit-ride.php?id=<?= $r['id']; ?>">Editar</a>
                        <a class="btn-delete"
                           href="delete-ride.php?id=<?= $r['id']; ?>"
                           onclick="return confirm('¿Eliminar este viaje?');">
                           Eliminar
                        </a>
                    </div>
                <?php endif; ?>

            </div>
        <?php endforeach; ?>
    <?php endif; ?>

</div>

<script src="js/theme.js"></script>
<script src="js/user-menu.js"></script>

</body>
</html>
