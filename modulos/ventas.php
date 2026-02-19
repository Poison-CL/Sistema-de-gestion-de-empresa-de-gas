<?php
session_start();
require_once '../Includes/db.php';

// Obtener fecha actual
$hoy = date('Y-m-d');
$inicioMes = date('Y-m-01');

// Cálculo de kilos vendidos hoy
$resHoy = $conn->query("SELECT carga, cantidad FROM ventas_local WHERE DATE(fecha) = '$hoy'");
$kilosHoy = 0;
while ($r = $resHoy->fetch_assoc()) {
  $cargaNum = intval($r['carga']); // Asumimos que la carga es algo como "15 Kilos"
  $kilosHoy += $cargaNum * $r['cantidad'];
}

// Cálculo de kilos vendidos en el mes
$resMes = $conn->query("SELECT carga, cantidad FROM ventas_local WHERE fecha BETWEEN '$inicioMes' AND '$hoy'");
$kilosMes = 0;
while ($r = $resMes->fetch_assoc()) {
  $cargaNum = intval($r['carga']);
  $kilosMes += $cargaNum * $r['cantidad'];
}

// Procesar venta confirmada
if (isset($_POST['accion']) && $_POST['accion'] === 'confirmar_venta') {
  $items = $_POST['items']; // array de objetos (json decodificado)
  $medio_pago = $_POST['medio_pago'];
  $descuento = intval($_POST['descuento']);
  $total = floatval($_POST['total']);
  $fecha = date('Y-m-d H:i:s');

  foreach ($items as $item) {
    $carga = $item['carga'];
    $cantidad = intval($item['cantidad']);
    $precio_unitario = floatval($item['precio_unitario']);

    // Descontar stock del inventario
    $stmt = $conn->prepare("UPDATE inventario SET cantidad = cantidad - ? WHERE carga = ?");
    $stmt->bind_param("is", $cantidad, $carga);
    $stmt->execute();

    // Insertar venta
    $query = "INSERT INTO ventas_local (fecha, carga, cantidad, precio_unitario, medio_pago, descuento, total)
              VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssidsid", $fecha, $carga, $cantidad, $precio_unitario, $medio_pago, $descuento, $total);
    $stmt->execute();
  }

  exit;
}

// Obtener datos para selects
$cargas = $conn->query("SELECT carga, precio_venta FROM inventario");
$medios = $conn->query("SELECT medio_pago FROM medios_pago");
$descuentos = $conn->query("SELECT DISTINCT descuento FROM descuentos ORDER BY descuento ASC");
?>

<div class="container">
  <h4 class="mb-4">Ventas Local</h4>

  <!-- 🟢 Formulario -->
  <div class="row g-3 mb-3">
    <div class="col-md-4">
      <select id="carga" class="form-select">
        <option value="">Seleccionar Carga</option>
        <?php while ($row = $cargas->fetch_assoc()): ?>
          <option value="<?= $row['carga'] ?>" data-precio="<?= $row['precio_venta'] ?>">
            <?= $row['carga'] ?>
          </option>
        <?php endwhile; ?>
      </select>
    </div>
    <div class="col-md-3">
      <input type="number" id="cantidad" class="form-control" placeholder="Cantidad" min="1">
    </div>
    <div class="col-md-2">
      <button class="btn btn-success w-100" onclick="agregarItem()"><i class="fas fa-plus"></i></button>
    </div>
  </div>

  <!-- 🟡 Tabla de venta -->
  <div class="table-responsive mb-3">
    <table class="table table-bordered text-center align-middle" id="tablaVenta">
      <thead class="table-dark">
        <tr>
          <th>Carga</th>
          <th>Cantidad</th>
          <th>Precio Unitario</th>
          <th>Total</th>
          <th>Eliminar</th>
        </tr>
      </thead>
      <tbody></tbody>
      <tfoot>
        <tr class="table-light fw-bold">
          <td colspan="3" class="text-end">Precio Bruto:</td>
          <td colspan="2" id="precioBruto">$0</td>
        </tr>
      </tfoot>
    </table>
  </div>

  <!-- 🔽 Selección de medio de pago y descuento -->
  <div class="row g-3 mb-3">
    <div class="col-md-4">
      <select id="medio_pago" class="form-select">
        <option value="">Seleccionar Medio de Pago</option>
        <?php while ($m = $medios->fetch_assoc()): ?>
          <option value="<?= $m['medio_pago'] ?>"><?= $m['medio_pago'] ?></option>
        <?php endwhile; ?>
      </select>
    </div>
    <div class="col-md-3">
      <select id="descuento" class="form-select">
        <option value="0">Sin Descuento</option>
        <?php while ($d = $descuentos->fetch_assoc()): ?>
          <option value="<?= $d['descuento'] ?>"><?= '$' . number_format($d['descuento'], 0) ?></option>
        <?php endwhile; ?>
      </select>
    </div>
    <div class="col-md-2 text-end">
      <div class="form-control fw-bold bg-light">Total: <span id="totalVenta">$0</span></div>
      <div class="alert alert-info"><strong>Kilos vendidos hoy:</strong> <?= $kilosHoy ?> Kg — <strong>Este mes:</strong> <?= $kilosMes ?> Kg </div>
