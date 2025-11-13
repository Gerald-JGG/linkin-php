<?php
session_start();

// Seguridad: solo admin
if (!isset($_SESSION['user_id']) || empty($_SESSION['roles'])) {
    header('Location: login.php');
    exit;
}

$roles = $_SESSION['roles'];
$roleIds = array_column($roles, 'role_id');
$isAdmin = in_array(1, $roleIds);

if (!$isAdmin) {
    header('Location: dashboard.php');
    exit;
}

require_once __DIR__ . '/../app/Config/database.php';
require_once __DIR__ . '/../app/Models/Vehicle.php';

$database = new Database();
$db       = $database->getConnection();
$vehicleModel = new Vehicle($db);

// Datos
$pendingVehicles = $vehicleModel->getPending();
$approvedVehicles = $vehicleModel->getAllApproved();

// Navbar vars
$firstName = $_SESSION['first_name'] ?? 'Admin';
$photoPath = $_SESSION['photo'] ?? null;
$initial   = strtoupper(substr($firstName, 0, 1));
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehículos - Admin</title>
    <link rel="stylesheet" href="css/styles.css">

    <style>
        .section-title { font-size:20px; font-weight:700; margin:14px 0; }
        .vehicle-list { display:grid; gap:18px; }
        .vehicle-card {
            padding:18px;
            border-radius:14px;
            background:white;
            box-shadow:0 3px 10px rgba(0,0,0,0.08);
        }
        body.dark-mode .vehicle-card {
            background:#0f172a;
            color:#e2e8f0;
            box-shadow:0 3px 12px rgba(255,255,255,0.05);
        }

        .vehicle-photo {
            width:100%;
            max-width:320px;
            border-radius:8px;
            margin-bottom:12px;
        }

        .actions { margin-top:10px; display:flex; gap:10px; flex-wrap:wrap; }
        .actions a, .actions button {
            padding:8px 14px;
            border-radius:8px;
            font-size:14px;
            text-decoration:none;
            cursor:pointer;
            font-weight:600;
        }

        .btn-approve { background:#10b981; color:white; }
        .btn-reject { background:#ef4444; color:white; }
        .btn-edit { background:#2563eb; color:white; }
        .btn-delete { background:#1e293b; color:white; }

        body.dark-mode .btn-delete { background:#334155; }
    </style>
</head>

<body>

<!-- NAVBAR -->
<nav class="navbar-custom"
    style="padding: 12px 24px; display: flex; justify-content: space-between; align-items: center;">
    <div style="font-size: 20px; font-weight: bold; color: white;">
        Aventones - Admin
    </div>

    <div class="user-menu-container">
        <button type="button" class="user-avatar-button" id="userMenuButton">
            <?php if ($photoPath): ?>
                <img src="<?php echo htmlspecialchars($photoPath); ?>" alt="Foto perfil" class="user-avatar">
            <?php else: ?>
                <div class="user-avatar-placeholder"><?php echo $initial; ?></div>
            <?php endif; ?>
            <span class="user-name-label"><?php echo htmlspecialchars($firstName); ?></span>
            <span class="user-chevron">▾</span>
        </button>

        <div class="user-menu" id="userMenu">
            <a href="profile.php">Mi perfil</a>
            <a href="settings.php">Configuración</a>
            <hr>
            <a href="api/logout.php">Salir</a>
        </div>
    </div>
</nav>

<div class="container-main">
    <aside class="sidebar">
        <nav>
            <a class="nav-link" href="dashboard.php">🏠 Inicio</a>
            <hr style="margin: 12px 0;">
            <h6 class="text-muted" style="padding-left: 12px;">ADMIN</h6>
            <a class="nav-link active" href="admin-vehicles.php">🚗 Vehículos</a>
            <a class="nav-link" href="admin-rides.php">🛣️ Rides</a>
            <a class="nav-link" href="admin-users.php">👥 Usuarios</a>
        </nav>
    </aside>

    <main class="content-area">

        <!-- PENDIENTES -->
        <h2 class="section-title">Vehículos pendientes de aprobación</h2>
        <?php if (empty($pendingVehicles)): ?>
            <p>No hay vehículos pendientes.</p>
        <?php else: ?>
            <div class="vehicle-list">
                <?php foreach ($pendingVehicles as $v): ?>
                    <div class="vehicle-card">
                        <?php if ($v['photo']): ?>
                            <img src="<?php echo htmlspecialchars($v['photo']); ?>" class="vehicle-photo">
                        <?php endif; ?>

                        <h3><?php echo htmlspecialchars($v['brand'] . ' ' . $v['model']); ?></h3>
                        <p>
                            Dueño: <strong><?php echo htmlspecialchars($v['first_name'] . ' ' . $v['last_name']); ?></strong><br>
                            Año: <?php echo htmlspecialchars($v['year']); ?><br>
                            Color: <?php echo htmlspecialchars($v['color']); ?><br>
                            Placa: <?php echo htmlspecialchars($v['plate']); ?><br>
                            Email: <?php echo htmlspecialchars($v['email'] ?? 'No disponible'); ?>

                        </p>

                        <div class="actions">
                            <a href="approve-vehicle.php?id=<?php echo $v['id']; ?>" class="btn-approve">Aprobar</a>
                            <a href="reject-vehicle.php?id=<?php echo $v['id']; ?>" class="btn-reject">Rechazar</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <hr style="margin: 30px 0;">

        <!-- APROBADOS -->
        <h2 class="section-title">Vehículos aprobados</h2>

        <?php if (empty($approvedVehicles)): ?>
            <p>No hay vehículos aprobados.</p>
        <?php else: ?>
            <div class="vehicle-list">
                <?php foreach ($approvedVehicles as $v): ?>
                    <div class="vehicle-card">
                        <?php if ($v['photo']): ?>
                            <img src="<?php echo htmlspecialchars($v['photo']); ?>" class="vehicle-photo">
                        <?php endif; ?>

                        <h3><?php echo htmlspecialchars($v['brand'] . ' ' . $v['model']); ?></h3>
                        <p>
                            Año: <?php echo htmlspecialchars($v['year']); ?><br>
                            Color: <?php echo htmlspecialchars($v['color']); ?><br>
                            Placa: <?php echo htmlspecialchars($v['plate']); ?>
                        </p>

                        <div class="actions">
                            <a href="edit-vehicle.php?id=<?php echo $v['id']; ?>" class="btn-edit">Editar</a>
                            <a href="delete-vehicle.php?id=<?php echo $v['id']; ?>" class="btn-delete">Eliminar</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </main>
</div>

<script src="js/theme.js"></script>
<script src="js/user-menu.js"></script>

</body>
</html>
