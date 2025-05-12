<?php

include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Si se solicita obtener los registros del día
        if (isset($_POST['action']) && $_POST['action'] == 'get_registros_dia') {
            obtenerRegistrosDia($conn);
            exit;
        }

        // Validar datos requeridos
        $barcode = isset($_POST['barcode']) ? trim($_POST['barcode']) : null;
        $tipo_pago = isset($_POST['tipo_pago']) ? (int)$_POST['tipo_pago'] : null;
        $cantidad = isset($_POST['cantidad']) ? (int)$_POST['cantidad'] : null;

        if (!$barcode || !$tipo_pago ||  !$cantidad) {
            throw new Exception("Todos los campos son obligatorios.");
        }

        date_default_timezone_set('America/Mexico_City');
        $currentTime = new DateTime();
        $formattedTime = $currentTime->format('Y-m-d H:i:s');


        // Buscar producto en la BD
        $sql_producto = "SELECT id, consumo, costo, barcode 
                            FROM punto_consumo
                            WHERE barcode = :barcode 
                            AND estatus = 1";
        $stmt_producto = $conn->prepare($sql_producto);
        $stmt_producto->bindParam(':barcode', $barcode);
        $stmt_producto->execute();
        $producto = $stmt_producto->fetch(PDO::FETCH_ASSOC);

        if (!$producto) {
            throw new Exception("Código de barras no reconocido.");
        }

        // Insertar cortesía en la BD
        $sql_punto_venta = "
            INSERT INTO control_puntoventa (barcode, consumo_id, cantidad, tipo_pago, fecha_registro, estatus)
            VALUES (:barcode, :consumo_id, :cantidad, :tipo_pago, NOW(), 1)
        ";
        $stmt_punto_venta = $conn->prepare($sql_punto_venta);
        $stmt_punto_venta->bindParam(':barcode', $producto['barcode']);
        $stmt_punto_venta->bindParam(':consumo_id', $producto['id']);
        $stmt_punto_venta->bindParam(':cantidad', $cantidad);
        $stmt_punto_venta->bindParam(':tipo_pago', $tipo_pago);


        $stmt_punto_venta->execute();

        // Obtener registros del día tras la inserción
        $registros_dia = obtenerRegistrosDia($conn, true);

        echo json_encode([
            "success" => true,
            "message" => "Servicio Registrado correctamente.",
            "data" => [
                "consumo" => "PUNTO DE VENTA",
                "cantidad" => $cantidad,
                "fecha_registro" => $formattedTime,
                "registros_dia_cafe" => $registros_dia
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
            cpv.barcode AS barcode,
            pc.consumo AS nombre_consumo,
            cpv.cantidad,
             pc.costo,
             (cpv.cantidad * pc.costo) AS total,
            tp.nombre AS nombre_tipo_pago,
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
