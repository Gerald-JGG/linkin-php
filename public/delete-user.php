<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); exit;
}

$roles = $_SESSION['roles'];
$roleIds = array_column($roles, 'role_id');

if (!in_array(1, $roleIds)) {
    header('Location: dashboard.php'); exit;
}

$userId = $_GET['id'] ?? 0;

require_once __DIR__.'/../app/Config/database.php';
require_once __DIR__.'/../app/Models/user.php';

$db = (new Database())->getConnection();
$userModel = new User($db);

$user = $userModel->findById($userId);
if (!$user) die("Usuario inválido");

$userRoles = $userModel->getUserRoles($userId);
$isAdminTarget = in_array(1, array_column($userRoles,'role_id'));

if ($isAdminTarget) die("No se puede eliminar a un administrador.");

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $userModel->delete($userId);
    header("Location: admin-users.php?deleted=1"); exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Eliminar usuario</title>
<link rel="stylesheet" href="css/styles.css">

<style>
body {
    height:100vh; display:flex; align-items:center; justify-content:center;
    background:var(--light-bg);
}
body.dark-mode { background:#020617; }
.modal {
    padding:24px; border-radius:16px; background:white;
    width:90%; max-width:380px; text-align:center;
    box-shadow:0 6px 20px rgba(0,0,0,0.2);
}
body.dark-mode .modal { background:#0f172a; color:#e2e8f0; }
.btn-row { display:flex; gap:12px; justify-content:center; margin-top:20px; }
.btn-cancel { padding:10px 18px; background:#e5e7eb; border-radius:8px; }
.btn-delete { padding:10px 18px; background:#ef4444; color:white; border-radius:8px; }
</style>
</head>

<body>
<div class="modal">
    <h2>¿Eliminar este usuario?</h2>
    <p><?php echo htmlspecialchars($user['first_name'].' '.$user['last_name']); ?></p>

    <form method="POST">
        <div class="btn-row">
            <a href="admin-users.php" class="btn-cancel">Cancelar</a>
            <button class="btn-delete">Eliminar</button>
        </div>
    </form>
</div>

<script src="js/theme.js"></script>
</body>
</html>
