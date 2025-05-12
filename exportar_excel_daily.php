<?php

require 'vendor/autoload.php'; // Incluye PhpSpreadsheet
include 'db_connection.php'; // Conexión a la base de datos

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Consulta para obtener los registros del día actual
$query = "
    SELECT 
        ca.id AS ID, 
        tu.tipo_usuario AS TIPO_USUARIO, 
        ca.num_empleado AS NUM_EMPLEADO,
        cm.nombre_completo AS NOMBRE_COMPLETO,
        e.nombre AS EMPRESA,
        d.nombre  AS DEPARTAMENTO,
        p.producto AS PRODUCTO,
        FORMAT(p.costo, 2) AS IMPORTE,
        ca.fecha_registro AS FECHA_REGISTRO
    FROM control_alimentos ca
    LEFT JOIN comensal cm ON ca.barcode = cm.barcode
    LEFT JOIN producto p ON ca.tipo_producto = p.id
    LEFT JOIN tipo_usuario tu ON ca.comensal_id = tu.id
    LEFT JOIN empresa e ON cm.empresa = e.id
    LEFT JOIN departamento d ON cm.departamento = d.id
    WHERE DATE(ca.fecha_registro) = CURDATE()
    ORDER BY ca.fecha_registro DESC
";

// Preparar y ejecutar la consulta
$stmt = $conn->prepare($query);
$stmt->execute();

// Validar si la consulta devolvió resultados
if ($stmt->rowCount() === 0) {
    die('No se encontraron registros para el día actual.');
}

// Crear un nuevo documento Excel
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Configurar encabezados
$headers = ['ID', 'TIPO USUARIO', 'NÚM. DE EMPLEADO', 'NOMBRE COMPLETO', 'EMPRESA', 'DEPARTAMENTO', 'PRODUCTO', 'IMPORTE', 'FECHA DE REGISTRO'];
$sheet->fromArray($headers, null, 'A1');

// Agregar datos
$row = 2; 
while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
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
