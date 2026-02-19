<?php
require_once '../Includes/db.php';

$movil = $_GET['movil'];
$asignados = $conn->prepare("SELECT * FROM pedidos WHERE movil = ? AND estado = 'No Asignado' ORDER BY fecha DESC");
$asignados->bind_param("s", $movil);
$asignados->execute();
$result = $asignados->get_result();
?>

<h5>Pedidos asignados a "<?= htmlspecialchars($movil) ?>"</h5>
<div class="table-responsive mb-4">
  <input class="form-control mb-2" id="buscarAsignados" placeholder="Buscar...">
  <table class="table table-bordered text-center" id="tablaAsignados">
    <thead class="table-dark">
      <tr>
        <th>#</th><th>Fecha</th><th>Carga</th><th>Cantidad</th><th>Cliente</th><th>Dirección</th><th>Estado</th><th>Entregar</th><th>Editar</th><th>Eliminar</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($p = $result->fetch_assoc()): ?>
        <tr>
          <td><?= $p['numero_pedido'] ?></td>
          <td><?= $p['fecha'] ?></td>
          <td><?= $p['carga'] ?></td>
          <td><?= $p['cantidad'] ?></td>
          <td><?= $p['nombre_cliente'] ?></td>
          <td><?= $p['direccion_cliente'] ?></td>
          <td><?= $p['estado'] ?></td>
          <td><button class="btn btn-success btn-sm" onclick="cambiarEstado(<?= $p['id'] ?>, 'Entregado')">Entregar</button></td>
          <td><button class="btn btn-warning btn-sm">Editar</button></td>
          <td><button class="btn btn-danger btn-sm" onclick="eliminarPedido(<?= $p['id'] ?>)">Eliminar</button></td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
  <nav><ul class="pagination justify-content-center" id="paginacionAsignados"></ul></nav>
</div>

<?php
$resumen = $conn->prepare("SELECT carga, SUM(cantidad) AS total_cantidad, precio_unitario, SUM(total) AS total_vendido, medio_pago, SUM(descuento) AS total_descuento FROM pedidos WHERE movil = ? AND estado = 'Entregado' GROUP BY carga, medio_pago, precio_unitario ORDER BY fecha DESC");
$resumen->bind_param("s", $movil);
$resumen->execute();
$res = $resumen->get_result();
?>

<h5>Resumen de Ventas por Móvil (Entregados)</h5>
<div class="table-responsive mb-4">
  <input class="form-control mb-2" id="buscarResumen" placeholder="Buscar...">
  <table class="table table-bordered text-center" id="tablaResumen">
    <thead class="table-light">
      <tr>
        <th>Carga</th><th>Cantidad</th><th>Precio Unitario</th><th>Total Vendido</th><th>Medio de Pago</th><th>Total Descuento</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($r = $res->fetch_assoc()): ?>
        <tr>
          <td><?= $r['carga'] ?></td>
          <td><?= $r['total_cantidad'] ?></td>
          <td>$<?= number_format($r['precio_unitario'], 0, '', '.') ?></td>
          <td>$<?= number_format($r['total_vendido'], 0, '', '.') ?></td>
          <td><?= $r['medio_pago'] ?></td>
          <td>$<?= number_format($r['total_descuento'], 0, '', '.') ?></td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
  <nav><ul class="pagination justify-content-center" id="paginacionResumen"></ul></nav>
</div>

<?php
$general = $conn->query("SELECT carga, SUM(cantidad) AS total_cantidad, AVG(precio_unitario) AS precio_prom, SUM(total) AS total_vendido, SUM(descuento) AS total_descuento FROM pedidos WHERE estado = 'Entregado' GROUP BY carga ORDER BY fecha DESC");
$pagos = $conn->query("SELECT medio_pago, COUNT(*) AS cantidad FROM pedidos WHERE estado = 'Entregado' GROUP BY medio_pago");
?>

