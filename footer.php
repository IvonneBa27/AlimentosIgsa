<?php
include("conexion.php");
date_default_timezone_set("America/Mexico_City");
$fechaHoraActual = date("Y-m-d\TH:i:s");


if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['resultado'])) {
    header('Location: index.html');
    exit;
} else {
    $sesi = $_SESSION['resultado'];
}

// Asegúrate de que las claves coincidan con las columnas en tu BD
$sesionUsuario = $sesi['usuario']; // Reemplaza con el nombre exacto de la columna
$sesionNombre = $sesi['nombre'];   // Reemplaza con el nombre exacto de la columna
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>I G S A - M E D I C A L </title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            height: 60px;
            line-height: 60px;
            background-color: #343a40;
        }

        .footer p {
            color: #fff;
            margin: 0;
            text-align: center;
        }
    </style>
</head>



<body>



    <!-- Sticky Footer -->
    <footer class="bg-light text-center py-3 mt-auto border-top">
        <p class="mb-0">© <?= date('Y') ?> IGSA Medical. Todos los derechos reservados.</p>
    </footer>


    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>