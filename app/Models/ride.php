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

if (!$isAdmin && !$isDriver) {
    header("Location: dashboard.php");
    exit;
}

require_once __DIR__ . '/../app/Config/database.php';
require_once __DIR__ . '/../app/Models/Ride.php';

$db = (new Database())->getConnection();
$rideModel = new Ride($db);

// Admin = puede ver todos
$rides = $isAdmin 
    ? $rideModel->getAll()
    : $rideModel->getByDriver($userId);

// Navbar info
$firstName = $_SESSION['first_name'] ?? "Usuario";
$photoPath = $_SESSION['photo'] ?? null;
$initial   = strtoupper(substr($firstName, 0, 1));
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Mis Rides</title>
<link rel="stylesheet" href="css/styles.css">

<style>
.page-container {
    max-width: 1000px;
    margin: 24px auto;
    padding: 16px;
}

.table-custom {
    width:100%;
    border-collapse: collapse;
}
.table-custom th, .table-custom td {
    padding:12px;
    border-bottom:1px solid #e5e7eb;
}
body.dark-mode .table-custom td, 
body.dark-mode .table-custom th {
    border-bottom:1px solid #334155;
}

.btn-small {
    padding:6px 12px;
    border-radius:6px;
    font-size:13px;
    display:inline-block;
}
.btn-edit {
    background:#2563eb;
    color:white;
}
.btn-delete {
    background:#dc2626;
    color:white;
}
.btn-create {
    background:#059669;
    color:white;
    padding:10px 18px;
    border-radius:8px;
    font-weight:600;
    text-decoration:none;
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

<div class="page-container">

    <h2 style="margin-bottom:16px;">Mis Rides</h2>

    <a class="btn-create" href="create-ride.php">➕ Crear ride</a>

    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-success" style="margin-top:16px;">
            Ride eliminado correctamente.
        </div>
    <?php endif; ?>

    <table class="table-custom" style="margin-top:20px;">
        <thead>
            <tr>
                <th>Nombre</th>
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
                <td><?php echo htmlspecialchars($r['ride_name']); ?></td>
                <td><?php echo htmlspecialchars($r['departure_location']); ?></td>
                <td><?php echo htmlspecialchars($r['arrival_location']); ?></td>
                <td><?php echo htmlspecialchars($r['weekdays']); ?></td>
                <td><?php echo htmlspecialchars($r['vehicle_info']); ?></td>
                <td><?php echo htmlspecialchars($r['price_per_seat']); ?></td>
                <td><?php echo htmlspecialchars($r['available_seats']."/".$r['total_seats']); ?></td>

                <td>
                    <a class="btn-small btn-edit" href="edit-ride.php?id=<?php echo $r['id']; ?>">Editar</a>
                    <a class="btn-small btn-delete" href="delete-ride.php?id=<?php echo $r['id']; ?>">Eliminar</a>
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
