<?php

require 'vendor/autoload.php'; // Incluye PhpSpreadsheet
include 'db_connection.php'; // Conexión a la base de datos

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Capturar filtros enviados por GET
$fecha_inicio = $_GET['fecha_inicio'] ?? null;
$fecha_fin = $_GET['fecha_fin'] ?? null;
$tipo_producto = $_GET['tipo_producto'] ?? null;
$tipo_usuario = $_GET['tipo_usuario'] ?? null;
$num_empleado = $_GET['num_empleado'] ?? null;

// Construir la consulta con parámetros preparados
$query = "
    SELECT 
        ca.id AS ID, 
        tu.tipo_usuario AS TIPO_USUARIO, 
        ca.num_empleado AS NUM_EMPLEADO,
        cm.nombre_completo AS NOMBRE_COMPLETO,
        e.nombre  AS EMPRESA,
        d.nombre AS DEPARTAMENTO,
        p.producto AS PRODUCTO,
        FORMAT(p.costo, 2) AS IMPORTE,
        ca.fecha_registro AS FECHA_REGISTRO
    FROM control_alimentos ca
    LEFT JOIN comensal cm ON ca.barcode = cm.barcode
    LEFT JOIN producto p ON ca.tipo_producto = p.id
    LEFT JOIN tipo_usuario tu ON ca.comensal_id = tu.id
    LEFT JOIN empresa e ON cm.empresa = e.id
    LEFT JOIN departamento d ON cm.departamento = d.id
    WHERE 1 = 1
";

// Inicializar los parámetros de la consulta
$params = [];

// Agregar filtros a la consulta
if ($fecha_inicio && $fecha_fin) {
    $query .= " AND ca.fecha_registro BETWEEN :fecha_inicio AND :fecha_fin";
    $params[':fecha_inicio'] = $fecha_inicio . ' 00:00:00';
    $params[':fecha_fin'] = $fecha_fin . ' 23:59:59';
}

if ($tipo_producto) {
    $query .= " AND p.id = :tipo_producto";
    $params[':tipo_producto'] = $tipo_producto;
}
if ($tipo_usuario) {
    $query .= " AND tu.id = :tipo_usuario";
    $params[':tipo_usuario'] = $tipo_usuario;
}
if ($num_empleado) {
    $query .= " AND ca.num_empleado = :num_empleado";
    $params[':num_empleado'] = $num_empleado;
}

// Preparar la consulta
$stmt = $conn->prepare($query);

// Vincular los parámetros
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}

// Ejecutar la consulta
$stmt->execute();

// Obtener los resultados
$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Verificar si hay resultados
if ($stmt->rowCount() === 0) {
    die('No se encontraron registros con los filtros seleccionados.');
}

// Crear un nuevo documento Excel
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Configurar encabezados
$headers = ['ID', 'TIPO USUARIO', 'NÚM. DE EMPLEADO', 'NOMBRE COMPLETO', 'EMPRESA', 'DEPARTAMENTO', 'PRODUCTO', 'IMPORTE', 'FECHA DE REGISTRO'];
$sheet->fromArray($headers, null, 'A1');

// Agregar datos
$row = 2; 
foreach ($resultados as $data) {
    $sheet->fromArray(array_values($data), null, 'A' . $row);
    $row++;
}

// Establecer nombre del archivo
$filename = 'Reporte_' . date('Ymd_His') . '.xlsx';

// Limpiar cualquier salida previa
if (ob_get_contents()) {
    ob_end_clean();
}

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit();
?>
