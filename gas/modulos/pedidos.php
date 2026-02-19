<?php
session_start();
require_once '../Includes/db.php';

// Calcular kilos vendidos hoy y este mes desde pedidos
$hoy = date('Y-m-d');
$inicioMes = date('Y-m-01');

$kilosHoy = 0;
$resHoy = $conn->query("SELECT carga, cantidad FROM pedidos WHERE DATE(fecha) = '$hoy'");
while ($r = $resHoy->fetch_assoc()) {
  $cargaNum = intval($r['carga']);
  $kilosHoy += $cargaNum * $r['cantidad'];
}

$kilosMes = 0;
$resMes = $conn->query("SELECT carga, cantidad FROM pedidos WHERE fecha BETWEEN '$inicioMes' AND '$hoy'");
while ($r = $resMes->fetch_assoc()) {
  $cargaNum = intval($r['carga']);
  $kilosMes += $cargaNum * $r['cantidad'];
}

// Confirmar pedido
if (isset($_POST['accion']) && $_POST['accion'] === 'confirmar_pedido') {
    $items = json_decode($_POST['items'], true);
  $telefono = $_POST['telefono'];
  $nombre = $_POST['nombre'];
  $direccion = $_POST['direccion'];
  $medio_pago = $_POST['medio_pago'];
  $descuento = intval($_POST['descuento']);
  $total = floatval($_POST['total']);
  $movil = $_POST['movil'];
  $fecha = date('Y-m-d H:i:s');

  $last = $conn->query("SELECT MAX(numero_pedido) AS ultimo FROM pedidos")->fetch_assoc();
  $numero = $last['ultimo'] ? $last['ultimo'] + 1 : 1;
// Insertar cliente si no existe
if ($telefono !== 'Anónimo' && !empty($telefono)) {
    $existe = $conn->query("SELECT telefono FROM clientes WHERE telefono = '$telefono'")->num_rows;
    if ($existe === 0) {
      $stmt = $conn->prepare("INSERT INTO clientes (telefono, nombre, direccion) VALUES (?, ?, ?)");
      $stmt->bind_param("sss", $telefono, $nombre, $direccion);
      $stmt->execute();
    }
  }
  foreach ($items as $item) {
    $carga = $item['carga'];
    $cantidad = intval($item['cantidad']);
    $precio_unitario = floatval($item['precio_unitario']);

    $stmt = $conn->prepare("UPDATE inventario SET cantidad = cantidad - ? WHERE carga = ?");
    $stmt->bind_param("is", $cantidad, $carga);
    $stmt->execute();

    $query = "INSERT INTO pedidos (
        fecha, numero_pedido, telefono_cliente, nombre_cliente, direccion_cliente,
        carga, cantidad, precio_unitario, medio_pago, descuento, total, movil, estado
      ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
   $stmt = $conn->prepare($query);
   $estado = 'Sin Asignar';
   $stmt->bind_param("sissssiisidss", $fecha, $numero, $telefono, $nombre, $direccion, $carga, $cantidad, $precio_unitario, $medio_pago, $descuento, $total, $movil, $estado);
    $stmt->execute();
  }

  if ($telefono !== 'Anónimo') {
    $conn->query("UPDATE clientes SET ultimo_pedido = '$fecha' WHERE telefono = '$telefono'");
  }

  exit;
}

if (isset($_POST['accion']) && $_POST['accion'] === 'buscar_cliente') {
    $telefono = $_POST['telefono'];
  
    // Obtener datos del cliente
    $stmt = $conn->prepare("SELECT * FROM clientes WHERE telefono = ?");
    $stmt->bind_param("s", $telefono);
    $stmt->execute();
    $cliente = $stmt->get_result()->fetch_assoc();
  
    if ($cliente) {
      // Obtener último pedido
      $ultimo = $conn->query("SELECT * FROM pedidos WHERE telefono_cliente = '$telefono' ORDER BY fecha DESC LIMIT 1")->fetch_assoc();
      $cliente['ultimo_pedido_completo'] = $ultimo;
    }
  
    echo json_encode($cliente);
    exit;
  }
  
$cargas = $conn->query("SELECT carga, precio_venta FROM inventario");
$medios = $conn->query("SELECT medio_pago FROM medios_pago");
$descuentos = $conn->query("SELECT DISTINCT descuento FROM descuentos ORDER BY descuento ASC");
$moviles = $conn->query("SELECT movil FROM moviles ORDER BY movil ASC");
?>

<div class="container">
  <h4 class="mb-4">Ingreso de Pedido</h4>
  <div class="alert alert-info mb-3">
  <strong>Kilos vendidos hoy (Pedidos):</strong> <?= $kilosHoy ?> Kg —
  <strong>Este mes:</strong> <?= $kilosMes ?> Kg
