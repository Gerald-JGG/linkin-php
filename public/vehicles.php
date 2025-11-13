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

$userId = (int)$_SESSION['user_id'];

// Roles
$roles   = $_SESSION['roles'] ?? [];
$roleIds = array_column($roles, 'role_id');
$isAdmin = in_array(1, $roleIds);

// Mis vehículos (solo los del chofer)
$vehicles = $vehicleModel->getByUserId($userId);

// Navbar data
$firstName = $_SESSION['first_name'] ?? $_SESSION['username'] ?? 'Usuario';
$photoPath = $_SESSION['photo'] ?? null;
$initial   = strtoupper(substr($firstName, 0, 1));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis vehículos - Aventones</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .vehicles-container {
            max-width: 1100px;
            margin: 24px auto;
            padding: 0 16px;
        }
        .top-bar {
            display:flex;
            justify-content:space-between;
            align-items:center;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 20px;
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
        .btn-create {
            padding: 8px 14px;
            border-radius: 8px;
            background-color: var(--success-color);
            color:white;
            font-size:14px;
            text-decoration:none;
            font-weight:600;
        }
        .btn-create:hover { filter:brightness(1.05); }

        .vehicles-grid {
            display:grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 16px;
        }
        .vehicle-photo {
            width: 100%;
            height: 170px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 8px;
        }
        .badge-status {
            display:inline-block;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 11px;
            margin-top: 4px;
        }
        .badge-pending-vehicle { background-color: #fef3c7; color: #92400e; }
        .badge-approved-vehicle { background-color: #dcfce7; color: #166534; }
        .badge-rejected-vehicle { background-color: #fee2e2; color: #b91c1c; }

        .btn-small {
            display:inline-block;
            padding: 6px 10px;
            border-radius: 6px;
            font-size: 13px;
            text-decoration:none;
            border: none;
            cursor:pointer;
        }
        .btn-edit { background-color: var(--primary-color); color:white; }
        .btn-delete { background-color: #ef4444; color:white; }

        .alert-info-custom {
            padding: 10px 14px;
            border-radius: 6px;
            font-size: 14px;
            background-color:#eff6ff;
            color:#1d4ed8;
            margin-bottom: 16px;
        }
        body.dark-mode .alert-info-custom {
            background-color:#1e293b;
            color:#bfdbfe;
        }

        /* MODAL */
        .confirm-overlay {
            position: fixed;
            top:0; left:0;
            width:100%; height:100%;
            background-color: rgba(0,0,0,0.6);
            display:none;
            justify-content:center;
            align-items:center;
            z-index:2000;
        }
        .confirm-overlay.show { display:flex; }

        .confirm-box {
            background:white;
            padding:24px;
            border-radius:12px;
            width:300px;
            text-align:center;
            animation: fadeIn .25s ease-out;
        }
        @keyframes fadeIn {
            from { transform:scale(.8); opacity:0; }
            to   { transform:scale(1); opacity:1; }
        }

        body.dark-mode .confirm-box {
            background:#0f172a;
            color:white;
        }

        .confirm-actions {
            display:flex;
            justify-content:space-around;
            margin-top:20px;
        }
        .btn-cancel, .btn-confirm {
            padding:6px 14px;
            border-radius:6px;
            cursor:pointer;
            border:none;
        }
        .btn-cancel { background-color:#e5e7eb; }
        body.dark-mode .btn-cancel { background-color:#1f2937; color:white; }

        .btn-confirm { background-color:#ef4444; color:white; }
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
                    <img src="<?php echo htmlspecialchars($photoPath); ?>" class="user-avatar">
                <?php else: ?>
                    <div class="user-avatar-placeholder"><?php echo htmlspecialchars($initial); ?></div>
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

    <div class="vehicles-container">

        <a href="dashboard.php" class="btn-back">← Volver al panel</a>

        <div class="top-bar">
            <div>
                <h2>Mis vehículos</h2>
                <p style="font-size:14px; color:gray;">Registra vehículos para poder publicar tus rides.</p>
            </div>
            <div>
                <a href="vehicle-form.php" class="btn-create">+ Registrar vehículo</a>
            </div>
        </div>

        <?php if (empty($vehicles)): ?>
            <div class="alert-info-custom">
                No has registrado vehículos aún.  
                Empieza registrando tu primer auto. 🚗
            </div>
        <?php else: ?>

            <div class="vehicles-grid">
                <?php foreach ($vehicles as $v): ?>
                    <div class="card-custom" style="padding:14px;">
                        
                        <?php if (!empty($v['photo'])): ?>
                            <img src="<?php echo htmlspecialchars($v['photo']); ?>" class="vehicle-photo">
                        <?php endif; ?>

                        <h3><?php echo htmlspecialchars($v['brand'] . ' ' . $v['model']); ?></h3>

                        <div style="font-size:13px; color:gray;">
                            Año: <?php echo htmlspecialchars($v['year']); ?> · 
                            Color: <?php echo htmlspecialchars($v['color']); ?>
                        </div>

                        <div style="font-size:13px; color:gray;">
                            Placa: <strong><?php echo htmlspecialchars($v['plate']); ?></strong>
                        </div>

                        <?php
                            $status = $v['status'];
                            $badge = "badge-pending-vehicle";
                            $label = "Pendiente";
                            if ($status == "approved") { $badge = "badge-approved-vehicle"; $label="Aprobado"; }
                            if ($status == "rejected") { $badge = "badge-rejected-vehicle"; $label="Rechazado"; }
                        ?>

                        <div class="badge-status <?php echo $badge; ?>"><?php echo $label; ?></div>

                        <div style="margin-top:10px; display:flex; gap:8px;">
                            <a href="vehicle-form.php?id=<?php echo $v['id']; ?>" class="btn-small btn-edit">Editar</a>

                            <button class="btn-small btn-delete btn-open-delete" data-id="<?php echo $v['id']; ?>">
                                Eliminar
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        <?php endif; ?>

    </div>

    <!-- MODAL -->
    <div class="confirm-overlay" id="confirmDeleteOverlay">
        <div class="confirm-box">
            <h3>¿Eliminar vehículo?</h3>
            <p style="font-size:13px;">Si tiene rides asociados, no se podrá eliminar.</p>

            <div class="confirm-actions">
                <button class="btn-cancel" id="cancelDelete">Cancelar</button>
                <button class="btn-confirm" id="confirmDelete">Eliminar</button>
            </div>
        </div>
    </div>

    <script>
        (function(){
            const overlay = document.getElementById('confirmDeleteOverlay');
            const cancelBtn = document.getElementById('cancelDelete');
            const confirmBtn = document.getElementById('confirmDelete');
            let vehicleId = null;

            document.addEventListener('click', e => {
                const btn = e.target.closest('.btn-open-delete');
                if (!btn) return;

                vehicleId = btn.getAttribute('data-id');
                overlay.classList.add('show');
            });

            cancelBtn.onclick = () => overlay.classList.remove('show');

            confirmBtn.onclick = () => {
                if (vehicleId) {
                    window.location.href = "delete-vehicle.php?id=" + vehicleId;
                }
            };

            overlay.onclick = e => {
                if (e.target === overlay) overlay.classList.remove('show');
            };
        })();
    </script>

    <script src="js/theme.js"></script>
    <script src="js/user-menu.js"></script>
</body>
</html>
