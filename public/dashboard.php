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

// Variables de usuario
$firstName = $_SESSION['first_name'] ?? $_SESSION['username'] ?? 'Usuario';
$photoPath = $_SESSION['photo'] ?? null;
$initial   = strtoupper(substr($firstName, 0, 1));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel - Aventones</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .container-main {
            display: flex;
            min-height: calc(100vh - 56px);
        }
        .content-area {
            flex: 1;
            padding: 24px;
        }
    </style>
</head>

<body>

<!-- NAVBAR -->
<nav class="navbar-custom"
     style="padding: 12px 24px; display:flex; justify-content:space-between; align-items:center;">
    <div style="font-size:20px; font-weight:bold; color:white;">Aventones</div>

    <div class="user-menu-container">
        <button class="user-avatar-button" id="userMenuButton">
            <?php if ($photoPath): ?>
                <img src="<?php echo htmlspecialchars($photoPath); ?>" class="user-avatar">
            <?php else: ?>
                <div class="user-avatar-placeholder"><?php echo htmlspecialchars($initial); ?></div>
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

<!-- CONTENEDOR GENERAL -->
<div class="container-main">

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <nav>

            <!-- SIEMPRE -->
            <a class="nav-link active" href="dashboard.php">🏠 Inicio</a>

            <!-- TODOS LOS ROLES PUEDEN VER RIDES Y RESERVAS -->
            <a class="nav-link" href="rides-available.php">🔍 Buscar viajes</a>
            <a class="nav-link" href="my-bookings.php">📘 Mis reservaciones</a>

            <!-- CHOFER -->
            <?php if ($isDriver): ?>
                <a class="nav-link" href="vehicles.php">🚗 Mis vehículos</a>
                <a class="nav-link" href="rides.php">🛣️ Mis viajes</a>
                <a class="nav-link" href="bookings-driver.php">📥 Solicitudes</a>
            <?php endif; ?>

            <!-- ADMIN -->
            <?php if ($isAdmin): ?>
                <hr style="margin:12px 0;">
                <h6 class="text-muted" style="padding-left:12px;">ADMIN</h6>

                <a class="nav-link" href="admin-rides.php">🛣️ Rides</a>
                <a class="nav-link" href="admin-vehicles.php">🚗 Vehículos</a>
                <a class="nav-link" href="admin-users.php">👥 Usuarios</a>
            <?php endif; ?>

        </nav>
    </aside>

    <!-- ÁREA PRINCIPAL -->
    <main class="content-area">

        <!-- Bienvenida -->
        <section class="card-custom" style="padding:24px; margin-bottom:24px;">
            <h2 style="margin-bottom:8px;">Bienvenido, <?php echo htmlspecialchars($firstName); ?> 👋</h2>
            <p style="color:gray;">Este es tu panel de Aventones. Aquí puedes gestionar todo según tu rol.</p>

            <div style="margin-top:12px;">
                <?php if ($isAdmin): ?>
                    <span class="badge badge-approved" style="padding:6px 12px;">Administrador</span>
                <?php endif; ?>
                <?php if ($isDriver): ?>
                    <span class="badge badge-pending" style="padding:6px 12px;">Chofer</span>
                <?php endif; ?>
                <?php if ($isPassenger): ?>
                    <span class="badge badge-approved" style="padding:6px 12px;">Pasajero</span>
                <?php endif; ?>
            </div>
        </section>

        <!-- Tarjetas -->
        <section style="display:grid; grid-template-columns:repeat(auto-fit,minmax(320px,1fr)); gap:24px;">

            <div class="card-custom" style="padding:20px;">
                <h3>Buscar viajes</h3>
                <p>Encuentra viajes con espacios disponibles y pide tu ride.</p>
                <a href="rides-available.php" class="btn-primary-custom" style="text-decoration:none;">
                    Ver viajes disponibles
                </a>
            </div>

            <?php if ($isDriver): ?>
                <div class="card-custom" style="padding:20px;">
                    <h3>Como Chofer</h3>
                    <p>Gestiona tus vehículos y crea tus viajes.</p>
                    <a href="vehicles.php" class="btn-primary-custom" style="text-decoration:none;">
                        Mis vehículos
                    </a>
                </div>
            <?php endif; ?>

            <?php if ($isAdmin): ?>
                <div class="card-custom" style="padding:20px;">
                    <h3>Panel Administrativo</h3>
                    <p>Control total del sistema: usuarios, vehículos y rides.</p>
                    <a href="admin-vehicles.php" class="btn-primary-custom" style="text-decoration:none;">
                        Revisar vehículos
                    </a>
                </div>
            <?php endif; ?>

        </section>

    </main>

</div>

<script src="js/theme.js"></script>
<script src="js/user-menu.js"></script>
</body>
</html>
