<?php
include 'db_connection.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$fecha_inicio = $_POST['fecha_inicio'] ?? null;
$fecha_fin = $_POST['fecha_fin'] ?? null;
$pagina = $_POST['pagina'] ?? 1;
$registros_por_pagina = 20;
$offset = ($pagina - 1) * $registros_por_pagina;

$params = [];

// Base de la consulta
$sql_base = "SELECT 
            cp.id, 
            cp.barcode,            
            es.nombre as establecimiento,
            pc.consumo as producto,
            pc.costo as importe,
            cp.cantidad,
            (cp.cantidad * pc.costo) AS total,
            tp.nombre as tipo_pago,
            cp.folio,
            cp.fecha_registro,
            cs.status as estatus
        FROM control_puntoventa cp 
        LEFT JOIN punto_consumo pc ON cp.consumo_id = pc.id
        LEFT JOIN tipo_pago tp ON cp.tipo_pago = tp.id
        LEFT JOIN establecimientos es ON es.id = pc.establecimiento_id
        LEFT JOIN catalog_status cs ON cs.status_id = cp.estatus

        WHERE 1=1"; // <- para que puedas agregar más condiciones después

if ($fecha_inicio && $fecha_fin) {
    $fecha_inicio = date('Y-m-d', strtotime($fecha_inicio)) . ' 00:00:00';
    $fecha_fin = date('Y-m-d', strtotime($fecha_fin)) . ' 23:59:59';

    $sql_base .= " AND cp.fecha_registro BETWEEN :fecha_inicio AND :fecha_fin";
    $params[':fecha_inicio'] = $fecha_inicio;
    $params[':fecha_fin'] = $fecha_fin;
}

// Primero contar SIN paginación
$stmt_count = $conn->prepare($sql_base);
foreach ($params as $key => &$value) {
    $stmt_count->bindParam($key, $value);
}
$stmt_count->execute();
$total_registros = $stmt_count->rowCount();

// Ahora agregar paginación
$sql_final = $sql_base . " LIMIT $offset, $registros_por_pagina";

$stmt = $conn->prepare($sql_final);
foreach ($params as $key => &$value) {
    $stmt->bindParam($key, $value);
}
$stmt->execute();
$registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Construir tabla
$tabla = '';
foreach ($registros as $registro) {
    $tabla .= '<tr>';
    $tabla .= "<td>{$registro['id']}</td>";
    $tabla .= "<td>{$registro['barcode']}</td>";
    $tabla .= "<td>{$registro['establecimiento']}</td>";
    $tabla .= "<td>{$registro['producto']}</td>";
    $tabla .= "<td>{$registro['importe']}</td>";
    $tabla .= "<td>{$registro['cantidad']}</td>";
    $tabla .= "<td>{$registro['total']}</td>";
    $tabla .= "<td>{$registro['tipo_pago']}</td>";
    $tabla .= "<td>{$registro['folio']}</td>";
    $tabla .= "<td>{$registro['fecha_registro']}</td>";
    $tabla .= "<td>{$registro['estatus']}</td>";
    $tabla .= '</tr>';
}

// Construir paginación
$total_paginas = ceil($total_registros / $registros_por_pagina);
$paginacion = '';
for ($i = 1; $i <= $total_paginas; $i++) {
    $active = $i == $pagina ? 'active' : '';
    $paginacion .= "<li class='page-item $active'><a href='#' class='page-link' data-pagina='$i'>$i</a></li>";
}

// Respuesta JSON
$response = [
    'tabla' => $tabla ?: '<tr><td colspan="9">No se encontraron resultados.</td></tr>',
    'paginacion' => $paginacion,
];

echo json_encode($response);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo 'JSON Error: ' . json_last_error_msg();
}
