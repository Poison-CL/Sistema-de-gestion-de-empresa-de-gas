<?php
session_start();
require_once '../Includes/db.php';

// Insertar descuento
if (isset($_POST['accion']) && $_POST['accion'] === 'agregar') {
  $descuento = $_POST['descuento'];
  $descripcion = $_POST['descripcion'];

  $query = "INSERT INTO descuentos (descuento, descripcion) VALUES (?, ?)";
  $stmt = $conn->prepare($query);
  $stmt->bind_param("is", $descuento, $descripcion);
  $stmt->execute();
  exit;
}

// Eliminar descuento
if (isset($_POST['accion']) && $_POST['accion'] === 'eliminar') {
  $id = $_POST['id'];
  $conn->query("DELETE FROM descuentos WHERE id = $id");
  exit;
}

// Obtener descuentos
$descuentos = $conn->query("SELECT * FROM descuentos ORDER BY id DESC");
?>

<div class="container">
  <h4 class="mb-4">Gestión de Descuentos</h4>

  <!-- 🟢 Formulario -->
  <form id="formDescuento" class="row g-3 mb-4">
    <input type="hidden" name="accion" value="agregar">
    <div class="col-md-3">
      <input type="number" name="descuento" id="descuento" class="form-control" placeholder="Descuento ($)" min="1" required>
    </div>
    <div class="col-md-7">
      <input type="text" name="descripcion" id="descripcion" class="form-control" placeholder="Descripción del descuento" required>
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
          <th>Descuento</th>
          <th>Descripción</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody id="tablaDescuentos">
        <?php while ($row = $descuentos->fetch_assoc()): ?>
          <tr>
            <td>$<?= number_format($row['descuento'], 0) ?></td>
            <td><?= $row['descripcion'] ?></td>
            <td>
              <button class="btn btn-sm btn-danger" onclick="eliminarDescuento(<?= $row['id'] ?>)">
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
  $('#descuento, #descripcion').on('input', function () {
    const d = $('#descuento').val().trim();
    const desc = $('#descripcion').val().trim();
    $('#btnGuardar').prop('disabled', !(d && desc));
  });

  // Enviar formulario
  $('#formDescuento').on('submit', function(e) {
    e.preventDefault();
    $.post('modulos/descuentos.php', $(this).serialize(), function() {
      cargarModulo('descuentos');
    });
  });

  // Eliminar descuento
  function eliminarDescuento(id) {
    if (confirm('¿Eliminar este descuento?')) {
      $.post('modulos/descuentos.php', {accion: 'eliminar', id}, function() {
        cargarModulo('descuentos');
      });
    }
  }
</script>
