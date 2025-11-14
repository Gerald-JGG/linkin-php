<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/../app/Config/database.php';
require_once __DIR__ . '/../app/Models/Booking.php';

$db = (new Database())->getConnection();
$bookingModel = new Booking($db);

$userId   = (int)$_SESSION['user_id'];
$bookings = $bookingModel->getByPassenger($userId);

// Navbar data
$firstName = $_SESSION['first_name'] ?? $_SESSION['username'] ?? 'Usuario';
$photo     = $_SESSION['photo'] ?? null;
$initial   = strtoupper(substr($firstName, 0, 1));

$msg = $_GET['msg'] ?? '';
$err = $_GET['err'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis reservaciones - Aventones</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .page-container {
            max-width: 1000px;
            margin: 24px auto;
            padding: 0 16px;
        }
        .booking-card {
            padding:16px;
            border-radius:12px;
            background:white;
            box-shadow:0 2px 8px rgba(0,0,0,0.08);
            margin-bottom:12px;
        }
        body.dark-mode .booking-card {
            background:#0f172a;
            color:#f1f5f9;
        }
        .badge {
            display:inline-block;
            padding:3px 8px;
            border-radius:999px;
            font-size:12px;
        }
        .badge-pending  { background:#f97316; color:white; }
        .badge-accepted { background:#16a34a; color:white; }
        .badge-rejected { background:#ef4444; color:white; }
        .badge-cancelled{ background:#6b7280; color:white; }

        .btn-cancel {
            display:inline-block;
            padding:7px 12px;
            border-radius:8px;
            background:#ef4444;
            color:white;
            font-size:14px;
            font-weight:600;
            text-decoration:none;
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

        .alert-ok {
            padding:8px 12px;
            border-radius:8px;
            background:#dcfce7;
            color:#166534;
            margin-bottom:12px;
        }
        .alert-err {
            padding:8px 12px;
            border-radius:8px;
            background:#fee2e2;
            color:#b91c1c;
            margin-bottom:12px;
        }
    </style>
</head>
<body>

<nav class="navbar-custom" style="padding:12px 24px; display:flex; justify-content:space-between;">
    <div style="color:white; font-size:20px; font-weight:bold;">Aventones</div>

    <div class="user-menu-container">
        <button type="button" class="user-avatar-button" id="userMenuButton">
            <?php if ($photo): ?>
                <img src="<?php echo htmlspecialchars($photo); ?>" class="user-avatar" alt="foto">
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

<div class="page-container">

    <a href="dashboard.php" class="btn-back">← Volver al panel</a>

    <h2 style="margin-bottom:8px;">Mis reservaciones</h2>
    <p style="color:gray; margin-bottom:16px;">
        Aquí ves los rides que has solicitado, su estado y puedes cancelar los que aún estén activos.
    </p>

    <?php if ($msg): ?>
        <div class="alert-ok"><?php echo htmlspecialchars($msg); ?></div>
    <?php endif; ?>
    <?php if ($err): ?>
        <div class="alert-err"><?php echo htmlspecialchars($err); ?></div>
    <?php endif; ?>

    <?php if (empty($bookings)): ?>
        <p style="color:gray;">Todavía no tienes reservaciones.</p>
    <?php else: ?>
        <?php foreach ($bookings as $b): 
            $status = strtolower($b['status']);
            $badgeClass = 'badge-'.$status;
        ?>
            <div class="booking-card">
                <h3 style="margin-bottom:4px;">
                    <?php echo htmlspecialchars($b['ride_name']); ?>
                </h3>
                <p style="font-size:14px; color:gray;">
                    Conductor:
                    <strong>
                        <?php echo htmlspecialchars($b['driver_first_name'].' '.$b['driver_last_name']); ?>
                    </strong><br>
                    Tarifa: ₡<?php echo number_format($b['price_per_seat'], 0); ?><br>
                    Solicitud: <?php echo htmlspecialchars($b['created_at']); ?>
                </p>

                <p>
                    <strong>Salida:</strong> <?php echo htmlspecialchars($b['departure_location']); ?>
                    (<?php echo htmlspecialchars($b['departure_time']); ?>)<br>
                    <strong>Llegada:</strong> <?php echo htmlspecialchars($b['arrival_location']); ?>
                    (<?php echo htmlspecialchars($b['arrival_time']); ?>)
                </p>

                <p style="margin-top:8px;">
                    Estado:
                    <span class="badge <?php echo $badgeClass; ?>">
                        <?php echo ucfirst($status); ?>
                    </span>
                </p>

                <?php if (in_array($status, ['pending','accepted'], true)): ?>
                    <form method="post" action="cancel-booking.php" style="margin-top:10px;">
                        <input type="hidden" name="booking_id" value="<?php echo (int)$b['id']; ?>">
                        <button type="submit" class="btn-cancel"
                                onclick="return confirm('¿Seguro que quieres cancelar esta reservación?');">
                            Cancelar reservación
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

</div>

<script src="js/theme.js"></script>
<script src="js/user-menu.js"></script>
</body>
</html>
