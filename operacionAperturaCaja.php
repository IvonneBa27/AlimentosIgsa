<?php
include("conexion.php");
date_default_timezone_set("America/Mexico_City");
$fechaHoraActual = date("Y-m-d\TH:i:s");
session_start();
if (!isset($_SESSION['resultado'])) {
    header('Location: index.html');
    exit;
} else {
    $sesi = $_SESSION['resultado'];
}
$sesionUsuario = $sesi['usuario']; // Reemplaza con el nombre exacto de la columna
$sesionNombre = $sesi['nombre'];   // Reemplaza con el nombre exacto de la columna

include 'db_connection.php';

// Obtener las empresas para el select
$sqlTipoPagos = "SELECT id, nombre FROM tipo_pago WHERE estatus = 1";
$stmtTipoPagos = $conn->prepare($sqlTipoPagos);
$stmtTipoPagos->execute();
$tipoPagos = $stmtTipoPagos->fetchAll(PDO::FETCH_ASSOC);



$fechaHoy = date("Y-m-d");

// Obtener datos de la caja abierta (hoy)
$sqlCaja = "SELECT * FROM caja_movimientos WHERE fecha = ? LIMIT 1";
$stmtCaja = $conn->prepare($sqlCaja);
$stmtCaja->execute([$fechaHoy]);
$cajaHoy = $stmtCaja->fetch(PDO::FETCH_ASSOC);

// Si hay caja abierta, obtener movimientos
$movimientos = [];
if ($cajaHoy) {
    $cajaID = $cajaHoy['id'];

    $sqlMovs = "SELECT * FROM caja_detalles WHERE caja_movimiento_id = ?";
    $stmtMovs = $conn->prepare($sqlMovs);
    $stmtMovs->execute([$cajaID]);
    $movimientos = $stmtMovs->fetchAll(PDO::FETCH_ASSOC);
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> O P E R A C I Ó N &nbsp; &nbsp; P U N T O &nbsp; D E &nbsp; V E N T A</title>
    <link rel="stylesheet" href="node_modules/bootstrap/dist/css/bootstrap.min.css">
    <script src="js/color-modes.js"></script>
    <link rel="stylesheet" href="node_modules/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="node_modules/sweetalert2/dist/sweetalert2.min.css">
    <script src="node_modules/sweetalert2/dist/sweetalert2.min.js"></script>
    <script src="js/color-modes.js"></script>
    <link rel="stylesheet" href="node_modules/bootstrap-icons/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">





</head>

<div>
    <div class="d-flex">
        <?php include 'sidebar.php'; ?>

        <!-- ========== MAIN CONTENT ========== -->
        <main class="main-content">

            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h3 class="h2">APERTURA DE CAJA</h3>
                <div class="btn-toolbar mb-2 mb-md-0">

                    <form action="">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="location.href = 'operacionAperturaCaja.php';">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-clockwise" viewBox="0 0 16 16">
                                    <path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2z" />
                                    <path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466" />
                                </svg>
                            </button>
                        </div>
                    </form>
                </div>
            </div>




            <!-- APERTURA DE CAJA -->
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-success text-white">
                    <strong>Apertura de Caja</strong>
                </div>
                <div class="card-body">
                    <form action="procesar_apertura.php" method="POST">
                        <div class="mb-3">
                            <label for="monto_inicial" class="form-label">Monto Inicial</label>
                            <input type="number" step="0.01" name="monto_inicial" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-success">Abrir Caja</button>
                    </form>
                </div>
            </div>

            <!-- Estado actual de la caja -->
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <strong>Estado de Caja del Día</strong>
                </div>
                <div class="card-body">
                    <?php if ($cajaHoy): ?>
                        <p><strong>Fecha:</strong> <?= htmlspecialchars($cajaHoy['fecha']) ?></p>
                        <p><strong>Hora de Apertura:</strong> <?= htmlspecialchars($cajaHoy['hora_apertura']) ?></p>
                        <p><strong>Monto de Apertura:</strong> $<?= number_format($cajaHoy['monto_apertura'], 2) ?></p>
                        <p><strong>Estatus:</strong>
                            <span class="badge bg-<?= $cajaHoy['estatus'] == 'abierta' ? 'success' : 'secondary' ?>">
                                <?= strtoupper($cajaHoy['estatus']) ?>
                            </span>
                        </p>
                    <?php else: ?>
                        <p class="text-danger">No se ha abierto la caja hoy.</p>
                    <?php endif; ?>
                </div>
            </div>


        </main>

    </div>


    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <script src="node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="node_modules/chart.js/dist/chart.umd.js"></script>
    <script src="js/sidebars.js"></script>
    <script src="js/seguridad.js"></script>


    <?php if (isset($_GET['success']) || isset($_GET['error'])): ?>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                const params = new URLSearchParams(window.location.search);

                if (params.get('success') === 'caja_abierta') {
                    const amount = params.get('amount') ?? '0.00';
                    Swal.fire({
                        icon: 'success',
                        title: 'Caja abierta correctamente',
                        text: 'Monto de apertura: $' + amount,
                        confirmButtonColor: '#3085d6'
                    }).then(() => {
                        params.delete('success');
                        params.delete('amount');
                        const newUrl = window.location.origin + window.location.pathname;
                        window.history.replaceState({}, document.title, newUrl);
                    });
                }

                if (params.get('error') === 'caja_abierta') {
                    Swal.fire({
                        icon: 'warning',
                        title: '¡Atención!',
                        text: 'Ya hay una caja abierta hoy.',
                        confirmButtonColor: '#3085d6'
                    }).then(() => {
                        params.delete('error');
                        const newUrl = window.location.origin + window.location.pathname;
                        window.history.replaceState({}, document.title, newUrl);
                    });
                }

                if (params.get('error') === 'apertura_fallida') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudo abrir la caja. Inténtalo nuevamente.',
                        confirmButtonColor: '#d33'
                    }).then(() => {
                        params.delete('error');
                        const newUrl = window.location.origin + window.location.pathname;
                        window.history.replaceState({}, document.title, newUrl);
                    });
                }
            });
        </script>
    <?php endif; ?>




    <?php include 'footer.php'; ?>
    </body>

</html>