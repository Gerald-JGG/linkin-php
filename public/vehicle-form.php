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

// Navbar
$firstName = $_SESSION['first_name'] ?? $_SESSION['username'] ?? 'Usuario';
$photoPath = $_SESSION['photo'] ?? null;
$initial   = strtoupper(substr($firstName, 0, 1));

// ID para editar
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$editing = $id > 0;

$vehicle = null;

if ($editing) {
    $vehicle = $vehicleModel->findById($id);

    if (!$vehicle) {
        die("Vehículo no encontrado.");
    }

    // Solo admin o dueño del vehículo pueden editarlo
    if (!$isAdmin && $vehicle['user_id'] != $userId) {
        die("No tienes permiso para editar este vehículo.");
    }
}

$error = "";
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $brand = trim($_POST['brand'] ?? '');
    $model = trim($_POST['model'] ?? '');
    $year  = trim($_POST['year'] ?? '');
    $color = trim($_POST['color'] ?? '');
    $plate = trim($_POST['plate'] ?? '');

    if ($brand === "" || $model === "" || $year === "" || $color === "" || $plate === "") {
        $error = "Todos los campos son obligatorios excepto la fotografía.";
    } else {

        // Guardar imagen si existe
        $photoPathSaved = $vehicle['photo'] ?? null;

        if (!empty($_FILES['photo']['name'])) {
            $uploadDir = __DIR__ . '/../uploads/vehicles/';
            if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);

            $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
            $filename = uniqid('veh_') . '.' . $ext;

            if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir . $filename)) {
                $photoPathSaved = 'uploads/vehicles/' . $filename;
            } else {
                $error = "Error al subir la fotografía.";
            }
        }

        if (!$error) {
            if ($editing) {

                // EDITAR
                $ok = $vehicleModel->update($id, [
                    'brand' => $brand,
                    'model' => $model,
                    'year'  => $year,
                    'color' => $color,
                    'plate' => $plate
                ]);

                if ($ok && !empty($photoPathSaved)) {
                    $stmt = $db->prepare("UPDATE vehicles SET photo = :p WHERE id = :id");
                    $stmt->bindParam(':p', $photoPathSaved);
                    $stmt->bindParam(':id', $id);
                    $stmt->execute();
                }

                // REDIRECCIÓN SEGÚN ROL
                if ($isAdmin) {
                    header("Location: admin-vehicles.php");
                } else {
                    header("Location: vehicles.php");
                }
                exit;

            } else {

                // CREAR NUEVO
                $result = $vehicleModel->create([
                    'user_id' => $userId,
                    'brand'   => $brand,
                    'model'   => $model,
                    'year'    => $year,
                    'color'   => $color,
                    'plate'   => $plate,
                    'photo'   => $photoPathSaved
                ]);

                if ($result === "duplicate_plate") {
                    $error = "La placa ingresada ya existe. Intente otra.";
                } else {

                    // Si es pasajero y registra vehículo → volverse chofer
                    if (!$isAdmin) {
                        $hasDriver = in_array(3, $roleIds);

                        if (!$hasDriver) {
                            $stmt = $db->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (:u, 3)");
                            $stmt->bindParam(':u', $userId);
                            $stmt->execute();

                            $_SESSION['roles'][] = ['role_id' => 3, 'role_name' => 'Chofer'];
                        }
                    }

                    // REDIRECCIÓN
                    if ($isAdmin) {
                        header("Location: admin-vehicles.php");
                    } else {
                        header("Location: vehicles.php");
                    }
                    exit;
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $editing ? "Editar vehículo" : "Registrar vehículo"; ?></title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .form-container {
            max-width: 800px;
            margin: 24px auto;
            padding: 0 16px;
        }
        .btn-back {
            display:inline-block;
            padding:6px 12px;
            background:#e5e7eb;
            border-radius:6px;
            color:#1e293b;
            text-decoration:none;
            margin-bottom:16px;
            font-size:14px;
        }
        body.dark-mode .btn-back {
            background:#1f2937;
            color:white;
        }
        .form-group { margin-bottom:12px; }
        .form-control {
            width:100%;
            padding:8px 10px;
            border:1px solid #cbd5e1;
            border-radius:6px;
            font-size:14px;
        }
        body.dark-mode .form-control {
            background:#0f172a;
            border-color:#334155;
            color:#e2e8f0;
        }
        .form-label {
            display:block;
            margin-bottom:5px;
            font-size:14px;
            font-weight:600;
        }
        .btn-submit {
            background:var(--primary-color);
            padding:10px 18px;
            border:none;
            border-radius:8px;
            font-size:15px;
            color:white;
            cursor:pointer;
            font-weight:600;
        }
        .alert-success, .alert-error {
            padding:14px;
            border-radius:6px;
            margin-bottom:16px;
            font-size:14px;
        }
        .alert-success { background:#dcfce7; color:#166534; }
        .alert-error { background:#fee2e2; color:#b91c1c; }
    </style>
</head>
<body>

    <!-- NAVBAR -->
    <nav class="navbar-custom"
        style="padding:12px 24px; display:flex; justify-content:space-between; align-items:center;">
        <div style="font-size:20px; font-weight:bold; color:white;">Aventones</div>

        <div class="user-menu-container">
            <button class="user-avatar-button" id="userMenuButton">
                <?php if ($photoPath): ?>
                    <img src="<?php echo $photoPath; ?>" class="user-avatar">
                <?php else: ?>
                    <div class="user-avatar-placeholder"><?php echo $initial; ?></div>
                <?php endif; ?>
                <span class="user-name-label"><?php echo $firstName; ?></span>
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

    <div class="form-container">

        <a href="vehicles.php" class="btn-back">← Volver</a>

        <div class="card-custom" style="padding:20px;">

            <h2><?php echo $editing ? "Editar vehículo" : "Registrar vehículo"; ?></h2>
            <p style="font-size:14px; color:gray;">Completa la información del vehículo.</p>

            <?php if ($error): ?>
                <div class="alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">

                <div class="form-group">
                    <label class="form-label">Marca</label>
                    <input class="form-control" name="brand" required
                           value="<?php echo $editing ? htmlspecialchars($vehicle['brand']) : ""; ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Modelo</label>
                    <input class="form-control" name="model" required
                           value="<?php echo $editing ? htmlspecialchars($vehicle['model']) : ""; ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Año</label>
                    <input class="form-control" type="number" name="year" required
                           value="<?php echo $editing ? htmlspecialchars($vehicle['year']) : ""; ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Color</label>
                    <input class="form-control" name="color" required
                           value="<?php echo $editing ? htmlspecialchars($vehicle['color']) : ""; ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Placa</label>
                    <input class="form-control" name="plate" required
                           value="<?php echo $editing ? htmlspecialchars($vehicle['plate']) : ""; ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Fotografía (opcional)</label>
                    <input type="file" class="form-control" name="photo">
                </div>

                <button class="btn-submit">
                    <?php echo $editing ? "Guardar cambios" : "Registrar vehículo"; ?>
                </button>

            </form>

        </div>
    </div>

    <script src="js/theme.js"></script>
    <script src="js/user-menu.js"></script>

</body>
</html>