</div>
  <!-- Cliente -->
  <div class="row g-3 mb-3">
    <div class="col-md-3">
      <input type="text" id="telefonoCliente" class="form-control" placeholder="Teléfono Cliente">
    </div>
    <div class="col-md-2">
      <button class="btn btn-outline-primary w-100" onclick="buscarCliente()">Buscar</button>
    </div>
    <div class="col-md-3">
      <button class="btn btn-outline-secondary w-100" onclick="usarAnonimo()">Pedido Anónimo</button>
    </div>
  </div>

  <!-- Formulario cliente nuevo -->
  <div class="row g-3 mb-3" id="formCliente" style="display: none;">
    <div class="col-md-3">
      <input type="text" id="nombreCliente" class="form-control" placeholder="Nombre">
    </div>
    <div class="col-md-4">
      <input type="text" id="direccionCliente" class="form-control" placeholder="Dirección">
    </div>
    <div class="col-md-5 text-end">
      <div class="alert alert-info py-2 px-3 mb-0">Cliente no registrado. Se creará automáticamente.</div>
    </div>
  </div>

  <!-- Último pedido -->
  <div class="mb-3" id="ultimoPedido"></div>

  <!-- Agregar cargas -->
  <div class="row g-3 mb-3">
    <div class="col-md-4">
      <select id="carga" class="form-select">
        <option value="">Seleccionar Carga</option>
        <?php while ($row = $cargas->fetch_assoc()): ?>
          <option value="<?= $row['carga'] ?>" data-precio="<?= $row['precio_venta'] ?>"><?= $row['carga'] ?></option>
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

  <!-- Tabla de cargas -->
  <div class="table-responsive mb-3">
    <table class="table table-bordered text-center" id="tablaPedido">
      <thead class="table-dark">
        <tr><th>Carga</th><th>Cantidad</th><th>Precio</th><th>Total</th><th>Eliminar</th></tr>
      </thead>
      <tbody></tbody>
      <tfoot>
        <tr><td colspan="3" class="text-end fw-bold">Total Bruto:</td><td colspan="2" id="totalBruto">$0</td></tr>
      </tfoot>
    </table>
  </div>

  <!-- Medio de pago, descuento y móvil -->
  <div class="row g-3 mb-3">
    <div class="col-md-3">
      <select id="medio_pago" class="form-select">
        <option value="">Medio de Pago</option>
        <?php while ($m = $medios->fetch_assoc()): ?>
          <option value="<?= $m['medio_pago'] ?>"><?= $m['medio_pago'] ?></option>
        <?php endwhile; ?>
      </select>
    </div>
    <div class="col-md-2">
      <select id="descuento" class="form-select">
        <option value="0">Sin Descuento</option>
        <?php while ($d = $descuentos->fetch_assoc()): ?>
          <option value="<?= $d['descuento'] ?>"><?= '$' . number_format($d['descuento'], 0) ?></option>
        <?php endwhile; ?>
      </select>
    </div>
    <div class="col-md-3">
      <select id="movil" class="form-select">
        <option value="">Móvil que entrega</option>
        <?php while ($m = $moviles->fetch_assoc()): ?>
          <option value="<?= $m['movil'] ?>"><?= $m['movil'] ?></option>
        <?php endwhile; ?>
      </select>
    </div>
    <div class="col-md-2 text-end">
      <div class="form-control bg-light fw-bold">Total: <span id="totalPedido">$0</span></div>
    </div>
    <div class="col-md-2">
      <button class="btn btn-primary w-100" onclick="visualizarPedido()" id="btnVisualizar" disabled>
        <i class="fas fa-eye"></i> Visualizar Pedido
      </button>
    </div>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="modalPedido" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">Resumen del Pedido</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="resumenPedido"></div>
      <div class="modal-footer">
        <button class="btn btn-success" onclick="confirmarPedido()"><i class="fas fa-check"></i> Realizar Pedido</button>
      </div>
    </div>
  </div>
</div>