<h5>Resumen General de Ventas (Entregados)</h5>
<div class="table-responsive mb-4">
  <input class="form-control mb-2" id="buscarGeneral" placeholder="Buscar...">
  <table class="table table-bordered text-center" id="tablaGeneral">
    <thead class="table-light">
      <tr><th>Carga</th><th>Cantidad</th><th>Precio Promedio</th><th>Total Vendido</th><th>Total Descuento</th></tr>
    </thead>
    <tbody>
      <?php while ($g = $general->fetch_assoc()): ?>
        <tr>
          <td><?= $g['carga'] ?></td>
          <td><?= $g['total_cantidad'] ?></td>
          <td>$<?= number_format($g['precio_prom'], 0, '', '.') ?></td>
          <td>$<?= number_format($g['total_vendido'], 0, '', '.') ?></td>
          <td>$<?= number_format($g['total_descuento'], 0, '', '.') ?></td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
  <nav><ul class="pagination justify-content-center" id="paginacionGeneral"></ul></nav>
</div>

<h5>Desglose por Medio de Pago</h5>
<div class="table-responsive mb-5">
  <table class="table table-bordered text-center">
    <thead class="table-light">
      <tr><th>Medio de Pago</th><th>Pedidos</th></tr>
    </thead>
    <tbody>
      <?php while ($p = $pagos->fetch_assoc()): ?>
        <tr>
          <td><?= $p['medio_pago'] ?></td>
          <td><?= $p['cantidad'] ?></td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<script>
  function aplicarPaginacion(tablaId, paginacionId, maxFilas = 5) {
    const tabla = document.getElementById(tablaId);
    const paginacion = document.getElementById(paginacionId);
    const filas = tabla.querySelectorAll("tbody tr");
    let paginaActual = 1;

    function mostrarPagina(pagina) {
      const inicio = (pagina - 1) * maxFilas;
      const fin = inicio + maxFilas;
      filas.forEach((fila, i) => {
        fila.style.display = (i >= inicio && i < fin) ? '' : 'none';
      });
    }

    function construirPaginacion() {
      paginacion.innerHTML = '';
      const totalPaginas = Math.ceil(filas.length / maxFilas);
      for (let i = 1; i <= totalPaginas; i++) {
        const li = document.createElement('li');
        li.classList.add('page-item');
        if (i === paginaActual) li.classList.add('active');
        li.innerHTML = `<a class="page-link" href="#">${i}</a>`;
        li.addEventListener('click', function(e) {
          e.preventDefault();
          paginaActual = i;
          mostrarPagina(i);
          construirPaginacion();
        });
        paginacion.appendChild(li);
      }
    }

    mostrarPagina(paginaActual);
    construirPaginacion();
  }

  document.getElementById('buscarAsignados').addEventListener('keyup', function() {
    const filtro = this.value.toLowerCase();
    const filas = document.querySelectorAll('#tablaAsignados tbody tr');
    filas.forEach(fila => {
      fila.style.display = fila.textContent.toLowerCase().includes(filtro) ? '' : 'none';
    });
  });

  document.getElementById('buscarResumen').addEventListener('keyup', function() {
    const filtro = this.value.toLowerCase();
    const filas = document.querySelectorAll('#tablaResumen tbody tr');
    filas.forEach(fila => {
      fila.style.display = fila.textContent.toLowerCase().includes(filtro) ? '' : 'none';
    });
  });

  document.getElementById('buscarGeneral').addEventListener('keyup', function() {
    const filtro = this.value.toLowerCase();
    const filas = document.querySelectorAll('#tablaGeneral tbody tr');
    filas.forEach(fila => {
      fila.style.display = fila.textContent.toLowerCase().includes(filtro) ? '' : 'none';
    });
  });

  window.addEventListener('DOMContentLoaded', () => {
    aplicarPaginacion('tablaAsignados', 'paginacionAsignados');
    aplicarPaginacion('tablaResumen', 'paginacionResumen');
    aplicarPaginacion('tablaGeneral', 'paginacionGeneral');
  });
</script>
