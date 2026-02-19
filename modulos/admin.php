<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rango'] !== 'administrador') {
  echo "<div class='alert alert-danger'>Acceso restringido.</div>";
  exit;
}
?>

<div class="container">
  <h4 class="mb-4"><i class="fas fa-cogs me-2"></i>Panel de Administración</h4>

  <div class="row g-3">

    <!-- 1. Gestión de Usuarios -->
    <div class="col-md-4">
      <div class="card shadow-sm h-100">
        <div class="card-body text-center">
          <i class="fas fa-users-cog fa-2x mb-2 text-primary"></i>
          <h6>Gestión de Usuarios</h6>
          <a href="#" onclick="cargarModulo('usuarios')" class="stretched-link"></a>
        </div>
      </div>
    </div>

    <!-- 2. Gestión de Clientes -->
    <div class="col-md-4">
      <div class="card shadow-sm h-100">
        <div class="card-body text-center">
          <i class="fas fa-user-friends fa-2x mb-2 text-success"></i>
          <h6>Gestión de Clientes</h6>
          <a href="#" onclick="cargarModulo('clientes')" class="stretched-link"></a>
        </div>
      </div>
    </div>

    <!-- 3. Gestión de Inventario -->
    <div class="col-md-4">
      <div class="card shadow-sm h-100">
        <div class="card-body text-center">
          <i class="fas fa-boxes fa-2x mb-2 text-warning"></i>
          <h6>Gestión de Inventario</h6>
          <a href="#" onclick="cargarModulo('inventario')" class="stretched-link"></a>
        </div>
      </div>
    </div>

    <!-- 4. Gestión de Móviles y Stock -->
    <div class="col-md-4">
      <div class="card shadow-sm h-100">
        <div class="card-body text-center">
          <i class="fas fa-truck-moving fa-2x mb-2 text-danger"></i>
          <h6>Gestión de Móviles y Stock</h6>
          <a href="#" onclick="cargarModulo('moviles')" class="stretched-link"></a>
        </div>
      </div>
    </div>

    <!-- 5. Gestión de Facturas / Guías -->
    <div class="col-md-4">
      <div class="card shadow-sm h-100">
        <div class="card-body text-center">
          <i class="fas fa-file-invoice-dollar fa-2x mb-2 text-info"></i>
          <h6>Gestión de Facturas / Guías</h6>
          <a href="#" onclick="cargarModulo('facturas')" class="stretched-link"></a>
        </div>
      </div>
    </div>

    <!-- 6. Gestión de Medios de Pago -->
    <div class="col-md-4">
      <div class="card shadow-sm h-100">
        <div class="card-body text-center">
          <i class="fas fa-credit-card fa-2x mb-2 text-secondary"></i>
          <h6>Gestión de Medios de Pago</h6>
          <a href="#" onclick="cargarModulo('pagos')" class="stretched-link"></a>
        </div>
      </div>
    </div>

    <!-- 7. Gestión de Descuentos -->
    <div class="col-md-4">
      <div class="card shadow-sm h-100">
        <div class="card-body text-center">
          <i class="fas fa-percentage fa-2x mb-2 text-success"></i>
          <h6>Gestión de Descuentos</h6>
          <a href="#" onclick="cargarModulo('descuentos')" class="stretched-link"></a>
        </div>
      </div>
    </div>

    <!-- 8. Gestión de Ventas -->
    <div class="col-md-4">
      <div class="card shadow-sm h-100">
        <div class="card-body text-center">
          <i class="fas fa-shopping-cart fa-2x mb-2 text-primary"></i>
          <h6>Gestión de Ventas</h6>
          <a href="#" onclick="cargarModulo('ventas')" class="stretched-link"></a>
        </div>
      </div>
    </div>

    <!-- 9. Gestión de Pedidos -->
    <div class="col-md-4">
      <div class="card shadow-sm h-100">
        <div class="card-body text-center">
          <i class="fas fa-list fa-2x mb-2 text-warning"></i>
          <h6>Gestión de Pedidos</h6>
          <a href="#" onclick="cargarModulo('pedidos')" class="stretched-link"></a>
        </div>
      </div>
    </div>

    <!-- 10. Gestión de Cuadratura -->
    <div class="col-md-4">
      <div class="card shadow-sm h-100">
        <div class="card-body text-center">
          <i class="fas fa-balance-scale fa-2x mb-2 text-dark"></i>
          <h6>Gestión de Cuadratura</h6>
          <a href="#" onclick="cargarModulo('cuadratura')" class="stretched-link"></a>
        </div>
      </div>
    </div>

    <!-- 11. Reportes -->
    <div class="col-md-4">
      <div class="card shadow-sm h-100">
        <div class="card-body text-center">
          <i class="fas fa-chart-bar fa-2x mb-2 text-info"></i>
          <h6>Reportes</h6>
          <a href="#" onclick="cargarModulo('reportes')" class="stretched-link"></a>
        </div>
      </div>
    </div>

  </div>
</div>
