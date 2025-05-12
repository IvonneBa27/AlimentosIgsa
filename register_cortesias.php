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
        $empresa = isset($_POST['empresa_id']) ? (int)$_POST['empresa_id'] : null;
        $departamento = isset($_POST['departamento_id']) ? (int)$_POST['departamento_id'] : null;
        $solicitante = isset($_POST['solicitante']) ? trim($_POST['solicitante']) : null;
        $cantidad = isset($_POST['cantidad']) ? (int)$_POST['cantidad'] : null;

        if (!$barcode || !$empresa || !$departamento || !$solicitante || !$cantidad) {
            throw new Exception("Todos los campos son obligatorios.");
        }

        date_default_timezone_set('America/Mexico_City');
        $currentTime = new DateTime();
        $formattedTime = $currentTime->format('Y-m-d H:i:s');

        // Definir tipo de producto según la hora
        $time = (int)date('H') * 60 + (int)date('i');
        if ($time >= 480 && $time < 660) {
            $tipo_producto = 1; // Desayuno
        } elseif ($time >= 780 && $time < 1760) {
            $tipo_producto = 2; // Comida
        } elseif ($time >= 1290 && $time < 1810) {
            $tipo_producto = 3; // Cena
        } elseif ($time >= 1440 || $time < 120) {
            $tipo_producto = 4; // Colación nocturna
        } else {
            throw new Exception("Fuera de horario de servicio. Los horarios son:
                - Desayuno: 08:00 - 11:00
                - Comida: 13:00 - 16:00
                - Cena: 21:30 - 23:30
                - Colación nocturna: 00:00 - 02:00");
        }

        // Buscar producto en la BD
        $sql_producto = "SELECT id, producto, costo, barcode 
        FROM producto 
        WHERE barcode = :barcode 
        OR producto LIKE '%CORTESIA%'
        AND estatus = 1";
        $stmt_producto = $conn->prepare($sql_producto);
        $stmt_producto->bindParam(':barcode', $barcode);
        $stmt_producto->execute();
        $producto = $stmt_producto->fetch(PDO::FETCH_ASSOC);

        if (!$producto) {
            throw new Exception("Código de barras no reconocido.");
        }

        // Insertar cortesía en la BD
        $sql_cortesia = "
            INSERT INTO control_cortesias (barcode, empresa_id, departamento_id, solicitante, tipo_producto, cantidad, fecha_registro, estatus_id)
            VALUES (:barcode, :empresa_id, :departamento_id, :solicitante, :tipo_producto, :cantidad, NOW(), 1)
        ";
        $stmt_cortesia = $conn->prepare($sql_cortesia);
        $stmt_cortesia->bindParam(':barcode', $producto['barcode']);
        $stmt_cortesia->bindParam(':empresa_id', $empresa);
        $stmt_cortesia->bindParam(':departamento_id', $departamento);
        $stmt_cortesia->bindParam(':solicitante', $solicitante);
        $stmt_cortesia->bindParam(':tipo_producto', $tipo_producto);
        $stmt_cortesia->bindParam(':cantidad', $cantidad);
        $stmt_cortesia->execute();

        // Obtener registros del día tras la inserción
        $registros_dia = obtenerRegistrosDia($conn, true);

        echo json_encode([
            "success" => true,
            "message" => "Producto registrado correctamente.",
            "data" => [
                "nombre_completo" => "VISITANTE",
                "empresa" => $producto['producto'],
                "tipo_producto" => $producto['producto'],
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
            co.id,
            'CORTESÍA' AS barcode,
            em.nombre AS nombre_empresa,
            de.nombre AS nombre_departamento,
            co.solicitante,
            p.producto AS tipo_producto,
            co.cantidad,
            co.fecha_registro,
            cs.status AS estatus
        FROM control_cortesias co
        LEFT JOIN producto p ON co.tipo_producto = p.id
        LEFT JOIN empresa em ON em.id = co.empresa_id
        LEFT JOIN departamento de ON de.id = co.departamento_id 
        LEFT JOIN catalog_status cs ON co.estatus_id = cs.status_id
        WHERE DATE(co.fecha_registro) = CURDATE()
        ORDER BY co.fecha_registro DESC
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
