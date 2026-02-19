<?php
require_once '../Includes/db.php';


if (isset($_GET['stock_actual']) && isset($_GET['movil_id'])) {
  $id = intval($_GET['movil_id']);
  $res = $conn->query("SELECT carga, cantidad FROM stock_moviles WHERE movil_id = $id");
  if ($res->num_rows > 0) {
    echo "<table class='table table-bordered'><thead><tr><th>Carga</th><th>Cantidad</th></tr></thead><tbody>";
    while ($row = $res->fetch_assoc()) {
      echo "<tr><td>{$row['carga']}</td><td>{$row['cantidad']}</td></tr>";
    }
    echo "</tbody></table>";
  } else {
    echo "<div class='alert alert-info'>Este móvil no tiene stock asignado actualmente.</div>";
  }
  exit;
}
// Guardar cuadratura
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar'])) {
    $movil_id = $_POST['movil_id'];
    $total_venta = $_POST['total_venta'];
    $total_debitos = $_POST['total_debitos'];
    $total_cupones = $_POST['total_cupones'];
    $total_descuentos = $_POST['total_descuentos'];
    $total_entregar = $_POST['total_entregar'];
    $total_entregado = $_POST['total_entregado'];
    $saldo = $_POST['saldo'];
    $detalle_json = $_POST['detalle'];
    $detalle = json_decode($detalle_json, true);
    $total_kilos = 0;
if (isset($detalle['final'])) {
  foreach ($detalle['final'] as $item) {
    $numCarga = intval($item['carga']); // Extraer número desde "15K", "5", etc.
    $cantidad = intval($item['cantidad']);
    $total_kilos += $numCarga * $cantidad;
  }
}

  
    // Guardar la cuadratura
    $stmt = $conn->prepare("INSERT INTO cuadraturas (movil_id, total_venta, total_debitos, total_cupones, total_descuentos, total_entregar, total_entregado, saldo, detalle, total_kilos)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("idddddddsd", $movil_id, $total_venta, $total_debitos, $total_cupones, $total_descuentos, $total_entregar, $total_entregado, $saldo, $detalle_json, $total_kilos);

    $stmt->execute();
  
    // Actualizar stock del móvil
    foreach ($detalle['final'] as $item) {
      $carga = $item['carga'];
      $cantidadFinal = intval($item['cantidad']);
  
      // Verificar si existe esa carga ya en stock_moviles
      $verifica = $conn->query("SELECT id, cantidad FROM stock_moviles WHERE movil_id = $movil_id AND carga = '$carga'");
  
      if ($verifica->num_rows > 0) {
        $row = $verifica->fetch_assoc();
        $idStock = $row['id'];
        // Reemplaza con la cantidad final
        $conn->query("UPDATE stock_moviles SET cantidad = $cantidadFinal WHERE id = $idStock");
      } else {
        // Si no existe, la insertamos
        $stmt = $conn->prepare("INSERT INTO stock_moviles (movil_id, carga, cantidad) VALUES (?, ?, ?)");
        $stmt->bind_param("isi", $movil_id, $carga, $cantidadFinal);
        $stmt->execute();
      }
    }
  
    echo 'ok';
    exit;
      }
  
// Cargas y móviles
$cargas = $conn->query("SELECT carga, precio_venta FROM inventario");
$moviles = $conn->query("SELECT id, movil FROM moviles ORDER BY movil ASC");
?>

