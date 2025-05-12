<?php
include("conexion.php");
date_default_timezone_set("America/Mexico_City");
$fechaHoraActual = date("Y-m-d");
session_start();
if (!isset($_SESSION['resultado'])) {
  header('Location: index.html');
  exit;
} else {
  $sesi = $_SESSION['resultado'];
}
$sesionUsuario = $sesi['usuario']; // Reemplaza con el nombre exacto de la columna
$sesionNombre = $sesi['nombre'];   // Reemplaza con el nombre exacto de la columna


// Obtener la fecha seleccionada o la fecha actual si no se selecciona ninguna
$fechaFiltro = isset($_GET['fechaFiltro']) && !empty($_GET['fechaFiltro']) ? $_GET['fechaFiltro'] : $fechaHoraActual;

// Numero de resultados por página
$results_per_page = 15;

// Obtener el número total de registros
/*$total_query = "SELECT COUNT(*) AS total FROM dietas WHERE (area = 'UTIP' OR area = 'Privados') AND DATE(Fecha_Hora_Creacion) = '$fechaFiltro'";*/
$total_query = "SELECT COUNT(*) AS total FROM dietas WHERE area = 'UTIP' AND DATE(Fecha_Hora_Creacion) = '$fechaFiltro'";
$total_result = mysqli_query($con, $total_query);
$total_row = mysqli_fetch_assoc($total_result);
$total_records = $total_row['total'];

// Calcular el numero total de páginas
$total_pages = ceil($total_records / $results_per_page);

// Obtener la página actual desde la URL, si no está presente, por defecto es 1
$page = isset($_GET['page']) ? $_GET['page'] : 1;

// Calcular el inicio del registro para la consulta 
$start_from = ($page - 1) * $results_per_page;

