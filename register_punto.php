<?php
include 'db_connection.php';
include('conexion.php');
session_start();

if (!isset($_SESSION['resultado'])) {
    header('Location: index.html');
    exit;
} else {
    $sesi = $_SESSION['resultado'];
}

$sesionUsuarioId = $sesi['id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // 1. Obtener registros del día
        if (isset($_POST['action']) && $_POST['action'] == 'get_registros_dia') {
            obtenerRegistrosDia($conn);
            exit;
        }

        // 2. Actualizar folio
        if (isset($_POST['action']) && $_POST['action'] == 'actualizar_folio') {
            $id = isset($_POST['id']) ? (int)$_POST['id'] : null;
            $folio = isset($_POST['folio']) ? trim($_POST['folio']) : null;

            file_put_contents('log.txt', "Entrando a actualizar folio: ID={$id}, FOLIO={$folio}\n", FILE_APPEND);

            if (!$id || $folio === '') {
                echo json_encode(["success" => false, "message" => "ID y folio son requeridos."]);
                exit;
            }

            $sql = "UPDATE control_puntoventa SET folio = :folio WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':folio', $folio);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            // Verificar si se actualizó algo
            if ($stmt->rowCount() === 0) {
                echo json_encode(["success" => false, "message" => "No se actualizó ningún registro."]);
                exit;
            }

            // Obtener registros del día actualizados
            $registros_dia = obtenerRegistrosDia($conn, true);

            echo json_encode([
                "success" => true,
                "message" => "Folio registrado correctamente.",
                "data" => [
                    "consumo" => "PUNTO DE VENTA",
                    "folio" => $folio,
                    "registros_dia" => $registros_dia
                ]
            ]);
            exit;
        }

        // 3. Registro de venta
        $barcode = isset($_POST['barcode']) ? trim($_POST['barcode']) : null;
        $tipo_pago = isset($_POST['tipo_pago']) ? (int)$_POST['tipo_pago'] : null;
        $cantidad = isset($_POST['cantidad']) ? (int)$_POST['cantidad'] : null;

        if (!$barcode || !$tipo_pago || !$cantidad) {
            throw new Exception("Todos los campos son obligatorios.");
        }

        date_default_timezone_set('America/Mexico_City');
        $currentTime = new DateTime();
        $formattedTime = $currentTime->format('Y-m-d H:i:s');

        // Verificar producto
        $sql_producto = "SELECT id, consumo, costo, barcode 
                         FROM punto_consumo
                         WHERE barcode = :barcode AND estatus = 1";
        $stmt_producto = $conn->prepare($sql_producto);
        $stmt_producto->bindParam(':barcode', $barcode);
        $stmt_producto->execute();
        $producto = $stmt_producto->fetch(PDO::FETCH_ASSOC);

        if (!$producto) {
            throw new Exception("Código de barras no reconocido.");
        }

        // Insertar en control_puntoventa
        $sql_punto_venta = "
            INSERT INTO control_puntoventa 
            (barcode, consumo_id, cantidad, tipo_pago, fecha_registro, estatus, user_id)
            VALUES (:barcode, :consumo_id, :cantidad, :tipo_pago, NOW(), 1, :user_id)
        ";
        $stmt_punto_venta = $conn->prepare($sql_punto_venta);
        $stmt_punto_venta->bindParam(':barcode', $producto['barcode']);
        $stmt_punto_venta->bindParam(':consumo_id', $producto['id']);
        $stmt_punto_venta->bindParam(':cantidad', $cantidad);
        $stmt_punto_venta->bindParam(':tipo_pago', $tipo_pago);
        $stmt_punto_venta->bindParam(':user_id', $sesionUsuarioId);
        $stmt_punto_venta->execute();

        // Obtener registros del día actualizados
        $registros_dia = obtenerRegistrosDia($conn, true);

        echo json_encode([
            "success" => true,
            "message" => "Servicio registrado correctamente.",
            "data" => [
                "consumo" => "PUNTO DE VENTA",
                "cantidad" => $cantidad,
                "fecha_registro" => $formattedTime,
                "registros_dia" => $registros_dia
            ]
        ]);
    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
    }
}

// Función para obtener los registros del día
function obtenerRegistrosDia($conn, $returnData = false)
{
    $sql = "
        SELECT 
            cpv.id,
            cpv.barcode,
            pc.consumo AS nombre_consumo,
            cpv.cantidad,
            pc.costo,
            (cpv.cantidad * pc.costo) AS total,
            tp.nombre AS nombre_tipo_pago,
            cpv.folio,
            cpv.fecha_registro,
            cs.status AS estatus
        FROM control_puntoventa cpv
        LEFT JOIN punto_consumo pc ON pc.id = cpv.consumo_id
        LEFT JOIN tipo_pago tp ON tp.id = cpv.tipo_pago
        LEFT JOIN catalog_status cs ON cpv.estatus = cs.status_id
        WHERE DATE(cpv.fecha_registro) = CURDATE()
        ORDER BY cpv.fecha_registro DESC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($returnData) {
        return $registros;
    } else {
        echo json_encode(["success" => true, "data" => ["registros_dia" => $registros]]);
    }
}
