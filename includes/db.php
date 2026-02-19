<?php
$host = "localhost";
$usuario = "root";
$clave = ""; // Cambia esto si tienes clave en tu servidor local
$base_datos = "distribuidora_gas";

$conn = new mysqli($host, $usuario, $clave, $base_datos);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
?>
