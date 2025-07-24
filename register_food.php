<?php
include 'db_connection.php';
session_start();

if (!isset($_SESSION['resultado'])) {
    header('Location: index.html');
    exit;
}

$sesi = $_SESSION['resultado'];
$sesionUsuarioId = $sesi['id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Solicitud para obtener registros del día
    if (isset($_POST['action']) && $_POST['action'] === 'get_registros_dia') {
        $sql = "
            SELECT ca.id, tu.tipo_usuario, 
                cm.imagePath,
                ca.num_empleado,
                cm.nombre_completo,
                p.producto AS tipo_producto,
                ca.cantidad,
                FORMAT(p.costo, 2) AS importe,
                ca.fecha_registro,
                cs.status AS estatus
            FROM control_alimentos ca
            LEFT JOIN comensal cm ON ca.barcode = cm.barcode
            LEFT JOIN producto p ON ca.tipo_producto = p.id
            LEFT JOIN tipo_usuario tu ON cm.tipo_usuario = tu.id
            LEFT JOIN catalog_status cs ON ca.estatus_id = cs.status_id
            WHERE DATE(ca.fecha_registro) = CURDATE()
            ORDER BY ca.fecha_registro DESC
        ";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $registros_dia = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            "success" => true,
            "data" => ["registros_dia" => $registros_dia]
        ]);
        exit;
    }

    // Procesar escaneo
    $barcode = !empty($_POST['barcode']) ? str_pad($_POST['barcode'], 12, '0', STR_PAD_LEFT) : null;

    if (!$barcode) {
        echo json_encode(["success" => false, "message" => "El código de barras es obligatorio."]);
        exit;
    }

    try {
        date_default_timezone_set('America/Mexico_City');
        $now = new DateTime();
        $formattedTime = $now->format('Y-m-d H:i:s');
        $totalMinutes = (int)$now->format('H') * 60 + (int)$now->format('i');

        $tipo_producto = null;
        $tipoProductoNombre = "";

        if ($totalMinutes >= 480 && $totalMinutes <= 660) {
            $tipo_producto = 1;
            $tipoProductoNombre = "DESAYUNO";
        } elseif ($totalMinutes >= 661 && $totalMinutes <= 1020) {
            $tipo_producto = 2;
            $tipoProductoNombre = "COMIDA";
        } elseif ($totalMinutes >= 1021 && $totalMinutes <= 1439) {
            $tipo_producto = 3;
            $tipoProductoNombre = "CENA";
        } elseif ($totalMinutes >= 0 && $totalMinutes < 480) {
            $tipo_producto = 4;
            $tipoProductoNombre = "COLACIÓN NOCTURNA";
        } else {
            echo json_encode([
                "success" => false,
                "message" => "Fuera de horario de servicio. Los horarios son:\n- Desayuno: 08:00 - 11:00\n- Comida: 13:00 - 16:00\n- Cena: 21:30 - 23:30\n- Colación: 00:00 - 02:00"
            ]);
            exit;
        }

        // Buscar comensal solo si está activo
        $sql = "
            SELECT c.id AS comensal_id, c.num_empleado, c.barcode, c.nombre_completo, 
                   c.tipo_usuario, c.imagePath, e.nombre AS empresa,
                   c.t_desayuno, c.t_comida, c.t_cena, c.t_colacion,
                   c.estatus
            FROM comensal c
            INNER JOIN empresa e ON c.empresa = e.id
            WHERE c.barcode = :barcode AND c.estatus = 1
        ";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':barcode', $barcode);
        $stmt->execute();
        $comensal = $stmt->fetch(PDO::FETCH_ASSOC);

        // Si no hay resultado, revisar si está dado de baja o no existe
        if (!$comensal) {
            $stmt = $conn->prepare("SELECT estatus FROM comensal WHERE barcode = :barcode");
            $stmt->bindParam(':barcode', $barcode);
            $stmt->execute();
            $registro = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($registro && $registro['estatus'] != 1) {
                echo json_encode(["success" => false, "message" => "Este comensal fue dado de baja. No se permite el registro."]);
            } else {
                echo json_encode(["success" => false, "message" => "Código no registrado."]);
            }
            exit;
        }

        // Validar turnos
        $turnosActivos = (int)$comensal['t_desayuno'] + (int)$comensal['t_comida'] + (int)$comensal['t_cena'] + (int)$comensal['t_colacion'];

        if ($turnosActivos === 1) {
            $sql_verificar = "
                SELECT id FROM control_alimentos
                WHERE barcode = :barcode
                AND DATE(fecha_registro) = CURDATE()
                AND estatus_id = 1
            ";
            $stmt = $conn->prepare($sql_verificar);
            $stmt->bindParam(':barcode', $barcode);
        } else {
            $sql_verificar = "
                SELECT id FROM control_alimentos
                WHERE barcode = :barcode
                AND tipo_producto = :tipo_producto
                AND DATE(fecha_registro) = CURDATE()
                AND estatus_id = 1
            ";
            $stmt = $conn->prepare($sql_verificar);
            $stmt->bindParam(':barcode', $barcode);
            $stmt->bindParam(':tipo_producto', $tipo_producto);
        }

        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            echo json_encode([
                "success" => false,
                "message" => ($turnosActivos === 1)
                    ? "Ya ha consumido su alimento asignado del día."
                    : "Ya se registró este tipo de comida para hoy. Solicite autorización para otro servicio."
            ]);
            exit;
        }

        // Insertar registro válido
        $sql_insert = "
            INSERT INTO control_alimentos (comensal_id, barcode, num_empleado, tipo_producto, cantidad, fecha_registro, estatus_id, user_id)
            VALUES (:comensal_id, :barcode, :num_empleado, :tipo_producto, 1, NOW(), 1, :user_id)
        ";
        $stmt = $conn->prepare($sql_insert);
        $stmt->bindParam(':comensal_id', $comensal['comensal_id']);
        $stmt->bindParam(':barcode', $barcode);
        $stmt->bindParam(':num_empleado', $comensal['num_empleado']);
        $stmt->bindParam(':tipo_producto', $tipo_producto);
        $stmt->bindParam(':user_id', $sesionUsuarioId);
        $stmt->execute();

        // Obtener registros del día
        $sql = "
            SELECT ca.id, tu.tipo_usuario, 
                cm.imagePath,
                ca.num_empleado,
                cm.nombre_completo,
                p.producto AS tipo_producto,
                ca.cantidad,
                FORMAT(p.costo, 2) AS importe,
                ca.fecha_registro,
                cs.status AS estatus
            FROM control_alimentos ca
            LEFT JOIN comensal cm ON ca.barcode = cm.barcode
            LEFT JOIN producto p ON ca.tipo_producto = p.id
            LEFT JOIN tipo_usuario tu ON cm.tipo_usuario = tu.id
            LEFT JOIN catalog_status cs ON ca.estatus_id = cs.status_id
            WHERE DATE(ca.fecha_registro) = CURDATE()
            ORDER BY ca.fecha_registro DESC
        ";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $registros_dia = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            "success" => true,
            "message" => "Registro exitoso.",
            "data" => [
                "imagePath" => $comensal['imagePath'],
                "empresa" => $comensal['empresa'],
                "num_empleado" => $comensal['num_empleado'],
                "nombre_completo" => $comensal['nombre_completo'],
                "tipo_producto" => $tipoProductoNombre,
                "fecha_registro" => $formattedTime,
                "cantidad" => 1,
                "registros_dia" => $registros_dia
            ]
        ]);
        exit;
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Error en base de datos: " . $e->getMessage()]);
        exit;
    }
}
