<?php
session_start();
require_once '../Includes/db.php';

// Procesar inserción múltiple
if (isset($_POST['accion']) && $_POST['accion'] === 'agregar') {
  $fecha = $_POST['fecha'];
  $numero = $_POST['numero_factura'];
  $cargas = $_POST['carga']; // arreglo con nombres de carga
  $cantidades = $_POST['cantidad'];
  $precios = $_POST['precio_unitario'];

  foreach ($cargas as $index => $carga) {
    $cantidad = intval($cantidades[$index]);
    $precio_unitario = floatval($precios[$index]);

    if ($cantidad > 0) {
      // Insertar factura
      $query = "INSERT INTO facturas (fecha, numero_factura, carga, cantidad, precio_unitario) VALUES (?, ?, ?, ?, ?)";
      $stmt = $conn->prepare($query);
      $stmt->bind_param("sssii", $fecha, $numero, $carga, $cantidad, $precio_unitario);
      $stmt->execute();

      // Actualizar inventario
      $verifica = $conn->prepare("SELECT id, cantidad FROM inventario WHERE carga = ?");
      $verifica->bind_param("s", $carga);
      $verifica->execute();
      $res = $verifica->get_result();

      if ($res->num_rows === 1) {
        $fila = $res->fetch_assoc();
        $nuevo_stock = $fila['cantidad'] + $cantidad;
        $update = $conn->prepare("UPDATE inventario SET cantidad = ?, precio_unitario = ? WHERE id = ?");
        $update->bind_param("idi", $nuevo_stock, $precio_unitario, $fila['id']);
        $update->execute();
      }
    }
  }
  exit;
}

// Obtener facturas
$facturas = $conn->query("SELECT * FROM facturas ORDER BY fecha DESC");

// Obtener cargas desde inventario
$cargas = $conn->query("SELECT DISTINCT carga FROM inventario");
?>

<div class="container">
  <h4 class="mb-4">Gestión de Facturas / Guías</h4>

  <!-- 🟢 Formulario -->
  <form id="formFactura" class="mb-4">
    <input type="hidden" name="accion" value="agregar">
    <div class="row g-3 mb-3">
      <div class="col-md-3">
        <input type="date" name="fecha" id="fecha" class="form-control" required>
      </div>
      <div class="col-md-4">
        <input type="text" name="numero_factura" id="numero_factura" class="form-control" placeholder="N° Factura/Guía" required>
      </div>
      <div class="col-md-5 text-end">
        <button type="submit" class="btn btn-success" id="btnGuardar" disabled>
          <i class="fas fa-plus"></i> Registrar Factura/Guía
        </button>
      </div>
    </div>

    <div class="table-responsive">
      <table class="table table-bordered align-middle text-center">
        <thead class="table-light">
          <tr>
            <th>Carga</th>
            <th>Cantidad</th>
            <th>Precio Unitario</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = $cargas->fetch_assoc()): ?>
            <tr>
              <td>
                <?= $row['carga'] ?>
                <input type="hidden" name="carga[]" value="<?= $row['carga'] ?>">
              </td>
              <td><input type="number" class="form-control" name="cantidad[]" min="0" value="0"></td>
              <td><input type="number" class="form-control" name="precio_unitario[]" step="0.01" min="0" value="0.00"></td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </form>

  <!-- 🟡 Tabla de facturas -->
  <div class="table-responsive">
    <table class="table table-bordered align-middle text-center">
      <thead class="table-dark">
        <tr>
          <th>Fecha</th>
          <th>N° Factura/Guía</th>
          <th>Carga</th>
          <th>Cantidad</th>
          <th>Precio Unitario</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $facturas->fetch_assoc()): ?>
          <tr>
            <td><?= $row['fecha'] ?></td>
            <td><?= $row['numero_factura'] ?></td>
            <td><?= $row['carga'] ?></td>
            <td><?= $row['cantidad'] ?></td>
            <td>$<?= number_format($row['precio_unitario'], 2) ?></td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
  // Validar que haya fecha, número y al menos una carga con cantidad > 0
  function validarFactura() {
    const fecha = $('#fecha').val().trim();
    const numero = $('#numero_factura').val().trim();
    let algunaCantidad = false;
    $('input[name="cantidad[]"]').each(function () {
      if (parseInt($(this).val()) > 0) {
        algunaCantidad = true;
      }
    });
    $('#btnGuardar').prop('disabled', !(fecha && numero && algunaCantidad));
  }

  $('#fecha, #numero_factura, input[name="cantidad[]"]').on('input change', validarFactura);

  // Enviar formulario
  $('#formFactura').on('submit', function(e) {
    e.preventDefault();
    $.post('modulos/facturas.php', $(this).serialize(), function() {
      cargarModulo('facturas');
    });
  });
</script>
