<?php
session_start();

// Validar admin
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$roles = $_SESSION['roles'] ?? [];
$roleIds = array_column($roles, 'role_id');
$isAdmin = in_array(1, $roleIds);

if (!$isAdmin) {
    header('Location: dashboard.php');
    exit;
}

require_once __DIR__ . '/../app/Config/database.php';
require_once __DIR__ . '/../app/Models/Ride.php';

$db = (new Database())->getConnection();
$rideModel = new Ride($db);

// ESTE ES EL MÉTODO CORRECTO PARA ADMINS
$rides = $rideModel->getAllRidesAdmin();

// Navbar
$firstName = $_SESSION['first_name'] ?? $_SESSION['username'] ?? 'Admin';
$photoPath = $_SESSION['photo'] ?? null;
$initial = strtoupper(substr($firstName, 0, 1));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Rides - Admin</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .page-container { max-width:1100px; margin:24px auto; padding:0 16px; }
        .table-custom { width:100%; border-collapse:collapse; }
        .table-custom th, .table-custom td {
            padding:10px 12px; border-bottom:1px solid #e5e7eb; font-size:14px;
        }
        body.dark-mode .table-custom th,
        body.dark-mode .table-custom td {
            border-bottom:1px solid #334155;
        }
        .btn-back {
            display:inline-block; padding:6px 12px; border-radius:6px;
            background:#e5e7eb; color:#1e293b; text-decoration:none; font-size:14px;
        }
        body.dark-mode .btn-back { background:#1f2937; color:#e5e7eb; }
        .btn-small { padding:6px 10px; border-radius:6px; font-size:12px;
            text-decoration:none; display:inline-block; }
        .btn-edit { background:#2563eb; color:white; }
        .btn-delete { background:#dc2626; color:white; }
    </style>
</head>

<body>

<nav class="navbar-custom"
     style="padding:12px 24px; display:flex; justify-content:space-between; align-items:center;">
    <div style="color:white; font-size:20px; font-weight:700;">
        Aventones — Admin (Rides)
    </div>

    <div class="user-menu-container">
        <button class="user-avatar-button" id="userMenuButton">
            <?php if ($photoPath): ?>
                <img src="<?= htmlspecialchars($photoPath) ?>" class="user-avatar">
            <?php else: ?>
                <div class="user-avatar-placeholder"><?= htmlspecialchars($initial) ?></div>
            <?php endif; ?>
            <span class="user-name-label"><?= htmlspecialchars($firstName) ?></span>
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

    <h2 style="margin:16px 0;">Todos los rides registrados</h2>

    <table class="table-custom">
        <thead>
            <tr>
                <th>ID</th>
                <th>Chofer</th>
                <th>Viaje</th>
                <th>Salida</th>
                <th>Llegada</th>
                <th>Días</th>
                <th>Vehículo</th>
                <th>Precio</th>
                <th>Asientos</th>
                <th>Acciones</th>
            </tr>
        </thead>

        <tbody>
            <?php foreach ($rides as $r): ?>
                <tr>
                    <td>#<?= (int)$r['id'] ?></td>
                    <td><?= htmlspecialchars($r['driver_name']) ?></td>
                    <td><?= htmlspecialchars($r['ride_name']) ?></td>
                    <td><?= htmlspecialchars($r['departure_location']) ?></td>
                    <td><?= htmlspecialchars($r['arrival_location']) ?></td>
                    <td><?= htmlspecialchars($r['weekdays']) ?></td>
                    <td><?= htmlspecialchars($r['vehicle_info']) ?></td>
                    <td>₡<?= htmlspecialchars($r['price_per_seat']) ?></td>
                    <td><?= $r['available_seats'] ?>/<?= $r['total_seats'] ?></td>
                    <td>
                        <a class="btn-small btn-edit" href="edit-ride.php?id=<?= $r['id'] ?>">Editar</a>
                        <a class="btn-small btn-delete"
                           href="delete-ride.php?id=<?= $r['id'] ?>"
                           onclick="return confirm('¿Eliminar ride?')">Eliminar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script src="js/theme.js"></script>
<script src="js/user-menu.js"></script>

</body>
</html>
