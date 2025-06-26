<?php
require 'vendor/autoload.php';
include 'db_connection.php';

// 1) Leer según el método que uses (POST o GET)
$metodo = $_SERVER['REQUEST_METHOD'];
$fecha_inicio = $metodo === 'POST'
    ? ($_POST['fecha_inicio'] ?? '')
    : ($_GET['fecha_inicio']  ?? '');
$fecha_fin    = $metodo === 'POST'
    ? ($_POST['fecha_fin']    ?? '')
    : ($_GET['fecha_fin']     ?? '');

// DEBUG: registra valores
// file_put_contents('debug_export.txt', "$metodo|INI=$fecha_inicio|FIN=$fecha_fin\n", FILE_APPEND);

// 2) Construir SQL
$sql = "
  SELECT cc.id, cc.barcode, 'CORTESIA' AS producto,
         e.nombre AS empresa, d.nombre AS departamento,
         p.producto AS tipo_producto,
         cc.solicitante, cs.status AS estatus,
         cc.fecha_registro
  FROM control_cortesias cc
  LEFT JOIN producto       p  ON p.id = cc.tipo_producto
  LEFT JOIN empresa        e  ON cc.empresa_id    = e.id
  LEFT JOIN departamento   d  ON cc.departamento_id = d.id
  LEFT JOIN catalog_status cs ON cc.estatus_id    = cs.status_id
  WHERE 1=1
";

$params = [];
if ($fecha_inicio !== '' && $fecha_fin !== '') {
    $sql .= " AND cc.fecha_registro BETWEEN :fi AND :ff";
    $params[':fi'] = $fecha_inicio . ' 00:00:00';
    $params[':ff'] = $fecha_fin    . ' 23:59:59';
}

// 3) Ejecutar
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 4) Generar Excel
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Encabezados
$headers = ['ID','CÓDIGO','PRODUCTO','EMPRESA','DEPARTAMENTO','TIPO','SOLICITANTE','ESTATUS','FECHA'];
$sheet->fromArray($headers, null, 'A1');

// Datos
$r = 2;
foreach ($resultados as $row) {
    $sheet->fromArray(array_values($row), null, "A{$r}");
    $r++;
}

$filename = 'Cortesias_' . date('Ymd_His') . '.xlsx';
if (ob_get_length()) ob_end_clean();
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment; filename=\"{$filename}\"");
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