</div>
    </div>
    <div class="col-md-3">
      <button class="btn btn-primary w-100" onclick="visualizarVenta()" id="btnVisualizar" disabled>
        <i class="fas fa-eye"></i> Visualizar Venta
      </button>
    </div>
  </div>
</div>

<!-- 🔵 Modal resumen venta -->
<div class="modal fade" id="modalVenta" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">Resumen de Venta</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="resumenVenta">
        <!-- Aquí se carga la tabla resumen -->
      </div>
      <div class="modal-footer">
        <button class="btn btn-success" onclick="confirmarVenta()"><i class="fas fa-check"></i> Realizar Venta</button>
      </div>
    </div>
  </div>
</div>

<script>
  let items = [];

  function agregarItem() {
    const carga = $('#carga').val();
    const cantidad = parseInt($('#cantidad').val());
    const precio = parseFloat($('#carga option:selected').data('precio'));

    if (!carga || cantidad <= 0) return;

    items.push({ carga, cantidad, precio_unitario: precio });
    renderTabla();
    $('#carga').val('');
    $('#cantidad').val('');
  }

  function renderTabla() {
    let tbody = '';
    let bruto = 0;

    items.forEach((item, index) => {
      const total = item.cantidad * item.precio_unitario;
      bruto += total;
      tbody += `
        <tr>
          <td>${item.carga}</td>
          <td>${item.cantidad}</td>
          <td>$${item.precio_unitario.toFixed(2)}</td>
          <td>$${total.toFixed(2)}</td>
          <td><button class="btn btn-sm btn-danger" onclick="eliminarItem(${index})"><i class="fas fa-trash"></i></button></td>
        </tr>
      `;
    });

    $('#tablaVenta tbody').html(tbody);
    $('#precioBruto').text(`$${bruto.toFixed(2)}`);
    calcularTotal();
    $('#btnVisualizar').prop('disabled', items.length === 0);
  }

  function eliminarItem(index) {
    items.splice(index, 1);
    renderTabla();
  }

  function calcularTotal() {
    let bruto = items.reduce((acc, i) => acc + (i.precio_unitario * i.cantidad), 0);
    let descuento = parseInt($('#descuento').val()) || 0;
    let medio = $('#medio_pago').val();

    let total = (medio === 'Cupón' || medio === 'No Paga') ? 0 : Math.max(0, bruto - descuento);
    $('#totalVenta').text(`$${total.toFixed(2)}`);
  }

  $('#medio_pago, #descuento').on('change', calcularTotal);

  function visualizarVenta() {
    let resumen = `
      <table class="table table-bordered text-center">
        <thead><tr><th>Carga</th><th>Cantidad</th><th>Precio Unitario</th><th>Total</th></tr></thead>
        <tbody>
          ${items.map(i => `
            <tr>
              <td>${i.carga}</td>
              <td>${i.cantidad}</td>
              <td>$${i.precio_unitario.toFixed(2)}</td>
              <td>$${(i.cantidad * i.precio_unitario).toFixed(2)}</td>
            </tr>`).join('')}
        </tbody>
      </table>
      <p><strong>Medio de Pago:</strong> ${$('#medio_pago').val()}</p>
      <p><strong>Descuento:</strong> $${parseInt($('#descuento').val())}</p>
      <p><strong>Total Final:</strong> ${$('#totalVenta').text()}</p>
    `;
    $('#resumenVenta').html(resumen);
    new bootstrap.Modal(document.getElementById('modalVenta')).show();
  }

  function confirmarVenta() {
  $.post('modulos/ventas.php', {
    accion: 'confirmar_venta',
    items: items,
    medio_pago: $('#medio_pago').val(),
    descuento: $('#descuento').val(),
    total: $('#totalVenta').text().replace('$', '')
  }, function() {
    $('#modalVenta').modal('hide'); // Cierra el modal
    Swal.fire({
      icon: 'success',
      title: 'Venta realizada',
      text: 'La venta se guardó correctamente',
      confirmButtonText: 'Aceptar'
    }).then(() => {
      location.reload(); // Recarga para limpiar y actualizar
    });
  });
}

</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>