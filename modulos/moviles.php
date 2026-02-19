<?php
session_start();
require_once '../Includes/db.php';

// Agregar móvil
if (isset($_POST['accion']) && $_POST['accion'] === 'agregar_movil') {
  $telefono = $_POST['telefono'];
  $movil = $_POST['movil'];
  $patente = $_POST['patente'];

  // Verificar si el móvil ya existe
  $query = $conn->prepare("SELECT * FROM moviles WHERE movil = ?");
  $query->bind_param("s", $movil);
  $query->execute();
  $result = $query->get_result();

  if ($result->num_rows > 0) {
    echo "error:mobil_existente";
    exit;
  }

  // Insertar móvil
  $query = "INSERT INTO moviles (telefono, movil, patente) VALUES (?, ?, ?)";
  $stmt = $conn->prepare($query);
  $stmt->bind_param("sss", $telefono, $movil, $patente);
  $stmt->execute();
  
  exit;
}

// Agregar stock
if (isset($_POST['accion']) && $_POST['accion'] === 'agregar_stock') {
  $movil_id = $_POST['movil_id'];
  $carga = $_POST['carga'];
  $cantidad = $_POST['cantidad'];

  // Verificar si hay suficiente stock en el inventario
  $query = $conn->prepare("SELECT cantidad FROM inventario WHERE carga = ?");
  $query->bind_param("s", $carga);
  $query->execute();
  $res = $query->get_result()->fetch_assoc();

  if ($res['cantidad'] < $cantidad) {
    echo "error:stock_insuficiente";
    exit;
  }

  // Insertar el stock asignado al móvil
  $stmt = $conn->prepare("INSERT INTO stock_moviles (movil_id, carga, cantidad) VALUES (?, ?, ?)");
  $stmt->bind_param("isi", $movil_id, $carga, $cantidad);
  $stmt->execute();

  // Descontar el inventario
  $stmt = $conn->prepare("UPDATE inventario SET cantidad = cantidad - ? WHERE carga = ?");
  $stmt->bind_param("is", $cantidad, $carga);
  $stmt->execute();

  exit;
}
// Eliminar stock
if (isset($_POST['accion']) && $_POST['accion'] === 'eliminar') {
    $id = $_POST['id'];
    $conn->query("DELETE FROM stock_movil WHERE id = $id");
    exit;
  }
// Obtener los móviles
$moviles = $conn->query("SELECT * FROM moviles ORDER BY movil ASC");

// Obtener las cargas disponibles
$cargas = $conn->query("SELECT carga FROM inventario");

// Eliminar stock asignado
if (isset($_POST['accion']) && $_POST['accion'] === 'eliminar_stock') {
    $id = $_POST['id'];
    $cantidad = $_POST['cantidad'];
    $carga = $_POST['carga'];
  
    // Eliminar el registro de stock en stock_moviles
    $query = $conn->prepare("DELETE FROM stock_moviles WHERE id = ?");
    $query->bind_param("i", $id);
    $query->execute();
  
    // Recuperar el precio de la carga en inventario
    $query = $conn->prepare("UPDATE inventario SET cantidad = cantidad + ? WHERE carga = ?");
    $query->bind_param("is", $cantidad, $carga);
    $query->execute();
  
    echo "success";
    exit;
  }
 // Editar móvil
