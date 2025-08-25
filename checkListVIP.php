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


// Número de resultados por página
$results_per_page = 35;

// Obtener el número total de registros
$total_query = "SELECT COUNT(*) AS total FROM dietas WHERE area = 'PRIVADOS' AND DATE(Fecha_Hora_Creacion) = '$fechaFiltro'";
$total_result = mysqli_query($con, $total_query);
$total_row = mysqli_fetch_assoc($total_result);
$total_records = $total_row['total'];

// Calcular el número total de páginas
$total_pages = ceil($total_records / $results_per_page);

// Obtener la página actual desde la URL, si no está presente, por defecto es 1
$page = isset($_GET['page']) ? $_GET['page'] : 1;

// Calcular el inicio del registro para la consulta SQL
$start_from = ($page - 1) * $results_per_page;

// Modificar la consulta para limitar los resultados

$consulta = "SELECT p.* 
FROM dietas p
JOIN pacientes pa ON p.idPaciente = pa.idPaciente
WHERE p.area = 'PRIVADOS' 
  AND DATE(p.Fecha_Hora_Creacion) = '$fechaFiltro'
  AND pa.statusP = 'Activo'
ORDER BY 
  CASE 
    WHEN p.Cama_Paciente LIKE 'A-%' THEN 1
    ELSE 2
  END,
  CAST(
    CASE 
      WHEN p.Cama_Paciente LIKE '%-%' THEN SUBSTRING_INDEX(p.Cama_Paciente, '-', -1)
      ELSE p.Cama_Paciente
    END AS UNSIGNED
  )
LIMIT $start_from, $results_per_page;";


$query = mysqli_query($con, $consulta);


$pacientes = "SELECT * FROM pacientes WHERE area = 'PRIVADOS' AND statusP = 'Activo'";
$ejecutar = mysqli_query($con, $pacientes);



