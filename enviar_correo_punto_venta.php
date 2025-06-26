<?php
require 'vendor/autoload.php';
include 'db_connection.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$correos = $_POST['correos'] ?? '';
$filtros = $_POST['filtros'] ?? '';

// Parsear los filtros
parse_str($filtros, $params);

$fecha_inicio = $params['fecha_inicio'] ?? null;
$fecha_fin = $params['fecha_fin'] ?? null;
$tipo_producto = $params['tipo_producto'] ?? null;
$tipo_usuario = $params['tipo_usuario'] ?? null;
$num_empleado = $params['num_empleado'] ?? null;

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
    $query .= " AND DATE(cp.fecha_registro) BETWEEN :fecha_inicio AND :fecha_fin";
    $params[':fecha_inicio'] = $fecha_inicio;
    $params[':fecha_fin'] = $fecha_fin;
}

// Preparar y ejecutar la consulta
$stmt = $conn->prepare($query);
$stmt->execute($params);

// Validar si la consulta devolvió resultados
if ($stmt->rowCount() === 0) {
    die(json_encode(['mensaje' => 'No se encontraron registros con los filtros seleccionados.']));
}

// Crear un nuevo documento Excel
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Configurar encabezados
$headers = ['ID', 'CODIGO', 'ESTABLECIMIENTO', 'PRODUCTO', 'IMPORTE', 'CANTIDAD', 'TOTAL', 'FORMA DE PAGO','FOLIO', 'FECHA DE REGISTRO', 'ESTATUS'];
$sheet->fromArray($headers, null, 'A1');

// Agregar datos
$row = 2; 
while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $sheet->fromArray(array_values($data), null, 'A' . $row);
    $row++;
}

// Guardar el archivo Excel en el servidor temporalmente
$writer = new Xlsx($spreadsheet);
$filename = 'REGISTRO_VENTAS' . date('Ymd_His') . '.xlsx';
$filepath = sys_get_temp_dir() . '/' . $filename;
$writer->save($filepath);

// Enviar el correo con el archivo adjunto
$mail = new PHPMailer(true);

try {
    // Configuración del servidor SMTP
    $mail->isSMTP();
    $mail->Host = 'smtp-mail.outlook.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'apps-zumpango@igsamedical.com';
    $mail->Password = '4ps2Umpango_98';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    // Configuración del correo
    $mail->setFrom('apps-zumpango@igsamedical.com', 'SISTEMA NUTRIGSA');
    $correosArray = explode(',', $correos);
    foreach ($correosArray as $correo) {
        $mail->addAddress(trim($correo));
    }

    // Contenido del correo
    $mail->isHTML(true);
    $mail->Subject = 'REGISTRO DE VENTAS';
    $mail->Body    = 'Reporte de registros de Ventas';
    $mail->AltBody = 'Adjunto reporte solicitado.';

    // Adjuntar el archivo Excel
    $mail->addAttachment($filepath);

    // Enviar correo
    $mail->send();
    echo json_encode(['mensaje' => 'Correo enviado con éxito']);
} catch (Exception $e) {
    echo json_encode(['mensaje' => "Error al enviar correo: {$mail->ErrorInfo}"]);
} finally {
    // Eliminar el archivo temporal
    unlink($filepath);
}
?>