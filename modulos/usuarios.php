<?php
session_start();
require_once '../Includes/db.php';

// Insertar nuevo usuario
if (isset($_POST['accion']) && $_POST['accion'] === 'agregar') {
    $usuario = $_POST['usuario'];
    $password = $_POST['password'];
    $rango = $_POST['rango'];

    $query = "INSERT INTO usuarios (usuario, password, rango) VALUES (?, SHA2(?, 256), ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sss", $usuario, $password, $rango);
    $stmt->execute();
    exit;
}

// Eliminar usuario
if (isset($_POST['accion']) && $_POST['accion'] === 'eliminar') {
    $id = $_POST['id'];
    $conn->query("DELETE FROM usuarios WHERE id = $id");
    exit;
}

// Cambiar contraseña
if (isset($_POST['accion']) && $_POST['accion'] === 'cambiar_clave') {
    $id = $_POST['id'];
    $clave = $_POST['clave'];
    $query = "UPDATE usuarios SET password = SHA2(?, 256) WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $clave, $id);
    $stmt->execute();
    exit;
}

// Obtener lista de usuarios
$usuarios = $conn->query("SELECT id, usuario, rango FROM usuarios ORDER BY id DESC");
?>

<div class="container">
  <h4 class="mb-4">Gestión de Usuarios</h4>

  <!-- 🟢 Formulario -->
  <form id="formUsuario" class="row g-3 mb-4">
    <input type="hidden" name="accion" value="agregar">
    <div class="col-md-4">
      <input type="text" name="usuario" id="usuario" class="form-control" placeholder="Usuario" required>
    </div>
    <div class="col-md-4">
      <input type="password" name="password" id="password" class="form-control" placeholder="Contraseña" required>
    </div>
    <div class="col-md-3">
      <select name="rango" id="rango" class="form-select" required>
        <option value="">Seleccione rango</option>
        <option value="administrador">Administrador</option>
        <option value="vendedor">Vendedor</option>
      </select>
    </div>
    <div class="col-md-1">
      <button type="submit" class="btn btn-success w-100" id="btnGuardar" disabled><i class="fas fa-plus"></i></button>
    </div>
  </form>

  <!-- 🟡 Tabla dinámica -->
  <div class="table-responsive">
    <table class="table table-bordered align-middle text-center">
      <thead class="table-dark">
        <tr>
          <th>Usuario</th>
          <th>Rango</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody id="tablaUsuarios">
        <?php while ($row = $usuarios->fetch_assoc()): ?>
          <tr>
            <td><?= $row['usuario'] ?></td>
            <td><?= ucfirst($row['rango']) ?></td>
            <td>
              <button class="btn btn-sm btn-warning me-2" onclick="abrirModalClave(<?= $row['id'] ?>)">
                <i class="fas fa-key"></i>
              </button>
              <button class="btn btn-sm btn-danger" onclick="eliminarUsuario(<?= $row['id'] ?>)">
                <i class="fas fa-trash"></i>
              </button>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- 🔴 Modal cambiar clave -->
<div class="modal fade" id="modalClave" tabindex="-1">
  <div class="modal-dialog">
    <form class="modal-content" id="formClave">
      <input type="hidden" name="accion" value="cambiar_clave">
      <input type="hidden" name="id" id="claveId">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="fas fa-key me-2"></i>Cambiar Contraseña</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="password" name="clave" class="form-control" placeholder="Nueva contraseña" required>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Guardar</button>
      </div>
    </form>
  </div>
</div>

<script>
  // Habilitar botón al completar campos
  $('#usuario, #password, #rango').on('input change', function () {
    const u = $('#usuario').val().trim();
    const p = $('#password').val().trim();
    const r = $('#rango').val();
    $('#btnGuardar').prop('disabled', !(u && p && r));
  });

  // Enviar formulario AJAX
  $('#formUsuario').on('submit', function(e) {
    e.preventDefault();
    $.post('modulos/usuarios.php', $(this).serialize(), function() {
      cargarModulo('usuarios');
    });
  });

  // Eliminar usuario
  function eliminarUsuario(id) {
    if (confirm('¿Eliminar este usuario?')) {
      $.post('modulos/usuarios.php', {accion: 'eliminar', id}, function() {
        cargarModulo('usuarios');
      });
    }
  }

  // Abrir modal de cambio de clave
  function abrirModalClave(id) {
    $('#claveId').val(id);
    $('#modalClave').modal('show');
  }

  // Guardar nueva clave
  $('#formClave').on('submit', function(e) {
    e.preventDefault();
    $.post('modulos/usuarios.php', $(this).serialize(), function() {
      $('#modalClave').modal('hide');
    });
  });
</script>
