<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$roles = $_SESSION['roles'] ?? [];
$roleIds = array_column($roles, 'role_id');

$isAdmin  = in_array(1, $roleIds);
$isDriver = in_array(3, $roleIds);

if (!$isAdmin && !$isDriver) {
    die("No tienes permiso para crear viajes.");
}

require_once __DIR__ . '/../app/Config/database.php';
require_once __DIR__ . '/../app/Models/Ride.php';
require_once __DIR__ . '/../app/Models/Vehicle.php';

$db = (new Database())->getConnection();
$rideModel = new Ride($db);
$vehicleModel = new Vehicle($db);

$userId = $_SESSION['user_id'];

// Si es admin → puede usar cualquier vehiculo aprobado
// Si es chofer → solo sus vehículos aprobados
$vehicles = $isAdmin ? 
    $vehicleModel->getAllApproved() : 
    $vehicleModel->getApprovedByUser($userId);

$message = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'driver_id'         => $isAdmin ? $_POST['driver_id'] : $userId,
        'vehicle_id'        => $_POST['vehicle_id'],
        'ride_name'         => $_POST['ride_name'],
        'departure_location'=> $_POST['departure_location'],
        'departure_time'    => $_POST['departure_time'],
        'arrival_location'  => $_POST['arrival_location'],
        'arrival_time'      => $_POST['arrival_time'],
        'weekdays'          => implode(",", $_POST['weekdays'] ?? []),
        'price_per_seat'    => $_POST['price_per_seat'],
        'total_seats'       => $_POST['total_seats'],
        'available_seats'   => $_POST['total_seats'],
    ];

    $ok = $rideModel->create($data);

    if ($ok) {
        header("Location: rides.php?created=1");
        exit;
    } else {
        $error = "No se pudo crear el viaje.";
    }
}

// NAVBAR INFO
$firstName = $_SESSION['first_name'] ?? $_SESSION['username'] ?? "Usuario";
$photo     = $_SESSION['photo'] ?? null;
$initial   = strtoupper(substr($firstName, 0, 1));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Viaje - Aventones</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>

        .page-container {
            max-width: 900px;
            margin: 24px auto;
            padding: 0 16px;
        }

        .form-card {
            padding:20px;
            background:white;
            border-radius:12px;
            box-shadow:0 2px 10px rgba(0,0,0,0.1);
        }
        body.dark-mode .form-card {
            background:#0f172a;
            color:#f1f5f9;
        }

        .form-group {
            margin-bottom:14px;
        }

        label {
            font-weight:600;
            margin-bottom:4px;
            display:block;
        }

        input, select {
            width:100%;
            padding:10px;
            border-radius:8px;
            border:1px solid #cbd5e1;
            font-size:14px;
        }

        .btn-save {
            padding:10px 16px;
            border-radius:8px;
            background:#2563eb;
            color:white;
            font-weight:600;
            text-decoration:none;
            border:none;
        }

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

        .weekdays-box {
            display:flex;
            flex-wrap:wrap;
            gap:12px;
            margin-top:5px;
        }

        .alert-error {
            padding:12px;
            background:#fee2e2;
            color:#b91c1c;
            border-radius:8px;
            margin-bottom:14px;
        }

    </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar-custom" style="padding: 12px 24px; display:flex; justify-content:space-between;">
    <div style="color:white; font-size:20px; font-weight:bold;">Crear Viaje</div>

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
            <hr>
            <a href="api/logout.php">Salir</a>
        </div>
    </div>
</nav>

<div class="page-container">

    <a class="btn-back" href="rides.php">← Volver</a>

    <div class="form-card">

        <h2 style="margin-bottom:16px;">Crear nuevo viaje</h2>

        <?php if ($error): ?>
            <div class="alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">

            <?php if ($isAdmin): ?>
                <div class="form-group">
                    <label>Chofer</label>
                    <input type="number" name="driver_id" placeholder="ID del chofer" required>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label>Vehículo</label>
                <select name="vehicle_id" required>
                    <option value="">Selecciona un vehículo</option>

                    <?php foreach ($vehicles as $v): ?>
                        <option value="<?= $v['id']; ?>">
                            <?= htmlspecialchars($v['brand'] . " " . $v['model'] . " - " . $v['plate']); ?>
                        </option>
                    <?php endforeach; ?>

                </select>
            </div>

            <div class="form-group">
                <label>Nombre del viaje</label>
                <input type="text" name="ride_name" required>
            </div>

            <div class="form-group">
                <label>Lugar de salida</label>
                <input type="text" name="departure_location" required>
            </div>

            <div class="form-group">
                <label>Hora de salida</label>
                <input type="time" name="departure_time" required>
            </div>

            <div class="form-group">
                <label>Lugar de llegada</label>
                <input type="text" name="arrival_location" required>
            </div>

            <div class="form-group">
                <label>Hora de llegada</label>
                <input type="time" name="arrival_time" required>
            </div>

            <div class="form-group">
                <label>Días de la semana</label>
                <div class="weekdays-box">
                    <?php
                    $days = ["monday"=>"Lunes","tuesday"=>"Martes","wednesday"=>"Miércoles","thursday"=>"Jueves","friday"=>"Viernes","saturday"=>"Sábado","sunday"=>"Domingo"];
                    foreach ($days as $key => $label):
                    ?>
                        <label>
                            <input type="checkbox" name="weekdays[]" value="<?= $key; ?>"> <?= $label; ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="form-group">
                <label>Tarifa por asiento</label>
                <input type="number" name="price_per_seat" required>
            </div>

            <div class="form-group">
                <label>Total de asientos</label>
                <input type="number" name="total_seats" required min="1">
            </div>

            <button class="btn-save" type="submit">Guardar viaje</button>

        </form>

    </div>
</div>

<script src="js/theme.js"></script>
<script src="js/user-menu.js"></script>

</body>
</html>
