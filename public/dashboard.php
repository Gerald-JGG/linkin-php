<?php
session_start();

// Si no está logueado → fuera
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Roles desde sesión
$roles = $_SESSION['roles'] ?? [];
$roleIds = array_column($roles, 'role_id');

$isAdmin = in_array(1, $roleIds);
$isPassenger = in_array(2, $roleIds);
$isDriver = in_array(3, $roleIds);

// Variables para la foto de perfil
$firstName = $_SESSION['first_name'] ?? $_SESSION['username'] ?? 'Usuario';
$photoPath = $_SESSION['photo'] ?? null;
$initial = strtoupper(substr($firstName, 0, 1));
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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

        .nav-icon {
            width: 18px;
            height: 18px;
            margin-right: 10px;
            opacity: .85;
        }
    </style>
</head>

<body>

    <!-- NAVBAR SUPERIOR -->
    <nav class="navbar-custom"
        style="padding: 12px 24px; display: flex; justify-content: space-between; align-items: center;">
        <div style="font-size: 20px; font-weight: bold; color: white;">
            Aventones
        </div>

        <div class="user-menu-container">
            <button type="button" class="user-avatar-button" id="userMenuButton">
                <?php if ($photoPath): ?>
                    <img src="<?php echo htmlspecialchars($photoPath); ?>" alt="Foto de perfil" class="user-avatar">
                <?php else: ?>
                    <div class="user-avatar-placeholder">
                        <?php echo htmlspecialchars($initial); ?>
                    </div>
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
                <a class="nav-link active" href="dashboard.php">🏠 Inicio</a>

                <?php if ($isPassenger): ?>
                    <a class="nav-link" href="rides-available.php">🔍 Buscar viajes</a>
                    <a class="nav-link" href="my-bookings.php">📘 Mis reservaciones</a>
                <?php endif; ?>

                <?php if ($isDriver): ?>
                    <a class="nav-link" href="vehicles.php">🚗 Mis vehículos</a>
                    <a class="nav-link" href="rides.php">🛣️ Mis viajes</a>
                    <a class="nav-link" href="bookings-driver.php">📥 Solicitudes</a>
                <?php endif; ?>

                <?php if ($isAdmin): ?>
                    <hr style="margin:12px 0;">
                    <h6 class="text-muted" style="padding-left: 12px;">ADMIN</h6>

                    <!-- Nuevo apartado de Rides (admin) -->
                    <a class="nav-link" href="admin-rides.php">🛣️ Rides</a>

                    <!-- Seguimos teniendo la aprobación de vehículos -->
                    <a class="nav-link" href="vehicles.php">🚗 Mis vehículos</a>

                    <a class="nav-link" href="admin-users.php">👥 Usuarios</a>
                <?php endif; ?>

            </nav>
        </aside>

        <!-- ÁREA PRINCIPAL -->
        <main class="content-area">

            <!-- Bienvenida -->
            <section class="card-custom" style="padding:24px; margin-bottom:24px;">
                <h2 style="margin-bottom:8px;">Bienvenido, <?php echo htmlspecialchars($firstName); ?> 👋</h2>
                <p style="color:gray;">
                    Este es tu panel de Aventones. Aquí puedes gestionar todo según tu rol.
                </p>

                <div style="margin-top:12px;">
                    <?php if ($isAdmin): ?>
                        <span class="badge badge-approved" style="padding:6px 12px; border-radius:6px;">Administrador</span>
                    <?php endif; ?>

                    <?php if ($isDriver): ?>
                        <span class="badge badge-pending" style="padding:6px 12px; border-radius:6px;">Chofer</span>
                    <?php endif; ?>

                    <?php if ($isPassenger): ?>
                        <span class="badge badge-approved" style="padding:6px 12px; border-radius:6px;">Pasajero</span>
                    <?php endif; ?>
                </div>
            </section>

            <!-- Tarjetas estadísticas -->
            <section
                style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:20px; margin-bottom:24px;">

                <div class="stat-card blue">
                    <h6 class="text-uppercase mb-1">Viajes activos</h6>
                    <h2 id="stat-rides">0</h2>
                </div>

                <div class="stat-card green">
                    <h6 class="text-uppercase mb-1">Reservas</h6>
                    <h2 id="stat-bookings">0</h2>
                </div>

                <?php if ($isDriver): ?>
                    <div class="stat-card orange">
                        <h6 class="text-uppercase mb-1">Vehículos</h6>
                        <h2 id="stat-vehicles">0</h2>
                    </div>
                <?php endif; ?>

                <?php if ($isAdmin): ?>
                    <div class="stat-card purple">
                        <h6 class="text-uppercase mb-1">Vehículos pendientes</h6>
                        <h2 id="stat-pending">0</h2>
                    </div>
                <?php endif; ?>
            </section>

            <!-- Tarjetas de acciones por rol -->
            <section style="display:grid; grid-template-columns:repeat(auto-fit, minmax(320px, 1fr)); gap:24px;">

                <?php if ($isPassenger): ?>
                    <div class="card-custom" style="padding:20px;">
                        <h3>Como Pasajero</h3>
                        <p>Busca viajes, reserva espacios y revisa el estado de tus solicitudes.</p>
                        <a href="rides-available.php" class="btn-primary-custom" style="text-decoration:none;">Buscar
                            viajes</a>
                    </div>
                <?php endif; ?>

                <?php if ($isDriver): ?>
                    <div class="card-custom" style="padding:20px;">
                        <h3>Como Chofer</h3>
                        <p>Gestiona tus vehículos y publica nuevas rutas.</p>
                        <a href="vehicles.php" class="btn-primary-custom" style="text-decoration:none;">Mis vehículos</a>
                    </div>
                <?php endif; ?>

                <?php if ($isAdmin): ?>
                    <div class="card-custom" style="padding:20px;">
                        <h3>Panel Administrativo</h3>
                        <p>Aprueba vehículos, revisa usuarios y controla la plataforma.</p>
                        <a href="admin-vehicles.php" class="btn-primary-custom" style="text-decoration:none;">Revisar
                            vehículos</a>
                    </div>
                <?php endif; ?>

            </section>

        </main>

    </div>

    <!-- JS -->
    <script src="js/theme.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const btn = document.getElementById('userMenuButton');
            const menu = document.getElementById('userMenu');

            if (!btn || !menu) return;

            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                menu.classList.toggle('show');
            });

            document.addEventListener('click', function () {
                menu.classList.remove('show');
            });
        });
    </script>
</body>

</html>