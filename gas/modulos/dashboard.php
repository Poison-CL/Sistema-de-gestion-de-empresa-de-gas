<?php
session_start();
require_once '../includes/db.php';

$usuario = $_SESSION['usuario'] ?? 'Usuario';
$rango = $_SESSION['rango'] ?? 'Sin rango';

// 🟦 Ventas del Día
$hoy = date('Y-m-d');
$ventasDia = $conn->query("SELECT SUM(total) AS total FROM ventas_local WHERE DATE(fecha) = '$hoy'")->fetch_assoc()['total'] ?? 0;

// 🟨 Pedidos Pendientes
$pendientes = $conn->query("SELECT COUNT(*) AS total FROM pedidos WHERE movil = '' OR movil IS NULL")->fetch_assoc()['total'] ?? 0;

// 🟩 Stock Actual Total
$stock = $conn->query("SELECT SUM(cantidad) AS total FROM inventario")->fetch_assoc()['total'] ?? 0;

// 🟦 Cargas Realizadas (por facturas o guías)
$cargas = $conn->query("SELECT COUNT(*) AS total FROM facturas")->fetch_assoc()['total'] ?? 0;
?>

<div class="container">
  <!-- 🟦 Bienvenida -->
  <div class="mb-4">
    <h3>Hola, <?= ucfirst($usuario) ?> 👋</h3>
    <p class="text-muted">Rango: <?= ucfirst($rango) ?></p>
  </div>

  <!-- 🟩 Tarjetas de resumen -->
  <div class="row g-3">
    <div class="col-md-3">
      <div class="card shadow-sm border-0">
        <div class="card-body text-center">
          <h6>Ventas del Día</h6>
          <h4 class="text-primary">$<?= number_format($ventasDia, 0, ',', '.') ?></h4>
        </div>
      </div>
    </div>

    <div class="col-md-3">
      <div class="card shadow-sm border-0">
        <div class="card-body text-center">
          <h6>Pedidos Pendientes</h6>
          <h4 class="text-warning"><?= $pendientes ?></h4>
        </div>
      </div>
    </div>

    <div class="col-md-3">
      <div class="card shadow-sm border-0">
        <div class="card-body text-center">
          <h6>Stock Actual</h6>
          <h4 class="text-success"><?= $stock ?></h4>
        </div>
      </div>
    </div>

    <div class="col-md-3">
      <div class="card shadow-sm border-0">
        <div class="card-body text-center">
          <h6>Cargas Realizadas</h6>
          <h4 class="text-info"><?= $cargas ?></h4>
        </div>
      </div>
    </div>
  </div>
</div>

