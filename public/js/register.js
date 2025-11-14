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

document.getElementById('registerForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    // Validar que las contraseñas coincidan
    const password = formData.get('password');
    const passwordConfirm = formData.get('password_confirm');
    
    if (password !== passwordConfirm) {
        showAlert('Las contraseñas no coinciden. Por favor, verifica e intenta nuevamente.', 'danger');
        return;
    }
    
    try {
        const response = await fetch('api/register.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showAlert(result.message, 'success');
            // Esperar 2 segundos antes de redirigir
            setTimeout(() => {
                window.location.href = 'login.php';
            }, 2000);
        } else {
            showAlert('Error: ' + result.message, 'danger');
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('Error al procesar el registro. Por favor, intenta nuevamente.', 'danger');
    }
});