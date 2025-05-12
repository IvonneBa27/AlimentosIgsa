<?php

include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Si se solicita obtener los registros del día
        if (isset($_POST['action']) && $_POST['action'] == 'get_registros_dia_cafe') {
            obtenerRegistrosDia($conn);
            exit;
        }

        // Validar datos requeridos
        $barcode = isset($_POST['barcode']) ? trim($_POST['barcode']) : null;
        $empresa = isset($_POST['empresa_id']) ? (int)$_POST['empresa_id'] : null;
        $departamento = isset($_POST['departamento_id']) ? (int)$_POST['departamento_id'] : null;
        $sala = isset($_POST['sala_id']) ? (int)$_POST['sala_id'] : null;
        $solicitante = isset($_POST['solicitante']) ? trim($_POST['solicitante']) : null;
        $cantidad = isset($_POST['cantidad']) ? (int)$_POST['cantidad'] : null;

        if (!$barcode || !$empresa || !$departamento || !$sala || !$solicitante || !$cantidad) {
            throw new Exception("Todos los campos son obligatorios.");
        }

        date_default_timezone_set('America/Mexico_City');
        $currentTime = new DateTime();
        $formattedTime = $currentTime->format('Y-m-d H:i:s');


        // Buscar producto en la BD
        $sql_producto = "SELECT id, producto, costo, barcode 
                            FROM producto 
                            WHERE barcode = :barcode 
                            OR producto  LIKE '%SERVICIO DE CAFE%'
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
            INSERT INTO control_cafeteria (barcode, empresa_id, departamento_id, sala_id, solicitante, cantidad, fecha_registro, estatus_id)
            VALUES (:barcode, :empresa_id, :departamento_id, :sala_id, :solicitante, :cantidad, NOW(), 1)
        ";
        $stmt_cortesia = $conn->prepare($sql_cortesia);
        $stmt_cortesia->bindParam(':barcode', $producto['barcode']);
        $stmt_cortesia->bindParam(':empresa_id', $empresa);
        $stmt_cortesia->bindParam(':departamento_id', $departamento);
        $stmt_cortesia->bindParam(':sala_id', $sala);
        $stmt_cortesia->bindParam(':solicitante', $solicitante);
        $stmt_cortesia->bindParam(':cantidad', $cantidad);
        $stmt_cortesia->execute();

        // Obtener registros del día tras la inserción
        $registros_dia_cafe = obtenerRegistrosDia($conn, true);

        echo json_encode([
            "success" => true,
            "message" => "Servicio Registrado correctamente.",
            "data" => [
                "servicio" => "SERVICIO DE CAFÉ",
                "cantidad" => $cantidad,
                "fecha_registro" => $formattedTime,
                "registros_dia_cafe" => $registros_dia_cafe
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
            caf.id,
            'SERVICIO DE CAFE' AS barcode,
            em.nombre AS nombre_empresa,
            de.nombre AS nombre_departamento,
            sc.nombre AS nombre_sala,
            caf.solicitante,
            caf.cantidad,
            caf.fecha_registro,
            cs.status AS estatus
        FROM control_cafeteria caf
        LEFT JOIN empresa em ON em.id = caf.empresa_id
        LEFT JOIN departamento de ON de.id = caf.departamento_id 
        LEFT JOIN salas_conferencias sc ON sc.id = caf.sala_id 
        LEFT JOIN catalog_status cs ON caf.estatus_id = cs.status_id
        WHERE DATE(caf.fecha_registro) = CURDATE()
        ORDER BY caf.fecha_registro DESC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($returnData) {
        return $registros;
    } else {
        echo json_encode(["success" => true, "data" => ["registros_dia_cafe" => $registros]]);
    }
}
