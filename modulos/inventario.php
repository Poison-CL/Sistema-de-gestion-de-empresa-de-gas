<?php
session_start();
require_once '../Includes/db.php';

// Insertar nueva carga
if (isset($_POST['accion']) && $_POST['accion'] === 'agregar') {
  $carga = $_POST['carga'];
  $cantidad = $_POST['cantidad'];
  $precio_unitario = $_POST['precio_unitario'];
  $precio_venta = $_POST['precio_venta'];

  $query = "INSERT INTO inventario (carga, cantidad, precio_unitario, precio_venta) VALUES (?, ?, ?, ?)";
  $stmt = $conn->prepare($query);
  $stmt->bind_param("sidd", $carga, $cantidad, $precio_unitario, $precio_venta);
  $stmt->execute();
  exit;
}

// Eliminar carga
if (isset($_POST['accion']) && $_POST['accion'] === 'eliminar') {
  $id = $_POST['id'];
  $conn->query("DELETE FROM inventario WHERE id = $id");
  exit;
}

// Obtener inventario
$inventario = $conn->query("SELECT * FROM inventario ORDER BY id DESC");
?>

<div class="container">
  <h4 class="mb-4">Gestión de Inventario</h4>

  <!-- 🟢 Formulario -->
  <form id="formInventario" class="row g-3 mb-4">
    <input type="hidden" name="accion" value="agregar">
    <div class="col-md-3">
      <input type="text" name="carga" id="carga" class="form-control" placeholder="Carga (ej: 5kg)" required>
    </div>
    <div class="col-md-2">
      <input type="number" name="cantidad" id="cantidad" class="form-control" placeholder="Cantidad" min="1" required>
    </div>
    <div class="col-md-3">
      <input type="number" step="0.01" name="precio_unitario" id="precio_unitario" class="form-control" placeholder="Precio Unitario" required>
    </div>
    <div class="col-md-3">
      <input type="number" step="0.01" name="precio_venta" id="precio_venta" class="form-control" placeholder="Precio Venta" required>
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
          <th>Carga</th>
          <th>Cantidad</th>
          <th>Precio Unitario</th>
          <th>Precio Venta</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody id="tablaInventario">
        <?php while ($row = $inventario->fetch_assoc()): ?>
          <tr>
            <td><?= $row['carga'] ?></td>
            <td><?= $row['cantidad'] ?></td>
            <td>$<?= number_format($row['precio_unitario'], 2) ?></td>
            <td>$<?= number_format($row['precio_venta'], 2) ?></td>
            <td>
              <button class="btn btn-sm btn-danger" onclick="eliminarCarga(<?= $row['id'] ?>)">
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
  $('#carga, #cantidad, #precio_unitario, #precio_venta').on('input', function () {
    const c = $('#carga').val().trim();
    const q = $('#cantidad').val().trim();
    const pu = $('#precio_unitario').val().trim();
    const pv = $('#precio_venta').val().trim();
    $('#btnGuardar').prop('disabled', !(c && q && pu && pv));
  });

  // Enviar formulario
  $('#formInventario').on('submit', function(e) {
    e.preventDefault();
    $.post('modulos/inventario.php', $(this).serialize(), function() {
      cargarModulo('inventario');
    });
  });

  // Eliminar carga
  function eliminarCarga(id) {
    if (confirm('¿Eliminar esta carga del inventario?')) {
      $.post('modulos/inventario.php', {accion: 'eliminar', id}, function() {
        cargarModulo('inventario');
      });
    }
  }
</script>