<div class="container">
  <h4 class="mb-4">Cuadratura de Móvil</h4>

  <!-- Selección de móvil -->
  <div class="row mb-3">
    <div class="col-md-4">
      <select id="movil" class="form-select" onchange="mostrarStock()">
        <option value="">Seleccionar Móvil</option>
        <?php while ($m = $moviles->fetch_assoc()): ?>
          <option value="<?= $m['id'] ?>"><?= $m['movil'] ?></option>
        <?php endwhile; ?>
      </select>
    </div>
  </div>

  <!-- Glosa de stock actual -->
  <div id="glosaStockActual" class="mb-4"></div>

  <!-- Sección stock final -->
  <h5>Stock Final</h5>
  <div class="row mb-2">
    <div class="col-md-4">
      <select id="cargaFinal" class="form-select">
        <option value="">Seleccionar Carga</option>
        <?php mysqli_data_seek($cargas, 0); while ($c = $cargas->fetch_assoc()): ?>
          <option value="<?= $c['carga'] ?>" data-precio="<?= $c['precio_venta'] ?>"><?= $c['carga'] ?></option>
        <?php endwhile; ?>
      </select>
    </div>
    <div class="col-md-3">
      <input type="number" id="cantidadFinal" class="form-control" placeholder="Cantidad">
    </div>
    <div class="col-md-2">
      <button class="btn btn-success w-100" onclick="agregarFinal()">Agregar</button>
    </div>
  </div>

  <table class="table table-bordered text-center" id="tablaFinal">
    <thead><tr><th>Carga</th><th>Cantidad</th><th>Total</th><th>Eliminar</th></tr></thead>
    <tbody></tbody>
  </table>

  <!-- Débitos -->
  <h5>Débitos</h5>
  <div class="row mb-2">
    <div class="col-md-4">
      <input type="number" id="debito" class="form-control" placeholder="Monto">
    </div>
    <div class="col-md-2">
      <button class="btn btn-outline-primary w-100" onclick="agregarDebito()">Agregar</button>
    </div>
  </div>
  <ul class="list-group mb-2" id="listaDebitos"></ul>

  <!-- Cupones -->
  <h5>Cupones</h5>
  <div class="row mb-2">
    <div class="col-md-4">
      <select id="cargaCupon" class="form-select">
        <option value="">Carga</option>
        <?php mysqli_data_seek($cargas, 0); while ($c = $cargas->fetch_assoc()): ?>
          <option value="<?= $c['carga'] ?>" data-precio="<?= $c['precio_venta'] ?>"><?= $c['carga'] ?></option>
        <?php endwhile; ?>
      </select>
    </div>
    <div class="col-md-3">
      <input type="number" id="cantidadCupon" class="form-control" placeholder="Cantidad">
    </div>
    <div class="col-md-2">
      <button class="btn btn-outline-primary w-100" onclick="agregarCupon()">Agregar</button>
    </div>
  </div>
  <ul class="list-group mb-2" id="listaCupones"></ul>

  <!-- Descuentos -->
  <h5>Descuentos</h5>
  <div class="row mb-2">
    <div class="col-md-3">
      <input type="number" id="montoDescuento" class="form-control" placeholder="Monto">
    </div>
    <div class="col-md-5">
      <input type="text" id="descDescuento" class="form-control" placeholder="Descripción">
    </div>
    <div class="col-md-2">
      <button class="btn btn-outline-primary w-100" onclick="agregarDescuento()">Agregar</button>
    </div>
  </div>
  <ul class="list-group mb-2" id="listaDescuentos"></ul>

  <!-- Totales -->
  <hr>
  <div class="mb-3">
    <p><strong>Total Venta:</strong> $<span id="totalVenta">0</span></p>
    <p><strong>Total Débitos:</strong> $<span id="totalDebitos">0</span></p>
    <p><strong>Total Cupones:</strong> $<span id="totalCupones">0</span></p>
    <p><strong>Total Descuentos:</strong> $<span id="totalDescuentos">0</span></p>
    <p><strong>Total a Entregar:</strong> $<span id="totalEntregar">0</span></p>
    <p><strong>Total Kilos Vendidos:</strong> <span id="totalKilos">0</span> kg</p>

  </div>

  <div class="row mb-3">
    <div class="col-md-4">
      <input type="number" id="totalEntregado" class="form-control" placeholder="Total Entregado" oninput="calcularSaldo()">
    </div>
    <div class="col-md-4">
      <p><strong>Saldo:</strong> $<span id="saldo">0</span></p>
    </div>
  </div>

  <button class="btn btn-primary" onclick="visualizarCuadratura()">Visualizar Cuadratura</button>
