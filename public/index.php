<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Aventones - Viaja compartido, ahorra y conecta</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="css/styles.css" />
</head>

<body>
  <header>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-custom">
      <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">
          <img src="uploads/img/car-icon.png" alt="Logo" style="width: 32px; height: 32px; margin-right: 8px" />
          Aventones
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
          <ul class="navbar-nav ms-auto">
            <li class="nav-item">
              <a class="nav-link" href="login.php">Iniciar Sesión</a>
            </li>
            <li class="nav-item">
              <a class="btn btn-light ms-2" href="register.php">Registrarse</a>
            </li>
          </ul>
        </div>
      </div>
    </nav>
  </header>

  <!-- Hero Section -->
  <section class="hero-section">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-lg-6">
          <h1 class="display-4 fw-bold mb-4">
            Comparte tus viajes, ahorra dinero
          </h1>
          <p class="lead mb-4">
            Conecta con personas que van a tu mismo destino. Comparte gastos
            de gasolina y contribuye al medio ambiente.
          </p>
          <div class="d-flex gap-3">
            <a href="register.php" class="btn btn-light btn-lg">Registrarme como Pasajero</a>
            <a href="register.php" class="btn btn-outline-light btn-lg">Ofrecer Viajes</a>
          </div>
        </div>
        <div class="col-lg-6 text-center">
          <div class="mt-5 mt-lg-0">
            <h2 class="display-1">
              <img src="uploads/img/car-icon.png" alt="Carro" style="width: 100px; height: auto" />
            </h2>
            <p class="fs-5">Miles de viajes disponibles</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Cómo Funciona -->
  <section id="como-funciona" class="py-5">
    <div class="container">
      <h2 class="text-center mb-5">¿Cómo funciona?</h2>
      <div class="row g-4">
        <div class="col-md-4">
          <div class="card card-custom text-center p-4">
            <div class="fs-1 mb-3">👤</div>
            <h4>1. Regístrate</h4>
            <p>
              Crea tu cuenta gratis en menos de 2 minutos. Solo necesitas tu
              información básica.
            </p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card card-custom text-center p-4">
            <div class="fs-1 mb-3">🔍</div>
            <h4>2. Busca o Publica</h4>
            <p>Busca viajes disponibles o publica tu ruta si eres chofer.</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card card-custom text-center p-4">
            <div class="fs-1 mb-3">✅</div>
            <h4>3. Reserva y Viaja</h4>
            <p>
              Reserva tu espacio, coordina con el chofer y disfruta del viaje.
            </p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Call to Action -->
  <section class="py-5">
    <div class="container text-center">
      <h2 class="mb-4">¿Listo para comenzar?</h2>
      <p class="lead mb-4">Únete a la comunidad de Aventones hoy mismo</p>
      <a href="register.php" class="btn btn-primary-custom btn-lg">Crear Cuenta Gratis</a>
    </div>
  </section>

  <!-- Footer -->
  <footer class="bg-dark text-white py-4">
    <div class="container text-center">
      <p class="mb-0">&copy; 2025 Aventones - Código abierto</p>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>