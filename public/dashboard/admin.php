<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

// Verificar si es administrador
$isAdmin = false;
if (isset($_SESSION['roles'])) {
    foreach ($_SESSION['roles'] as $role) {
        if ($role['role_id'] == 1) {
            $isAdmin = true;
            break;
        }
    }
}

if (!$isAdmin) {
    header('Location: passenger.php');
    exit;
}

// Obtener foto del usuario
require_once __DIR__ . '/../../app/Config/database.php';
require_once __DIR__ . '/../../app/Models/User.php';

$database = new Database();
$db = $database->getConnection();
$userModel = new User($db);
$currentUser = $userModel->findById($_SESSION['user_id']);
$userPhoto = $currentUser['photo'] ?? null;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administrador - Aventones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="#">
                🚗 Aventones - Admin
            </a>
            <div class="d-flex align-items-center">
                <?php if ($userPhoto && file_exists(__DIR__ . '/../' . $userPhoto)): ?>
                    <img src="../<?php echo htmlspecialchars($userPhoto); ?>" 
                         alt="Foto de perfil" 
                         class="user-avatar me-2"
                         style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid white;">
                <?php else: ?>
                    <div class="user-avatar me-2 bg-white text-primary d-flex align-items-center justify-content-center" 
                         style="width: 40px; height: 40px; border-radius: 50%; font-weight: bold; border: 2px solid white;">
                        <?php echo strtoupper(substr($_SESSION['first_name'], 0, 1)); ?>
                    </div>
                <?php endif; ?>
                <span class="text-white me-3">Admin: <?php echo htmlspecialchars($_SESSION['first_name']); ?></span>
                <a href="../api/logout.php" class="btn btn-outline-light btn-sm">Cerrar Sesión</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar p-3">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active" href="#" data-section="vehicles">
                            🚗 Aprobar Vehículos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-section="users">
                            👥 Gestionar Usuarios
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 p-4">
                <!-- Sección: Vehículos Pendientes -->
                <div id="section-vehicles" class="content-section">
                    <h2 class="mb-4">Vehículos Pendientes de Aprobación</h2>
                    <div id="pending-vehicles">
                        <!-- Los vehículos se cargarán aquí -->
                    </div>
                </div>

                <!-- Sección: Usuarios -->
                <div id="section-users" class="content-section d-none">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2>Gestión de Usuarios</h2>
                        <button class="btn btn-primary-custom" data-bs-toggle="modal" data-bs-target="#userModal" onclick="openCreateUserModal()">
                            + Crear Usuario
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover" id="users-table">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre Completo</th>
                                    <th>Email</th>
                                    <th>Usuario</th>
                                    <th>Cédula</th>
                                    <th>Teléfono</th>
                                    <th>Roles</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Los usuarios se cargarán aquí -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Rechazar Vehículo -->
    <div class="modal fade" id="rejectModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Rechazar Vehículo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="rejectForm">
                        <input type="hidden" id="reject_vehicle_id" name="vehicle_id">
                        <div class="mb-3">
                            <label class="form-label">Motivo del Rechazo *</label>
                            <textarea class="form-control" name="reason" rows="3" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-danger w-100">Confirmar Rechazo</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Crear/Editar Usuario -->
    <div class="modal fade" id="userModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="userModalTitle">Crear Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="userForm">
                        <input type="hidden" id="user_id" name="user_id">
                        <input type="hidden" id="user_action" name="action" value="create">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nombre *</label>
                                <input type="text" class="form-control" name="first_name" id="first_name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Apellidos *</label>
                                <input type="text" class="form-control" name="last_name" id="last_name" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Cédula *</label>
                                <input type="text" class="form-control" name="cedula" id="cedula" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Fecha de Nacimiento *</label>
                                <input type="date" class="form-control" name="birth_date" id="birth_date" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email *</label>
                                <input type="email" class="form-control" name="email" id="email" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Teléfono *</label>
                                <input type="tel" class="form-control" name="phone" id="phone" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nombre de Usuario *</label>
                            <input type="text" class="form-control" name="username" id="username" required>
                        </div>

                        <div class="mb-3" id="password_section">
                            <label class="form-label">Contraseña *</label>
                            <input type="password" class="form-control" name="password" id="password">
                            <small class="form-text text-muted">Al editar, dejar en blanco para mantener la contraseña actual</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Roles *</label>
                            <div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="roles[]" value="1" id="role_admin">
                                    <label class="form-check-label" for="role_admin">
                                        Administrador
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="roles[]" value="2" id="role_passenger">
                                    <label class="form-check-label" for="role_passenger">
                                        Pasajero
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="roles[]" value="3" id="role_driver">
                                    <label class="form-check-label" for="role_driver">
                                        Chofer
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Estado</label>
                            <select class="form-select" name="is_active" id="is_active">
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary-custom w-100" id="userSubmitBtn">Crear Usuario</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/admin.js"></script>
    <script src="../js/admin-users.js"></script>
</body>
</html>