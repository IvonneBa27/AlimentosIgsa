<?php

include('conexion.php');
session_start();
if (!isset($_SESSION['resultado'])) {
    header('Location: index.html');
    exit;
} else {
    $sesi = $_SESSION['resultado'];
}
$sesionUsuario = $sesi['usuario']; // Reemplaza con el nombre exacto de la columna
$sesionNombre = $sesi['nombre'];   // Reemplaza con el nombre exacto de la columna

require 'vendor/autoload.php';

use Picqer\Barcode\BarcodeGeneratorPNG;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $a_paterno = strtoupper($_POST['a_paterno']);
    $a_materno = strtoupper($_POST['a_materno']);
    $nombre = strtoupper($_POST['nombre']);
    $nombre_completo = trim("$a_paterno $a_materno $nombre");
    $num_empleado = $_POST['num_empleado'];
    $empresaId = $_POST['empresa'];
    $departamento = strtoupper($_POST['departamento']);
    $puesto = strtoupper($_POST['puesto']);
    $correo = strtoupper($_POST['correo']);
    $barcode = strtoupper($_POST['barcode']);

    $imagePath = null;

    // Manejar nueva foto (si existe)
    if (isset($_POST['photo']) && !empty($_POST['photo'])) {
        $imageData = $_POST['photo'];
        $imageParts = explode(";base64,", $imageData);

        if (count($imageParts) == 2) {
            $imageBase64 = base64_decode($imageParts[1]);
            $newFileName = uniqid() . '.png';
            $uploadFileDir = 'images/uploads/';
            $destPath = $uploadFileDir . $newFileName;

            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0777, true);
            }

            if (file_put_contents($destPath, $imageBase64)) {
                $imagePath = $destPath;
            } else {
                echo "Error al guardar la imagen.";
                exit();
            }
        } else {
            echo "Formato de imagen no válido.";
            exit();
        }
    }

    // Crear imagen del código de barras con texto y márgenes
    $barcodePath = 'images/barcodesComensal/' . $barcode . '.png';
    $generator = new BarcodeGeneratorPNG();
    $barcodeImage = $generator->getBarcode($barcode, $generator::TYPE_CODE_128);

    if (!is_dir('images/barcodesComensal/')) {
        mkdir('images/barcodesComensal/', 0777, true);
    }

    $margen = 20; // Tamaño del margen alrededor del código de barras
    $barcodeWidth = 300; // Ancho del código de barras
    $barcodeHeight = 50; // Alto del código de barras
    $imageHeight = $barcodeHeight + 50; // Espacio para el texto
    $imageWidth = $barcodeWidth + 2 * $margen; // Ancho con márgenes
    $finalImageHeight = $imageHeight + 2 * $margen; // Altura con márgenes

    $finalImage = imagecreatetruecolor($imageWidth, $finalImageHeight);
    $white = imagecolorallocate($finalImage, 255, 255, 255);
    $black = imagecolorallocate($finalImage, 0, 0, 0);

    // Rellenar fondo blanco
    imagefilledrectangle($finalImage, 0, 0, $imageWidth, $finalImageHeight, $white);

    // Insertar el código de barras en el centro con márgenes
    $barcodeImageResource = imagecreatefromstring($barcodeImage);
    $barcodeX = $margen; // Margen izquierdo
    $barcodeY = $margen; // Margen superior
    imagecopy($finalImage, $barcodeImageResource, $barcodeX, $barcodeY, 0, 0, $barcodeWidth, $barcodeHeight);

    // Agregar texto (número de empleado) debajo del código de barras
    $fontPath = __DIR__ . '/css/arial.ttf';
    $textX = $margen + 10; // Posición X ajustada dentro del margen
    $textY = $barcodeY + $barcodeHeight + 30; // Posición Y debajo del código de barras
    imagettftext($finalImage, 12, 0, $textX, $textY, $black, $fontPath, "Código del Empleado: $barcode");

    // Guardar la imagen final
    if (!imagepng($finalImage, $barcodePath)) {
        echo "Error al guardar la imagen del código de barras.";
        exit();
    }

    imagedestroy($barcodeImageResource);
    imagedestroy($finalImage);

    // Asignar ruta del código de barras
    $barcode_path = $barcodePath;

    // Actualizar registro con mysqli
    $sql = "UPDATE comensal 
            SET a_paterno = ?, 
                a_materno = ?, 
                nombre = ?, 
                nombre_completo = ?, 
                num_empleado = ?, 
                empresa = ?, 
                departamento = ?, 
                puesto = ?, 
                correo = ?,
                imagePath = ?,
                barcode_path = ?
            WHERE id = ?";
    $stmt = $con->prepare($sql);

    if ($stmt) {
        $stmt->bind_param(
            "ssssissssssi",
            $a_paterno,
            $a_materno,
            $nombre,
            $nombre_completo,
            $num_empleado,
            $empresaId,
            $departamento,
            $puesto,
            $correo,
            $imagePath,
            $barcode_path,
            $id
        );

        if ($stmt->execute()) {
            if ($correo) {
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
                    $mail->addAddress($correo);

                    $mail->isHTML(true);
                    $mail->Subject = 'Codigo de Barras - Sistema Control de Alimentos';
                    $mail->Body = "
                                    <h1>Bienvenido al Sistema de Comedor</h1>
                                    <p>Estimado(a) $nombre_completo,</p>
                                    <p>Gracias por actualizar tu información en nuestro sistema. Por este medio, se ha generado y compartido tu código de barras personal.</p>
                                    <p>Este código será necesario para ingresar al comedor y realizar tu checklist correspondiente.</p>
                                    <p>Si tienes alguna duda, no dudes en contactarnos.</p>
                                    <p>Atentamente,</p>
                                    <p><strong>Sistema de Control de Alimentos</strong></p>
                                ";

                    $mail->addAttachment($barcodePath);
                    $mail->send();
                } catch (Exception $e) {
                    echo "Error al enviar correo: {$mail->ErrorInfo}";
                }
            }

            header("Location: adminComensales.php");
            exit();
        } else {
            echo "Error al registrar comensal: " . $stmt->error;
        }

        $stmt->close();
        $con->close();
    } else {
        echo "Error al preparar la consulta SQL.";
    }
} else {
    echo "Método de solicitud no permitido.";
}
