<?php
// Backend completo
require_once '../Includes/db.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-Type: text/html; charset=UTF-8');
if (!isset($_GET['ajax'])):
?>

<div class="container mt-4">
  <h4 class="mb-4">Reportes Completos</h4>

  <!-- Filtros -->
  <form id="formFiltros" class="row g-3 mb-4">
    <div class="col-md-2">
      <select class="form-select" name="tipo">
        <option value="ventas">Ventas</option>
        <option value="pedidos">Pedidos</option>
        <option value="cuadraturas">Cuadraturas</option>
      </select>
    </div>
    <div class="col-md-2">
      <input type="date" name="inicio" class="form-control" value="<?= date('Y-m-01') ?>">
    </div>
    <div class="col-md-2">
      <input type="date" name="fin" class="form-control" value="<?= date('Y-m-d') ?>">
    </div>
    <div class="col-md-2">
      <select class="form-select" name="movil">
        <option value="">Todos los Móviles</option>
        <?php $m = $conn->query("SELECT DISTINCT movil FROM moviles");
        while ($r = $m->fetch_assoc()): ?>
          <option value="<?= $r['movil'] ?>"><?= $r['movil'] ?></option>
        <?php endwhile; ?>
      </select>
    </div>
    <div class="col-md-2">
      <select class="form-select" name="medio">
        <option value="">Todos los Medios</option>
        <?php $m = $conn->query("SELECT DISTINCT medio_pago FROM ventas_local");
        while ($r = $m->fetch_assoc()): ?>
          <option value="<?= $r['medio_pago'] ?>"><?= $r['medio_pago'] ?></option>
        <?php endwhile; ?>
      </select>
    </div>
    <div class="col-md-2">
      <select class="form-select" name="carga">
        <option value="">Todas las Cargas</option>
        <?php $c = $conn->query("SELECT DISTINCT carga FROM inventario");
        while ($r = $c->fetch_assoc()): ?>
          <option value="<?= $r['carga'] ?>"><?= $r['carga'] ?></option>
        <?php endwhile; ?>
      </select>
    </div>
    <div class="col-md-2">
      <button class="btn btn-primary w-100" type="submit">Filtrar</button>
    </div>
  </form>

  <!-- Resultados -->
  <div id="contenedorResumen" class="mb-3"></div>
  <div id="contenedorKilos" class="mb-3"></div>
  <div id="contenedorTabla"></div>
  <canvas id="grafico" height="100"></canvas>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.getElementById('formFiltros').addEventListener('submit', function(e) {
  e.preventDefault();
  const datos = new URLSearchParams(new FormData(this)).toString();
  fetch('modulos/reportes.php?ajax=1&' + datos)
  .then(r => r.json())
  .then(data => {
    document.getElementById('contenedorResumen').innerHTML = data.resumen + (data.kilos ?? '');
    document.getElementById('contenedorTabla').innerHTML = data.tabla;
        renderGrafico(data.grafico);
  })

    .catch(err => {
      alert('Error al cargar reporte');
      console.error(err);
    });
});

let chart;
function renderGrafico(data) {
  if (chart) chart.destroy();
  const ctx = document.getElementById('grafico');
  chart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: data.labels,
      datasets: [{ label: data.label, data: data.values }]
    },
    options: { responsive: true }
  });
}
</script>

<?php exit; endif; ?>

<?php
// ================= BACKEND JSON =====================

$inicio = $_GET['inicio'] ?? date('Y-m-01');
$fin = $_GET['fin'] ?? date('Y-m-d');
$tipo = $_GET['tipo'] ?? 'ventas';
$movil = $_GET['movil'] ?? '';
$medio = $_GET['medio'] ?? '';
$carga = $_GET['carga'] ?? '';
$where = "WHERE fecha >= '$inicio 00:00:00' AND fecha <= '$fin 23:59:59'";
if ($movil) $where .= " AND movil = '$movil'";
if ($medio) $where .= " AND medio_pago = '$medio'";
if ($carga) $where .= " AND carga = '$carga'";

$tabla = '';
$resumen = '';
$grafico = ['labels' => [], 'values' => [], 'label' => ''];

