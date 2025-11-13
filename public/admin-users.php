<?php
session_start();

// Debe estar logueado
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Debe ser admin (role_id = 1)
$roles   = $_SESSION['roles'] ?? [];
$roleIds = array_column($roles, 'role_id');
$isAdmin = in_array(1, $roleIds);

if (!$isAdmin) {
    header('Location: dashboard.php');
    exit;
}

require_once __DIR__ . '/../app/Config/database.php';
require_once __DIR__ . '/../app/Models/User.php';

$database  = new Database();
$db        = $database->getConnection();
$userModel = new User($db);

$users = $userModel->getAll();

// Filtrar para que NO salgan usuarios que tengan rol "Administrador"
$filteredUsers = [];
foreach ($users as $u) {
    $rolesNames = $u['roles'] ?? '';
    if (strpos($rolesNames, 'Administrador') === false) {
        $filteredUsers[] = $u;
    }
}

// Datos para navbar
$firstName = $_SESSION['first_name'] ?? $_SESSION['username'] ?? 'Admin';
$photoPath = $_SESSION['photo'] ?? null;
$initial   = strtoupper(substr($firstName, 0, 1));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de usuarios - Aventones</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .users-page-container {
            max-width: 1100px;
            margin: 24px auto;
            padding: 0 16px;
        }
        .top-bar {
            display:flex;
            justify-content:space-between;
            align-items:center;
            margin-bottom:20px;
        }
        .users-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 20px;
        }
        .user-card-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 10px;
        }
        .user-info-line {
            font-size: 14px;
            margin: 2px 0;
        }
        .user-role-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 999px;
            font-size: 12px;
            margin-right: 4px;
            background-color: #e5e7eb;
        }
        .btn-link-simple {
            display: inline-block;
            padding: 8px 14px;
            border-radius: 8px;
            background-color: var(--primary-color);
            color: white;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: background-color 0.25s ease, transform 0.2s ease, box-shadow 0.2s ease;
        }
        .btn-link-simple:hover {
            filter: brightness(1.05);
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(37,99,235,0.4);
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
    </style>
</head>
<body>

    <!-- NAVBAR con tema + menú usuario -->
    <nav class="navbar-custom"
         style="padding: 12px 24px; display: flex; justify-content: space-between; align-items: center;">
        <div style="font-size: 20px; font-weight: bold; color: white;">
            Aventones - Admin
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

    <div class="users-page-container">
        <a href="dashboard.php" class="btn-back">← Volver al panel</a>

        <div class="top-bar">
            <h2>Gestión de usuarios</h2>
            <span style="font-size:14px; color:gray;">
                Mostrando <?php echo count($filteredUsers); ?> usuarios (sin administradores)
            </span>
        </div>

        <?php if (empty($filteredUsers)): ?>
            <p>No hay usuarios registrados (o todos son administradores).</p>
        <?php else: ?>
            <div class="users-grid">
                <?php foreach ($filteredUsers as $user): 
                    $fullName = $user['first_name'] . ' ' . $user['last_name'];
                    $initialU = strtoupper(substr($user['first_name'], 0, 1));
                    $photo    = $user['photo'] ?? null;
                    $rolesNames = $user['roles'] ?? '';
                ?>
                    <div class="card-custom" style="padding:16px;">
                        <div class="user-card-header">
                            <?php if ($photo): ?>
                                <img src="<?php echo htmlspecialchars($photo); ?>" 
                                     alt="Foto de usuario" 
                                     class="user-avatar">
                            <?php else: ?>
                                <div class="user-avatar-placeholder">
                                    <?php echo htmlspecialchars($initialU); ?>
                                </div>
                            <?php endif; ?>

                            <div>
                                <strong><?php echo htmlspecialchars($fullName); ?></strong><br>
                                <span style="font-size:13px; color:gray;">
                                    @<?php echo htmlspecialchars($user['username']); ?>
                                </span>
                            </div>
                        </div>

                        <div class="user-info-line">
                            📧 <?php echo htmlspecialchars($user['email']); ?>
                        </div>
                        <div class="user-info-line">
                            📱 <?php echo htmlspecialchars($user['phone']); ?>
                        </div>
                        <div class="user-info-line">
                            🆔 Cédula: <?php echo htmlspecialchars($user['cedula']); ?>
                        </div>
                        <div class="user-info-line">
                            🎂 Nacimiento: <?php echo htmlspecialchars($user['birth_date']); ?>
                        </div>

                        <div class="user-info-line" style="margin-top:6px;">
                            Rol(es):
                            <?php
                                if (!empty($rolesNames)) {
                                    $splitRoles = explode(',', $rolesNames);
                                    foreach ($splitRoles as $rName) {
                                        $rName = trim($rName);
                                        if ($rName === '') continue;
                                        echo '<span class="user-role-badge">'.htmlspecialchars($rName).'</span>';
                                    }
                                } else {
                                    echo '<span class="user-role-badge">Sin rol</span>';
                                }
                            ?>
                        </div>

                        <div style="margin-top:12px;">
                            <a href="edit-user.php?id=<?php echo (int)$user['id']; ?>" class="btn-link-simple">
                                Editar usuario
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
