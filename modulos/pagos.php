<?php
session_start();
require_once '../Includes/db.php';

// Insertar medio de pago
if (isset($_POST['accion']) && $_POST['accion'] === 'agregar') {
  $medio = $_POST['medio_pago'];
  $query = "INSERT INTO medios_pago (medio_pago) VALUES (?)";
  $stmt = $conn->prepare($query);
  $stmt->bind_param("s", $medio);
  $stmt->execute();
  exit;
}

// Eliminar medio de pago
if (isset($_POST['accion']) && $_POST['accion'] === 'eliminar') {
  $id = $_POST['id'];
  $conn->query("DELETE FROM medios_pago WHERE id = $id");
  exit;
}

// Obtener medios de pago
$medios = $conn->query("SELECT * FROM medios_pago ORDER BY id DESC");
?>

<div class="container">
  <h4 class="mb-4">Gestión de Medios de Pago</h4>

  <!-- 🟢 Formulario -->
  <form id="formPago" class="row g-3 mb-4">
    <input type="hidden" name="accion" value="agregar">
    <div class="col-md-10">
      <input type="text" name="medio_pago" id="medio_pago" class="form-control" placeholder="Ej: Efectivo, Débito, Transferencia" required>
    </div>
    <div class="col-md-2">
      <button type="submit" class="btn btn-success w-100" id="btnGuardar" disabled><i class="fas fa-plus"></i></button>
    </div>
  </form>

  <!-- 🟡 Tabla dinámica -->
  <div class="table-responsive">
    <table class="table table-bordered text-center align-middle">
      <thead class="table-dark">
        <tr>
          <th>Medio de Pago</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody id="tablaMedios">
        <?php while ($row = $medios->fetch_assoc()): ?>
          <tr>
            <td><?= $row['medio_pago'] ?></td>
            <td>
              <button class="btn btn-sm btn-danger" onclick="eliminarMedio(<?= $row['id'] ?>)">
                <i class="fas fa-trash"></i>
              </button>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
  // Validación en vivo
  $('#medio_pago').on('input', function () {
    $('#btnGuardar').prop('disabled', !$(this).val().trim());
  });

  // Enviar formulario
  $('#formPago').on('submit', function(e) {
    e.preventDefault();
    $.post('modulos/pagos.php', $(this).serialize(), function() {
      cargarModulo('pagos');
    });
  });

  // Eliminar medio
  function eliminarMedio(id) {
    if (confirm('¿Eliminar este medio de pago?')) {
      $.post('modulos/pagos.php', {accion: 'eliminar', id}, function() {
        cargarModulo('pagos');
      });
    }
  }
</script>