</div>

<!-- Modal resumen -->
<div class="modal fade" id="modalCuadratura" tabindex="-1">
  <div class="modal-dialog modal-lg"><div class="modal-content">
    <div class="modal-header bg-primary text-white">
      <h5 class="modal-title">Resumen de Cuadratura</h5>
      <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body" id="resumenCuadratura"></div>
    <div class="modal-footer">
    <form id="formCuadratura">
        <input type="hidden" name="guardar" value="1">
        <input type="hidden" name="movil_id" id="m_movil">
        <input type="hidden" name="total_venta" id="m_total_venta">
        <input type="hidden" name="total_debitos" id="m_total_debitos">
        <input type="hidden" name="total_cupones" id="m_total_cupones">
        <input type="hidden" name="total_descuentos" id="m_total_descuentos">
        <input type="hidden" name="total_entregar" id="m_total_entregar">
        <input type="hidden" name="total_entregado" id="m_total_entregado">
        <input type="hidden" name="saldo" id="m_saldo">
        <input type="hidden" name="detalle" id="m_detalle">
        <input type="hidden" name="total_kilos" id="m_total_kilos">
        <button class="btn btn-success">Cuadrar Móvil</button>
      </form>
    </div>
  </div></div>
</div>

<script>
let final = [], debitos = [], cupones = [], descuentos = [];

function agregarFinal() {
  const carga = cargaFinal.value;
  const cantidad = parseInt(cantidadFinal.value);
  const precio = parseFloat(cargaFinal.selectedOptions[0].dataset.precio);
  if (!carga || !cantidad) return;
  final.push({carga, cantidad, total: cantidad * precio});
  renderFinal();
}

function renderFinal() {
  let html = '', total = 0;
  final.forEach((f, i) => {
    total += f.total;
    html += `<tr><td>${f.carga}</td><td>${f.cantidad}</td><td>$${f.total}</td><td><button onclick="final.splice(${i},1);renderFinal()" class="btn btn-sm btn-danger">X</button></td></tr>`;
  });
  document.querySelector('#tablaFinal tbody').innerHTML = html;
  document.getElementById('totalVenta').textContent = total;
  calcularTotales();
  let kilos = 0;
final.forEach((f) => {
  const numero = parseInt(f.carga); // Extraer solo número de la carga
  kilos += numero * f.cantidad;
});
document.getElementById('totalKilos').textContent = kilos;
}

function agregarDebito() {
  const monto = parseFloat(debito.value);
  if (!monto) return;
  debitos.push(monto);
  renderList('listaDebitos', debitos, 'totalDebitos');
}

function agregarCupon() {
  const carga = cargaCupon.value;
  const cantidad = parseInt(cantidadCupon.value);
  const precio = parseFloat(cargaCupon.selectedOptions[0].dataset.precio);
  if (!carga || !cantidad) return;
  cupones.push({ carga, cantidad, total: cantidad * precio });
  renderCupones();
}

function agregarDescuento() {
  const monto = parseFloat(montoDescuento.value);
  const desc = descDescuento.value;
  if (!monto || !desc) return;
  descuentos.push({ monto, desc });
  renderDescuentos();
}

function renderList(ulId, arrayRef, totalId) {
  let html = '', total = 0;
  arrayRef.forEach((val, i) => {
    html += `<li class="list-group-item d-flex justify-content-between">
      $${val} 
      <button class="btn btn-sm btn-danger" onclick="eliminarItem('${ulId}', ${i}, '${totalId}')">X</button>
    </li>`;
    total += val;
  });
  document.getElementById(ulId).innerHTML = html;
  document.getElementById(totalId).textContent = total;
  calcularTotales();
}

