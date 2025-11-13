<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$firstName = $_SESSION['first_name'] ?? $_SESSION['username'] ?? 'Usuario';
$photoPath = $_SESSION['photo'] ?? null;
$initial = strtoupper(substr($firstName, 0, 1));
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración - Aventones</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .settings-container {
            max-width: 800px;
            margin: 24px auto;
            padding: 0 16px;
        }

        .settings-section {
            margin-bottom: 24px;
        }

        .theme-toggle {
            display: inline-flex;
            border-radius: 999px;
            padding: 4px;
            background-color: #e5e7eb;
        }

        .theme-toggle button {
            border: none;
            background: transparent;
            padding: 8px 16px;
            border-radius: 999px;
            cursor: pointer;
            font-size: 14px;
        }

        .theme-toggle button.active {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            font-weight: 600;
        }

        body.dark-mode .theme-toggle {
            background-color: #1f2937;
        }

        body.dark-mode .theme-toggle button {
            color: #e5e7eb;
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

    <!-- NAVBAR -->
    <nav class="navbar-custom"
        style="padding: 12px 24px; display: flex; justify-content: space-between; align-items: center;">
        <div style="font-size: 20px; font-weight: bold; color: white;">
            Aventones
        </div>

        <div class="user-menu-container">
            <button type="button" class="user-avatar-button" id="userMenuButton">
                <?php if ($photoPath): ?>
                    <img src="<?php echo htmlspecialchars($photoPath); ?>" alt="Foto de perfil" class="user-avatar">
                <?php else: ?>
                    <div class="user-avatar-placeholder">
                        <?php echo htmlspecialchars($initial); ?>
                    </div>
                <?php endif; ?>
                <span class="user-name-label"><?php echo htmlspecialchars($firstName); ?></span>
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

    <div class="settings-container">
        <a href="dashboard.php" class="btn-back">← Volver al panel</a>

        <div class="card-custom" style="padding:20px;">
            <h2 style="margin-bottom:8px;">Configuración</h2>
            <p style="color:gray; margin-bottom:20px;">
                Ajusta tus preferencias de la aplicación.
            </p>

            <div class="settings-section">
                <h3>Tema de la aplicación</h3>
                <p style="font-size:14px; color:gray;">
                    Elige entre tema claro u oscuro. Esta preferencia se guarda en este dispositivo.
                </p>

                <div class="theme-toggle">
                    <button type="button" id="btnThemeLight">Claro</button>
                    <button type="button" id="btnThemeDark">Oscuro</button>
                </div>

            </div>

            <div class="settings-section">
                <h3>Información básica</h3>
                <p style="font-size:14px;">
                    Nombre: <strong><?php echo htmlspecialchars($firstName); ?></strong><br>
                    Usuario: <strong><?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?></strong><br>
                    ID de usuario: <strong><?php echo (int) ($_SESSION['user_id'] ?? 0); ?></strong>
                </p>
            </div>
        </div>
    </div>

    <script src="js/theme.js"></script>
    <script src="js/user-menu.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const btnLight = document.getElementById('btnThemeLight');
            const btnDark = document.getElementById('btnThemeDark');

            function updateButtons() {
                const current = window.getTheme();
                if (current === 'dark') {
                    btnDark.classList.add('active');
                    btnLight.classList.remove('active');
                } else {
                    btnLight.classList.add('active');
                    btnDark.classList.remove('active');
                }
            }

            btnLight.addEventListener('click', function () {
                window.setTheme('light');
                updateButtons();
            });

            btnDark.addEventListener('click', function () {
                window.setTheme('dark');
                updateButtons();
            });

            updateButtons();
        });
    </script>
</body>

</html>