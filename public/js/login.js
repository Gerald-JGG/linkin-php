document.getElementById('loginForm').addEventListener('submit', async function (e) {
    e.preventDefault();
    
    const formData = new FormData(this);

    try {
        const response = await fetch('api/login.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            // Ya no redirigimos por rol.
            // El dashboard lee los roles desde $_SESSION.
            window.location.href = 'dashboard.php';
        } else {
            alert('Error: ' + result.message);
        }

    } catch (error) {
        console.error('Error:', error);
        alert('Error al iniciar sesión');
    }
});
