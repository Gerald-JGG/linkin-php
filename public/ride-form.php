<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Roles
$roles   = $_SESSION['roles'] ?? [];
$roleIds = array_column($roles, 'role_id');
$isDriver = in_array(3, $roleIds);
$isAdmin  = in_array(1, $roleIds);

if (!$isDriver && !$isAdmin) {
    header('Location: dashboard.php');
    exit;
}

require_once __DIR__ . '/../app/Config/database.php';
require_once __DIR__ . '/../app/Models/Ride.php';
require_once __DIR__ . '/../app/Models/Vehicle.php';

$database    = new Database();
$db          = $database->getConnection();
$rideModel   = new Ride($db);
$vehicleModel = new Vehicle($db);

$userId = (int)$_SESSION['user_id'];

$rideId    = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$isEditing = $rideId > 0;

$ride     = null;
$message  = '';
$error    = '';

// Si está editando, cargamos y validamos que tenga permisos
if ($isEditing) {
    $ride = $rideModel->findById($rideId);
    if (!$ride) {
        die("Ride no encontrado.");
    }

    if (!$isAdmin && (int)$ride['driver_id'] !== $userId) {
        die("No tienes permiso para editar este ride.");
    }
}

// Vehículos disponibles:
if ($isAdmin) {
    // Admin ve todos los vehículos aprobados
    $vehicles = $vehicleModel->getAllApproved();
} else {
    // Chofer solo sus vehículos aprobados
    $vehicles = $vehicleModel->getApprovedByUser($userId);
}

if (empty($vehicles)) {
    // No debería estar aquí si no tiene vehículos, pero por si acaso:
    $error = "No hay vehículos aprobados disponibles para crear rides.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {
    // Si admin y quiere cambiar el chofer, podríamos aceptar driver_id por POST.
    // Para simplificar, si no es admin → driver_id = userId
    if ($isAdmin) {
        $driverId = isset($_POST['driver_id']) ? (int)$_POST['driver_id'] : $ride['driver_id'] ?? $userId;
    } else {
        $driverId = $userId;
    }

    $vehicleId = isset($_POST['vehicle_id']) ? (int)$_POST['vehicle_id'] : 0;

    $rideName  = trim($_POST['ride_name'] ?? '');
    $depLoc    = trim($_POST['departure_location'] ?? '');
    $depTime   = $_POST['departure_time'] ?? '';
    $arrLoc    = trim($_POST['arrival_location'] ?? '');
    $arrTime   = $_POST['arrival_time'] ?? '';
    $price     = (float)($_POST['price_per_seat'] ?? 0);
    $totalSeats = (int)($_POST['total_seats'] ?? 0);
    $weekdaysPost = $_POST['weekdays'] ?? [];

    $weekdays = implode(',', $weekdaysPost);
    $availableSeats = $isEditing ? (int)($ride['available_seats']) : $totalSeats;

    if (!$vehicleId || $rideName === '' || $depLoc === '' || $arrLoc === '' || $totalSeats <= 0) {
        $error = "Por favor completa todos los campos obligatorios.";
    } else {
        $data = [
            'driver_id'          => $driverId,
            'vehicle_id'         => $vehicleId,
            'ride_name'          => $rideName,
            'departure_location' => $depLoc,
            'departure_time'     => $depTime,
            'arrival_location'   => $arrLoc,
            'arrival_time'       => $arrTime,
            'weekdays'           => $weekdays,
            'price_per_seat'     => $price,
            'total_seats'        => $totalSeats,
            'available_seats'    => $availableSeats,
        ];

        if ($isEditing) {
            $ok = $rideModel->update($rideId, $data);
            if ($ok) {
                $message = "Ride actualizado correctamente.";
                $ride = $rideModel->findById($rideId);
            } else {
                $error = "No se pudo actualizar el ride.";
            }
        } else {
            $ok = $rideModel->create($data);
            if ($ok) {
                // Redirigimos a Mis rides o admin-rides según rol
                if ($isAdmin) {
                    header('Location: admin-rides.php');
                } else {
                    header('Location: rides.php');
                }
                exit;
            } else {
                $error = "No se pudo crear el ride.";
            }
        }
    }
}

// Para navbar
$firstName = $_SESSION['first_name'] ?? $_SESSION['username'] ?? 'Usuario';
$photoPath = $_SESSION['photo'] ?? null;
$initial   = strtoupper(substr($firstName, 0, 1));

