<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../app/Config/database.php';
require_once __DIR__ . '/../app/Models/Vehicle.php';

$database = new Database();
$db       = $database->getConnection();
$vehicleModel = new Vehicle($db);

// Roles
$roles   = $_SESSION['roles'] ?? [];
$roleIds = array_column($roles, 'role_id');
$isAdmin = in_array(1, $roleIds);
$userId  = (int)$_SESSION['user_id'];

// Vehicle ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    die("Vehículo inválido.");
}

// Verificar vehículo
$vehicle = $vehicleModel->findById($id);
if (!$vehicle) {
    die("Vehículo no encontrado.");
}

// Permiso: admin o dueño
if (!$isAdmin && $vehicle['user_id'] != $userId) {
    die("No tienes permiso para eliminar este vehículo.");
}

// Si confirmaron -> eliminar
if (isset($_POST['confirm']) && $_POST['confirm'] === "yes") {
    $vehicleModel->delete($id);

    if ($isAdmin) {
        header("Location: admin-vehicles.php");
    } else {
        header("Location: vehicles.php");
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eliminar vehículo</title>
    <link rel="stylesheet" href="css/styles.css">

    <style>
        body {
            display:flex;
            align-items:center;
            justify-content:center;
            height:100vh;
            background:var(--light-bg);
        }
        body.dark-mode {
            background:#020617;
        }

        .modal-box {
            background:white;
            width: 95%;
            max-width: 420px;
            padding:28px;
            border-radius:18px;
            text-align:center;
            box-shadow:0 8px 25px rgba(0,0,0,0.18);
            animation:fadeIn 0.25s ease;
        }
        body.dark-mode .modal-box {
            background:#0f172a;
            color:#e2e8f0;
            box-shadow:0 8px 25px rgba(255,255,255,0.08);
        }

        @keyframes fadeIn {
            from { opacity:0; transform:scale(0.96); }
            to   { opacity:1; transform:scale(1); }
        }

        .modal-title {
            font-size:22px;
            font-weight:700;
            margin-bottom:10px;
        }

        .modal-text {
            font-size:15px;
            margin-bottom:20px;
            color:#475569;
        }
        body.dark-mode .modal-text {
            color:#cbd5e1;
        }

        .btn-row {
            display:flex;
            justify-content:center;
            gap:12px;
        }

        .btn-cancel {
            padding:10px 18px;
            border-radius:8px;
            background:#e5e7eb;
            color:#1e293b;
            font-weight:600;
            cursor:pointer;
            text-decoration:none;
        }
        body.dark-mode .btn-cancel {
            background:#1f2937;
            color:#e2e8f0;
        }

        .btn-delete {
            padding:10px 18px;
            border-radius:8px;
            background:#ef4444;
            color:white;
            font-weight:600;
            border:none;
            cursor:pointer;
        }
    </style>
</head>

<body>

    <div class="modal-box">
        <h2 class="modal-title">¿Eliminar este vehículo?</h2>

        <p class="modal-text">
            Marca: <strong><?php echo htmlspecialchars($vehicle['brand']); ?></strong><br>
            Modelo: <strong><?php echo htmlspecialchars($vehicle['model']); ?></strong><br>
            Placa: <strong><?php echo htmlspecialchars($vehicle['plate']); ?></strong><br><br>
            Esta acción no se puede deshacer.
        </p>

        <form method="POST">
            <div class="btn-row">
                <a href="<?php echo $isAdmin ? 'admin-vehicles.php' : 'vehicles.php'; ?>" class="btn-cancel">
                    Cancelar
                </a>

                <button type="submit" name="confirm" value="yes" class="btn-delete">
                    Eliminar
                </button>
            </div>
        </form>
    </div>

    <script src="js/theme.js"></script>
</body>
</html>