if ($tipo === 'ventas') {
    $total_kilos = 0; // Inicialización para evitar errores
    $kilos = 0;       // Si es necesario

    $q = $conn->query("SELECT fecha, carga, cantidad, total, medio_pago, descuento FROM ventas_local $where");
    $tabla .= '<table class="table table-bordered"><thead><tr><th>Fecha</th><th>Carga</th><th>Cantidad</th><th>Total</th><th>Medio de Pago</th><th>Descuento</th></tr></thead><tbody>';
    $tot = $cant = 0; $porMedio = []; $porCarga = [];
  
    while ($r = $q->fetch_assoc()) {
      // VALIDACIÓN de datos
      $fecha = $r['fecha'] ?? '-';
      $carga = $r['carga'] ?? '-';
      $cantidad = (int) $r['cantidad'];
      $total = (float) $r['total'];
      $medio = $r['medio_pago'] ?? '-';
      $desc = (float) $r['descuento'];

      preg_match('/\d+/', $r['carga'], $match);
      $soloNumeroCarga = isset($match[0]) ? intval($match[0]) : 0;
      $kilos = intval($r['cantidad']) * $soloNumeroCarga;
      $total_kilos += $kilos;
      
      $tabla .= "<tr>
        <td>$fecha</td>
        <td>$carga</td>
        <td>$cantidad</td>
        <td>\$$total</td>
        <td>$medio</td>
        <td>\$$desc</td>
      </tr>";
  
      $tot += $total;
      $cant += $cantidad;
      $porMedio[$medio] = ($porMedio[$medio] ?? 0) + $total;
      $porCarga[$carga] = ($porCarga[$carga] ?? 0) + $cantidad;
    }
  
    $tabla .= '</tbody></table>';
    $resumen .= "<div class='alert alert-info'><strong>Total Ventas:</strong> \$$tot — <strong>Total Cargas:</strong> $cant";
    foreach ($porMedio as $k => $v) $resumen .= " — <strong>$k:</strong> \$$v";
    $resumen .= '</div>';
    if ($porCarga) {
      $resumen .= "<div class='mt-2'><strong>Cargas Vendidas:</strong> ";
      foreach ($porCarga as $c => $q) $resumen .= "$c: $q &nbsp;";
      $resumen .= '</div>';
    }
  
    $grafico = [
      'labels' => array_keys($porCarga),
      'values' => array_values($porCarga),
      'label' => 'Ventas por carga'
    ];
  }
if ($tipo === 'pedidos') {
  $total_kilos = 0; // Inicialización para evitar errores
  $kilos = 0;       // Si es necesario
  $q = $conn->query("SELECT nombre_cliente, carga, cantidad, total, medio_pago, descuento, movil FROM pedidos $where");
  $tabla .= '<table class="table table-bordered"><thead><tr><th>Cliente</th><th>Carga</th><th>Cant</th><th>Total</th><th>Pago</th><th>Desc</th><th>Móvil</th></tr></thead><tbody>';
  $tot = $cant = 0; $porMedio = []; $porCarga = [];
  while ($r = $q->fetch_assoc()) {
    $tabla .= "<tr><td>{$r['nombre_cliente']}</td><td>{$r['carga']}</td><td>{$r['cantidad']}</td><td>\${$r['total']}</td><td>{$r['medio_pago']}</td><td>\${$r['descuento']}</td><td>{$r['movil']}</td></tr>";
    $tot += $r['total'];
    $cant += $r['cantidad'];
    $porMedio[$r['medio_pago']] = ($porMedio[$r['medio_pago']] ?? 0) + $r['total'];
    $porCarga[$r['carga']] = ($porCarga[$r['carga']] ?? 0) + $r['cantidad'];
    preg_match('/\d+/', $r['carga'], $match);
    $soloNumeroCarga = isset($match[0]) ? intval($match[0]) : 0;
    $kilos = intval($r['cantidad']) * $soloNumeroCarga;
    $total_kilos += $kilos;
  }
  $tabla .= '</tbody></table>';
  $resumen .= "<div class='alert alert-success'><strong>Total Pedidos:</strong> \$$tot — <strong>Total Cargas:</strong> $cant";
  foreach ($porMedio as $k => $v) $resumen .= " — <strong>$k:</strong> \$$v";
  $resumen .= '</div>';
  if ($porCarga) {
    $resumen .= "<div class='mt-2'><strong>Cargas Pedidas:</strong> ";
    foreach ($porCarga as $c => $q) $resumen .= "$c: $q &nbsp;";
    $resumen .= '</div>';
  }
 $grafico = ['labels' => array_keys($porCarga), 'values' => array_values($porCarga), 'label' => 'Pedidos por carga'];
}

