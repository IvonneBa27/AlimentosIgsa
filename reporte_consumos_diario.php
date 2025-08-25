<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

require 'vendor/autoload.php';
include 'db_connection.php'; // tu conexión con PDO

// --- NO USAMOS session_start() ---
// Aquí puedes poner un usuario fijo si lo requieres
$user_id = 1; // por ejemplo, o quita esta variable si no es necesaria

// ===============================
// 1. Ejecutar SP con PDO
// ===============================
try {
    $stmt = $conn->prepare("CALL sp_reporte_consumos_ayer()");
    $stmt->execute();

    $totalData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt->nextRowset();
    $detalleExceso = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt->closeCursor();
} catch (PDOException $e) {
    die("Error ejecutando el procedimiento: " . $e->getMessage());
}

// ===============================
// 2. Crear Excel
// ===============================
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle("Reporte Consumos Ayer");

$sheet->setCellValue('A1', 'Total Desayuno');
$sheet->setCellValue('B1', 'Total Comida');
$sheet->setCellValue('C1', 'Total Cena');
$sheet->setCellValue('D1', 'Total General');

if (!empty($totalData)) {
    $sheet->setCellValue('A2', $totalData[0]['total_desayuno']);
    $sheet->setCellValue('B2', $totalData[0]['total_comida']);
    $sheet->setCellValue('C2', $totalData[0]['total_cena']);
    $sheet->setCellValue('D2', $totalData[0]['total_general']);
}

if (!empty($detalleExceso)) {
    $startRow = 4;
    $sheet->setCellValue("A{$startRow}", "Barcode");
    $sheet->setCellValue("B{$startRow}", "Nombre Completo");
    $sheet->setCellValue("C{$startRow}", "Departamento");
    $sheet->setCellValue("D{$startRow}", "Tipo Comida");
    $sheet->setCellValue("E{$startRow}", "Fecha Registro");

    $row = $startRow + 1;
    foreach ($detalleExceso as $d) {
        $sheet->setCellValue("A{$row}", $d['barcode']);
        $sheet->setCellValue("B{$row}", $d['nombre_completo']);
        $sheet->setCellValue("C{$row}", $d['departamento']);
        $sheet->setCellValue("D{$row}", $d['tipo_comida']);
        $sheet->setCellValue("E{$row}", $d['fecha_registro']);
        $row++;
    }
}

$filename = sys_get_temp_dir() . '/reporte_consumos_' . date('Y-m-d') . '.xlsx';
$writer = new Xlsx($spreadsheet);
$writer->save($filename);

// ===============================
// 3. Enviar correo
// ===============================
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = 'smtp-mail.outlook.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'apps-zumpango@igsamedical.com';
    $mail->Password = '4ps2Umpango_98';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom('apps-zumpango@igsamedical.com', 'SISTEMA CONTROL DE ALIMENTOS');
    $mail->addAddress('ibaca@igsamedical.com');
    $mail->CharSet = 'UTF-8';
    $mail->Encoding = 'base64';
    $mail->Subject = 'Corte Diario de Consumos HRAEZ - ' . date('Y-m-d');
    $mail->Body = 'Adjunto encontrarás el reporte de consumos del día anterior.';
    $mail->addAttachment($filename);

    $mail->send();
    echo "Reporte enviado correctamente.\n";
} catch (Exception $e) {
    echo "No se pudo enviar el correo. Error: {$mail->ErrorInfo}\n";
}