if (isset($_POST['accion']) && $_POST['accion'] === 'editar_movil') {
    $id = $_POST['id'];
    $telefono = $_POST['telefono'];
    $movil = $_POST['movil'];
    $patente = $_POST['patente'];
  
    $stmt = $conn->prepare("UPDATE moviles SET telefono = ?, movil = ?, patente = ? WHERE id = ?");
    $stmt->bind_param("sssi", $telefono, $movil, $patente, $id);
    $stmt->execute();
    exit;
  }
  
  // Eliminar móvil
  if (isset($_POST['accion']) && $_POST['accion'] === 'eliminar_movil') {
    $id = $_POST['id'];
  
    $stmt = $conn->prepare("DELETE FROM moviles WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    exit;
  }
  
?>

<div class="container">
  <h4 class="mb-4">Gestión de Móviles</h4>

  <!-- Formulario para agregar móvil -->
  <form id="formMovil" class="row g-3 mb-4">
    <input type="hidden" name="accion" value="agregar_movil">
    <div class="col-md-3">
      <input type="text" name="telefono" class="form-control" placeholder="Teléfono" required>
    </div>
    <div class="col-md-3">
      <input type="text" name="movil" class="form-control" placeholder="Móvil" required>
    </div>
    <div class="col-md-3">
      <input type="text" name="patente" class="form-control" placeholder="Patente" required>
    </div>
    <div class="col-md-3">
      <button type="submit" class="btn btn-success w-100">Agregar Móvil</button>
    </div>
  </form>
  <form id="formEditarMovil" class="row g-3 mb-4" style="display: none;">
  <input type="hidden" name="accion" value="editar_movil">
  <input type="hidden" name="id" id="editar_id">
  <div class="col-md-3"><input type="text" name="telefono" id="editar_telefono" class="form-control" placeholder="Teléfono" required></div>
  <div class="col-md-3"><input type="text" name="movil" id="editar_movil" class="form-control" placeholder="Móvil" required></div>
  <div class="col-md-3"><input type="text" name="patente" id="editar_patente" class="form-control" placeholder="Patente" required></div>
  <div class="col-md-3">
    <button type="submit" class="btn btn-primary w-100">Actualizar Móvil</button>
  </div>
</form>
  <!-- Tabla de Móviles Agregados -->
  <div class="table-responsive mb-4">
  <h5>Móviles Registrados</h5>
  <table class="table table-bordered text-center">
    <thead class="table-dark">
      <tr>
        <th>Teléfono</th>
        <th>Móvil</th>
        <th>Patente</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php
      $moviles = $conn->query("SELECT * FROM moviles ORDER BY id DESC");
      while ($row = $moviles->fetch_assoc()):
      ?>
        <tr>
          <td><?= $row['telefono'] ?></td>
          <td><?= $row['movil'] ?></td>
          <td><?= $row['patente'] ?></td>
          <td>
            <button class="btn btn-warning btn-sm" onclick="editarMovil(<?= $row['id'] ?>, '<?= $row['telefono'] ?>', '<?= $row['movil'] ?>', '<?= $row['patente'] ?>')">
              Editar
            </button>
            <button class="btn btn-danger btn-sm" onclick="eliminarMovil(<?= $row['id'] ?>)">
              Eliminar
              </button>
              <button class="btn btn-info btn-sm" onclick="mostrarCargas(<?= $row['id'] ?>, '<?= $row['movil'] ?>')">Asignar Stock</button>
             </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>
  <div class="table-responsive mb-4">
  <h5>Stock Asignado por Móvil</h5>
  <table class="table table-bordered text-center">
    <thead class="table-dark">
      <tr>
        <th>Móvil</th>
        <th>Carga</th>
        <th>Cantidad Asignada</th>
        <th>Eliminar</th>
      </tr>
    </thead>
    <tbody>
      <?php
      // Obtener el stock asignado por cada móvil
      $stockQuery = $conn->query("SELECT moviles.movil, stock_moviles.id, stock_moviles.carga, stock_moviles.cantidad
                                  FROM stock_moviles
                                  JOIN moviles ON moviles.id = stock_moviles.movil_id");

      while ($stockRow = $stockQuery->fetch_assoc()) {
        echo "<tr>
                <td>{$stockRow['movil']}</td>
                <td>{$stockRow['carga']}</td>
                <td>{$stockRow['cantidad']}</td>
                <td><button class='btn btn-danger btn-sm' onclick='eliminarStock({$stockRow['id']}, {$stockRow['cantidad']}, \"{$stockRow['carga']}\")'>Eliminar</button></td>
              </tr>";
      }
      ?>
    </tbody>
  </table>
</div>
  <!-- Agregar stock -->
  <div id="agregarStockModal" class="mb-4" style="display:none;">
    <h5>Agregar Stock para <span id="movilSeleccionado"></span></h5>
    <form id="formStock" class="row g-3">
      <input type="hidden" name="accion" value="agregar_stock">
      <input type="hidden" name="movil_id" id="movil_id">
      <div class="col-md-4">
      <select name="carga" class="form-select" id="cargaSelect" required>
  <option value="">Seleccionar Carga</option>
  <?php
  // Asegurarnos de que obtenemos las cargas disponibles
  $cargas = $conn->query("SELECT carga FROM inventario");

if ($cargas->num_rows == 0) {
  echo "No hay cargas disponibles en el inventario.";
} else {
  echo "Cargas encontradas: " . $cargas->num_rows;
}
  while ($row = $cargas->fetch_assoc()) {
    echo '<option value="' . $row['carga'] . '">' . $row['carga'] . '</option>';
  }
  ?>
</select>
      </div>
      <div class="col-md-4">
        <input type="number" name="cantidad" class="form-control" placeholder="Cantidad" min="1" required>
      </div>
      <div class="col-md-4">
        <button type="submit" class="btn btn-success w-100">Agregar</button>
      </div>
    </form>
    <div id="glosaStock" class="mt-3"></div>
  </div>
</div>

<script>
$('#formMovil').on('submit', function(e) {
  e.preventDefault();
  $.post('modulos/moviles.php', $(this).serialize(), function(res) {
    if (res === 'error:mobil_existente') {
      alert('Este móvil ya existe.');
    } else {
      cargarModulo('moviles');
    }
  });
});

$('#formStock').on('submit', function(e) {
  e.preventDefault();
  $.post('modulos/moviles.php', $(this).serialize(), function(res) {
    if (res === 'error:stock_insuficiente') {
      alert('No hay suficiente stock en el inventario para esta carga.');
    } else {
      $('#agregarStockModal').hide();
      cargarModulo('moviles');
    }
  });
});

function mostrarCargas(movilId, movil) {
  $('#movil_id').val(movilId);
  $('#movilSeleccionado').text(movil);
  $('#agregarStockModal').show();
}

  // Eliminar carga
  function eliminarCarga(id) {
    if (confirm('¿Eliminar stock del móvil?')) {
      $.post('modulos/moviles.php', {accion: 'eliminar', id}, function() {
        cargarModulo('moviles');
      });
    }
  }
  function eliminarStock(id, cantidad, carga) {
  if (confirm('¿Estás seguro de que deseas eliminar este stock?')) {
    $.post('modulos/moviles.php', {
      accion: 'eliminar_stock',
      id: id,
      cantidad: cantidad,
      carga: carga
    }, function(response) {
      if (response === 'success') {
        alert('Stock eliminado correctamente.');
        cargarModulo('moviles'); // Recargar el módulo para ver los cambios
      } else {
        alert('Hubo un error al eliminar el stock.');
      }
    });
  }
}
function editarMovil(id, telefono, movil, patente) {
  $('#editar_id').val(id);
  $('#editar_telefono').val(telefono);
  $('#editar_movil').val(movil);
  $('#editar_patente').val(patente);
  $('#formEditarMovil').show();
}

$('#formEditarMovil').on('submit', function(e) {
  e.preventDefault();
  $.post('modulos/moviles.php', $(this).serialize(), function() {
    cargarModulo('moviles');
  });
});

function eliminarMovil(id) {
  if (confirm('¿Deseas eliminar este móvil? Esto eliminará también su stock.')) {
    $.post('modulos/moviles.php', {accion: 'eliminar_movil', id: id}, function() {
      cargarModulo('moviles');
    });
  }
}

</script>
