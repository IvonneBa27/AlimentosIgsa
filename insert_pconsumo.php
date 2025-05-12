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
$sesionUsuarioId = $sesi['id'];


require 'vendor/autoload.php';

use Picqer\Barcode\BarcodeGeneratorPNG;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Suponiendo que estos valores provienen de un formulario
$consumo = !empty($_POST['consumo']) ? strtoupper($_POST['consumo']) : null;
$costo = !empty($_POST['costo']) ? $_POST['costo'] : null;
$establecimiento_id = !empty($_POST['establecimiento_id']) ? $_POST['establecimiento_id'] : null;

$estatus = 1;

try {
    // Crear y ejecutar la consulta SQL para insertar el producto
    $sql = "INSERT INTO punto_consumo (consumo, costo, establecimiento_id, estatus, user_id) VALUES (?, ?, ?,  ?, ?)";
    $stmt = $con->prepare($sql);

    if (!$stmt) {
        throw new Exception("Error al preparar la consulta: " . $con->error);
    }

    // Enlazar parámetros
    $stmt->bind_param("sdiii", $consumo, $costo,  $establecimiento_id, $estatus, $sesionUsuarioId);

    if ($stmt->execute()) {
        $consumo_id = $stmt->insert_id; // Obtener el ID del producto recién insertado
        $consumo = strtoupper(trim($_POST['consumo']));

        // Tomamos las 3 primeras letras del nombre del producto
        $inicialesNombre = substr(preg_replace('/[^A-Z]/', '', $consumo), 0, 3);

        // Generamos un número aleatorio de 6 cifras
        $numerosAleatorios = str_pad(mt_rand(0, 999999), 9, '0', STR_PAD_LEFT);

        // Concatenamos para crear el código
        $codigoFinal = $inicialesNombre . $numerosAleatorios;

        // Crear imagen del código de barras con texto y márgenes
        $barcodePath = 'images/barcodesConsumo/' . $codigoFinal . '.png';
        $generator = new BarcodeGeneratorPNG();
        $barcodeImage = $generator->getBarcode($codigoFinal, $generator::TYPE_CODE_128);

        if (!is_dir('images/barcodesConsumo/')) {
            mkdir('images/barcodesConsumo/', 0777, true);
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

        // Agregar texto (código del producto) debajo del código de barras
        $fontPath = __DIR__ . '/css/arial.ttf';
        $textX = $margen + 10; // Posición X ajustada dentro del margen
        $textY = $barcodeY + $barcodeHeight + 30; // Posición Y debajo del código de barras
        imagettftext($finalImage, 12, 0, $textX, $textY, $black, $fontPath, "Código del Producto: $codigoFinal");

        // Guardar la imagen final
        if (!imagepng($finalImage, $barcodePath)) {
            throw new Exception("Error al guardar la imagen del código de barras.");
        }

        imagedestroy($barcodeImageResource);
        imagedestroy($finalImage);

        // Preparar y ejecutar la actualización
        $updateSql = "UPDATE punto_consumo SET barcode_path = ?, barcode = ? WHERE id = ?";
        $updateStmt = $con->prepare($updateSql);

        if (!$updateStmt) {
            throw new Exception("Error al preparar la consulta de actualización: " . $con->error);
        }

        $updateStmt->bind_param("ssi", $barcodePath, $codigoFinal, $consumo_id);

        if ($updateStmt->execute()) {
            echo "Producto registrado exitosamente con código de barras.";
            header("Location: adminPuntoConsumo.php");
            exit();
        } else {
            throw new Exception("Error al ejecutar la actualización: " . $updateStmt->error);
        }
    } else {
        throw new Exception("Error al ejecutar la inserción: " . $stmt->error);
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

// Cerrar la conexión
$con->close();
