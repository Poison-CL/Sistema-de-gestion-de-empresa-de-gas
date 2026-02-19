<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Panel - Distribuidora de Gas</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Bootstrap, FontAwesome, Google Font -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/estilos.css">
</head>
<body style="font-family: 'Roboto', sans-serif;">

  <!-- 🟦 Navbar -->
  <?php include "Includes/navbar.php"; ?>

  <div class="container-fluid">
    <div class="row">
      <!-- 🟧 Panel lateral -->
      <div class="col-md-2 bg-light border-end vh-100" id="sidebar">
        <ul class="nav flex-column p-3">
          <li class="nav-item mb-2">
            <a href="#" class="nav-link text-dark" onclick="cargarModulo('dashboard')">
              <i class="fas fa-home me-2"></i> Dashboard
            </a>
          </li>
          <?php if ($_SESSION['rango'] === 'administrador'): ?>
          <li class="nav-item mb-2">
            <a href="#" class="nav-link text-dark" onclick="cargarModulo('admin')">
              <i class="fas fa-cogs me-2"></i> Administración
            </a>
          </li>
          <?php endif; ?>
        </ul>
      </div>

      <!-- 🟩 Contenido dinámico -->
      <div class="col-md-10 p-4" id="contenido">
        <!-- Aquí se cargan los módulos dinámicos -->
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="js/script.js"></script>

  <script>
    // Carga el módulo por defecto
    $(document).ready(function() {
      cargarModulo('dashboard');
    });

    function cargarModulo(nombre) {
      $('#contenido').load(`modulos/${nombre}.php`);
    }
  </script>
</body>
</html>
