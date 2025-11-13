<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Verificar admin
$roles   = $_SESSION['roles'] ?? [];
$roleIds = array_column($roles, 'role_id');
$isAdmin = in_array(1, $roleIds);

if (!$isAdmin) {
    header('Location: dashboard.php');
    exit;
}

require_once __DIR__ . '/../app/Config/database.php';
require_once __DIR__ . '/../app/Models/Ride.php';

$database = new Database();
$db       = $database->getConnection();
$rideModel = new Ride($db);

// Listado general de rides
$rides = $rideModel->getAllWithDetails();

// Datos navbar
$firstNameAdmin = $_SESSION['first_name'] ?? $_SESSION['username'] ?? 'Admin';
$photoAdmin     = $_SESSION['photo'] ?? null;
$initialAdmin   = strtoupper(substr($firstNameAdmin, 0, 1));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rides - Admin - Aventones</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .admin-rides-container {
            max-width: 1100px;
            margin: 24px auto;
            padding: 0 16px;
        }
        .top-bar {
            display:flex;
            justify-content:space-between;
            align-items:center;
            margin-bottom:20px;
            flex-wrap: wrap;
            gap: 10px;
        }
        .rides-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 16px;
        }
        .ride-meta {
            font-size: 13px;
            color: gray;
            margin-bottom: 4px;
        }
        .badge-pill {
            display:inline-block;
            padding: 3px 8px;
            border-radius:999px;
            font-size:11px;
            background-color:#e5e7eb;
            margin-right:4px;
            margin-bottom:2px;
        }
        .btn-small {
            display:inline-block;
            padding: 6px 10px;
            border-radius: 6px;
            font-size: 13px;
            text-decoration:none;
            border: none;
            cursor:pointer;
        }
        .btn-edit {
            background-color: var(--primary-color);
            color:white;
        }
        .btn-delete {
            background-color: #ef4444;
            color:white;
        }
        .btn-create {
            padding: 8px 14px;
            border-radius: 8px;
            background-color: var(--success-color);
            color:white;
            font-size:14px;
            text-decoration:none;
            font-weight:600;
        }
        .btn-create:hover {
            filter:brightness(1.05);
        }
        .btn-back {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
            background-color: #e5e7eb;
            color: var(--dark-text);
            margin-bottom: 12px;
        }
        body.dark-mode .btn-back {
            background-color: #1f2937;
            color: #e5e7eb;
        }
        body.dark-mode .badge-pill {
            background-color:#1f2937;
            color:#cbd5e1;
        }
        .rides-empty {
            font-size: 14px;
            color: gray;
            margin-top: 12px;
        }
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
                <?php if ($photoAdmin): ?>
                    <img src="<?php echo htmlspecialchars($photoAdmin); ?>" 
                         alt="Foto de perfil" 
                         class="user-avatar">
                <?php else: ?>
                    <div class="user-avatar-placeholder">
                        <?php echo htmlspecialchars($initialAdmin); ?>
                    </div>
                <?php endif; ?>
                <span class="user-name-label"><?php echo htmlspecialchars($firstNameAdmin); ?></span>
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

    <div class="admin-rides-container">
        <a href="dashboard.php" class="btn-back">← Volver al panel</a>

        <div class="top-bar">
            <div>
                <h2 style="margin-bottom:4px;">Gestión de Rides</h2>
                <p style="font-size:14px; color:gray;">
                    Aquí puedes ver, crear, editar y eliminar todos los rides del sistema.
                </p>
            </div>
            <div>
                <!-- Más adelante apuntaremos esto a un formulario real -->
                <a href="ride-form.php" class="btn-create">+ Crear nuevo ride</a>
            </div>
        </div>

        <?php if (empty($rides)): ?>
            <p class="rides-empty">No hay rides registrados todavía.</p>
        <?php else: ?>
            <div class="rides-grid">
                <?php foreach ($rides as $ride): ?>
                    <div class="card-custom" style="padding:16px;">
                        <h3 style="margin-bottom:6px;">
                            <?php echo htmlspecialchars($ride['ride_name']); ?>
                        </h3>

                        <div class="ride-meta">
                            👤 Chofer: 
                            <?php echo htmlspecialchars($ride['driver_first_name'] . ' ' . $ride['driver_last_name']); ?>
                            (@<?php echo htmlspecialchars($ride['driver_username']); ?>)
                        </div>

                        <div class="ride-meta">
                            🚗 Vehículo:
                            <?php echo htmlspecialchars($ride['vehicle_brand'] . ' ' . $ride['vehicle_model']); ?>
                            (<?php echo htmlspecialchars($ride['vehicle_plate']); ?>)
                        </div>

                        <div class="ride-meta">
                            📍 <?php echo htmlspecialchars($ride['departure_location']); ?>
                            → <?php echo htmlspecialchars($ride['arrival_location']); ?>
                        </div>

                        <div class="ride-meta">
                            ⏰ <?php echo htmlspecialchars(substr($ride['departure_time'],0,5)); ?> 
                            → <?php echo htmlspecialchars(substr($ride['arrival_time'],0,5)); ?>
                        </div>

                        <div class="ride-meta">
                            💲 ₡<?php echo htmlspecialchars(number_format($ride['price_per_seat'], 0)); ?> por espacio
                        </div>

                        <div class="ride-meta">
                            🪑 Asientos: 
                            <?php echo (int)$ride['available_seats']; ?> disponibles / 
                            <?php echo (int)$ride['total_seats']; ?> totales
                        </div>

                        <div class="ride-meta" style="margin-top:6px;">
                            Días:
                            <?php
                                $days = array_filter(array_map('trim', explode(',', $ride['weekdays'] ?? '')));
                                if (empty($days)) {
                                    echo '<span class="badge-pill">No especificado</span>';
                                } else {
                                    foreach ($days as $day) {
                                        echo '<span class="badge-pill">'.htmlspecialchars($day).'</span>';
                                    }
                                }
                            ?>
                        </div>

                        <div style="margin-top:10px; display:flex; gap:8px; flex-wrap:wrap;">
                            <a href="edit-ride.php?id=<?php echo (int)$ride['id']; ?>" 
                               class="btn-small btn-edit">
                                Editar
                            </a>

                            <!-- Más adelante lo podemos cambiar a formulario POST con confirmación JS -->
                            <a href="delete-ride.php?id=<?php echo (int)$ride['id']; ?>" 
                               class="btn-small btn-delete"
                               onclick="return confirm('¿Seguro que deseas eliminar este ride?');">
                                Eliminar
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>

    <script src="js/theme.js"></script>
    <script src="js/user-menu.js"></script>
</body>
</html>
