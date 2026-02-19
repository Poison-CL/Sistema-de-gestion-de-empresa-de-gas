<?php
session_start();
require_once 'Includes/db.php';

$usuario = $_POST['usuario'] ?? '';
$clave = $_POST['clave'] ?? '';

if ($usuario && $clave) {
    $query = "SELECT * FROM usuarios WHERE usuario = ? AND password = SHA2(?, 256) LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $usuario, $clave);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $usuarioData = $resultado->fetch_assoc();
        $_SESSION['usuario'] = $usuarioData['usuario'];
        $_SESSION['rango'] = $usuarioData['rango'];
        header("Location: dashboard.php");
        exit;
    } else {
        header("Location: index.php?error=1");
        exit;
    }
} else {
    header("Location: index.php?error=1");
    exit;
}
?>