// Para checkboxes de días
$selectedDays = [];
if ($isEditing && !empty($ride['weekdays'])) {
    $selectedDays = array_map('trim', explode(',', $ride['weekdays']));
}
$allDays = [
    'monday'    => 'Lunes',
    'tuesday'   => 'Martes',
    'wednesday' => 'Miércoles',
    'thursday'  => 'Jueves',
    'friday'    => 'Viernes',
    'saturday'  => 'Sábado',
    'sunday'    => 'Domingo',
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $isEditing ? 'Editar ride' : 'Crear ride'; ?> - Aventones</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .ride-form-container {
            max-width: 900px;
            margin: 24px auto;
            padding: 0 16px;
        }
        .btn-back {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
            background-color: #e5e7eb;
            color: var(--dark-text);
            margin-bottom: 16px;
        }
        body.dark-mode .btn-back {
            background-color: #1f2937;
            color: #e5e7eb;
        }
        .form-wrapper {
            max-width: 650px;
            margin: 0 auto;
        }
        .form-row {
            display:flex;
            gap:16px;
            flex-wrap: wrap;
        }
        .form-row > div {
            flex:1;
            min-width: 240px;
        }
        .form-group {
            margin-bottom:12px;
        }
        .form-control {
            width: 100%;
            padding: 8px 10px;
            border-radius: 6px;
            border: 1px solid #cbd5e1;
            font-size: 14px;
        }
        .form-label {
            display:block;
            font-size:14px;
            font-weight:600;
            margin-bottom:4px;
        }
        .alert {
            padding:10px 14px;
            border-radius:6px;
            font-size:14px;
            margin-bottom:12px;
        }
        .alert-success {
            background-color:#dcfce7;
            color:#166534;
        }
        .alert-error {
            background-color:#fee2e2;
            color:#b91c1c;
        }
        .weekday-group {
            display:flex;
            flex-wrap:wrap;
            gap:8px 16px;
            font-size:13px;
        }
        .weekday-group label {
            display:flex;
            align-items:center;
            gap:4px;
        }
    </style>
</head>
<body>

    <!-- NAVBAR -->
    <nav class="navbar-custom"
         style="padding: 12px 24px; display: flex; justify-content: space-between; align-items: center;">
        <div style="font-size: 20px; font-weight: bold; color: white;">
            Aventones
        </div>

        <div class="user-menu-container">
            <button type="button" class="user-avatar-button" id="userMenuButton">
                <?php if ($photoPath): ?>
                    <img src="<?php echo htmlspecialchars($photoPath); ?>" 
                         alt="Foto de perfil" 
                         class="user-avatar">
                <?php else: ?>
                    <div class="user-avatar-placeholder">
                        <?php echo htmlspecialchars($initial); ?>
                    </div>
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

    <div class="ride-form-container">
        <a href="<?php echo $isAdmin ? 'admin-rides.php' : 'rides.php'; ?>" class="btn-back">
            ← Volver a la lista de rides
        </a>

        <div class="card-custom" style="padding:20px;">
            <h2 style="margin-bottom:8px;">
                <?php echo $isEditing ? 'Editar ride' : 'Crear nuevo ride'; ?>
            </h2>
            <p style="font-size:14px; color:gray; margin-bottom:16px;">
                Define el recorrido, el vehículo y los espacios disponibles para tu viaje.
            </p>

            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if (!empty($vehicles)): ?>
                <div class="form-wrapper">
                    <form method="post">
                        <?php if ($isAdmin): ?>
                            <div class="form-group">
                                <label class="form-label" for="driver_id">
                                    ID del chofer (temporal para admin)
                                </label>
                                <input class="form-control" type="number" id="driver_id" name="driver_id"
                                       value="<?php echo htmlspecialchars($ride['driver_id'] ?? $userId); ?>">
                                <small style="font-size:12px; color:gray;">
                                    Más adelante se puede mejorar con un selector amigable de choferes.
                                </small>
                            </div>
                        <?php endif; ?>

                        <div class="form-group">
                            <label class="form-label" for="vehicle_id">Vehículo</label>
                            <select class="form-control" id="vehicle_id" name="vehicle_id" required>
                                <option value="">Selecciona un vehículo</option>
                                <?php foreach ($vehicles as $v): ?>
                                    <?php
                                        $label = $v['brand'].' '.$v['model'].' ('.$v['plate'].')';
                                        $selected = ($isEditing && (int)$ride['vehicle_id'] === (int)$v['id']) ? 'selected' : '';
                                    ?>
                                    <option value="<?php echo (int)$v['id']; ?>" <?php echo $selected; ?>>
                                        <?php echo htmlspecialchars($label); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="ride_name">Nombre del viaje</label>
                            <input class="form-control" type="text" id="ride_name" name="ride_name"
                                   value="<?php echo htmlspecialchars($ride['ride_name'] ?? ''); ?>" required>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="departure_location">Lugar de salida</label>
                                <input class="form-control" type="text" id="departure_location" name="departure_location"
                                       value="<?php echo htmlspecialchars($ride['departure_location'] ?? ''); ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="departure_time">Hora de salida</label>
                                <input class="form-control" type="time" id="departure_time" name="departure_time"
                                       value="<?php echo htmlspecialchars(substr($ride['departure_time'] ?? '',0,5)); ?>" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="arrival_location">Lugar de llegada</label>
                                <input class="form-control" type="text" id="arrival_location" name="arrival_location"
                                       value="<?php echo htmlspecialchars($ride['arrival_location'] ?? ''); ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="arrival_time">Hora de llegada</label>
                                <input class="form-control" type="time" id="arrival_time" name="arrival_time"
                                       value="<?php echo htmlspecialchars(substr($ride['arrival_time'] ?? '',0,5)); ?>" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Días de la semana</label>
                            <div class="weekday-group">
                                <?php foreach ($allDays as $key => $label): ?>
                                    <label>
                                        <input type="checkbox" name="weekdays[]" value="<?php echo $key; ?>"
                                            <?php echo in_array($key, $selectedDays) ? 'checked' : ''; ?>>
                                        <?php echo $label; ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="price_per_seat">Tarifa por espacio (₡)</label>
                                <input class="form-control" type="number" step="0.01" min="0" id="price_per_seat" name="price_per_seat"
                                       value="<?php echo htmlspecialchars($ride['price_per_seat'] ?? ''); ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="total_seats">Cantidad de espacios</label>
                                <input class="form-control" type="number" min="1" id="total_seats" name="total_seats"
                                       value="<?php echo htmlspecialchars($ride['total_seats'] ?? ''); ?>" required>
                            </div>
                        </div>

                        <button type="submit" class="btn-primary-custom">
                            <?php echo $isEditing ? 'Guardar cambios' : 'Crear ride'; ?>
                        </button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="js/theme.js"></script>
    <script src="js/user-menu.js"></script>
</body>
</html>
