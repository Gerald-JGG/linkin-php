<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

// Verificar si tiene rol de chofer
$hasDriverRole = false;
if (isset($_SESSION['roles'])) {
    foreach ($_SESSION['roles'] as $role) {
        if ($role['role_id'] == 3) {
            $hasDriverRole = true;
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Chofer - Aventones</title>
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
                        <a class="nav-link active" href="#" data-section="vehicles">
                            🚗 Mis Vehículos
                        </a>
                    </li>
                    <?php if ($hasDriverRole): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-section="rides">
                            🛣️ Mis Viajes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-section="bookings">
                            📋 Reservas Pendientes
                        </a>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="passenger.php">
                            🔙 Vista Pasajero
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 p-4">
                <!-- Sección: Vehículos -->
                <div id="section-vehicles" class="content-section">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2>Mis Vehículos</h2>
                        <button class="btn btn-primary-custom" data-bs-toggle="modal" data-bs-target="#vehicleModal">
                            + Registrar Vehículo
                        </button>
                    </div>
                    <div id="vehicles-list" class="row">
                        <!-- Los vehículos se cargarán aquí -->
                    </div>
                </div>

                <!-- Sección: Viajes -->
                <?php if ($hasDriverRole): ?>
                <div id="section-rides" class="content-section d-none">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2>Mis Viajes</h2>
                        <button class="btn btn-primary-custom" data-bs-toggle="modal" data-bs-target="#rideModal" onclick="openCreateRideModal()">
                            + Crear Viaje
                        </button>
                    </div>
                    <div id="rides-list">
                        <!-- Los viajes se cargarán aquí -->
                    </div>
                </div>

                <!-- Sección: Reservas Pendientes -->
                <div id="section-bookings" class="content-section d-none">
                    <h2 class="mb-4">Reservas Pendientes</h2>
                    <div id="pending-bookings">
                        <!-- Las reservas se cargarán aquí -->
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal Vehículo -->
    <div class="modal fade" id="vehicleModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Registrar Vehículo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="vehicleForm" method="post" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label">Marca *</label>
                            <select class="form-select" name="brand" id="brand_select" required>
                                <option value="">Seleccione una marca</option>
                                <option value="Toyota">Toyota</option>
                                <option value="Honda">Honda</option>
                                <option value="Nissan">Nissan</option>
                                <option value="Mazda">Mazda</option>
                                <option value="Hyundai">Hyundai</option>
                                <option value="Kia">Kia</option>
                                <option value="Chevrolet">Chevrolet</option>
                                <option value="Ford">Ford</option>
                                <option value="Volkswagen">Volkswagen</option>
                                <option value="Mitsubishi">Mitsubishi</option>
                                <option value="Suzuki">Suzuki</option>
                                <option value="Subaru">Subaru</option>
                                <option value="Mercedes-Benz">Mercedes-Benz</option>
                                <option value="BMW">BMW</option>
                                <option value="Audi">Audi</option>
                                <option value="Otra">Otra</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Modelo *</label>
                            <select class="form-select" name="model" id="model_select" required disabled>
                                <option value="">Seleccione primero una marca</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Año *</label>
                            <select class="form-select" name="year" required>
                                <option value="">Seleccione un año</option>
                                <?php for($year = 2025; $year >= 1990; $year--): ?>
                                    <option value="<?php echo $year; ?>"><?php echo $year; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Color *</label>
                            <select class="form-select" name="color" required>
                                <option value="">Seleccione un color</option>
                                <option value="Blanco">Blanco</option>
                                <option value="Negro">Negro</option>
                                <option value="Gris">Gris</option>
                                <option value="Plata">Plata</option>
                                <option value="Azul">Azul</option>
                                <option value="Rojo">Rojo</option>
                                <option value="Verde">Verde</option>
                                <option value="Amarillo">Amarillo</option>
                                <option value="Naranja">Naranja</option>
                                <option value="Café">Café</option>
                                <option value="Dorado">Dorado</option>
                                <option value="Otro">Otro</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Placa *</label>
                            <input type="text" class="form-control" name="plate" placeholder="Ej: ABC-123" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Fotografía (opcional)</label>
                            <input type="file" class="form-control" name="photo" accept="image/*">
                        </div>

                        <button type="submit" class="btn btn-primary-custom w-100">Registrar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Viaje (Crear/Editar) -->
    <div class="modal fade" id="rideModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="rideModalTitle">Crear Viaje</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="rideForm">
                        <input type="hidden" id="ride_id" name="ride_id">
                        <input type="hidden" id="ride_action" name="action" value="create">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Vehículo *</label>
                                <select class="form-select" name="vehicle_id" id="ride_vehicle_select" required>
                                    <option value="">Seleccione un vehículo</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nombre del Viaje *</label>
                                <input type="text" class="form-control" name="ride_name" id="ride_name" placeholder="Ej: Ruta San José - Heredia" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Lugar de Salida *</label>
                                <input type="text" class="form-control" name="departure_location" id="departure_location" placeholder="Ej: San José Centro" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Hora de Salida *</label>
                                <input type="time" class="form-control" name="departure_time" id="departure_time" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Lugar de Llegada *</label>
                                <input type="text" class="form-control" name="arrival_location" id="arrival_location" placeholder="Ej: Heredia Centro" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Hora de Llegada *</label>
                                <input type="time" class="form-control" name="arrival_time" id="arrival_time" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Días de la Semana *</label>
                            <div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" name="weekdays[]" value="monday" id="monday">
                                    <label class="form-check-label" for="monday">Lunes</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" name="weekdays[]" value="tuesday" id="tuesday">
                                    <label class="form-check-label" for="tuesday">Martes</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" name="weekdays[]" value="wednesday" id="wednesday">
                                    <label class="form-check-label" for="wednesday">Miércoles</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" name="weekdays[]" value="thursday" id="thursday">
                                    <label class="form-check-label" for="thursday">Jueves</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" name="weekdays[]" value="friday" id="friday">
                                    <label class="form-check-label" for="friday">Viernes</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" name="weekdays[]" value="saturday" id="saturday">
                                    <label class="form-check-label" for="saturday">Sábado</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" name="weekdays[]" value="sunday" id="sunday">
                                    <label class="form-check-label" for="sunday">Domingo</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tarifa por Asiento (₡) *</label>
                                <input type="number" class="form-control" name="price_per_seat" id="price_per_seat" min="0" step="0.01" placeholder="1500.00" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Asientos Disponibles *</label>
                                <input type="number" class="form-control" name="total_seats" id="total_seats" min="1" max="10" placeholder="4" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary-custom w-100" id="rideSubmitBtn">Crear Viaje</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/driver.js"></script>
    <script src="../js/vehicle-models.js"></script>
    <script src="../js/ride-crud.js"></script>
</body>
</html>