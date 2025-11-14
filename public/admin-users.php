<?php
session_start();

// verificar admin
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$roles   = $_SESSION['roles'] ?? [];
$roleIds = array_column($roles, 'role_id');
$isAdmin = in_array(1, $roleIds);

if (!$isAdmin) {
    header("Location: dashboard.php");
    exit;
}

require_once __DIR__ . '/../app/Config/database.php';
require_once __DIR__ . '/../app/Models/user.php';

$db = (new Database())->getConnection();
$userModel = new User($db);

// ESTA ES LA CORRECTA
$users = $userModel->getAll();

// Datos del admin para navbar
$firstName = $_SESSION['first_name'] ?? 'Admin';
$photoPath = $_SESSION['photo'] ?? null;
$initial   = strtoupper(substr($firstName, 0, 1));
?>
<!DOCTYPE html>
<html lang="es">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Usuarios - Admin</title>
<link rel="stylesheet" href="css/styles.css">

<style>
.list-wrapper { display:grid; gap:15px; }
.user-card {
    padding:18px;
    border-radius:12px;
    background:white;
    box-shadow:0 2px 10px rgba(0,0,0,0.1);
}
body.dark-mode .user-card {
    background:#0f172a;
    color:#f1f5f9;
}
.actions a {
    display:inline-block;
    padding:8px 14px;
    border-radius:8px;
    font-weight:600;
    text-decoration:none;
}
.btn-edit { background:#2563eb; color:white; }
.btn-delete { background:#ef4444; color:white; }

.btn-back {
    display:inline-block;
    margin-bottom:20px;
    padding:8px 14px;
    border-radius:8px;
    background:#e2e8f0;
    color:#1e293b;
    text-decoration:none;
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

    <div style="font-size:20px; font-weight:bold; color:white;">Aventones — Admin</div>

    <div class="user-menu-container">
        <button type="button" class="user-avatar-button" id="userMenuButton">

            <?php if ($photoPath): ?>
                <img src="<?php echo htmlspecialchars($photoPath); ?>" class="user-avatar">
            <?php else: ?>
                <div class="user-avatar-placeholder"><?php echo $initial; ?></div>
            <?php endif; ?>

            <span class="user-name-label"><?php echo htmlspecialchars($firstName); ?></span>
            <span class="user-chevron">▾</span>

        </button>

        <div class="user-menu" id="userMenu">
            <a href="dashboard.php">Panel principal</a>
            <a href="profile.php">Mi perfil</a>
            <a href="settings.php">Configuración</a>
            <hr>
            <a href="api/logout.php">Salir</a>
        </div>
    </div>

</nav>

<div style="padding:24px;">

<a href="dashboard.php" class="btn-back">← Volver al panel</a>

<h2>Usuarios registrados</h2>

<div class="list-wrapper">

<?php foreach ($users as $u): ?>

    <?php 
        // ocultar admins
        if (str_contains($u['roles'] ?? '', 'Administrador')) continue; 
    ?>

    <div class="user-card">
        <h3><?php echo htmlspecialchars($u['first_name'].' '.$u['last_name']); ?></h3>
        <p><strong>@<?php echo htmlspecialchars($u['username']); ?></strong></p>
        <p>Rol actual: <strong><?php echo htmlspecialchars($u['roles']); ?></strong></p>

        <div class="actions">
            <a class="btn-edit" href="edit-user.php?id=<?php echo $u['id']; ?>">Editar</a>
            <a class="btn-delete" href="delete-user.php?id=<?php echo $u['id']; ?>">Eliminar</a>
        </div>
    </div>

<?php endforeach; ?>

</div></div>

<script src="js/theme.js"></script>
<script src="js/user-menu.js"></script>

</body>
</html>
