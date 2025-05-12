<?php
require 'vendor/autoload.php';
include 'db_connection.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$correos = $_POST['correos'] ?? '';

// Consulta para obtener los registros del día actual
$query = "
    SELECT 
        ca.id AS ID, 
        tu.tipo_usuario AS TIPO_USUARIO, 
        CASE 
            WHEN ca.num_empleado = 0 THEN 'S/N' 
            ELSE ca.num_empleado 
        END AS NUM_EMPLEADO,
        CASE 
            WHEN ca.num_empleado = 0 THEN 'VISITANTE' 
            ELSE cm.nombre_completo 
        END AS NOMBRE_COMPLETO,
        CASE 
            WHEN ca.num_empleado = 0 THEN 'VISITANTE' 
            ELSE e.nombre 
        END AS EMPRESA,
        CASE 
            WHEN ca.num_empleado = 0 THEN 'VISITANTE' 
            ELSE d.nombre 
        END AS DEPARTAMENTO,
        p.producto AS PRODUCTO,
        FORMAT(p.costo, 2) AS IMPORTE,
        ca.fecha_registro AS FECHA_REGISTRO
    FROM control_alimentos ca
    LEFT JOIN comensal cm ON ca.num_empleado = cm.num_empleado
    LEFT JOIN producto p ON ca.tipo_producto = p.id
    LEFT JOIN tipo_usuario tu ON ca.comensal_id = tu.id
    LEFT JOIN empresa e ON cm.empresa = e.id
    LEFT JOIN departamento d ON cm.departamento = d.id
    WHERE DATE(ca.fecha_registro) = CURDATE()
    ORDER BY ca.fecha_registro DESC
";

// Ejecutar la consulta
$stmt = $conn->prepare($query);
$stmt->execute();

// Validar si la consulta devolvió resultados
if ($stmt->rowCount() === 0) {
    die(json_encode(['mensaje' => 'No se encontraron registros para el día actual.']));
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

// Guardar el archivo Excel en el servidor temporalmente
$writer = new Xlsx($spreadsheet);
$filename = 'Reporte_' . date('Ymd_His') . '.xlsx';
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
    $mail->setFrom('apps-zumpango@igsamedical.com', 'SISTEMA CONTROL DE ALIMENTOS');
    $correosArray = explode(',', $correos);
    foreach ($correosArray as $correo) {
        $mail->addAddress(trim($correo));
    }

    // Contenido del correo
    $mail->isHTML(true);
    $mail->Subject = 'Reporte Servicio de  Comedor Diario';
    $mail->Body    = 'Reporte Servicio de  Comedor Diario';
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
