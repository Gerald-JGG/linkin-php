// Función para mostrar mensajes estéticos
function showAlert(message, type = 'danger') {
    // Tipos: success, danger, warning, info
    const alertHTML = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    
    // Buscar o crear contenedor de alertas
    let alertContainer = document.getElementById('alert-container');
    if (!alertContainer) {
        alertContainer = document.createElement('div');
        alertContainer.id = 'alert-container';
        // Insertar antes del formulario
        const form = document.getElementById('registerForm');
        form.parentNode.insertBefore(alertContainer, form);
    }
    
    alertContainer.innerHTML = alertHTML;
    
    // Scroll suave hacia la alerta
    alertContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

// Navegación entre secciones
document.querySelectorAll('.sidebar .nav-link').forEach(link => {
    link.addEventListener('click', function(e) {
        if (this.getAttribute('data-section')) {
            e.preventDefault();
            
            // Actualizar enlaces activos
            document.querySelectorAll('.sidebar .nav-link').forEach(l => l.classList.remove('active'));
            this.classList.add('active');
            
            // Mostrar sección correspondiente
            const section = this.getAttribute('data-section');
            document.querySelectorAll('.content-section').forEach(s => s.classList.add('d-none'));
            document.getElementById('section-' + section).classList.remove('d-none');
            
            // Cargar datos según la sección
            if (section === 'rides') {
                loadAvailableRides();
            } else if (section === 'bookings') {
                loadMyBookings();
            }
        }
    });
});

// Cargar viajes disponibles
async function loadAvailableRides() {
    try {
        const response = await fetch('../api/rides/available.php');
        const result = await response.json();
        
        const container = document.getElementById('available-rides');
        
        if (result.success && result.rides.length > 0) {
            container.innerHTML = result.rides.map(ride => `
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card ride-card h-100">
                        <div class="card-body">
                            <h5 class="card-title">${ride.ride_name}</h5>
                            <p class="mb-2"><strong>📍 Origen:</strong> ${ride.departure_location}</p>
                            <p class="mb-2"><strong>🎯 Destino:</strong> ${ride.arrival_location}</p>
                            <p class="mb-2"><strong>🕐 Salida:</strong> ${ride.departure_time}</p>
                            <p class="mb-2"><strong>🕐 Llegada:</strong> ${ride.arrival_time}</p>
                            <p class="mb-2"><strong>📅 Días:</strong> ${ride.weekdays}</p>
                            <p class="mb-2"><strong>💰 Precio:</strong> ₡${ride.price_per_seat}</p>
                            <p class="mb-2"><strong>🪑 Asientos:</strong> ${ride.available_seats} disponibles</p>
                            <hr>
                            <p class="mb-1"><strong>Chofer:</strong> ${ride.first_name} ${ride.last_name}</p>
                            <p class="mb-3"><strong>Vehículo:</strong> ${ride.brand} ${ride.model} (${ride.color})</p>
                            <button class="btn btn-primary-custom w-100" onclick="openBookingModal(${ride.id}, '${ride.ride_name}')">
                                Reservar
                            </button>
                        </div>
                    </div>
                </div>
            `).join('');
        } else {
            container.innerHTML = '<div class="col-12"><p class="text-center">No hay viajes disponibles</p></div>';
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

// Cargar mis reservas
async function loadMyBookings() {
    try {
        const response = await fetch('../api/bookings/my-bookings.php');
        const result = await response.json();
        
        const container = document.getElementById('my-bookings');
        
        if (result.success && result.bookings.length > 0) {
            container.innerHTML = result.bookings.map(booking => {
                const statusBadge = {
                    'pending': 'badge-pending',
                    'accepted': 'badge-approved',
                    'rejected': 'badge-rejected',
                    'cancelled': 'bg-secondary'
                };
                
                return `
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h5>${booking.ride_name}</h5>
                                    <p class="mb-1"><strong>Origen:</strong> ${booking.departure_location}</p>
                                    <p class="mb-1"><strong>Destino:</strong> ${booking.arrival_location}</p>
                                    <p class="mb-1"><strong>Fecha:</strong> ${booking.booking_date}</p>
                                    <p class="mb-1"><strong>Asientos:</strong> ${booking.seats_requested}</p>
                                    <p class="mb-1"><strong>Total:</strong> ₡${(booking.price_per_seat * booking.seats_requested).toFixed(2)}</p>
                                    <p class="mb-0"><strong>Chofer:</strong> ${booking.driver_first_name} ${booking.driver_last_name}</p>
                                </div>
                                <div class="text-end">
                                    <span class="badge ${statusBadge[booking.status]}">${booking.status}</span>
                                    ${booking.status === 'pending' || booking.status === 'accepted' ? 
                                        `<button class="btn btn-sm btn-danger mt-2" onclick="cancelBooking(${booking.id})">Cancelar</button>` : 
                                        ''}
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        } else {
            container.innerHTML = '<p class="text-center">No tienes reservas</p>';
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

// Abrir modal de reserva
function openBookingModal(rideId, rideName) {
    document.getElementById('ride_id').value = rideId;
    document.getElementById('modal_ride_name').value = rideName;
    new bootstrap.Modal(document.getElementById('bookingModal')).show();
}

// Enviar reserva
document.getElementById('bookingForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    try {
        const response = await fetch('../api/bookings/create.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert(result.message);
            bootstrap.Modal.getInstance(document.getElementById('bookingModal')).hide();
            loadAvailableRides();
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al procesar la reserva');
    }
});

// Cancelar reserva
async function cancelBooking(bookingId) {
    if (!confirm('¿Estás seguro de cancelar esta reserva?')) {
        return;
    }
    
    try {
        const response = await fetch('../api/bookings/cancel.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'booking_id=' + bookingId
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert(result.message);
            loadMyBookings();
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al cancelar la reserva');
    }
}

// Cargar viajes al inicio
loadAvailableRides();