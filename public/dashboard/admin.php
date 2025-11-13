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
                    <h2 class="mb-4">Gestión de Usuarios</h2>
                    <div class="table-responsive">
                        <table class="table table-striped" id="users-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Email</th>
                                    <th>Usuario</th>
                                    <th>Roles</th>
                                    <th>Fecha Registro</th>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/admin.js"></script>
</body>
</html>