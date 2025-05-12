<?php
include("conexion.php");
date_default_timezone_set("America/Mexico_City");
$fechaHoraActual = date("Y-m-d H:i:s");
session_start();
if (!isset($_SESSION['resultado'])) {
    header('Location: index.html');
    exit;
} else {
    $sesi = $_SESSION['resultado'];
}
$sesionUsuario = $sesi['usuario']; // Reemplaza con el nombre exacto de la columna
$sesionNombre = $sesi['nombre'];   // Reemplaza con el nombre exacto de la columna


// Consulta SQL para obtener los datos de los pacientes
$sql = "SELECT * FROM pacientes";
$query = mysqli_query($con, $sql);


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>D I E T A S </title>
    <link rel="stylesheet" href="node_modules/bootstrap/dist/css/bootstrap.min.css">
    <script src="js/color-modes.js"></script>
    <link rel="stylesheet" href="node_modules/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="node_modules/sweetalert2/dist/sweetalert2.min.css">
    <script src="node_modules/sweetalert2/dist/sweetalert2.min.js"></script>
    <script src="js/color-modes.js"></script>
    <link rel="stylesheet" href="node_modules/bootstrap-icons/font/bootstrap-icons.min.css">

</head>

<body>
    <div class="d-flex">
        <?php include 'sidebar.php'; ?>

          <!-- ========== MAIN CONTENT ========== -->
    <main class="main-content">
      <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h3 class="h3">PACIENTES</h3>
        <div class="btn-toolbar mb-2 mb-md-0">
          <form method="post" action="pdfPacientes.php" target="_blank">
            <div class="btn-group me-2">
              <button type="submit" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-filetype-pdf"></i> Exportar
              </button>
            </div>
          </form>
          <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="location.href = 'pacientes.php';">
              <i class="bi bi-arrow-clockwise"></i>
            </button>
          </div>
        </div>
      </div>

      <!-- Tabla de pacientes -->
      <div class="table-responsive">
        <table id="miTabla" class="table table-secondary table-sm table-striped table-bordered">
          <thead>
            <tr>
              <th class="text-center">Nombre del Paciente</th>
              <th class="text-center">Fecha de Nacimiento</th>
              <th class="text-center">ID</th>
              <th class="text-center">Cama</th>
              <th class="text-center">Edad Años</th>
              <th class="text-center">Edad Meses</th>
              <th class="text-center">Edad Días</th>
              <th class="text-center">Diagnóstico Médico y Nutricional</th>
              <th class="text-center">Prescripción Nutricional</th>
              <th class="text-center">Observaciones</th>
              <th class="text-center">Control de Tamizaje</th>
              <th class="text-center">Aislados</th>
              <th class="text-center">Área</th>
              <th class="text-center">Status</th>
              <th class="text-center">Creado por</th>
              <th class="text-center">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($dataRow = mysqli_fetch_array($query)) { ?>
              <tr>
                <td class="text-center"><?= $dataRow["nombre"] ?></td>
                <td class="text-center"><?= $dataRow["fechaNacimiento"] ?></td>
                <td class="text-center"><?= $dataRow["idPaciente"] ?></td>
                <td class="text-center"><?= $dataRow["cama"] ?></td>
                <td class="text-center"><?= $dataRow["edad"] ?></td>
                <td class="text-center"><?= $dataRow["edadMeses"] ?></td>
                <td class="text-center"><?= $dataRow["edadDias"] ?></td>
                <td class="text-center"><?= $dataRow["diagnosticoMed"] ?></td>
                <td class="text-center"><?= $dataRow["prescripcionNutri"] ?></td>
                <td class="text-center">
                  <textarea class="form-control-plaintext table-textarea" disabled><?= $dataRow["observaciones"] ?></textarea>
                </td>
                <td class="text-center">
                  <textarea class="form-control-plaintext table-textarea" disabled><?= $dataRow["controlTamizaje"] ?></textarea>
                </td>
                <td class="text-center"><?= $dataRow["vip"] ?></td>
                <td class="text-center"><?= $dataRow["area"] ?></td>
                <td class="text-center"><?= $dataRow["statusP"] ?></td>
                <td class="text-center"><?= $dataRow["creadoPor"] ?></td>
                <td class="text-center">
                  <a href="editPaciente.php?id=<?= $dataRow["id"] ?>" class="btn btn-warning btn-sm text-dark text-decoration-none">Editar</a>
                </td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
    </main>




    </div>
    <script src="node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="node_modules/chart.js/dist/chart.umd.js"></script>
    <script type="text/javascript" src="node_modules/jquery/dist/jquery.min.js"></script>
    <script src="js/sidebars.js"></script>

    <?php include 'footer.php'; ?>
</body>


</html>