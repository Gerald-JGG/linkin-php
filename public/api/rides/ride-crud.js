// Función para mostrar mensajes estéticos
function showAlert(message, type = 'success') {
    const alertHTML = `
        <div class="alert alert-${type} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3" 
             style="z-index: 9999; min-width: 300px;" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    
    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = alertHTML;
    document.body.appendChild(tempDiv.firstElementChild);
    
    setTimeout(() => {
        const alert = document.querySelector('.alert');
        if (alert) {
            const bsAlert = bootstrap.Alert.getInstance(alert);
            if (bsAlert) bsAlert.close();
            else alert.remove();
        }
    }, 5000);
}

// Abrir modal para CREAR viaje
function openCreateRideModal() {
    // Cambiar título y botón
    document.getElementById('rideModalTitle').textContent = 'Crear Viaje';
    document.getElementById('rideSubmitBtn').textContent = 'Crear Viaje';
    document.getElementById('ride_action').value = 'create';
    document.getElementById('ride_id').value = '';
    
    // Limpiar formulario
    document.getElementById('rideForm').reset();
    
    // Desmarcar todos los checkboxes
    document.querySelectorAll('input[name="weekdays[]"]').forEach(cb => cb.checked = false);
    
    // Cargar vehículos aprobados
    loadApprovedVehiclesForRide();
}

// Abrir modal para EDITAR viaje
async function openEditRideModal(rideId) {
    try {
        // Cambiar título y botón
        document.getElementById('rideModalTitle').textContent = 'Editar Viaje';
        document.getElementById('rideSubmitBtn').textContent = 'Guardar Cambios';
        document.getElementById('ride_action').value = 'update';
        document.getElementById('ride_id').value = rideId;
        
        // Obtener datos del viaje
        const response = await fetch(`../api/rides/get.php?id=${rideId}`);
        const result = await response.json();
        
        if (result.success) {
            const ride = result.ride;
            
            // Cargar vehículos primero
            await loadApprovedVehiclesForRide();
            
            // Llenar formulario con datos del viaje
            document.getElementById('ride_vehicle_select').value = ride.vehicle_id;
            document.getElementById('ride_name').value = ride.ride_name;
            document.getElementById('departure_location').value = ride.departure_location;
            document.getElementById('departure_time').value = ride.departure_time;
            document.getElementById('arrival_location').value = ride.arrival_location;
            document.getElementById('arrival_time').value = ride.arrival_time;
            document.getElementById('price_per_seat').value = ride.price_per_seat;
            document.getElementById('total_seats').value = ride.total_seats;
            
            // Marcar días de la semana
            const weekdays = ride.weekdays.split(',');
            document.querySelectorAll('input[name="weekdays[]"]').forEach(cb => {
                cb.checked = weekdays.includes(cb.value);
            });
            
            // Mostrar modal
            new bootstrap.Modal(document.getElementById('rideModal')).show();
        } else {
            showAlert('Error al cargar datos del viaje', 'danger');
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('Error al cargar el viaje', 'danger');
    }
}

// Cargar vehículos aprobados para el select
async function loadApprovedVehiclesForRide() {
    try {
        const response = await fetch('../api/vehicles/approved.php');
        const result = await response.json();
        
        const select = document.getElementById('ride_vehicle_select');
        
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
        showAlert('Error al cargar vehículos', 'danger');
    }
}

// Enviar formulario de viaje (Crear o Editar)
document.addEventListener('DOMContentLoaded', function() {
    const rideForm = document.getElementById('rideForm');
    if (rideForm) {
        rideForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const action = document.getElementById('ride_action').value;
            
            // Validar días de la semana
            const weekdays = [];
            document.querySelectorAll('input[name="weekdays[]"]:checked').forEach(cb => {
                weekdays.push(cb.value);
            });
            
            if (weekdays.length === 0) {
                showAlert('Debe seleccionar al menos un día de la semana', 'warning');
                return;
            }
            
            formData.delete('weekdays[]');
            formData.append('weekdays', weekdays.join(','));
            
            try {
                const url = action === 'update' ? '../api/rides/update.php' : '../api/rides/create.php';
                
                const response = await fetch(url, {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showAlert(result.message, 'success');
                    bootstrap.Modal.getInstance(document.getElementById('rideModal')).hide();
                    this.reset();
                    loadMyRides();
                } else {
                    showAlert('Error: ' + result.message, 'danger');
                }
            } catch (error) {
                console.error('Error:', error);
                showAlert('Error al procesar el viaje', 'danger');
            }
        });
    }
});

// Cargar mis viajes
async function loadMyRides() {
    try {
        const response = await fetch('../api/rides/my-rides.php');
        const result = await response.json();
        
        const container = document.getElementById('rides-list');
        
        if (result.success && result.rides.length > 0) {
            container.innerHTML = result.rides.map(ride => {
                // Traducir días al español
                const daysTranslation = {
                    'monday': 'Lunes',
                    'tuesday': 'Martes',
                    'wednesday': 'Miércoles',
                    'thursday': 'Jueves',
                    'friday': 'Viernes',
                    'saturday': 'Sábado',
                    'sunday': 'Domingo'
                };
                
                const daysArray = ride.weekdays.split(',');
                const daysInSpanish = daysArray.map(day => daysTranslation[day] || day).join(', ');
                
                return `
                <div class="card mb-3 ride-card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <h5 class="mb-3">${ride.ride_name}</h5>
                                <div class="row mb-2">
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>📍 Salida:</strong> ${ride.departure_location}</p>
                                        <p class="mb-1"><strong>🕐 Hora:</strong> ${ride.departure_time}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>🎯 Llegada:</strong> ${ride.arrival_location}</p>
                                        <p class="mb-1"><strong>🕐 Hora:</strong> ${ride.arrival_time}</p>
                                    </div>
                                </div>
                                <p class="mb-1"><strong>📅 Días:</strong> ${daysInSpanish}</p>
                                <p class="mb-0"><strong>🚗 Vehículo:</strong> ${ride.brand} ${ride.model} (${ride.plate})</p>
                            </div>
                            <div class="col-md-4 text-end">
                                <p class="mb-2"><strong>💰 Tarifa:</strong> ₡${parseFloat(ride.price_per_seat).toLocaleString('es-CR', {minimumFractionDigits: 2})}</p>
                                <p class="mb-3"><strong>🪑 Asientos:</strong> ${ride.available_seats}/${ride.total_seats} disponibles</p>
                                <div class="btn-group w-100 mb-2" role="group">
                                    <button class="btn btn-sm btn-primary" onclick="openEditRideModal(${ride.id})">
                                        ✏️ Editar
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteRide(${ride.id}, '${ride.ride_name}')">
                                        🗑️ Eliminar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            }).join('');
        } else {
            container.innerHTML = '<p class="text-center text-muted">No tienes viajes registrados. ¡Crea tu primer viaje!</p>';
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('Error al cargar viajes', 'danger');
    }
}

// Eliminar viaje
async function deleteRide(rideId, rideName) {
    if (!confirm(`¿Estás seguro de eliminar el viaje "${rideName}"?\n\nEsta acción no se puede deshacer.`)) {
        return;
    }
    
    try {
        const response = await fetch('../api/rides/delete.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'ride_id=' + rideId
        });
        
        const result = await response.json();
        
        if (result.success) {
            showAlert(result.message, 'success');
            loadMyRides();
        } else {
            showAlert('Error: ' + result.message, 'danger');
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('Error al eliminar el viaje', 'danger');
    }
}