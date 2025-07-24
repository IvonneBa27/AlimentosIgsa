<?php
include('conexion.php'); // Asegúrate que $con esté definido ahí
session_start();

// Verifica sesión activa
if (!isset($_SESSION['resultado'])) {
    header('Location: index.html');
    exit;
}

$sesi = $_SESSION['resultado'];
$sesionUsuario = $sesi['usuario'];
$sesionNombre = $sesi['nombre'];
$sesionUsuarioId = $sesi['id'];

date_default_timezone_set("America/Mexico_City");

$fechaHoy = date("Y-m-d");
$fechaAyer = date("Y-m-d", strtotime("-1 day"));
$horaActual = date("H:i:s");



// 1. Verificar si ya hay caja abierta hoy
$verifica = $con->prepare("SELECT id FROM caja_movimientos WHERE fecha = ? AND estatus = 'ABIERTA'");
$verifica->bind_param("s", $fechaHoy);
$verifica->execute();
$verifica->store_result();

// Caja abierta previamente
if ($verifica->num_rows > 0) {
    header("Location: operacionAperturaCaja.php?error=caja_abierta");
    exit;
}


// 2. Monto ingresado en el formulario
$montoFormulario = floatval($_POST['monto_inicial']);


// 3. Buscar ventas en efectivo del día anterior en control_puntoventa
$sql = "SELECT SUM(total) AS total_efectivo
        FROM control_puntoventa
        WHERE DATE(fecha_registro) = ? AND tipo_pago = 1";
$stmt = $con->prepare($sql);
$stmt->bind_param("s", $fechaAyer);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

$montoAnterior = floatval($row['total_efectivo'] ?? 0);



// 4. Total final de apertura (formulario + sobrante anterior)
$montoApertura = $montoFormulario + $montoAnterior;

// 5. Insertar apertura en caja_movimientos
$insert = $con->prepare("INSERT INTO caja_movimientos 
    (fecha, hora_apertura, monto_apertura, estatus, usuario_apertura_id)
    VALUES (?, ?, ?, 'abierta', ?)");
$insert->bind_param("ssdi", $fechaHoy, $horaActual, $montoApertura, $sesionUsuarioId);

if ($insert->execute()) {
    header("Location: operacionAperturaCaja.php?success=caja_abierta&amount=" . urlencode(number_format($montoApertura, 2)));
    exit;
} else {
    header("Location: operacionAperturaCaja.php?error=apertura_fallida");
    exit;
}

?>
