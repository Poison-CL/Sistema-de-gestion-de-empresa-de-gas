<?php session_start(); ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Ingreso - Distribuidora de Gas</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Bootstrap, FontAwesome, Google Font -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/estilos.css">
</head>
<body class="d-flex justify-content-center align-items-center vh-100" style="font-family: 'Roboto', sans-serif; background-color: #f8f9fa;">

  <!-- 🟩 Formulario de Login -->
  <div class="card shadow p-4" style="min-width: 300px; max-width: 400px;">
    <div class="text-center mb-3">
      <h4><i class="fas fa-gas-pump me-2"></i>Distribuidora de Gas</h4>
    </div>
    <form id="formLogin" method="POST" action="login.php">
      <div class="mb-3">
        <label for="usuario" class="form-label">Usuario</label>
        <input type="text" class="form-control" id="usuario" name="usuario" required>
      </div>
      <div class="mb-3">
        <label for="clave" class="form-label">Contraseña</label>
        <input type="password" class="form-control" id="clave" name="clave" required>
      </div>
      <button type="submit" class="btn btn-primary w-100" id="btnIngresar" disabled>Ingresar</button>
    </form>
  </div>

  <!-- 🟥 Modal de error -->
  <div class="modal fade" id="modalError" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>Error</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body" id="mensajeModal">
          Usuario o contraseña incorrectos.
        </div>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Validación en vivo
    $('#usuario, #clave').on('input', function() {
      const user = $('#usuario').val().trim();
      const pass = $('#clave').val().trim();
      $('#btnIngresar').prop('disabled', !(user && pass));
    });

    // Mostrar modal si viene con error por GET
    $(document).ready(function() {
      const urlParams = new URLSearchParams(window.location.search);
      if (urlParams.get('error') === '1') {
        const modal = new bootstrap.Modal(document.getElementById('modalError'));
        modal.show();
      }
    });
  </script>
</body>
</html>
