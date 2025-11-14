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

// Abrir modal para CREAR usuario
function openCreateUserModal() {
    document.getElementById('userModalTitle').textContent = 'Crear Usuario';
    document.getElementById('userSubmitBtn').textContent = 'Crear Usuario';
    document.getElementById('user_action').value = 'create';
    document.getElementById('user_id').value = '';
    
    // Limpiar formulario
    document.getElementById('userForm').reset();
    
    // Hacer contraseña obligatoria
    document.getElementById('password').required = true;
    
    // Desmarcar roles
    document.querySelectorAll('input[name="roles[]"]').forEach(cb => cb.checked = false);
}

// Abrir modal para EDITAR usuario
async function openEditUserModal(userId) {
    try {
        document.getElementById('userModalTitle').textContent = 'Editar Usuario';
        document.getElementById('userSubmitBtn').textContent = 'Guardar Cambios';
        document.getElementById('user_action').value = 'update';
        document.getElementById('user_id').value = userId;
        
        // Hacer contraseña opcional en edición
        document.getElementById('password').required = false;
        
        // Obtener datos del usuario
        const response = await fetch(`../api/users/get.php?id=${userId}`);
        const result = await response.json();
        
        if (result.success) {
            const user = result.user;
            
            // Llenar formulario
            document.getElementById('first_name').value = user.first_name;
            document.getElementById('last_name').value = user.last_name;
            document.getElementById('cedula').value = user.cedula;
            document.getElementById('birth_date').value = user.birth_date;
            document.getElementById('email').value = user.email;
            document.getElementById('phone').value = user.phone;
            document.getElementById('username').value = user.username;
            document.getElementById('is_active').value = user.is_active;
            document.getElementById('password').value = '';
            
            // Marcar roles
            document.querySelectorAll('input[name="roles[]"]').forEach(cb => cb.checked = false);
            if (result.roles && result.roles.length > 0) {
                result.roles.forEach(role => {
                    const checkbox = document.getElementById('role_' + getRoleSlug(role.role_id));
                    if (checkbox) checkbox.checked = true;
                });
            }
            
            // Mostrar modal
            new bootstrap.Modal(document.getElementById('userModal')).show();
        } else {
            showAlert('Error al cargar datos del usuario', 'danger');
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('Error al cargar el usuario', 'danger');
    }
}

// Helper para obtener slug del rol
function getRoleSlug(roleId) {
    const roles = {
        1: 'admin',
        2: 'passenger',
        3: 'driver'
    };
    return roles[roleId] || '';
}

// Cargar usuarios
async function loadUsers() {
    try {
        const response = await fetch('../api/users/list.php');
        const result = await response.json();
        
        const tbody = document.querySelector('#users-table tbody');
        
        if (result.success && result.users.length > 0) {
            tbody.innerHTML = result.users.map(user => {
                const statusBadge = user.is_active == 1 
                    ? '<span class="badge bg-success">Activo</span>' 
                    : '<span class="badge bg-secondary">Inactivo</span>';
                
                return `
                <tr>
                    <td>${user.id}</td>
                    <td>${user.first_name} ${user.last_name}</td>
                    <td>${user.email}</td>
                    <td>${user.username}</td>
                    <td>${user.cedula}</td>
                    <td>${user.phone || 'N/A'}</td>
                    <td><small>${user.roles || 'Sin roles'}</small></td>
                    <td>${statusBadge}</td>
                    <td>
                        <div class="btn-group btn-group-sm" role="group">
                            <button class="btn btn-outline-primary" onclick="openEditUserModal(${user.id})" title="Editar">
                                ✏️
                            </button>
                            <button class="btn btn-outline-danger" onclick="deleteUser(${user.id}, '${user.username}')" title="Eliminar">
                                🗑️
                            </button>
                        </div>
                    </td>
                </tr>
            `;
            }).join('');
        } else {
            tbody.innerHTML = '<tr><td colspan="9" class="text-center text-muted">No hay usuarios registrados</td></tr>';
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('Error al cargar usuarios', 'danger');
    }
}

// Enviar formulario de usuario
document.addEventListener('DOMContentLoaded', function() {
    const userForm = document.getElementById('userForm');
    if (userForm) {
        userForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const action = document.getElementById('user_action').value;
            
            // Validar que al menos un rol esté seleccionado
            const roles = formData.getAll('roles[]');
            if (roles.length === 0) {
                showAlert('Debe seleccionar al menos un rol', 'warning');
                return;
            }
            
            // Si es creación, validar contraseña
            if (action === 'create' && !formData.get('password')) {
                showAlert('La contraseña es obligatoria', 'warning');
                return;
            }
            
            try {
                const url = action === 'update' ? '../api/users/update.php' : '../api/users/create.php';
                
                const response = await fetch(url, {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showAlert(result.message, 'success');
                    bootstrap.Modal.getInstance(document.getElementById('userModal')).hide();
                    this.reset();
                    loadUsers();
                } else {
                    showAlert('Error: ' + result.message, 'danger');
                }
            } catch (error) {
                console.error('Error:', error);
                showAlert('Error al procesar el usuario', 'danger');
            }
        });
    }
});

// Eliminar usuario
async function deleteUser(userId, username) {
    if (!confirm(`¿Estás seguro de eliminar al usuario "${username}"?\n\nEsta acción no se puede deshacer y eliminará todos los datos asociados (vehículos, viajes, reservas).`)) {
        return;
    }
    
    try {
        const response = await fetch('../api/users/delete.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'user_id=' + userId
        });
        
        const result = await response.json();
        
        if (result.success) {
            showAlert(result.message, 'success');
            loadUsers();
        } else {
            showAlert('Error: ' + result.message, 'danger');
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('Error al eliminar el usuario', 'danger');
    }
}