if ($tipo === 'cuadraturas') {
  $total_kilos = 0; // Inicialización para evitar errores
  $kilos = 0;       // Si es necesario
  $q = $conn->query("SELECT m.movil, c.fecha, c.total_venta, c.total_debitos, c.total_cupones, c.total_descuentos, c.total_entregado, c.saldo FROM cuadraturas c JOIN moviles m ON m.id = c.movil_id $where");
  $tabla .= '<table class="table table-bordered"><thead><tr><th>Móvil</th><th>Fecha</th><th>Venta</th><th>Déb</th><th>Cupones</th><th>Desc</th><th>Entregado</th><th>Saldo</th></tr></thead><tbody>';
  $tv = $td = $tc = $desc = $ent = $sal = 0; $porMovil = [];
  while ($r = $q->fetch_assoc()) {
    $tabla .= "<tr><td>{$r['movil']}</td><td>{$r['fecha']}</td><td>\${$r['total_venta']}</td><td>\${$r['total_debitos']}</td><td>\${$r['total_cupones']}</td><td>\${$r['total_descuentos']}</td><td>\${$r['total_entregado']}</td><td>\${$r['saldo']}</td></tr>";
    $tv += $r['total_venta']; $td += $r['total_debitos']; $tc += $r['total_cupones'];
    $desc += $r['total_descuentos']; $ent += $r['total_entregado']; $sal += $r['saldo'];
    $porMovil[$r['movil']] = ($porMovil[$r['movil']] ?? 0) + $r['total_venta'];
    preg_match('/\d+/', $r['carga'], $match);
    $soloNumeroCarga = isset($match[0]) ? intval($match[0]) : 0;
    $kilos = intval($r['cantidad']) * $soloNumeroCarga;
    $total_kilos += $kilos;
  }
  $tabla .= '</tbody></table>';
  $resumen .= "<div class='alert alert-warning'><strong>Venta:</strong> \$$tv — <strong>Débitos:</strong> \$$td — <strong>Cupones:</strong> \$$tc — <strong>Descuentos:</strong> \$$desc — <strong>Entregado:</strong> \$$ent — <strong>Saldo:</strong> \$$sal</div>";
  $grafico = ['labels' => array_keys($porMovil), 'values' => array_values($porMovil), 'label' => 'Total venta por móvil'];
  $porCarga = [];
$q2 = $conn->query("SELECT detalle FROM cuadraturas c JOIN moviles m ON m.id = c.movil_id $where");
while ($r2 = $q2->fetch_assoc()) {
  $d = json_decode($r2['detalle'], true);
  if (isset($d['final'])) {
    foreach ($d['final'] as $item) {
      $porCarga[$item['carga']] = ($porCarga[$item['carga']] ?? 0) + $item['cantidad'];
    }
  }
}
if ($porCarga) {
  $resumen .= "<div class='mt-2'><strong>Cargas Finales por Móvil:</strong> ";
  foreach ($porCarga as $c => $q) $resumen .= "$c: $q &nbsp;";
  $resumen .= '</div>';
}

}
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Glosa de kilos vendidas
$kilosHTML = '';
if (isset($total_kilos)) {
  $kilosHTML = "<div class='alert alert-secondary'><strong>Kilos Vendidos:</strong> $total_kilos kg</div>";
} elseif (isset($tkilos)) {
  $kilosHTML = "<div class='alert alert-secondary'><strong>Kilos Vendidos:</strong> $tkilos kg</div>";
}
echo json_encode([
  'tabla' => $tabla,
  'resumen' => $resumen,
  'grafico' => $grafico,
  'kilos' => $kilosHTML
]);
exit;


if (!isset($tabla) || !isset($resumen) || !isset($grafico)) {
    http_response_code(500);
    echo json_encode(['error' => 'Error interno al procesar el reporte']);
    exit;
}
