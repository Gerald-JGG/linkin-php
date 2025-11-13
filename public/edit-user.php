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

$database = new Database();
$db       = $database->getConnection();
$userModel = new User($db);

$userId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($userId <= 0) {
    die("Usuario inválido.");
}

$user = $userModel->findById($userId);
if (!$user) {
    die("Usuario no encontrado.");
}

// Evitar editar admins (por seguridad)
$userRoles = $userModel->getUserRoles($userId);
foreach ($userRoles as $r) {
    if ((int)$r['role_id'] === 1) {
        die("No se permite editar usuarios administradores desde aquí.");
    }
}

$message = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'first_name' => $_POST['first_name'] ?? '',
        'last_name'  => $_POST['last_name'] ?? '',
        'cedula'     => $_POST['cedula'] ?? '',
        'birth_date' => $_POST['birth_date'] ?? '',
        'email'      => $_POST['email'] ?? '',
        'phone'      => $_POST['phone'] ?? '',
    ];

    // Subir nueva foto (opcional)
    $newPhotoPath = null;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
        $uploadDir = __DIR__ . '/uploads/users/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $extension = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $filename  = uniqid('user_') . '.' . $extension;
        $uploadPath = $uploadDir . $filename;

        if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadPath)) {
            $newPhotoPath = 'uploads/users/' . $filename;
        } else {
            $error = "Error al subir la foto.";
        }
    }

    if (!$error) {
        $ok = $userModel->update($userId, $data);

        if ($ok && $newPhotoPath) {
            $stmt = $db->prepare("UPDATE users SET photo = :photo WHERE id = :id");
            $stmt->bindParam(':photo', $newPhotoPath);
            $stmt->bindParam(':id', $userId);
            $stmt->execute();
            $user['photo'] = $newPhotoPath;
        }

        if ($ok) {
            $message = "Usuario actualizado correctamente.";
            $user = $userModel->findById($userId);
        } else {
            $error = "No se pudo actualizar el usuario.";
        }
    }
}

// Para navbar admin
$firstNameAdmin = $_SESSION['first_name'] ?? $_SESSION['username'] ?? 'Admin';
$photoAdmin     = $_SESSION['photo'] ?? null;
$initialAdmin   = strtoupper(substr($firstNameAdmin, 0, 1));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar usuario - Aventones</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .edit-container {
            max-width: 800px;
            margin: 24px auto;
            padding: 0 16px;
        }
        .edit-header {
            margin-bottom: 16px;
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
        .profile-avatar-small {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }
        .profile-avatar-placeholder-small {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 24px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }
        .form-row {
            display:flex;
            gap:16px;
        }
        .form-row > div {
            flex:1;
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
    </style>
</head>
<body>

    <!-- NAVBAR ADMIN -->
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
                <a href="profile.php">Mi perfil</a>
                <a href="settings.php">Configuración</a>
                <hr>
                <a href="api/logout.php">Salir</a>
            </div>
        </div>
    </nav>

    <div class="edit-container">
        <a href="admin-users.php" class="btn-back">← Volver a la lista de usuarios</a>

        <div class="card-custom" style="padding:20px;">
            <div class="edit-header" style="display:flex; align-items:center; gap:16px; margin-bottom:16px;">
                <?php 
                    $uInitial = strtoupper(substr($user['first_name'] ?? 'U', 0, 1));
                    if (!empty($user['photo'])): ?>
                    <img src="<?php echo htmlspecialchars($user['photo']); ?>" 
                         alt="Foto de usuario" 
                         class="profile-avatar-small">
                <?php else: ?>
                    <div class="profile-avatar-placeholder-small">
                        <?php echo htmlspecialchars($uInitial); ?>
                    </div>
                <?php endif; ?>
                <div>
                    <h2 style="margin-bottom:4px;">Editar usuario</h2>
                    <p style="color:gray; font-size:14px;">
                        <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?> (@<?php echo htmlspecialchars($user['username']); ?>)
                    </p>
                </div>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="post" enctype="multipart/form-data">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="first_name">Nombre</label>
                        <input class="form-control" type="text" id="first_name" name="first_name"
                               value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="last_name">Apellidos</label>
                        <input class="form-control" type="text" id="last_name" name="last_name"
                               value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="cedula">Cédula</label>
                        <input class="form-control" type="text" id="cedula" name="cedula"
                               value="<?php echo htmlspecialchars($user['cedula']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="birth_date">Fecha de nacimiento</label>
                        <input class="form-control" type="date" id="birth_date" name="birth_date"
                               value="<?php echo htmlspecialchars($user['birth_date']); ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="email">Correo</label>
                        <input class="form-control" type="email" id="email" name="email"
                               value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="phone">Teléfono</label>
                        <input class="form-control" type="text" id="phone" name="phone"
                               value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Usuario</label>
                    <input class="form-control" type="text" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                </div>

                <div class="form-group">
                    <label class="form-label" for="photo">Foto de perfil (opcional)</label>
                    <input class="form-control" type="file" id="photo" name="photo" accept="image/*">
                </div>

                <button type="submit" class="btn-primary-custom">
                    Guardar cambios
                </button>
            </form>
        </div>
    </div>

    <script src="js/theme.js"></script>
    <script src="js/user-menu.js"></script>
</body>
</html>