/*$consulta = "SELECT * FROM dietas WHERE (area = 'UTIP' OR area = 'Privados') AND DATE (Fecha_Hora_Creacion) = '$fechaFiltro' ORDER BY ID DESC LIMIT $start_from, $results_per_page";*/
$consulta = "SELECT * FROM dietas WHERE area = 'UTIP' AND DATE (Fecha_Hora_Creacion) = '$fechaFiltro' ORDER BY ID DESC LIMIT $start_from, $results_per_page";
$query = mysqli_query($con, $consulta);
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
        <h3 class="h3">UTIP</h3>
        <div class="btn-toolbar mb-2 mb-md-0">

          <form method="GET" action="ordenesUTIP.php">
            <div class="btn-group me-2">
              <button type="button" class="btn btn-sm btn-outline-secondary">Fecha</button>
              <input type="date" class="btn btn-sm btn-outline-secondary" name="fechaFiltro" id="fechaFiltro"
                value="<?php echo isset($_GET['fechaFiltro']) ? $_GET['fechaFiltro'] : ''; ?>">
              <button type="submit" class="btn btn-sm btn-outline-secondary">Filtrar</button>
            </div>
          </form>

          <form method="GET" action="pdfUTIPFiltro.php" target="_blank">
            <div class="btn-group me-2">
              <input type="hidden" name="fechaFiltro" value="<?php echo isset($_GET['fechaFiltro']) ? $_GET['fechaFiltro'] : $fechaHoraActual; ?>">
              <button type="submit" class="btn btn-sm btn-outline-secondary"><i class="bi bi-filetype-pdf"></i> Exportar</button>
            </div>
          </form>

          <form action="">
            <div class="btn-group me-2">
              <button type="button" class="btn btn-sm btn-outline-secondary" onclick="location.href = 'ordenesUTIP.php';">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-clockwise" viewBox="0 0 16 16">
                  <path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2z" />
                  <path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466" />
                </svg>
              </button>
            </div>
          </form>
        </div>
      </div>

      <canvas class="my-4 w-100" id="myChart" width="900" height="200"></canvas>

      <h4>Dietas Solicitadas</h4>
      <div class="pagination">
        <?php
        for ($i = 1; $i <= $total_pages; $i++) {
          $active = ($i == $page) ? "active" : "";
          echo "<li class='page-item $active'><a class='page-link' href='ordenesUTIP.php?page=" . $i . "&fechaFiltro=" . $fechaFiltro . "'>" . $i . "</a></li>";
        }
        ?>

      </div>
      <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
        <table class="table table-striped table-sm table-bordered">
          <thead>
            <tr>
              <th class="text-center" scope="col">Fecha de Solicitud</th>
              <th class="text-center" scope="col">Área</th>
              <th class="text-center" scope="col">ID</th>
              <th class="text-center" scope="col">Nombre del Paciente</th>
              <th class="text-center" scope="col">Fecha de Nacimiento</th>
              <th class="text-center" scope="col">Cama</th>
              <th class="text-center" scope="col">Edad</th>
              <th class="text-center" scope="col">Diagnostico Medico y Nutricional</th>
              <th class="text-center" scope="col">Prescripcion Nutricional</th>
              <th class="text-center" scope="col">Desayuno</th>
              <th class="text-center" scope="col">Colación Matutina</th>
              <th class="text-center" scope="col">Comida</th>
              <th class="text-center" scope="col">Colación Vespertina</th>
              <th class="text-center" scope="col">Cena</th>
              <th class="text-center" scope="col">Colación Nocturna</th>
              <th class="text-center" scope="col">Observaciones</th>
              <th class="text-center" scope="col">Control de Tamizaje</th>
              <th class="text-center" scope="col">Solicitado por</th>
              <!--<th class="text-center" scope="col">Acciones</th>-->
            </tr>
          </thead>
          <?php
          $i = 1;
          while ($dataRow = mysqli_fetch_array($query)) { ?>
            <tbody>
              <tr>
                <td class="text-center"><?php echo $dataRow["Fecha_Hora_Creacion"]; ?></td>
                <td class="text-center"><?php echo $dataRow["area"]; ?></td>
                <td class="text-center"><?php echo $dataRow["idPaciente"]; ?></td>
                <td class="text-center"><?php echo $dataRow["Nombre_Paciente"]; ?></td>
                <td class="text-center"><?php echo $dataRow["Fecha_Nacimiento_Paciente"]; ?></td>
                <td class="text-center"><?php echo $dataRow["Cama_Paciente"]; ?></td>
                <td class="text-center"><?php echo $dataRow["Edad"]; ?></td>
                <td class="text-center"><?php echo $dataRow["Diag_Med_Nutri"]; ?></td>
                <td class="text-center"><?php echo $dataRow["Prescripcion"]; ?></td>
                <td class="text-center"><?php echo $dataRow["Desayuno"]; ?></td>
                <td class="text-center"><?php echo $dataRow["Col_Matutina"]; ?></td>
                <td class="text-center"><?php echo $dataRow["Comida"]; ?></td>
                <td class="text-center"><?php echo $dataRow["Col_Vespertina"]; ?></td>
                <td class="text-center"><?php echo $dataRow["Cena"]; ?></td>
                <td class="text-center"><?php echo $dataRow["Col_Nocturna"]; ?></td>
                <td class="text-center"><?php echo $dataRow["Observaciones"]; ?></td>
                <td class="text-center"><?php echo $dataRow["Control_Tamizaje"]; ?></td>
                <td class="text-center"><?php echo $dataRow["Creado_por"]; ?></td>
                <?php
                //$nota = "SELECT * FROM notas WHERE idDieta = '" . $dataRow['ID'] . "'";
                $nota = "SELECT * FROM notas WHERE idDieta = '" . $dataRow['ID'] . "' AND DATE(Fecha_Creacion) = '$fechaHoraActual'";
                $queryNota = mysqli_query($con, $nota);
                $notaExists = mysqli_num_rows($queryNota) > 0;
                ?>
                <td>
                  <?php if ($notaExists) { ?>
                    <button type="button" class="btn btn-primary" data-id="<?php echo $dataRow["ID"]; ?>" data-target="#myModal" data-toggle="modal">Ver Notas</button>
                  <?php } ?>
                </td>
              </tr>
            </tbody>
          <?php } ?>
        </table>
      </div>
    </main>

    <div class="modal" id="myModal">
      <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content">

          <div class="modal-body">
            <form class="row g-3 needs-validation" method="POST" action="" id="myFormDetalles">
              <div class="modal-header">
                <h4 class="fs-2 col-md-11">Notas</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="CerrarModal()">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>

              <div class="form-group col-md-12" id="modal-content1">
              </div>

            </form>
            <div class="modal-footer">
              <button type="button" class="btn btn-danger" data-dismiss="modal" onclick="CerrarModal()">Cerrar</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
  </div>
  <script src="node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="node_modules/chart.js/dist/chart.umd.js"></script>
  <script type="text/javascript" src="node_modules/jquery/dist/jquery.min.js"></script>
  <script src="js/sidebars.js"></script>
  <script src="js/dashboardUTIP.js"></script>
  <script src="js/nota.js"></script>
  <script src="js/seguridad.js"></script>
  <?php include 'footer.php'; ?>

</body>

</html>
