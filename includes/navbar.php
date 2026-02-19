<?php
$usuario = $_SESSION['usuario'] ?? 'Invitado';
$rango = $_SESSION['rango'] ?? 'Sin rango';
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark px-3 shadow-sm">
  <a class="navbar-brand" href="#">
    <i class="fas fa-gas-pump me-2"></i>Distribuidora de Gas
  </a>

  <div class="collapse navbar-collapse">
    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
      <!-- Módulos principales -->
      <li class="nav-item">
        <a class="nav-link" href="#" onclick="cargarModulo('ventas')">Ventas</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#" onclick="cargarModulo('pedidos')">Pedidos</a>
      </li>
      <li class="nav-item">
  <a class="nav-link" href="#" onclick="cargarModulo('asignar_pedido')">Asignar Pedido</a>
    </li>
      <li class="nav-item">
        <a class="nav-link" href="#" onclick="cargarModulo('cuadratura')">Cuadratura</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#" onclick="cargarModulo('reportes')">Reportes</a>
      </li>
    </ul>
  </div>

  <div class="ms-auto d-flex align-items-center text-white">
    <span class="me-3">
      <i class="fas fa-user"></i> <?= $usuario ?> | <?= ucfirst($rango) ?>
    </span>
    <a href="logout.php" class="text-white" title="Cerrar sesión">
      <i class="fas fa-sign-out-alt"></i>
    </a>
  </div>
</nav>