<script>
// Encapsular el código en una función autoinvocada para evitar conflictos globales
(() => {
  let items = []; // Declarar la variable dentro del alcance de esta función

  function buscarCliente() {
    const tel = $('#telefonoCliente').val().trim();
    if (!tel) return;

    $.post('modulos/pedidos.php', {accion: 'buscar_cliente', telefono: tel}, function(res) {
      const data = JSON.parse(res);
      if (data) {
        $('#nombreCliente').val(data.nombre).prop('readonly', true);
        $('#direccionCliente').val(data.direccion).prop('readonly', true);
        $('#formCliente').show();
        if (data.ultimo_pedido_completo) {
          const p = data.ultimo_pedido_completo;
          $('#ultimoPedido').html(`
            <div class="alert alert-info">
              <strong>Último Pedido:</strong><br>
              <strong>Fecha:</strong> ${p.fecha}<br>
              <strong>Carga:</strong> ${p.carga} — 
              <strong>Cantidad:</strong> ${p.cantidad} — 
              <strong>Precio:</strong> $${parseFloat(p.precio_unitario).toFixed(2)}<br>
              <strong>Medio de Pago:</strong> ${p.medio_pago} — 
              <strong>Descuento:</strong> $${p.descuento}<br>
              <strong>Móvil:</strong> ${p.movil}
            </div>
          `);
        } else {
          $('#ultimoPedido').html('<div class="alert alert-info">Sin registros de pedidos anteriores.</div>');
        }
      } else {
        $('#formCliente').show();
        $('#nombreCliente').val('');
        $('#direccionCliente').val('');
        $('#ultimoPedido').html('<div class="alert alert-warning">Cliente no registrado. Se creará.</div>');
      }
    });
  }

  function usarAnonimo() {
    $('#telefonoCliente').val('Anónimo');
    $('#nombreCliente').val('Anónimo').prop('readonly', true);
    $('#direccionCliente').val('Sin Dirección').prop('readonly', true);
    $('#formCliente').show();
    $('#ultimoPedido').html('');
  }

  // Exponer funciones globalmente si es necesario
  window.buscarCliente = buscarCliente;
  window.usarAnonimo = usarAnonimo;
})();

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
  let html = '', bruto = 0;
  items.forEach((item, i) => {
    const total = item.cantidad * item.precio_unitario;
    bruto += total;
    html += `<tr>
      <td>${item.carga}</td>
      <td>${item.cantidad}</td>
      <td>$${item.precio_unitario.toFixed(2)}</td>
      <td>$${total.toFixed(2)}</td>
      <td><button class="btn btn-danger btn-sm" onclick="eliminarItem(${i})"><i class="fas fa-trash"></i></button></td>
    </tr>`;
  });
  $('#tablaPedido tbody').html(html);
  $('#totalBruto').text(`$${bruto.toFixed(2)}`);
  calcularTotal();
  $('#btnVisualizar').prop('disabled', items.length === 0);
}

function eliminarItem(i) {
  items.splice(i, 1);
  renderTabla();
}

function calcularTotal() {
  const medio = $('#medio_pago').val();
  const desc = parseInt($('#descuento').val()) || 0;
  const bruto = items.reduce((t, i) => t + i.precio_unitario * i.cantidad, 0);
  const total = (medio === 'Cupón' || medio === 'No Paga') ? 0 : Math.max(0, bruto - desc);
  $('#totalPedido').text(`$${total.toFixed(2)}`);
}

$('#medio_pago, #descuento').on('change', calcularTotal);

function visualizarPedido() {
  const resumen = `
    <table class="table table-bordered text-center">
      <thead><tr><th>Carga</th><th>Cantidad</th><th>Precio</th><th>Total</th></tr></thead><tbody>
      ${items.map(i => `<tr><td>${i.carga}</td><td>${i.cantidad}</td><td>$${i.precio_unitario.toFixed(2)}</td><td>$${(i.precio_unitario * i.cantidad).toFixed(2)}</td></tr>`).join('')}
      </tbody></table>
      <p><strong>Medio de Pago:</strong> ${$('#medio_pago').val()}</p>
      <p><strong>Descuento:</strong> $${$('#descuento').val()}</p>
      <p><strong>Móvil:</strong> ${$('#movil').val()}</p>
      <p><strong>Total Final:</strong> ${$('#totalPedido').text()}</p>`;
  $('#resumenPedido').html(resumen);
  new bootstrap.Modal('#modalPedido').show();
}

function confirmarPedido() {
  if (items.length === 0) {
    Swal.fire({
      icon: 'warning',
      title: 'Pedido vacío',
      text: 'Debe agregar al menos una carga al pedido.'
    });
    return;
  }

  $.post('modulos/pedidos.php', {
    accion: 'confirmar_pedido',
    items: JSON.stringify(items),
    telefono: $('#telefonoCliente').val(),
    nombre: $('#nombreCliente').val(),
    direccion: $('#direccionCliente').val(),
    medio_pago: $('#medio_pago').val(),
    descuento: $('#descuento').val(),
    total: $('#totalPedido').text().replace('$', ''),
    movil: $('#movil').val()
  }, function () {
    $('#modalPedido').modal('hide');

    Swal.fire({
      icon: 'question',
      title: '¿Desea asignar el pedido a un móvil?',
      showCancelButton: true,
      confirmButtonText: 'Sí',
      cancelButtonText: 'Sólo Guardar'
    }).then((result) => {
      if (result.isConfirmed) {
        cargarModulo('asignar_pedido');
      } else {
        cargarModulo('pedidos');
      }
    });
  });
}
</script>
