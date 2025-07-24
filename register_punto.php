<?php
// register_punto.php — Backend limpio que siempre devuelve JSON

header('Content-Type: application/json; charset=utf-8');
include 'db_connection.php'; // Debe definir $conn como instancia PDO
session_start();

if (!isset($_SESSION['resultado'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Usuario no autenticado.'
    ]);
    exit;
}

$sesi = $_SESSION['resultado'];
$sesionUsuarioId = $sesi['id'];

try {
    // 1) Obtener registros del día
    if (isset($_POST['action']) && $_POST['action'] === 'get_registros_dia') {
        $registros = obtenerRegistrosDia($conn, true);
        echo json_encode([
            'success' => true,
            'data'    => ['registros_dia' => $registros]
        ]);
        exit;
    }

    // 1.1) Obtener totales por forma de pago
    if (isset($_POST['action']) && $_POST['action'] === 'get_totales_pago') {
        $sql = "
            SELECT tp.nombre AS forma_pago, SUM(cpv.total) AS total
            FROM control_puntoventa cpv
            LEFT JOIN tipo_pago tp ON tp.id = cpv.tipo_pago
            WHERE DATE(cpv.fecha_registro) = CURDATE()
            AND cpv.estatus = 1
            GROUP BY tp.nombre
        ";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $totales = [
            'EFECTIVO'           => 0,
            'TARJETA DE CRÉDITO' => 0,
            'TARJETA DE DÉBITO'  => 0
        ];
        foreach ($rows as $r) {
            if (isset($totales[$r['forma_pago']])) {
                $totales[$r['forma_pago']] = (float)$r['total'];
            }
        }

        echo json_encode([
            'success' => true,
            'totales' => $totales
        ]);
        exit;
    }

    // 2) Actualizar folio
    if (isset($_POST['action']) && $_POST['action'] === 'actualizar_folio') {
        $id    = isset($_POST['id'])    ? (int) trim($_POST['id'])    : null;
        $folio = isset($_POST['folio']) ? trim($_POST['folio'])       : '';

        if (!$id || $folio === '') {
            echo json_encode([
                'success' => false,
                'message' => 'ID y folio son requeridos.'
            ]);
            exit;
        }

        $sql = "UPDATE control_puntoventa SET folio = :folio WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':folio', $folio);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() === 0) {
            echo json_encode([
                'success' => false,
                'message' => 'No se actualizó ningún registro.'
            ]);
            exit;
        }

        $registros = obtenerRegistrosDia($conn, true);
        echo json_encode([
            'success' => true,
            'message' => 'Folio registrado correctamente.',
            'data'    => [
                'folio'         => $folio,
                'registros_dia' => $registros
            ]
        ]);
        exit;
    }

    // 3) Registro de venta
    $barcode   = trim($_POST['barcode']   ?? '');
    $tipo_pago = (int) ($_POST['tipo_pago'] ?? 0);
    $cantidad  = (int) ($_POST['cantidad']  ?? 0);
    $pago_con  = isset($_POST['pago_con']) ? floatval($_POST['pago_con']) : 0;

    if ($barcode === '' || $tipo_pago <= 0 || $cantidad <= 0) {
        throw new Exception('Todos los campos son obligatorios.');
    }

    // Buscar producto por código de barras
    $sql = "
        SELECT id, consumo, costo, barcode
        FROM punto_consumo
        WHERE barcode = :barcode AND estatus = 1
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':barcode', $barcode);
    $stmt->execute();
    $producto = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$producto) {
        throw new Exception('Código de barras no reconocido.');
    }

    // Calcular total y cambio
    $total  = $cantidad * $producto['costo'];

    if ($tipo_pago === 1) {
        $cambio = max(0, $pago_con - $total);
    } else {
        $pago_con = 0;
        $cambio = 0;
    }


    // Insertar venta en control_puntoventa
    $sql = "
        INSERT INTO control_puntoventa
        (barcode, consumo_id, cantidad, tipo_pago, fecha_registro, estatus, user_id, total, pago_con, cambio)
        VALUES (:barcode, :cid, :cant, :tp, NOW(), 1, :uid, :tot, :pago_con, :cambio)
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':barcode', $producto['barcode']);
    $stmt->bindParam(':cid',     $producto['id'], PDO::PARAM_INT);
    $stmt->bindParam(':cant',    $cantidad,       PDO::PARAM_INT);
    $stmt->bindParam(':tp',      $tipo_pago,      PDO::PARAM_INT);
    $stmt->bindParam(':uid',     $sesionUsuarioId, PDO::PARAM_INT);
    $stmt->bindParam(':tot',     $total);
    $stmt->bindParam(':pago_con', $pago_con);
    $stmt->bindParam(':cambio', $cambio);
    $stmt->execute();

    $registros = obtenerRegistrosDia($conn, true);
    $formattedTime = (new DateTime())->format('Y-m-d H:i:s');

    echo json_encode([
        'success' => true,
        'message' => 'Servicio registrado correctamente.',
        'data'    => [
            'consumo'        => 'PUNTO DE VENTA',
            'cantidad'       => $cantidad,
            'fecha_registro' => $formattedTime,
            'total'          => $total,
            'registros_dia'  => $registros
        ]
    ]);
    exit;
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
    exit;
}

/**
 * Función helper para obtener registros del día
 */
function obtenerRegistrosDia(PDO $conn, bool $returnData = false)
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
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($returnData) {
        return $rows;
    }

    echo json_encode([
        'success' => true,
        'data'    => ['registros_dia' => $rows]
    ]);
    exit;
}
