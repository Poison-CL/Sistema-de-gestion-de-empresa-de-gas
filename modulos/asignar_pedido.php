<?php
session_start();
require_once '../Includes/db.php';

// Asignar pedido a móvil
if (isset($_POST['accion']) && $_POST['accion'] === 'asignar_pedido') {
  $id = $_POST['id'];
  $movil = $_POST['movil'];
  $conn->query("UPDATE pedidos SET movil = '$movil', estado = 'No asignado' WHERE id = $id");
  exit;
}

// Cambiar estado de pedido
if (isset($_POST['accion']) && $_POST['accion'] === 'cambiar_estado') {
  $id = $_POST['id'];
  $estado = $_POST['estado'];
  $conn->query("UPDATE pedidos SET estado = '$estado' WHERE id = $id");
  exit;
}

// Eliminar pedido
if (isset($_POST['accion']) && $_POST['accion'] === 'eliminar_pedido') {
  $id = $_POST['id'];
  $conn->query("DELETE FROM pedidos WHERE id = $id");
  exit;
}

$moviles = $conn->query("SELECT movil FROM moviles ORDER BY movil ASC");
$por_asignar = $conn->query("SELECT * FROM pedidos WHERE estado IS NULL OR estado = 'No Asignado'");
?>

<div class="container">
  <h4 class="mb-4">Asignar Pedido a Móvil</h4>

  <!-- Selección de móvil -->
  <div class="row mb-4">
    <div class="col-md-4">
      <select id="movilSeleccionado" class="form-select" onchange="mostrarAsignados()">
        <option value="">Seleccionar Móvil</option>
        <?php while ($m = $moviles->fetch_assoc()): ?>
          <option value="<?= $m['movil'] ?>"><?= $m['movil'] ?></option>
        <?php endwhile; ?>
      </select>
    </div>
  </div>

  <!-- Tabla de pedidos por asignar -->
  <h5>Pedidos por asignar</h5>
  <div class="table-responsive mb-4">
    <table class="table table-bordered text-center">
      <thead class="table-dark">
        <tr>
          <th>#</th><th>Fecha</th><th>Carga</th><th>Cantidad</th><th>Cliente</th><th>Dirección</th><th>Estado</th><th>Asignar</th><th>Editar</th><th>Eliminar</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($p = $por_asignar->fetch_assoc()): ?>
          <tr>
            <td><?= $p['numero_pedido'] ?></td>
            <td><?= $p['fecha'] ?></td>
            <td><?= $p['carga'] ?></td>
            <td><?= $p['cantidad'] ?></td>
            <td><?= $p['nombre_cliente'] ?></td>
            <td><?= $p['direccion_cliente'] ?></td>
            <td>Sin Asignar</td>
            <td><button class="btn btn-primary btn-sm" onclick="asignarPedido(<?= $p['id'] ?>)">Asignar</button></td>
            <td><button class="btn btn-warning btn-sm">Editar</button></td>
            <td><button class="btn btn-danger btn-sm" onclick="eliminarPedido(<?= $p['id'] ?>)">Eliminar</button></td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>

  <!-- Tabla de pedidos asignados -->
  <div id="tablaAsignados"></div>
</div>

<script>
function asignarPedido(id) {
  const movil = $('#movilSeleccionado').val();
  if (!movil) return alert('Debe seleccionar un móvil primero.');
  $.post('modulos/asignar_pedido.php', {accion: 'asignar_pedido', id, movil}, function() {
    cargarModulo('asignar_pedido');
  });
}

function eliminarPedido(id) {
  if (!confirm('¿Eliminar este pedido?')) return;
  $.post('modulos/asignar_pedido.php', {accion: 'eliminar_pedido', id}, function() {
    cargarModulo('asignar_pedido');
  });
}

function cambiarEstado(id, estado) {
  $.post('modulos/asignar_pedido.php', {accion: 'cambiar_estado', id, estado}, function() {
    cargarModulo('asignar_pedido');
  });
}

function mostrarAsignados() {
  const movil = $('#movilSeleccionado').val();
  if (!movil) return $('#tablaAsignados').html('');

  $.get('modulos/ajax_pedidos_movil.php?movil=' + movil, function(html) {
    $('#tablaAsignados').html(html);
  });
}
</script>
