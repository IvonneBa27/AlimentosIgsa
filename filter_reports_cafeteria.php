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
            cc.id, 
            cc.barcode,
            'SERVICIO DE CAFÉ' as producto,
            e.nombre AS empresa,
            d.nombre AS departamento,
            sc.nombre AS sala,
            cc.solicitante,
            cc.cantidad,
            cc.fecha_registro
        FROM control_cafeteria cc  
        LEFT JOIN empresa e ON cc.empresa_id = e.id
        LEFT JOIN departamento d ON cc.departamento_id = d.id
        LEFT JOIN salas_conferencias sc ON cc.sala_id = sc.id
        WHERE 1=1"; // <- para que puedas agregar más condiciones después

if ($fecha_inicio && $fecha_fin) {
    $fecha_inicio = date('Y-m-d', strtotime($fecha_inicio)) . ' 00:00:00';
    $fecha_fin = date('Y-m-d', strtotime($fecha_fin)) . ' 23:59:59';

    $sql_base .= " AND cc.fecha_registro BETWEEN :fecha_inicio AND :fecha_fin";
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
    $tabla .= "<td>{$registro['producto']}</td>";
    $tabla .= "<td>{$registro['empresa']}</td>";
    $tabla .= "<td>{$registro['sala']}</td>";
    $tabla .= "<td>{$registro['departamento']}</td>";
    $tabla .= "<td>{$registro['solicitante']}</td>";
    $tabla .= "<td>{$registro['cantidad']}</td>"; 
    $tabla .= "<td>{$registro['fecha_registro']}</td>";
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
?>
