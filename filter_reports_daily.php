<?php
include 'db_connection.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$pagina = $_POST['pagina'] ?? 1;
$registros_por_pagina = 15;
$offset = ($pagina - 1) * $registros_por_pagina;

// Consulta para obtener registros del día actual
$sql = "
    SELECT 
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
    WHERE DATE(ca.fecha_registro) = CURDATE()
    ORDER BY ca.fecha_registro DESC
";

// Obtener el total de registros
$stmt_count = $conn->prepare($sql);
$stmt_count->execute();
$total_registros = $stmt_count->rowCount();

// Agregar paginación a la consulta
$sql .= " LIMIT :offset, :registros";
$stmt = $conn->prepare($sql);
$stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
$stmt->bindValue(':registros', (int)$registros_por_pagina, PDO::PARAM_INT);

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

$response = [
    'tabla' => $tabla ?: '<tr><td colspan="8">No se encontraron resultados.</td></tr>',
    'paginacion' => $paginacion,
];

echo json_encode($response);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo 'JSON Error: ' . json_last_error_msg();
}
?>
