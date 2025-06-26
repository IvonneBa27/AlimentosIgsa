<?php
require 'vendor/autoload.php';
include 'db_connection.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$correos = $_POST['correos'] ?? '';
$filtros = $_POST['filtros'] ?? '';

// ✅ Parsear los filtros serializados (fecha_inicio y fecha_fin deben venir en 'filtros')
parse_str($filtros, $parsedFiltros);

// Obtener fechas desde los filtros parseados
$fecha_inicio = $parsedFiltros['fecha_inicio'] ?? '';
$fecha_fin    = $parsedFiltros['fecha_fin'] ?? '';

// ✅ Construir la consulta base
$query = "
    SELECT 
        cc.id, cc.barcode, 'CORTESIA' AS producto,
        e.nombre AS empresa, d.nombre AS departamento,
        p.producto AS tipo_producto,
        cc.solicitante, cs.status AS estatus,
        cc.fecha_registro
    FROM control_cortesias cc
    LEFT JOIN producto       p  ON p.id = cc.tipo_producto
    LEFT JOIN empresa        e  ON cc.empresa_id = e.id
    LEFT JOIN departamento   d  ON cc.departamento_id = d.id
    LEFT JOIN catalog_status cs ON cc.estatus_id = cs.status_id
    WHERE 1=1
";

// ✅ Agregar filtros si existen
$params = [];
if (!empty($fecha_inicio) && !empty($fecha_fin)) {
    $query .= " AND cc.fecha_registro BETWEEN :fi AND :ff";
    $params[':fi'] = $fecha_inicio . ' 00:00:00';
    $params[':ff'] = $fecha_fin . ' 23:59:59';
}

// ✅ Preparar y ejecutar consulta
$stmt = $conn->prepare($query);
$stmt->execute($params);

// Validar si hay resultados
if ($stmt->rowCount() === 0) {
    echo json_encode(['mensaje' => 'No se encontraron registros con los filtros seleccionados.']);
    exit;
}

// Crear Excel
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Encabezados
$headers = ['ID','CÓDIGO','PRODUCTO','EMPRESA','DEPARTAMENTO','TIPO','SOLICITANTE','ESTATUS','FECHA'];
$sheet->fromArray($headers, null, 'A1');

// Agregar datos
$row = 2; 
while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $sheet->fromArray(array_values($data), null, 'A' . $row);
    $row++;
}

// Guardar el archivo temporalmente
$filename = 'Cortesias_' . date('Ymd_His') . '.xlsx';
$filepath = sys_get_temp_dir() . '/' . $filename;
$writer = new Xlsx($spreadsheet);
$writer->save($filepath);

// Enviar correo
$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = 'smtp-mail.outlook.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'apps-zumpango@igsamedical.com';
    $mail->Password = '4ps2Umpango_98';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom('apps-zumpango@igsamedical.com', 'SISTEMA NUTRIGSA');

    // Añadir destinatarios
    foreach (explode(',', $correos) as $correo) {
        $mail->addAddress(trim($correo));
    }

    $mail->isHTML(true);
    $mail->Subject = 'REPORTE DE CORTESIAS';
    $mail->Body    = 'Adjunto reporte de cortesias.';
    $mail->AltBody = 'Adjunto reporte de cortesias.';

    // Adjuntar archivo
    $mail->addAttachment($filepath);

    $mail->send();
    echo json_encode(['mensaje' => 'Correo enviado con éxito']);
} catch (Exception $e) {
    echo json_encode(['mensaje' => "Error al enviar correo: {$mail->ErrorInfo}"]);
} finally {
    // Limpiar archivo temporal
    unlink($filepath);
}
?>
