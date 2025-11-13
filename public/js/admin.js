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
                loadPendingVehicles();
            } else if (section === 'users') {
                loadUsers();
            }
        }
    });
});

// Cargar vehículos pendientes
async function loadPendingVehicles() {
    try {
        const response = await fetch('../api/vehicles/pending.php');
        const result = await response.json();
        
        const container = document.getElementById('pending-vehicles');
        
        if (result.success && result.vehicles.length > 0) {
            container.innerHTML = result.vehicles.map(vehicle => `
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <h5>${vehicle.brand} ${vehicle.model} (${vehicle.year})</h5>
                                <p class="mb-1"><strong>Propietario:</strong> ${vehicle.first_name} ${vehicle.last_name}</p>
                                <p class="mb-1"><strong>Email:</strong> ${vehicle.email}</p>
                                <p class="mb-1"><strong>Color:</strong> ${vehicle.color}</p>
                                <p class="mb-1"><strong>Placa:</strong> ${vehicle.plate}</p>
                                <p class="mb-1"><strong>Fecha de Solicitud:</strong> ${vehicle.created_at}</p>
                            </div>
                            <div class="col-md-4 text-end">
                                <button class="btn btn-success mb-2 w-100" onclick="approveVehicle(${vehicle.id})">
                                    Aprobar
                                </button>
                                <button class="btn btn-danger w-100" onclick="openRejectModal(${vehicle.id})">
                                    Rechazar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `).join('');
        } else {
            container.innerHTML = '<p class="text-center">No hay vehículos pendientes de aprobación</p>';
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

// Aprobar vehículo
async function approveVehicle(vehicleId) {
    if (!confirm('¿Está seguro de aprobar este vehículo?')) {
        return;
    }
    
    try {
        const response = await fetch('../api/vehicles/approve.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'vehicle_id=' + vehicleId
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert(result.message);
            loadPendingVehicles();
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

// Abrir modal de rechazo
function openRejectModal(vehicleId) {
    document.getElementById('reject_vehicle_id').value = vehicleId;
    new bootstrap.Modal(document.getElementById('rejectModal')).show();
}

// Rechazar vehículo
document.getElementById('rejectForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    try {
        const response = await fetch('../api/vehicles/reject.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert(result.message);
            bootstrap.Modal.getInstance(document.getElementById('rejectModal')).hide();
            this.reset();
            loadPendingVehicles();
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        console.error('Error:', error);
    }
});

// Cargar usuarios
async function loadUsers() {
    try {
        const response = await fetch('../api/users/list.php');
        const result = await response.json();
        
        const tbody = document.querySelector('#users-table tbody');
        
        if (result.success && result.users.length > 0) {
            tbody.innerHTML = result.users.map(user => `
                <tr>
                    <td>${user.id}</td>
                    <td>${user.first_name} ${user.last_name}</td>
                    <td>${user.email}</td>
                    <td>${user.username}</td>
                    <td>${user.roles || 'Sin roles'}</td>
                    <td>${user.created_at}</td>
                </tr>
            `).join('');
        } else {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center">No hay usuarios</td></tr>';
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

// Cargar vehículos pendientes al inicio
loadPendingVehicles();