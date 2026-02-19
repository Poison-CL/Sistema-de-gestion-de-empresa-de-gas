<?php
require_once '../Includes/db.php';

header('Content-Type: application/json');
$tipo = $_GET['tipo'];
$inicio = $_GET['inicio'];
$fin = $_GET['fin'];
$movil = $_GET['movil'];

$where = "WHERE fecha BETWEEN '$inicio' AND '$fin'";
if ($movil !== '') $where .= " AND movil = '$movil'";

$tabla = '';
$resumen = '';
$grafico = ['labels' => [], 'values' => [], 'label' => ''];

// TODO: tus bloques de lógica de ventas, pedidos, cuadraturas...

echo json_encode(['tabla' => $tabla, 'resumen' => $resumen, 'grafico' => $grafico]);
exit;
?>