function eliminarItem(tipo, index, totalId) {
  if (tipo === 'listaDebitos') debitos.splice(index, 1);
  renderList('listaDebitos', debitos, totalId);
}
function renderCupones() {
  let html = '', total = 0;
  cupones.forEach((c, i) => {
    total += c.total;
    html += `<li class="list-group-item d-flex justify-content-between">${c.carga} (${c.cantidad}) - $${c.total} <button class="btn btn-sm btn-danger" onclick="cupones.splice(${i},1);renderCupones()">X</button></li>`;
  });
  listaCupones.innerHTML = html;
  totalCupones.textContent = total;
  calcularTotales();
}

function renderDescuentos() {
  let html = '', total = 0;
  descuentos.forEach((d, i) => {
    total += d.monto;
    html += `<li class="list-group-item d-flex justify-content-between">$${d.monto} (${d.desc}) <button class="btn btn-sm btn-danger" onclick="descuentos.splice(${i},1);renderDescuentos()">X</button></li>`;
  });
  listaDescuentos.innerHTML = html;
  totalDescuentos.textContent = total;
  calcularTotales();
}

function calcularTotales() {
  const venta = parseFloat(totalVenta.textContent) || 0;
  const deb = parseFloat(totalDebitos.textContent) || 0;
  const cup = parseFloat(totalCupones.textContent) || 0;
  const desc = parseFloat(totalDescuentos.textContent) || 0;
  totalEntregar.textContent = (venta - deb - cup - desc).toFixed(2);
  calcularSaldo();
}

function calcularSaldo() {
  const entregar = parseFloat(totalEntregar.textContent) || 0;
  const entregado = parseFloat(totalEntregado.value) || 0;
  saldo.textContent = (entregado - entregar).toFixed(2);
}

function visualizarCuadratura() {
  const resumen = `
    <p><strong>Total Venta:</strong> $${totalVenta.textContent}</p>
    <p><strong>Débitos:</strong> $${totalDebitos.textContent}</p>
    <p><strong>Cupones:</strong> $${totalCupones.textContent}</p>
    <p><strong>Descuentos:</strong> $${totalDescuentos.textContent}</p>
    <p><strong>Total a Entregar:</strong> $${totalEntregar.textContent}</p>
    <p><strong>Total Entregado:</strong> ${totalEntregado.value}</p>
    <p><strong>Saldo:</strong> ${saldo.textContent}</p>`;
  resumenCuadratura.innerHTML = resumen;

  // pasar datos al form
  m_movil.value = movil.value;
  m_total_venta.value = totalVenta.textContent;
  m_total_debitos.value = totalDebitos.textContent;
  m_total_cupones.value = totalCupones.textContent;
  m_total_descuentos.value = totalDescuentos.textContent;
  m_total_entregar.value = totalEntregar.textContent;
  m_total_entregado.value = totalEntregado.value;
  m_saldo.value = saldo.textContent;
  m_detalle.value = JSON.stringify({ final, debitos, cupones, descuentos });
  document.getElementById('m_total_kilos').value = document.getElementById('totalKilos').textContent;
  new bootstrap.Modal(modalCuadratura).show();
}

function mostrarStock() {
  const id = document.getElementById('movil').value;
  if (!id) return;
  fetch('modulos/cuadratura.php?stock_actual=1&movil_id=' + id)
    .then(res => res.text())
    .then(html => document.getElementById('glosaStockActual').innerHTML = html);
}
document.getElementById('formCuadratura').addEventListener('submit', function (e) {
  e.preventDefault();

  const form = e.target;
  const data = new FormData(form);

  fetch('modulos/cuadratura.php', {
    method: 'POST',
    body: data
  })
  .then(res => res.text())
  .then(resp => {
    if (resp.trim() === 'ok') {
      Swal.fire({
        icon: 'success',
        title: 'Cuadratura guardada correctamente',
        confirmButtonText: 'Aceptar'
      }).then(() => {
        location.reload();
      });
    } else {
      Swal.fire({
        icon: 'error',
        title: 'Error al guardar',
        text: resp
      });
    }
  });
});

</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


