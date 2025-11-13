document.getElementById('loginForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    try {
        const response = await fetch('api/login.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Redirigir según el rol
            const roles = result.roles || [];
            const roleIds = roles.map(r => r.role_id);
            
            if (roleIds.includes(1)) {
                // Administrador
                window.location.href = 'dashboard/admin.php';
            } else if (roleIds.includes(3)) {
                // Chofer (también puede ser pasajero)
                window.location.href = 'dashboard/driver.php';
            } else {
                // Pasajero
                window.location.href = 'dashboard/passenger.php';
            }
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al iniciar sesión');
    }
});