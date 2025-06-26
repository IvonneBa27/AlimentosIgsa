<?php
include('conexion.php');
session_start();

if (!isset($_SESSION['resultado'])) {
    header('Location: index.html');
    exit;
}

$sesi = $_SESSION['resultado'];
$sesionUsuarioId = $sesi['id'];

date_default_timezone_set("America/Mexico_City");

$fechaHoy = date("Y-m-d");
$horaCierre = date("H:i:s");
$montoCierre = floatval($_POST['monto_final']); // Este es el monto contado por el usuario

// 1. Buscar la caja abierta hoy
$queryCaja = $con->prepare("SELECT id, monto_apertura FROM caja_movimientos WHERE fecha = ? AND estatus = 'ABIERTA'");
$queryCaja->bind_param("s", $fechaHoy);
$queryCaja->execute();
$resultCaja = $queryCaja->get_result();

if ($resultCaja->num_rows === 0) {
    echo "<script>alert('No hay caja abierta hoy.'); window.location.href='operacionCierreCaja.php';</script>";
    exit;
}

$rowCaja = $resultCaja->fetch_assoc();
$cajaId = $rowCaja['id'];
$montoApertura = floatval($rowCaja['monto_apertura']);

// 2. Calcular ingresos y egresos del día desde `movimientos`
$sqlMovs = "SELECT tipo, SUM(monto) AS total FROM caja_detalles WHERE DATE(fecha_hora) = ? GROUP BY tipo";
$stmtMovs = $con->prepare($sqlMovs);
$stmtMovs->bind_param("s", $fechaHoy);
$stmtMovs->execute();
$resultMovs = $stmtMovs->get_result();

$ingresos = 0;
$egresos = 0;

while ($row = $resultMovs->fetch_assoc()) {
    if ($row['tipo'] == 'INGRESO') {
        $ingresos = floatval($row['total']);
    } elseif ($row['tipo'] == 'EGRESO') {
        $egresos = floatval($row['total']);
    }
}

// 3. Calcular ventas en efectivo desde `control_puntoventa`
$sqlVentas = "SELECT SUM(total) AS total_efectivo FROM control_puntoventa WHERE DATE(fecha_registro) = ? AND tipo_pago = 1";
$stmtVentas = $con->prepare($sqlVentas);
$stmtVentas->bind_param("s", $fechaHoy);
$stmtVentas->execute();
$resultVentas = $stmtVentas->get_result();
$rowVentas = $resultVentas->fetch_assoc();

$ventasEfectivo = floatval($rowVentas['total_efectivo'] ?? 0);

// 4. Calcular total esperado
$totalEsperado = $montoApertura + $ingresos + $ventasEfectivo - $egresos;

// 5. Calcular diferencia
$diferencia = $montoCierre - $totalEsperado;

// 6. Actualizar la tabla caja_movimientos
// 6. Actualizar la tabla caja_movimientos
$update = $con->prepare("UPDATE caja_movimientos 
    SET hora_cierre = ?, monto_cierre = ?, estatus = 'CERRADA', usuario_cierre_id = ?, diferencias = ?, total_ventas = ?
    WHERE id = ?");
$update->bind_param("sdiddi", $horaCierre, $montoCierre, $sesionUsuarioId, $diferencia, $ventasEfectivo, $cajaId);


if ($update->execute()) {
     echo "<script>
        alert('Caja cerrada correctamente.\\nMonto contado: $" . number_format($montoCierre, 2) . 
        "\\nEsperado: $" . number_format($totalEsperado, 2) . 
        "\\nDiferencia: $" . number_format($diferencia, 2) . "');
        window.location.href='operacionCierreCaja.php';
    </script>";
} else {
    echo "<script>alert('Error al cerrar la caja.'); window.location.href='operacionCierreCaja.php';</script>";
}
?>
