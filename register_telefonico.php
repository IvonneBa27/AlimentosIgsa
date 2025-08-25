<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';
include 'db_connection.php';

session_start();

// Verificar sesión
if (!isset($_SESSION['resultado'])) {
    header('Location: index.html');
    exit;
}

$sesi = $_SESSION['resultado'];
$user_id = $sesi['id'];

/**
 * Obtener registros del día
 */
function obtenerRegistrosDia($conn, $returnData = false)
{
    $sql = "
        SELECT 
            cse.id,
            cse.barcode,
            em.nombre AS nombre_empresa,
            de.nombre AS nombre_departamento,
            cse.solicitante,
            co.nombre_completo AS comensal,
            cse.tipo_producto,
            cse.fecha_registro,
            cs.status AS estatus
        FROM control_serviciotelefonico cse
        LEFT JOIN comensal co ON co.barcode = cse.barcode_comensal
        LEFT JOIN empresa em ON em.id = cse.empresa_id
        LEFT JOIN departamento de ON de.id = cse.departamento_id 
        LEFT JOIN catalog_status cs ON cse.estatus_id = cs.status_id
        WHERE DATE(cse.fecha_registro) = CURDATE()
        ORDER BY cse.fecha_registro DESC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($returnData) {
        return $registros;
    } else {
        echo json_encode([
            "success" => true,
            "data" => ["registros_dia" => $registros]
        ]);
    }
}

// ----------------------
// SI ES CONSULTA DE REGISTROS DEL DÍA
// ----------------------
if (isset($_POST['action']) && $_POST['action'] === 'get_registros_dia') {
    obtenerRegistrosDia($conn);
    exit;
}


// ----------------------
// SI ES REGISTRO NUEVO
// ----------------------
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

try {
    // Recibir datos
    $empresa_id      = $_POST['empresa'] ?? null;
    $departamento_id = $_POST['departamento'] ?? null;
    $solicitante     = $_POST['solicitante'] ?? null;
    $tipo_producto   = $_POST['tipo_producto'] ?? null;
    $barcode         = $_POST['barcode'] ?? null;
    $comensales      = $_POST['comensales'] ?? [];
    $observaciones   = $_POST['observaciones'] ?? null;
    $ubicacion       = $_POST['ubicacion'] ?? null;

    $estatus_id = 1;
    $cantidad   = 1;

    // Validación
    if (!$empresa_id || !$departamento_id || !$solicitante || !$tipo_producto || !$barcode || empty($comensales)) {
        throw new Exception('Faltan datos obligatorios.');
    }

    // Preparar inserción
    $sqlInsert = "
        INSERT INTO control_serviciotelefonico 
        (barcode, empresa_id, departamento_id, barcode_comensal, solicitante, tipo_producto, cantidad, fecha_registro, estatus_id, user_id, observaciones, ubicacion) 
        VALUES (:barcode, :empresa_id, :departamento_id, :barcode_comensal, :solicitante, :tipo_producto, :cantidad, NOW(), :estatus_id, :user_id, :observaciones, :ubicacion)
    ";
    $stmtInsert = $conn->prepare($sqlInsert);

    // Consulta para obtener datos del comensal
    $sqlComensal = "SELECT correo, barcode, nombre_completo FROM comensal WHERE barcode = :barcode LIMIT 1";
    $stmtComensal = $conn->prepare($sqlComensal);

    $mail = new PHPMailer(true);
    $erroresEnvio = [];
    $registrosExitosos = 0;

    foreach ($comensales as $barcode_comensal) {
        // Guardar registro
        $stmtInsert->execute([
            ':barcode'          => $barcode,
            ':empresa_id'       => $empresa_id,
            ':departamento_id'  => $departamento_id,
            ':barcode_comensal' => $barcode_comensal,
            ':solicitante'      => $solicitante,
            ':tipo_producto'    => $tipo_producto,
            ':cantidad'         => $cantidad,
            ':estatus_id'       => $estatus_id,
            ':user_id'          => $user_id,
            ':observaciones'    => $observaciones,
            ':ubicacion'        => $ubicacion
        ]);

        $registrosExitosos++;

        // Obtener datos del comensal
        $stmtComensal->execute([':barcode' => $barcode_comensal]);
        $comensalData = $stmtComensal->fetch(PDO::FETCH_ASSOC);

        if (!$comensalData) {
            continue;
        }

        // Enviar correo
        try {
            $mail->clearAllRecipients();
            $mail->isSMTP();
            $mail->Host = 'smtp-mail.outlook.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'apps-zumpango@igsamedical.com';
            $mail->Password = '4ps2Umpango_98';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('apps-zumpango@igsamedical.com', 'SISTEMA CONTROL DE ALIMENTOS');
            $mail->addAddress($comensalData['correo'], $comensalData['nombre_completo']);
            $mail->CharSet = 'UTF-8';
            $mail->Encoding = 'base64';
            $mail->isHTML(true);
            $mail->Subject = 'Solicitud de servicio por vía telefónica';
            $mail->Body = "
                <p>Estimado usuario <strong>{$comensalData['nombre_completo']}</strong>,</p>
                <p>Por este medio <strong>{$solicitante}</strong> ha solicitado un servicio de comida por vía telefónica para ti.</p>
                <p>
                    <strong>Tipo de producto:</strong> {$tipo_producto} <br>
                    <strong>Cantidad:</strong> {$cantidad} <br>
                    <strong>Observaciones:</strong> {$observaciones} <br>
                    <strong>Ubicación:</strong> {$ubicacion}
                </p>
                <p>Gracias por tu atención.</p>
            ";

            $mail->send();
        } catch (Exception $e) {
            $erroresEnvio[] = "Error enviando correo a {$comensalData['correo']}: {$mail->ErrorInfo}";
        }
    }

    $msg = "Se registraron {$registrosExitosos} servicios correctamente.";
    if ($erroresEnvio) {
        $msg .= " Sin embargo, hubo errores enviando algunos correos.";
    }

    echo json_encode(['success' => true, 'message' => $msg]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
