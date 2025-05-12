<?php

use PhpOffice\PhpSpreadsheet\Style\NumberFormat\FractionFormatter;

include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action']) && $_POST['action'] == 'get_registros_dia') {
        // Obtener los registros del día con el formato especificado
        $sql_registros_dia = "
            SELECT ca.id, tu.tipo_usuario, 
                cm.imagePath AS imagePath,
                ca.num_empleado  AS num_empleado,
                cm.nombre_completo AS nombre_completo,
                p.producto as tipo_producto,
                ca.cantidad as cantidad,
                FORMAT(p.costo, 2) AS importe,
                ca.fecha_registro as fecha_registro,
                cs.status as estatus   
            FROM control_alimentos ca
            LEFT JOIN comensal cm ON ca.barcode = cm.barcode
            LEFT JOIN producto p ON ca.tipo_producto = p.id
            LEFT JOIN tipo_usuario tu ON ca.comensal_id = tu.id
            LEFT JOIN catalog_status cs ON ca.estatus_id = cs.status_id
            WHERE DATE(ca.fecha_registro) = CURDATE()
            ORDER BY ca.fecha_registro DESC
        ";
        $stmt_registros_dia = $conn->prepare($sql_registros_dia);
        $stmt_registros_dia->execute();
        $registros_dia = $stmt_registros_dia->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            "success" => true,
            "data" => [
                "registros_dia" => $registros_dia
            ]
        ]);
        exit;
    }

    // Obtener y procesar el código de barras
    $barcode = !empty($_POST['barcode']) ? str_pad($_POST['barcode'], 12, '0', STR_PAD_LEFT) : null;

    if ($barcode) {
        try {
            date_default_timezone_set('America/Mexico_City');
            $currentTime = new DateTime();
            $formattedTime = $currentTime->format('Y-m-d H:i:s'); // Formato de fecha y hora
            $hour = (int)$currentTime->format('H');
            $tipoProducto = "DESAYUNO";

            // Obtener la hora actual
            $hour = date('H');
            $minute = date('i');
            $time = $hour * 60 + $minute; // Convertimos todo a minutos para facilitar comparación

            if ($time >= 480 && $time < 660) { // 08:00 - 11:00 -$time >= 480 && $time < 660
                $tipo_producto = 1; // Desayuno
                $tipoProducto = "DESAYUNO";
            } elseif ($time >= 780 && $time < 960) { // 13:00 - 16:00 960
                $tipo_producto = 2; // Comida
                $tipoProducto = "COMIDA";
            } elseif ($time >= 1290 && $time < 1410) { // 21:30 - 23:30
                $tipo_producto = 3; // Cena
                $tipoProducto = "CENA";
            } elseif ($time >= 1440 || $time < 120) { // 00:00 - 02:00
                $tipo_producto = 4;
                $tipoProducto = "COLACIÓN NOCTURNA";
            } else {
                // Si el escaneo se realiza fuera de los horarios establecidos, mostrar mensaje de error
                echo json_encode([
                    "success" => false,
                    "message" => "Fuera de horario de servicio. Los horarios son: 
                    - Desayuno: 08:00 - 11:00 
                    - Comida: 13:00 - 16:00 
                    - Cena: 21:30 - 23:30 
                    - Colación nocturna: 00:00 - 02:00"
                ]);
                exit;
            }


            // Verificar si el código de barras corresponde a un comensal registrado
            $sql_comensal = "
                SELECT c.id AS comensal_id, c.num_empleado as num_empleado, c.barcode as barcode,  c.nombre_completo, c.tipo_usuario, c.imagePath as imagePath, e.nombre as empresa
                FROM comensal c 
                    inner join empresa e on c.empresa = e.id
                WHERE c.barcode = :barcode
            ";
            $stmt_comensal = $conn->prepare($sql_comensal);
            $stmt_comensal->bindParam(':barcode', $barcode);
            $stmt_comensal->execute();
            $comensal = $stmt_comensal->fetch(PDO::FETCH_ASSOC);


            if ($comensal) {
                // Verificar si ya existe un registro reciente del mismo comensal
                $sql_verificar = "
                    SELECT id FROM control_alimentos
                    WHERE barcode = :barcode
                    AND tipo_producto = :tipo_producto
                    AND DATE(fecha_registro) = CURDATE()
                    AND estatus_id = 1
                ";
                $stmt_verificar = $conn->prepare($sql_verificar);
                $stmt_verificar->bindParam(':barcode', $comensal['barcode']);
                $stmt_verificar->bindParam(':tipo_producto', $tipo_producto);
                $stmt_verificar->execute();

                if ($stmt_verificar->rowCount() > 0) {
                    // Ya existe un registro para este tipo de comida
                    echo json_encode([
                        "success" => false,
                        "message" => "Ya se registró este tipo de comida para hoy. Solicite autorización para otro servicio."
                    ]);
                    exit;
                }


                // Registrar el consumo del comensal
                $sql_registro = "
                    INSERT INTO control_alimentos (comensal_id, barcode, num_empleado, tipo_producto, cantidad, fecha_registro, estatus_id)
                    VALUES (:comensal_id, :barcode, :num_empleado, :tipo_producto, :cantidad, NOW(), :estatus_id)
                ";
                $stmt_registro = $conn->prepare($sql_registro);
                $stmt_registro->bindValue(':comensal_id', 1);
                $stmt_registro->bindParam(':barcode', $comensal['barcode']);
                $stmt_registro->bindParam(':num_empleado', $comensal['num_empleado']);
                $stmt_registro->bindParam(':tipo_producto', $tipo_producto);
                $stmt_registro->bindValue(':cantidad', 1); // Cantidad fija de 1
                $stmt_registro->bindValue(':estatus_id', 1); // Cantidad fija de 1
                $stmt_registro->execute();

                // Obtener los registros del día con el formato especificado
                $sql_registros_dia = "
                SELECT ca.id, tu.tipo_usuario, 
                    cm.imagePath AS imagePath,
                    ca.num_empleado AS num_empleado,
                    cm.nombre_completo  AS nombre_completo,
                    ca.cantidad AS cantidad,
                    FORMAT(p.costo, 2) AS importe,
                    ca.fecha_registro AS fecha_registro,
                    cs.status as estatus   
                        FROM control_alimentos ca
                        LEFT JOIN comensal cm ON ca.barcode = cm.barcode
                        LEFT JOIN producto p ON ca.tipo_producto = p.id
                        LEFT JOIN tipo_usuario tu ON ca.comensal_id = tu.id
                        LEFT JOIN catalog_status cs ON ca.estatus_id = cs.status_id
                        WHERE DATE(ca.fecha_registro) = CURDATE()
                        ORDER BY ca.fecha_registro DESC
                ";
                $stmt_registros_dia = $conn->prepare($sql_registros_dia);
                $stmt_registros_dia->execute();
                $registros_dia = $stmt_registros_dia->fetchAll(PDO::FETCH_ASSOC);

                echo json_encode([
                    "success" => true,
                    "message" => "Registro exitoso.",
                    "data" => [
                        "imagePath" => $comensal['imagePath'],
                        "empresa" => $comensal['empresa'],
                        "num_empleado" => $comensal['num_empleado'],
                        "nombre_completo" => $comensal['nombre_completo'],
                        "tipo_producto" => $tipoProducto,
                        "fecha_registro" => $formattedTime,
                        "cantidad" => 1,
                        "registros_dia" => $registros_dia
                    ]
                ]);
                exit;
            } else {
                // Si no es un comensal, verificar si es un producto válido
                $sql_producto = "
                    SELECT id, producto, costo, barcode
                    FROM producto 
                    WHERE barcode = :barcode
                ";
                $stmt_producto = $conn->prepare($sql_producto);
                $stmt_producto->bindParam(':barcode', $barcode);
                $stmt_producto->execute();
                $producto = $stmt_producto->fetch(PDO::FETCH_ASSOC);

                if ($producto) {
                    // Registrar como cortesía
                    $sql_cortesia = "
                        INSERT INTO control_alimentos (comensal_id, barcode, num_empleado, tipo_producto, cantidad, fecha_registro, estatus_id)
                        VALUES (:comensal_id, :barcode, :num_empleado, :tipo_producto, :cantidad, NOW(), :estatus_id)
                    ";
                    $stmt_cortesia = $conn->prepare($sql_cortesia);
                    $stmt_cortesia->bindValue(':comensal_id', 2); // Tipo de usuario genérico para cortesías
                    $stmt_cortesia->bindParam(':barcode', $producto['barcode']);
                    $stmt_cortesia->bindValue(':num_empleado', 0); // Número de empleado como 0
                    $stmt_cortesia->bindParam(':tipo_producto', $producto['id']);
                    $stmt_cortesia->bindValue(':cantidad', 1);
                    $stmt_cortesia->bindValue(':estatus_id', 1); // Cantidad fija de 1
                    $stmt_cortesia->execute();

                    // Obtener los registros del día con el formato especificado
                    $sql_registros_dia = "
                        SELECT ca.id, tu.tipo_usuario, 
                            cm.imagePath AS imagePath,
                            ca.num_empleado AS num_empleado,
                            cm.nombre_completo  AS nombre_completo,
                            p.producto as tipo_producto,
                            ca.cantidad as cantidad,
                            FORMAT(p.costo, 2) AS importe,
                            ca.fecha_registro as fecha_registro,
                            cs.status as estatus   
                            FROM control_alimentos ca
                            LEFT JOIN comensal cm ON ca.barcode = cm.barcode
                            LEFT JOIN producto p ON ca.tipo_producto = p.id
                            LEFT JOIN tipo_usuario tu ON ca.comensal_id = tu.id
                            LEFT JOIN catalog_status cs ON ca.estatus_id = cs.status_id
                            WHERE DATE(ca.fecha_registro) = CURDATE()
                            ORDER BY ca.fecha_registro DESC
                    ";
                    $stmt_registros_dia = $conn->prepare($sql_registros_dia);
                    $stmt_registros_dia->execute();
                    $registros_dia = $stmt_registros_dia->fetchAll(PDO::FETCH_ASSOC);

                    echo json_encode([
                        "success" => true,
                        "message" => "Producto registrado como cortesía.",
                        "data" => [
                            "num_empleado" => "SIN NÚMERO",
                            "nombre_completo" => "VISITANTE",
                            "empresa" => "---",
                            "tipo_producto" => $producto['producto'],
                            "cantidad" => 1,
                            "fecha_registro" => $formattedTime,
                            "fecha" => $currentTime,
                            "registros_dia" => $registros_dia
                        ]
                    ]);
                    exit;
                }
            }

            // Código no reconocido
            echo json_encode([
                "success" => false,
                "message" => "Código de barras no reconocido."
            ]);
        } catch (PDOException $e) {
            echo json_encode([
                "success" => false,
                "message" => "Error: " . $e->getMessage()
            ]);
        }
    } else {
        echo json_encode([
            "success" => false,
            "message" => "El código de barras es obligatorio."
        ]);
    }

    // Cerrar conexión
    $conn = null;
}
