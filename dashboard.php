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
  <link rel="stylesheet" href="node_modules/bootstrap/dist/css/bootstrap.min.css">
  <script src="js/color-modes.js"></script>
  <link rel="stylesheet" href="node_modules/bootstrap-icons/font/bootstrap-icons.min.css">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>

<body>
  <div class="d-flex">
    <?php include 'sidebar.php'; ?>
    <style>
      .carrusel-img {
        height: 400px;
        object-fit: cover;
      }
    </style>

    <!-- ========== MAIN CONTENT ========== -->
    <main class="flex-grow-1 p-4">
      <div class="text-center mb-4">
        <h1 class="display-4 fw-bold text-success">Sistema NutrIGSA</h1>
        <p class="lead">Gestión integral de dietas y alimentación</p>
      </div>

      <div id="nutrigestCarousel" class="carousel slide shadow rounded-4" data-bs-ride="carousel">
        <div class="carousel-inner">
          <div class="carousel-item active">
            <img src="images/carrusel/diet1.jpg" class="d-block w-100 rounded" alt="Desayuno saludable">
          </div>
          <div class="carousel-item">
            <img src="images/carrusel/diet2.jpg" class="d-block w-100 rounded" alt="Almuerzo balanceado">
          </div>
          <div class="carousel-item">
            <img src="images/carrusel/diet3.jpg" class="d-block w-100 rounded" alt="Cena nutritiva">
          </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#nutrigestCarousel" data-bs-slide="prev">
          <span class="carousel-control-prev-icon" aria-hidden="true"></span>
          <span class="visually-hidden">Anterior</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#nutrigestCarousel" data-bs-slide="next">
          <span class="carousel-control-next-icon" aria-hidden="true"></span>
          <span class="visually-hidden">Siguiente</span>
        </button>
      </div>
    </main>
  </div>

  <?php include 'footer.php'; ?>
</body>


</html>