<?php

include('conexion.php');
session_start();
if (!isset($_SESSION['resultado'])) {
    header('Location: index.html');
    exit;
} else {
    $sesi = $_SESSION['resultado'];
}
$sesionUsuario = $sesi['usuario'];
$sesionNombre = $sesi['nombre'];

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
    $t_desayuno = in_array('Desayuno', $_POST['edit_tiempos_comida'] ?? []) ? 1 : 0;
    $t_colacion = in_array('Colación', $_POST['edit_tiempos_comida'] ?? []) ? 1 : 0;
    $t_comida   = in_array('Comida', $_POST['edit_tiempos_comida'] ?? []) ? 1 : 0;
    $t_cena     = in_array('Cena', $_POST['edit_tiempos_comida'] ?? []) ? 1 : 0;

    // Obtener la imagen actual si no se sube una nueva
    $sql_img = "SELECT imagePath FROM comensal WHERE id = ?";
    $stmt_img = $con->prepare($sql_img);
    $stmt_img->bind_param("i", $id);
    $stmt_img->execute();
    $stmt_img->bind_result($imagePathActual);
    $stmt_img->fetch();
    $stmt_img->close();

    $imagePath = $imagePathActual;

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

    $barcodePath = 'images/barcodesComensal/' . $barcode . '.png';
    $generator = new BarcodeGeneratorPNG();
    $barcodeImage = $generator->getBarcode($barcode, $generator::TYPE_CODE_128);

    if (!is_dir('images/barcodesComensal/')) {
        mkdir('images/barcodesComensal/', 0777, true);
    }

    $margen = 20;
    $barcodeWidth = 300;
    $barcodeHeight = 50;
    $imageHeight = $barcodeHeight + 50;
    $imageWidth = $barcodeWidth + 2 * $margen;
    $finalImageHeight = $imageHeight + 2 * $margen;

    $finalImage = imagecreatetruecolor($imageWidth, $finalImageHeight);
    $white = imagecolorallocate($finalImage, 255, 255, 255);
    $black = imagecolorallocate($finalImage, 0, 0, 0);

    imagefilledrectangle($finalImage, 0, 0, $imageWidth, $finalImageHeight, $white);

    $barcodeImageResource = imagecreatefromstring($barcodeImage);
    $barcodeX = $margen;
    $barcodeY = $margen;
    imagecopy($finalImage, $barcodeImageResource, $barcodeX, $barcodeY, 0, 0, $barcodeWidth, $barcodeHeight);

    $fontPath = __DIR__ . '/css/arial.ttf';
    $textX = $margen + 10;
    $textY = $barcodeY + $barcodeHeight + 30;
    imagettftext($finalImage, 12, 0, $textX, $textY, $black, $fontPath, "Código del Empleado: $barcode");

    if (!imagepng($finalImage, $barcodePath)) {
        echo "Error al guardar la imagen del código de barras.";
        exit();
    }

    imagedestroy($barcodeImageResource);
    imagedestroy($finalImage);

    $barcode_path = $barcodePath;

    $sql = "UPDATE comensal 
            SET a_paterno = ?, a_materno = ?, nombre = ?, nombre_completo = ?, 
                num_empleado = ?, empresa = ?, departamento = ?, puesto = ?, correo = ?,
                imagePath = ?, barcode_path = ?,
                t_desayuno = ?, t_colacion = ?, t_comida = ?, t_cena = ?
            WHERE id = ?";
    $stmt = $con->prepare($sql);

    if ($stmt) {
        $stmt->bind_param(
            "ssssissssssiiiii",
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
            $t_desayuno,
            $t_colacion,
            $t_comida,
            $t_cena,
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
                    $mail->CharSet = 'UTF-8';
                    $mail->Encoding = 'base64';
                    $mail->Subject = 'Registro exitoso - Sistema Control de Alimentos';
                    $mail->Body = "<h1>Bienvenido al Sistema de Comedor</h1>
                        <p>Estimado(a) $nombre_completo,</p>
                        <p>Gracias por actualizar tu información en nuestro sistema. Por este medio, se ha generado y compartido tu código de barras personal.</p>
                        <p>Este código será necesario para ingresar al comedor y realizar tu checklist correspondiente.</p>
                        <p>Si tienes alguna duda, no dudes en contactarnos.</p>
                         <p>Consulta nuestro <a href='https://igsa1-my.sharepoint.com/:b:/g/personal/apps-zumpango_igsamedical_com/EfmeKXrrAxFEoyMPPyIunVgBZ-2YdTVkg1WYTWzuxg22mA?e=ho0DsW' target='_blank'>Aviso de Privacidad</a>.</p>
    <br>
                        <p>Atentamente,</p>
                        <p><strong>Sistema de Control de Alimentos</strong></p>";

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
