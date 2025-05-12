<?php
include 'db_connection.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$fecha_inicio = $_POST['fecha_inicio'] ?? null;
$fecha_fin = $_POST['fecha_fin'] ?? null;
$tipo_producto = $_POST['tipo_producto'] ?? null;
$tipo_usuario = $_POST['tipo_usuario'] ?? null;
$num_empleado = $_POST['num_empleado'] ?? null;
$pagina = $_POST['pagina'] ?? 1;
$registros_por_pagina = 20;
$offset = ($pagina - 1) * $registros_por_pagina;

$params = [];

$sql = "SELECT 
            ca.id, 
            tu.tipo_usuario, 
            ca.num_empleado AS num_empleado,
            cm.nombre_completo AS nombre_completo,
            e.nombre AS empresa,
            d.nombre AS departamento,
            p.producto, 
            FORMAT(p.costo, 2) AS importe,
            ca.fecha_registro AS fecha_registro
        FROM control_alimentos ca
        LEFT JOIN comensal cm ON ca.barcode = cm.barcode
        LEFT JOIN producto p ON ca.tipo_producto = p.id
        LEFT JOIN tipo_usuario tu ON ca.comensal_id = tu.id
        LEFT JOIN empresa e ON cm.empresa = e.id
        LEFT JOIN departamento d ON cm.departamento = d.id
        WHERE 1=1";

if ($fecha_inicio && $fecha_fin) {
    // Convertir fechas al formato correcto
    $fecha_inicio = date('Y-m-d', strtotime($fecha_inicio)) . ' 00:00:00';
    $fecha_fin = date('Y-m-d', strtotime($fecha_fin)) . ' 23:59:59';

    $sql .= " AND ca.fecha_registro BETWEEN :fecha_inicio AND :fecha_fin";
    $params[':fecha_inicio'] = $fecha_inicio;
    $params[':fecha_fin'] = $fecha_fin;
}

if ($tipo_producto) {
    $sql .= " AND ca.tipo_producto = :tipo_producto";
    $params[':tipo_producto'] = $tipo_producto;
}

if ($tipo_usuario) {
    $sql .= " AND ca.comensal_id = :tipo_usuario";
    $params[':tipo_usuario'] = $tipo_usuario;
}

if ($num_empleado) {
    $sql .= " AND ca.num_empleado = :num_empleado";
    $params[':num_empleado'] = $num_empleado;
}

// Contar registros totales sin paginación
$stmt_count = $conn->prepare($sql);
foreach ($params as $key => &$value) {
    $stmt_count->bindParam($key, $value);
}
$stmt_count->execute();
$total_registros = $stmt_count->rowCount();

// Aquí está la corrección en el LIMIT: no debes usar bindValue para los valores de LIMIT
$sql .= " LIMIT $offset, $registros_por_pagina";  // Pasamos los valores directamente

$stmt = $conn->prepare($sql);
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
    $tabla .= "<td>{$registro['tipo_usuario']}</td>";
    $tabla .= "<td>{$registro['num_empleado']}</td>";
    $tabla .= "<td>{$registro['nombre_completo']}</td>";
    $tabla .= "<td>{$registro['empresa']}</td>";
    $tabla .= "<td>{$registro['departamento']}</td>";
    $tabla .= "<td>{$registro['producto']}</td>";
    $tabla .= "<td>{$registro['importe']}</td>";
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
