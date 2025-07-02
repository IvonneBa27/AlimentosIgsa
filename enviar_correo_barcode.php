<?php
require 'conexion.php';
require 'vendor/autoload.php';

use Picqer\Barcode\BarcodeGeneratorPNG;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: adminComensales.php');
    exit;
}

$id = $_POST['id'];
$correo = $_POST['correo'];
$barcode = $_POST['barcode'];

$generator = new BarcodeGeneratorPNG();
$barcodeImage = $generator->getBarcode($barcode, $generator::TYPE_CODE_128);

// Crear carpeta si no existe
$carpeta = 'images/barcodesComensal/';
if (!is_dir($carpeta)) {
    mkdir($carpeta, 0777, true);
}

$barcodePath = $carpeta . $barcode . '.png';

// Crear imagen con márgenes y texto
$ancho = 300;
$alto = 50;
$margen = 20;
$imgFinal = imagecreatetruecolor($ancho + $margen * 2, $alto + 70);
$white = imagecolorallocate($imgFinal, 255, 255, 255);
$black = imagecolorallocate($imgFinal, 0, 0, 0);
imagefilledrectangle($imgFinal, 0, 0, imagesx($imgFinal), imagesy($imgFinal), $white);

$barcodeImg = imagecreatefromstring($barcodeImage);
imagecopy($imgFinal, $barcodeImg, $margen, $margen, 0, 0, $ancho, $alto);

$fuente = __DIR__ . '/css/arial.ttf';
imagettftext($imgFinal, 12, 0, $margen + 10, $alto + 50, $black, $fuente, "Código del Empleado: $barcode");

imagepng($imgFinal, $barcodePath);
imagedestroy($barcodeImg);
imagedestroy($imgFinal);

// Enviar por correo
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
    $mail->Subject = 'Codigo de Barras  - Sistema de Comedor';
    $mail->Body = "
        <p>Estimado(a)</p>
        <p>Tu código de barras ha sido generado y se adjunta a este mensaje.</p>
         <p>Consulta nuestro <a href='https://igsa1-my.sharepoint.com/:b:/g/personal/apps-zumpango_igsamedical_com/EfmeKXrrAxFEoyMPPyIunVgBZ-2YdTVkg1WYTWzuxg22mA?e=ho0DsW' target='_blank'>Aviso de Privacidad</a>.</p>
    <br>
        <p>Gracias por usar el sistema de control de alimentos.</p>";
    
        $mail->addAttachment($barcodePath);

    $mail->send();



    header("Location: adminComensales.php?alerta=correo");
    exit;
} catch (Exception $e) {
    echo "Error al enviar el correo: {$mail->ErrorInfo}";
}
