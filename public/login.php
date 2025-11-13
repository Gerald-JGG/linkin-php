<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Aventones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-5">
                <div class="card card-custom shadow-lg">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <h2 class="fw-bold"><img src="assets/img/car-icon.png" alt="Carro" style="width: 50px; height: auto" /> Aventones</h2>
                            <p class="text-muted">Inicia sesión en tu cuenta</p>
                        </div>

                        <form id="loginForm">
                            <div class="mb-3">
                                <label for="username" class="form-label">Usuario</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Contraseña</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>

                            <button type="submit" class="btn btn-primary-custom w-100 mb-3">
                                Iniciar Sesión
                            </button>

                            <div class="text-center">
                                <p class="mb-0">¿No tienes cuenta? <a href="register.php">Regístrate aquí</a></p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/login.js"></script>
</body>
</html>


