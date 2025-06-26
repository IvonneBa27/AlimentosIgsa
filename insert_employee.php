<?php
// Incluir archivos necesarios
include('conexion.php');
session_start();

if (!isset($_SESSION['resultado'])) {
    header('Location: index.html');
    exit;
} else {
    $sesi = $_SESSION['resultado'];
}

$sesi = $_SESSION['resultado'];
$sesionUsuario = $sesi['usuario']; // Reemplaza con el nombre exacto de la columna
$sesionNombre = $sesi['nombre'];   // Reemplaza con el nombre exacto de la columna
$sesionUsuarioId = $sesi['id'];

require 'vendor/autoload.php';

use Picqer\Barcode\BarcodeGeneratorPNG;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener datos del formulario
    $a_paterno = strtoupper(trim($_POST['a_paterno']));
    $a_materno = strtoupper(trim($_POST['a_materno']));
    $nombre = strtoupper(trim($_POST['nombre']));
    $nombre_completo = "$a_paterno $a_materno $nombre";
    $num_empleado = $_POST['num_empleado'];
    $empresaId = $_POST['empresa'];
    $departamento = strtoupper(trim($_POST['departamento']));
    $puesto = strtoupper(trim($_POST['puesto']));
    $correo = !empty($_POST['correo']) ? trim($_POST['correo']) : null;
    $t_desayuno = in_array('Desayuno', $_POST['tiempos_comida'] ?? []) ? 1 : 0;
    $t_colacion = in_array('Colación', $_POST['tiempos_comida'] ?? []) ? 1 : 0;
    $t_comida   = in_array('Comida', $_POST['tiempos_comida'] ?? []) ? 1 : 0;
    $t_cena     = in_array('Cena', $_POST['tiempos_comida'] ?? []) ? 1 : 0;



    $estatus = 1;
    $tipo_usuario = 1;

    // Validar y guardar imagen
    $imagePath = null;
    if (isset($_POST['photo']) && !empty($_POST['photo'])) {
        $imageData = $_POST['photo'];
        $imageParts = explode(";base64,", $imageData);
        if (count($imageParts) == 2) {
            $imageBase64 = base64_decode($imageParts[1]);
            $newFileName = uniqid() . '.png';
            $uploadFileDir = 'images/uploads/';
            $dest_path = $uploadFileDir . $newFileName;

            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0777, true);
            }

            if (file_put_contents($dest_path, $imageBase64)) {
                $imagePath = $dest_path;
            }
        }
    }

    // Generar código de barras
    $sqlEmpresa = "SELECT nombre FROM empresa WHERE id = ?";
    $stmtEmpresa = $con->prepare($sqlEmpresa);
    $stmtEmpresa->bind_param("i", $empresaId);
    $stmtEmpresa->execute();
    $resultEmpresa = $stmtEmpresa->get_result();
    $empresa = $resultEmpresa->fetch_assoc();
    $stmtEmpresa->close();

    $letraInicial = strtoupper(substr($empresa['nombre'], 0, 1));
    $nombreInicial = strtoupper(substr($nombre, 0, 1));
    $apellidoPaternoInicial = strtoupper(substr($a_paterno, 0, 1));
    $apellidoMaternoInicial = strtoupper(substr($a_materno, 0, 1));
    $baseCodigo = $letraInicial . $nombreInicial . $apellidoPaternoInicial . $apellidoMaternoInicial;
    $numerosAleatorios = str_pad(mt_rand(0, 99999999), 9, '0', STR_PAD_LEFT);
    $codigoFinal = $baseCodigo . substr($numerosAleatorios, 0, 12 - strlen($baseCodigo));

    // Crear imagen del código de barras con texto y márgenes
    $barcodePath = 'images/barcodesComensal/' . $codigoFinal . '.png';
    $generator = new BarcodeGeneratorPNG();
    $barcodeImage = $generator->getBarcode($codigoFinal, $generator::TYPE_CODE_128);

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
    imagettftext($finalImage, 12, 0, $textX, $textY, $black, $fontPath, "Código del Empleado: $codigoFinal");

    // Guardar la imagen final
    if (!imagepng($finalImage, $barcodePath)) {
        echo "Error al guardar la imagen del código de barras.";
        exit();
    }

    imagedestroy($barcodeImageResource);
    imagedestroy($finalImage);


    // Insertar en la base de datos — antes agregamos esta validación
    $check_sql = "SELECT id FROM comensal WHERE barcode = ? OR nombre_completo = ?";
    $check_stmt = $con->prepare($check_sql);
    $check_stmt->bind_param("ss", $codigoFinal, $nombre_completo);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows > 0) {
        header("Location: adminComensales.php?alerta=duplicado");
        exit();
    }


    // Insertar en la base de datos
    $sql = "INSERT INTO comensal (a_paterno, a_materno, nombre, nombre_completo, num_empleado, empresa, departamento, puesto, correo, imagePath, barcode, barcode_path, estatus, tipo_usuario, user_id, t_desayuno, t_colacion, t_comida, t_cena)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $con->prepare($sql);
    $stmt->bind_param(
        "ssssisssssssisiiiii",
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
        $codigoFinal,
        $barcodePath,
        $estatus,
        $tipo_usuario,
        $sesionUsuarioId,
        $t_desayuno,
        $t_colacion,
        $t_comida,
        $t_cena

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
                $mail->Subject = 'Registro exitoso - Sistema Control de Alimentos';
                $mail->Body = "
                                <h1>Bienvenido al Sistema de Comedor</h1>
                                <p>Estimado(a) $nombre_completo,</p>
                                <p>Gracias por registrarte en nuestro sistema. Por este medio, se ha generado y compartido tu código de barras personal.</p>
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
        header("Location: adminComensales.php?alerta=registrado");


        exit();
    } else {
        echo "Error al registrar comensal: " . $stmt->error;
    }

    $stmt->close();
    $con->close();
} else {
    echo "Método de solicitud no permitido.";
}
