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
$results_per_page = 15;

// Obtener el número total de registros
$total_query = "SELECT COUNT(*) AS total FROM dietas WHERE area = 'UCIA' AND DATE(Fecha_Hora_Creacion) = '$fechaFiltro'";
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
$consulta = "SELECT * FROM dietas WHERE area = 'UCIA' AND DATE (Fecha_Hora_Creacion) = '$fechaFiltro' ORDER BY ID DESC LIMIT $start_from, $results_per_page";
$query = mysqli_query($con, $consulta);


$pacientes = "SELECT * FROM pacientes WHERE area = 'UCIA'";
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
                <h3 class="h3">DIETAS UCIA</h3>
                <div class="btn-toolbar mb-2 mb-md-0">

                    <form method="GET" action="dietasUCIA.php">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary">Fecha</button>
                            <input type="date" class="btn btn-sm btn-outline-secondary" name="fechaFiltro" id="fechaFiltro" value="<?php echo isset($_GET['fechaFiltro']) ? $_GET['fechaFiltro'] : ''; ?>">
                            <button type="submit" class="btn btn-sm btn-outline-secondary">Filtrar</button>
                        </div>
                    </form>

                    <form method="GET" action="pdfUCIAFiltro.php" target="_blank">
                        <div class="btn-group me-2">
                            <input type="hidden" name="fechaFiltro" value="<?php echo isset($_GET['fechaFiltro']) ? $_GET['fechaFiltro'] : $fechaHoraActual; ?>">
                            <button type="submit" class="btn btn-sm btn-outline-secondary"><i class="bi bi-filetype-pdf"></i> Exportar</button>
                        </div>
                    </form>


                    <form method="" action="" target="_blank">
                        <div class="btn-group me-2">
                            <button id="openImpresora" type="button" onclick="printLabel()" class="btn btn-sm btn-outline-secondary" data-toggle="modal" data-target="#printModal"><i class="bi bi-printer"></i> Imprimir Etiquetas</button>
                        </div>
                    </form>

                    <form action="">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="location.href = 'dietasUCIA.php';">
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
                    echo "<li class='page-item $active'><a class='page-link' href='dietasUCIA.php?page=" . $i . "&fechaFiltro=" . $fechaFiltro . "'>" . $i . "</a></li>";
                }
                ?>
            </div>
            <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                <table class="table table-striped table-sm table-bordered">
                    <thead>
                        <tr>
                            <th scope="col">Fecha de Solicitud</th>
                            <th scope="col">Área</th>
                            <th scope="col">ID</th>
                            <th scope="col">Nombre del Paciente</th>
                            <th scope="col">Fecha de Nacimiento</th>
                            <th scope="col">Cama</th>
                            <th scope="col">Edad</th>
                            <th scope="col">Diagnostico Medico y Nutricional</th>
                            <th scope="col">Prescripcion Nutricional</th>
                            <th scope="col">Desayuno</th>
                            <th scope="col">Colación Matutina</th>
                            <th scope="col">Comida</th>
                            <th scope="col">Colación Vespertina</th>
                            <th scope="col">Cena</th>
                            <th scope="col">Colación Nocturna</th>
                            <th scope="col">Observaciones</th>
                            <th scope="col">Control de Tamizaje</th>
                            <th scope="col">Solicitado por</th>
                        </tr>
                    </thead>
                    <?php
                    $i = 1;
                    while ($dataRow = mysqli_fetch_array($query)) {
                        // Obtener la fecha actual
                        $currentDate = date('Y-m-d');
                        // Obtener la fecha de creación del dato
                        $creationDate = date('Y-m-d', strtotime($dataRow["Fecha_Hora_Creacion"])); ?>
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
                                <?php if ($currentDate == $creationDate) { ?>
                                    <td scope="col">
                                        <svg id="openModal" data-id="<?php echo $dataRow["ID"]; ?>" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-circle-fill" viewBox="0 0 16 16">
                                            <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M8.5 4.5a.5.5 0 0 0-1 0v3h-3a.5.5 0 0 0 0 1h3v3a.5.5 0 0 0 1 0v-3h3a.5.5 0 0 0 0-1h-3z" />
                                        </svg>
                                    </td>
                                <?php } ?>

                                <?php
                                //$nota = "SELECT * FROM notas WHERE idDieta = '" . $dataRow['ID'] . "'";
                                $nota = "SELECT * FROM notas WHERE idDieta = '" . $dataRow['ID'] . "' AND DATE(Fecha_Creacion) = '$fechaHoraActual'";
                                $queryNota = mysqli_query($con, $nota);
                                $notaExists = mysqli_num_rows($queryNota) > 0;
                                ?>
                                <td>
                                    <?php if ($notaExists) { ?>
                                        <button type="button" class="btn btn-primary" data-id="<?php echo $dataRow["ID"]; ?>" data-target="#myModal" data-toggle="modal">Ver Notas</button>
                                    <?php } //elseif ($fechaFiltro == $creationDate) { 
                                    ?>
                                    <!-- <button type="button" class="btn btn-primary" data-id="<?php //echo $dataRow["ID"]; 
                                                                                                ?>" data-target="#myModal" data-toggle="modal">Ver Notas</button>-->
                                    <?php //} 
                                    ?>
                                </td>
                            </tr>
                        </tbody>
                    <?php } ?>
                </table>
            </div>
        </main>
        <div class="modal" tabindex="-1" id="Modalnota">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Nota Adicional</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="CancelarNotas()"></button>
                    </div>
                    <div class="col-sm-6" hidden>
                        <label for="firstName" class="form-label">Fecha y Hora</label>
                        <input type="datetime" id="fechaHoraActual" name="fechaHoraActual" readonly required>
                    </div>
                    <div class="col-sm-6" hidden>
                        <label for="lastName" class="form-label">Usuario</label>
                        <input type="text" class="form-control" id="usuNotas" name="usuNotas" value="<?php echo $sesionNombre; ?>">
                    </div>
                    <div class="col-sm-6" hidden>
                        <input type="hidden" id="idPacienteModal" name="idPaciente">
                    </div>
                    <div class="modal-body">
                        <textarea class="form-control-plaintext table-textarea" name="addnota" id="addnota"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="CancelarNotas()">Cerrar</button>
                        <button type="button" class="btn btn-primary" onclick="guardarNota()">Guardar Nota</button>
                    </div>
                </div>
            </div>
        </div>

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





        <div class="modal" tabindex="-1" id="ModalImpresora">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-center">Generar Etiquetas</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="CancelarImpresion()"></button>
                    </div>
                    <form method="POST" action="datosEtiquetaUCIA.php" id="formImpresora">
                        <div class="col-sm-6" hidden>
                            <label for="firstName" class="form-label">Fecha y Hora</label>
                            <input type="datetime" id="fechaHoraActual" name="fechaHoraActual" readonly required>
                        </div>

                        <div class="col-sm-6">
                            <label for="" class="form-label">Tiempo de Comida</label>
                            <select name="selectTipo" id="selectTipo" class="form-select">
                                <option value="Desayuno">Desayuno</option>
                                <option value="Col_Matutina">Colación Matutina</option>
                                <option value="Comida">Comida</option>
                                <option value="Col_Vespertina">Colación Vespertina</option>
                                <option value="Cena">Cena</option>
                                <option value="Col_Nocturna">Colación Nocturna</option>
                            </select>
                        </div>

                        <div class="col-sm-6">
                            <label for="" class="form-label">Paciente</label>
                            <select name="selectPacientes" id="selectPacientes" class="form-select">
                                <option value="Todos">Todos</option>
                                <?php
                                while ($row = mysqli_fetch_assoc($ejecutar)) {
                                    echo "<option value='" . $row['nombre'] . "'>" . $row['nombre'] . "</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Imprimir</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>





        </main>
    </div>
    <script src="node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>

    <script src="node_modules/chart.js/dist/chart.umd.js"></script>
    <script src="js/sidebars.js"></script>
    <script src="js/dashboardDietasUCIA.js"></script>
    <script src="js/dietasUCIA.js"></script>
    <script type="text/javascript" src="node_modules/jquery/dist/jquery.min.js"></script>
    <script src="js/nota.js"></script>

    <?php include 'footer.php'; ?>
</body>

</html>
<script src="js/impresionUCIA.js"></script>