?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> A D M I N I S T R A C I Ó N &nbsp; &nbsp; D I E T A S </title>
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
                <h3 class="h3">CHECK LIST PRIVADOS</h3>
                <div class="btn-toolbar mb-2 mb-md-0">


                    <form action="">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="location.href = 'dietasVIP.php';">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left" viewBox="0 0 16 16">
                                    <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8" />
                                </svg>
                            </button>
                        </div>
                    </form>


                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="capturarTabla()">
                            Validacion
                        </button>
                    </div>



                    <form action="">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="location.href = 'checkListVIP.php';">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-clockwise" viewBox="0 0 16 16">
                                    <path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2z" />
                                    <path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466" />
                                </svg>
                            </button>
                        </div>
                    </form>

                </div>
            </div>

            <div class="pagination">
                <?php
                for ($i = 1; $i <= $total_pages; $i++) {
                    $active = ($i == $page) ? "active" : "";
                    echo "<li class='page-item $active'><a class='page-link' href='dietasVIP.php?page=" . $i . "&fechaFiltro=" . $fechaFiltro . "'>" . $i . "</a></li>";
                }
                ?>
            </div>
            <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                <?php
                function checkboxMarcado($idPaciente, $comida)
                {
                    include "conexion.php";

                    $sql = "SELECT seleccionado FROM EstadoCheckbox 
            WHERE idPaciente = ? AND comida = ? AND DATE(fecha) = CURDATE()";
                    $stmt = $con->prepare($sql);
                    $stmt->bind_param("is", $idPaciente, $comida);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($row = $result->fetch_assoc()) {
                        return $row["seleccionado"] == 1;
                    }

                    return false;
                }


                ?>
                <table class="table table-striped table-sm table-bordered">
                    <thead>
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Nombre del Paciente</th>
                            <th scope="col">Cama</th>
                            <th scope="col">Prescripcion Nutricional</th>
                            <th scope="col">Desayuno</th>
                            <th scope="col"></th>
                            <th scope="col">Colación Matutina</th>
                            <th scope="col"></th>
                            <th scope="col">Comida</th>
                            <th scope="col"></th>
                            <th scope="col">Colación Vespertina</th>
                            <th scope="col"></th>
                            <th scope="col">Cena</th>
                            <th scope="col"></th>
                            <th scope="col">Colación Nocturna</th>
                            <th scope="col"></th>
                            <th scope="col">Observaciones</th>
                        </tr>
                    </thead>
                    <?php
                    $i = 1;
                    while ($dataRow = mysqli_fetch_array($query)) {
                        // Obtener la fecha actual
                        $currentDate = date('Y-m-d');
                        // Obtener la fecha de creación del dato
                        $creationDate = date('Y-m-d', strtotime($dataRow["Fecha_Hora_Creacion"]));




                    ?>

                        <tbody>
                            <tr>
                                <td class="text-center"><?php echo $dataRow["idPaciente"]; ?></td>
                                <td class="text-center"><?php echo $dataRow["Nombre_Paciente"]; ?></td>
                                <td class="text-center"><?php echo $dataRow["Cama_Paciente"]; ?></td>
                                <td class="text-center"><?php echo $dataRow["Prescripcion"]; ?></td>
                                <td class="text-center"><?php echo $dataRow["Desayuno"]; ?></td>
                                <td class="text-center" scope="col">
                                    <input type="checkbox"
                                        class="form-check-input custom-checkbox"
                                        data-id="<?php echo $dataRow['idPaciente']; ?>"
                                        data-comida="Desayuno"
                                        <?php if (checkboxMarcado($dataRow['idPaciente'], 'Desayuno')) echo 'checked'; ?>>
                                </td>

                                <td class="text-center"><?php echo $dataRow["Col_Matutina"]; ?></td>
                                <td class="text-center" scope="col">
                                    <input type="checkbox"
                                        class="form-check-input custom-checkbox"
                                        data-id="<?php echo $dataRow['idPaciente']; ?>"
                                        data-comida="Col_Matutina"
                                        <?php if (checkboxMarcado($dataRow['idPaciente'], 'Col_Matutina')) echo 'checked'; ?>>
                                </td>
                                <td class="text-center"><?php echo $dataRow["Comida"]; ?></td>
                                <td class="text-center" scope="col">
                                    <input type="checkbox"
                                        class="form-check-input custom-checkbox"
                                        data-id="<?php echo $dataRow['idPaciente']; ?>"
                                        data-comida="Comida"
                                        <?php if (checkboxMarcado($dataRow['idPaciente'], 'Comida')) echo 'checked'; ?>>
                                </td>
                                <td class="text-center"><?php echo $dataRow["Col_Vespertina"]; ?></td>
                                <td class="text-center" scope="col">
                                    <input type="checkbox"
                                        class="form-check-input custom-checkbox"
                                        data-id="<?php echo $dataRow['idPaciente']; ?>"
                                        data-comida="Col_Vespertina"
                                        <?php if (checkboxMarcado($dataRow['idPaciente'], 'Col_Vespertina')) echo 'checked'; ?>>
                                </td>
                                <td class="text-center"><?php echo $dataRow["Cena"]; ?></td>
                                <td class="text-center" scope="col">
                                    <input type="checkbox"
                                        class="form-check-input custom-checkbox"
                                        data-id="<?php echo $dataRow['idPaciente']; ?>"
                                        data-comida="Cena"
                                        <?php if (checkboxMarcado($dataRow['idPaciente'], 'Cena')) echo 'checked'; ?>>
                                </td>
                                <td class="text-center"><?php echo $dataRow["Col_Nocturna"]; ?></td>
                                <td class="text-center" scope="col">
                                    <input type="checkbox"
                                        class="form-check-input custom-checkbox"
                                        data-id="<?php echo $dataRow['idPaciente']; ?>"
                                        data-comida="Col_Nocturna"
                                        <?php if (checkboxMarcado($dataRow['idPaciente'], 'Col_Nocturna')) echo 'checked'; ?>>
                                </td>
                                <td class="text-center"><?php echo $dataRow["Observaciones"]; ?></td>

                            </tr>
                        </tbody>
                    <?php } ?>
                </table>
            </div>
        </main>



        </main>
    </div>

    <script src="node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="node_modules/chart.js/dist/chart.umd.js"></script>
    <script src="js/sidebars.js"></script>
    <script src="js/dietasVIP.js"></script>
    <script type="text/javascript" src="node_modules/jquery/dist/jquery.min.js"></script>
    <script src="js/seguridad.js"></script>

    <?php include 'footer.php'; ?>

</body>

</html>