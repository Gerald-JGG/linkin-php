// Navegación entre secciones
document.querySelectorAll('.sidebar .nav-link').forEach(link => {
    link.addEventListener('click', function(e) {
        if (this.getAttribute('data-section')) {
            e.preventDefault();
            
            document.querySelectorAll('.sidebar .nav-link').forEach(l => l.classList.remove('active'));
            this.classList.add('active');
            
            const section = this.getAttribute('data-section');
            document.querySelectorAll('.content-section').forEach(s => s.classList.add('d-none'));
            document.getElementById('section-' + section).classList.remove('d-none');
            
            if (section === 'vehicles') {
                loadVehicles();
            } else if (section === 'rides') {
                loadMyRides();
                loadApprovedVehicles();
            } else if (section === 'bookings') {
                loadPendingBookings();
            }
        }
    });
});

// Cargar vehículos
async function loadVehicles() {
    try {
        const response = await fetch('../api/vehicles/my-vehicles.php');
        
        // DEBUG: Ver el contenido de la respuesta
        const text = await response.text();
        console.log('Respuesta raw:', text);
        
        // Intentar parsear como JSON
        let result;
        try {
            result = JSON.parse(text);
        } catch (e) {
            console.error('Error al parsear JSON:', e);
            console.error('Contenido recibido:', text);
            alert('Error del servidor. Revisa la consola para más detalles.');
            return;
        }
        
        const container = document.getElementById('vehicles-list');
        
        if (result.success && result.vehicles.length > 0) {
            container.innerHTML = result.vehicles.map(vehicle => {
                const statusBadge = {
                    'pending': 'badge-pending',
                    'approved': 'badge-approved',
                    'rejected': 'badge-rejected'
                };
                
                return `
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h5 class="card-title">${vehicle.brand} ${vehicle.model}</h5>
                                    <span class="badge ${statusBadge[vehicle.status]}">${vehicle.status}</span>
                                </div>
                                <p class="mb-1"><strong>Año:</strong> ${vehicle.year}</p>
                                <p class="mb-1"><strong>Color:</strong> ${vehicle.color}</p>
                                <p class="mb-1"><strong>Placa:</strong> ${vehicle.plate}</p>
                                ${vehicle.status === 'pending' ? '<p class="text-warning small">Pendiente de aprobación</p>' : ''}
                                ${vehicle.status === 'rejected' ? `<p class="text-danger small">Motivo: ${vehicle.rejection_reason || 'N/A'}</p>` : ''}
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        } else {
            container.innerHTML = '<div class="col-12"><p class="text-center">No tienes vehículos registrados</p></div>';
        }
    } catch (error) {
        console.error('Error completo:', error);
        alert('Error al cargar vehículos: ' + error.message);
    }
}

// Registrar vehículo
document.getElementById('vehicleForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    try {
        const response = await fetch('../api/vehicles/create.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert(result.message);
            bootstrap.Modal.getInstance(document.getElementById('vehicleModal')).hide();
            this.reset();
            loadVehicles();
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al registrar vehículo');
    }
});

// Cargar vehículos aprobados para el select
async function loadApprovedVehicles() {
    try {
        const response = await fetch('../api/vehicles/approved.php');
        const result = await response.json();
        
        const select = document.getElementById('vehicle_select');
        
        if (result.success && result.vehicles.length > 0) {
            select.innerHTML = '<option value="">Seleccione un vehículo</option>' + 
                result.vehicles.map(v => 
                    `<option value="${v.id}">${v.brand} ${v.model} - ${v.plate}</option>`
                ).join('');
        } else {
            select.innerHTML = '<option value="">No tiene vehículos aprobados</option>';
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

// Cargar mis viajes
async function loadMyRides() {
    try {
        const response = await fetch('../api/rides/my-rides.php');
        const result = await response.json();
        
        const container = document.getElementById('rides-list');
        
        if (result.success && result.rides.length > 0) {
            container.innerHTML = result.rides.map(ride => `
                <div class="card mb-3 ride-card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <h5>${ride.ride_name}</h5>
                                <p class="mb-1"><strong>De:</strong> ${ride.departure_location} (${ride.departure_time})</p>
                                <p class="mb-1"><strong>A:</strong> ${ride.arrival_location} (${ride.arrival_time})</p>
                                <p class="mb-1"><strong>Días:</strong> ${ride.weekdays}</p>
                                <p class="mb-1"><strong>Vehículo:</strong> ${ride.brand} ${ride.model} (${ride.plate})</p>
                            </div>
                            <div class="col-md-4 text-end">
                                <p class="mb-1"><strong>Precio:</strong> ₡${ride.price_per_seat}</p>
                                <p class="mb-1"><strong>Asientos:</strong> ${ride.available_seats}/${ride.total_seats}</p>
                            </div>
                        </div>
                    </div>
                </div>
            `).join('');
        } else {
            container.innerHTML = '<p class="text-center">No tienes viajes registrados</p>';
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

// Crear viaje
document.getElementById('rideForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    // Convertir checkboxes de días en una cadena separada por comas
    const weekdays = [];
    document.querySelectorAll('input[name="weekdays[]"]:checked').forEach(cb => {
        weekdays.push(cb.value);
    });
    
    if (weekdays.length === 0) {
        alert('Debe seleccionar al menos un día de la semana');
        return;
    }
    
    formData.delete('weekdays[]');
    formData.append('weekdays', weekdays.join(','));
    
    try {
        const response = await fetch('../api/rides/create.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert(result.message);
            bootstrap.Modal.getInstance(document.getElementById('rideModal')).hide();
            this.reset();
            loadMyRides();
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al crear viaje');
    }
});

// Cargar reservas pendientes
async function loadPendingBookings() {
    try {
        const response = await fetch('../api/bookings/pending.php');
        const result = await response.json();
        
        const container = document.getElementById('pending-bookings');
        
        if (result.success && result.bookings.length > 0) {
            container.innerHTML = result.bookings.map(booking => `
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h5>${booking.ride_name}</h5>
                                <p class="mb-1"><strong>Pasajero:</strong> ${booking.first_name} ${booking.last_name}</p>
                                <p class="mb-1"><strong>Email:</strong> ${booking.email}</p>
                                <p class="mb-1"><strong>Teléfono:</strong> ${booking.phone}</p>
                                <p class="mb-1"><strong>Asientos solicitados:</strong> ${booking.seats_requested}</p>
                                <p class="mb-1"><strong>Fecha:</strong> ${booking.booking_date}</p>
                            </div>
                            <div>
                                <button class="btn btn-success btn-sm mb-2 w-100" onclick="acceptBooking(${booking.id})">Aceptar</button>
                                <button class="btn btn-danger btn-sm w-100" onclick="rejectBooking(${booking.id})">Rechazar</button>
                            </div>
                        </div>
                    </div>
                </div>
            `).join('');
        } else {
            container.innerHTML = '<p class="text-center">No hay reservas pendientes</p>';
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

// Aceptar reserva
async function acceptBooking(bookingId) {
    try {
        const response = await fetch('../api/bookings/accept.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'booking_id=' + bookingId
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert(result.message);
            loadPendingBookings();
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

// Rechazar reserva
async function rejectBooking(bookingId) {
    if (!confirm('¿Estás seguro de rechazar esta reserva?')) {
        return;
    }
    
    try {
        const response = await fetch('../api/bookings/reject.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'booking_id=' + bookingId
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert(result.message);
            loadPendingBookings();
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

// Cargar vehículos al inicio
loadVehicles();