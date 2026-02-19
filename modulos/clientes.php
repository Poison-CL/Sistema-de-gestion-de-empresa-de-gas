<?php
session_start();
require_once '../Includes/db.php';

// Insertar cliente
if (isset($_POST['accion']) && $_POST['accion'] === 'agregar') {
  $telefono = $_POST['telefono'];
  $nombre = $_POST['nombre'];
  $direccion = $_POST['direccion'];

  $query = "INSERT INTO clientes (telefono, nombre, direccion) VALUES (?, ?, ?)";
  $stmt = $conn->prepare($query);
  $stmt->bind_param("sss", $telefono, $nombre, $direccion);
  $stmt->execute();
  exit;
}

// Eliminar cliente
if (isset($_POST['accion']) && $_POST['accion'] === 'eliminar') {
  $id = $_POST['id'];
  $conn->query("DELETE FROM clientes WHERE id = $id");
  exit;
}

// Obtener lista de clientes
$clientes = $conn->query("SELECT id, telefono, nombre, direccion FROM clientes ORDER BY id DESC");
?>

<div class="container">
  <h4 class="mb-4">Gestión de Clientes</h4>

  <!-- 🟢 Formulario -->
  <form id="formCliente" class="row g-3 mb-4">
    <input type="hidden" name="accion" value="agregar">
    <div class="col-md-3">
      <input type="text" name="telefono" id="telefono" class="form-control" placeholder="Teléfono" required>
    </div>
    <div class="col-md-4">
      <input type="text" name="nombre" id="nombre" class="form-control" placeholder="Nombre" required>
    </div>
    <div class="col-md-4">
      <input type="text" name="direccion" id="direccion" class="form-control" placeholder="Dirección" required>
    </div>
    <div class="col-md-1">
      <button type="submit" class="btn btn-success w-100" id="btnGuardar" disabled><i class="fas fa-plus"></i></button>
    </div>
  </form>

  <!-- 🟡 Tabla dinámica -->
  <div class="table-responsive">
    <table class="table table-bordered align-middle text-center">
      <thead class="table-dark">
        <tr>
          <th>Teléfono</th>
          <th>Nombre</th>
          <th>Dirección</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody id="tablaClientes">
        <?php while ($row = $clientes->fetch_assoc()): ?>
          <tr>
            <td><?= $row['telefono'] ?></td>
            <td><?= $row['nombre'] ?></td>
            <td><?= $row['direccion'] ?></td>
            <td>
              <button class="btn btn-sm btn-danger" onclick="eliminarCliente(<?= $row['id'] ?>)">
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
  // Habilitar botón si todos los campos están llenos
  $('#telefono, #nombre, #direccion').on('input', function () {
    const t = $('#telefono').val().trim();
    const n = $('#nombre').val().trim();
    const d = $('#direccion').val().trim();
    $('#btnGuardar').prop('disabled', !(t && n && d));
  });

  // Enviar formulario
  $('#formCliente').on('submit', function(e) {
    e.preventDefault();
    $.post('modulos/clientes.php', $(this).serialize(), function() {
      cargarModulo('clientes');
    });
  });

  // Eliminar cliente
  function eliminarCliente(id) {
    if (confirm('¿Eliminar este cliente?')) {
      $.post('modulos/clientes.php', {accion: 'eliminar', id}, function() {
        cargarModulo('clientes');
      });
    }
  }
</script>
