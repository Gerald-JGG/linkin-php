<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Verificar que sea admin
$roles   = $_SESSION['roles'] ?? [];
$roleIds = array_column($roles, 'role_id');
$isAdmin = in_array(1, $roleIds);

if (!$isAdmin) {
    header('Location: dashboard.php');
    exit;
}

require_once __DIR__ . '/../app/Config/database.php';
require_once __DIR__ . '/../app/Models/user.php';

$database  = new Database();
$db        = $database->getConnection();
$userModel = new User($db);

// ID del usuario a editar
$userId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($userId <= 0) {
    die("Usuario inválido.");
}

$user = $userModel->findById($userId);
if (!$user) {
    die("Usuario no encontrado.");
}

// Roles actuales del usuario objetivo
$userRoles = $userModel->getUserRoles($userId);
$roleIdsUser = array_column($userRoles, 'role_id');

// Bloquear edición de admins
if (in_array(1, $roleIdsUser)) {
    die("No se permite editar usuarios administradores desde aquí.");
}

// Mensajes
$message = '';
$error   = '';

/* ========================================================
   PROCESAR FORMULARIO
   ======================================================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Datos básicos
    $data = [
        'first_name' => trim($_POST['first_name'] ?? ''),
        'last_name'  => trim($_POST['last_name'] ?? ''),
        'cedula'     => trim($_POST['cedula'] ?? ''),
        'birth_date' => trim($_POST['birth_date'] ?? ''),
        'email'      => trim($_POST['email'] ?? ''),
        'phone'      => trim($_POST['phone'] ?? ''),
    ];

    // Subir foto (opcional)
    $newPhotoPath = null;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
        $uploadDir = __DIR__ . '/uploads/users/';
        if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);

        $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $filename = uniqid("user_") . "." . $ext;
        $path = $uploadDir . $filename;

        if (move_uploaded_file($_FILES['photo']['tmp_name'], $path)) {
            $newPhotoPath = "uploads/users/" . $filename;
        } else {
            $error = "Error al subir la foto.";
        }
    }

    // Rol nuevo
    $newRole = (int)($_POST['role_id'] ?? 2);

    // No permitir role_id inválido
    if (!in_array($newRole, [1,2,3])) {
        $error = "Rol inválido.";
    }

    if (!$error) {

        // Actualizar datos del usuario
        $ok = $userModel->update($userId, $data);

        // Actualizar foto
        if ($ok && $newPhotoPath) {
            $stmt = $db->prepare("UPDATE users SET photo = :p WHERE id = :id");
            $stmt->bindParam(':p', $newPhotoPath);
            $stmt->bindParam(':id', $userId);
            $stmt->execute();
        }

        // Actualizar rol
        if ($ok) {
            $userModel->updateRole($userId, $newRole);
            $message = "Usuario actualizado correctamente.";
            $user = $userModel->findById($userId);
        } else {
            $error = "No se pudo actualizar el usuario.";
        }
    }
}

// Para navbar
$firstNameAdmin = $_SESSION['first_name'] ?? 'Admin';
$photoAdmin     = $_SESSION['photo'] ?? null;
$initialAdmin   = strtoupper(substr($firstNameAdmin, 0, 1));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar usuario</title>
    <link rel="stylesheet" href="css/styles.css">

    <style>
        .edit-container {
            max-width: 900px;
            margin: 24px auto;
            padding: 0 18px;
        }

        .form-wrapper {
            max-width: 650px;
            margin: 0 auto;
        }

        .form-row {
            display:flex; 
            gap:16px; 
            flex-wrap:wrap;
        }
        .form-row > div { flex:1; min-width:240px; }

        .form-group { margin-bottom:12px; }

        .btn-back {
            display:inline-block;
            padding:6px 12px;
            border-radius:6px;
            background:#e5e7eb;
            text-decoration:none;
            color:#1e293b;
            margin-bottom:16px;
        }
        body.dark-mode .btn-back {
            background:#1f2937;
            color:#e2e8f0;
        }
    </style>
</head>

<body>

<!-- NAVBAR -->
<nav class="navbar-custom"
     style="padding: 12px 24px; display:flex; justify-content:space-between; align-items:center;">
    <div style="font-size:20px; font-weight:bold; color:white;">Aventones - Admin</div>

    <div class="user-menu-container">
        <button type="button" class="user-avatar-button" id="userMenuButton">
            <?php if ($photoAdmin): ?>
                <img src="<?php echo htmlspecialchars($photoAdmin); ?>" class="user-avatar">
            <?php else: ?>
                <div class="user-avatar-placeholder"><?php echo $initialAdmin; ?></div>
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

<div class="edit-container">
    <a href="admin-users.php" class="btn-back">← Volver</a>

    <div class="card-custom" style="padding:20px;">

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="edit-header" style="display:flex; gap:16px; align-items:center; margin-bottom:16px;">
            <?php
            $uInitial = strtoupper(substr($user['first_name'], 0, 1));
            if (!empty($user['photo'])): ?>
                <img src="<?php echo htmlspecialchars($user['photo']); ?>" 
                     class="profile-avatar-small">
            <?php else: ?>
                <div class="profile-avatar-placeholder-small"><?php echo $uInitial; ?></div>
            <?php endif; ?>

            <div>
                <h2>Editar usuario</h2>
                <p style="color:gray; font-size:14px;">
                    <?php echo htmlspecialchars($user['first_name'] . " " . $user['last_name']); ?>
                    (@<?php echo htmlspecialchars($user['username']); ?>)
                </p>
            </div>
        </div>

        <div class="form-wrapper">
            <form method="post" enctype="multipart/form-data">

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Nombre</label>
                        <input name="first_name" class="form-control" 
                               value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Apellidos</label>
                        <input name="last_name" class="form-control"
                               value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Cédula</label>
                        <input name="cedula" class="form-control"
                               value="<?php echo htmlspecialchars($user['cedula']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Fecha nacimiento</label>
                        <input type="date" name="birth_date" class="form-control"
                               value="<?php echo htmlspecialchars($user['birth_date']); ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Correo</label>
                        <input type="email" name="email" class="form-control"
                               value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Teléfono</label>
                        <input name="phone" class="form-control"
                               value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Usuario</label>
                    <input class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                </div>

                <!-- SELECT DE ROLES -->
                <div class="form-group">
                    <label class="form-label">Rol del usuario</label>
                    <select name="role_id" class="form-control" required>
                        <option value="2" <?php echo in_array(2, $roleIdsUser) ? 'selected':''; ?>>Pasajero</option>
                        <option value="3" <?php echo in_array(3, $roleIdsUser) ? 'selected':''; ?>>Chofer</option>
                        <option value="1" <?php echo in_array(1, $roleIdsUser) ? 'selected':''; ?>>Administrador</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Foto (opcional)</label>
                    <input type="file" name="photo" class="form-control" accept="image/*">
                </div>

                <button class="btn-primary-custom" style="margin-top:10px;">
                    Guardar cambios
                </button>

            </form>
        </div>

    </div>
</div>

<script src="js/theme.js"></script>
<script src="js/user-menu.js"></script>
</body>
</html>
