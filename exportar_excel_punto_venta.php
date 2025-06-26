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
            cp.id as ID, 
            cp.barcode as CODIGO,            
            es.nombre as ESTABLECIMIENTO,
            pc.consumo as PRODUCTO,
            pc.costo as IMPORTE,
            cp.cantidad as CANTIDAD,
            (cp.cantidad * pc.costo) AS TOTAL,
            tp.nombre as TIPO_PAGO,
            cp.folio as FOLIO,
            cp.fecha_registro as FECHA_REGISTRO,
            cs.status as ESTATUS
        FROM control_puntoventa cp 
        LEFT JOIN punto_consumo pc ON cp.consumo_id = pc.id
        LEFT JOIN tipo_pago tp ON cp.tipo_pago = tp.id
        LEFT JOIN establecimientos es ON es.id = pc.establecimiento_id
        LEFT JOIN catalog_status cs ON cs.status_id = cp.estatus

        WHERE 1=1
";

// Inicializar los parámetros de la consulta
$params = [];

// Agregar filtros a la consulta
if ($fecha_inicio && $fecha_fin) {
    $query .= " AND cp.fecha_registro BETWEEN :fecha_inicio AND :fecha_fin";
    $params[':fecha_inicio'] = $fecha_inicio . ' 00:00:00';
    $params[':fecha_fin'] = $fecha_fin . ' 23:59:59';
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
$headers = ['ID', 'CODIGO', 'ESTABLECIMIENTO', 'PRODUCTO', 'IMPORTE', 'CANTIDAD', 'TOTAL', 'FORMA DE PAGO','FOLIO', 'FECHA DE REGISTRO', 'ESTATUS'];
$sheet->fromArray($headers, null, 'A1');

// Agregar datos
$row = 2;
foreach ($resultados as $data) {
    $sheet->fromArray(array_values($data), null, 'A' . $row);
    $row++;
}

// Establecer nombre del archivo
$filename = 'REGISTRO_VENTAS_' . date('Ymd_His') . '.xlsx';

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
