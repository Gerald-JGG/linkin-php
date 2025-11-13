<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Pasajero - Aventones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="#">
                🚗 Aventones
            </a>
            <div class="d-flex align-items-center">
                <span class="text-white me-3">Hola, <?php echo htmlspecialchars($_SESSION['first_name']); ?></span>
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
                        <a class="nav-link active" href="#" data-section="rides">
                            🔍 Buscar Viajes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-section="bookings">
                            📋 Mis Reservas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="driver.php">
                            🚗 Ser Chofer
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 p-4">
                <!-- Sección: Buscar Viajes -->
                <div id="section-rides" class="content-section">
                    <h2 class="mb-4">Viajes Disponibles</h2>
                    <div id="available-rides" class="row">
                        <!-- Los viajes se cargarán aquí -->
                    </div>
                </div>

                <!-- Sección: Mis Reservas -->
                <div id="section-bookings" class="content-section d-none">
                    <h2 class="mb-4">Mis Reservas</h2>
                    <div id="my-bookings">
                        <!-- Las reservas se cargarán aquí -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Reservar -->
    <div class="modal fade" id="bookingModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reservar Viaje</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="bookingForm">
                        <input type="hidden" id="ride_id" name="ride_id">
                        <div class="mb-3">
                            <label class="form-label">Nombre del Viaje</label>
                            <input type="text" class="form-control" id="modal_ride_name" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Fecha del Viaje</label>
                            <input type="date" class="form-control" name="booking_date" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Asientos a Reservar</label>
                            <input type="number" class="form-control" name="seats_requested" value="1" min="1" required>
                        </div>
                        <button type="submit" class="btn btn-primary-custom w-100">Confirmar Reserva</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/passenger.js"></script>
</body>
</html>