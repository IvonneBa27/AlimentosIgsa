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

$id = $_GET["id"];

// Consulta SQL para obtener los datos de los pacientes
$sql = "SELECT * FROM pacientes WHERE id = '$id'";
$query = mysqli_query($con, $sql);


$vipOptions = ["APLICA", "N/A"];
$statusOptions = ["Activo", "Inactivo"];
$areaOptions = ["CIRUGÍA GENERAL", "MEDICINA INTERNA", "GINECOLOGÍA", "PEDIATRÍA", "PRIVADOS", "QUEMADOS", "UTIP", "UCIA"];

?>

<!DOCTYPE html>
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



    <!-- Custom styles for this template -->
    <link href="css/sidebars.css" rel="stylesheet">
    <!-- Custom styles for this template -->
    <link rel="stylesheet" href="node_modules/bootstrap-icons/font/bootstrap-icons.min.css">
    <!-- Custom styles for this template -->
    <link href="css/dashboard.css" rel="stylesheet">
    <script src="js/tablaCG.js"></script>
</head>

<body>
    <div class="d-flex">
        <?php include 'sidebar.php'; ?>

        <!-- ========== MAIN CONTENT ========== -->
        <main class="main-content">

            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h3 class="h3">PACIENTES</h3>
                <div class="btn-toolbar mb-2 mb-md-0">

                    <div class="btn-group me-2">
                        <!--<button type="button" class="btn btn-sm btn-outline-secondary" onclick="openModal();">
                            <i class="bi bi-person-plus"></i> Agregar Paciente
                        </button>-->
                    </div>

                    <form method="post" action="pdfCG.php" target="_blank">
                        <div class="btn-group me-2">
                            <!--<input type="hidden" name="fechaFiltro" value="<?php //echo isset($_GET['fechaFiltro']) ? $_GET['fechaFiltro'] : $fechaHoraActual; 
                                                                                ?>">-->
                            <!--<button type="submit" class="btn btn-sm btn-outline-secondary"><i class="bi bi-filetype-pdf"></i> Exportar</button>-->
                        </div>
                    </form>

                    <form action="">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="location.href = 'pacientes.php';">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-clockwise" viewBox="0 0 16 16">
                                    <path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2z" />
                                    <path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466" />
                                </svg>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="row g-12">
                <div class="mx-auto col-md-12 col-lg-12 ">
                    <h1 class="mb-3"></h1>
                    <div class="row g-3">
                        <form action="actPaciente.php" method="post">
                            <div class="table-responsive" style="max-height: 830px; overflow-y: auto;">
                                <table id="miTabla" class="table table-secondary table-sm table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th class="text-center" scope="col" hidden>ID Paciente</th>
                                            <th class="text-center" scope="col">Nombre del Paciente</th>
                                            <th class="text-center" scope="col">Fecha de Nacimiento</th>
                                            <th class="text-center" scope="col">ID</th>
                                            <th class="text-center" scope="col" hidden>ID2</th>
                                            <th class="text-center" scope="col">Cama</th>
                                            <th class="text-center" scope="col" hidden>Cama2</th>
                                            <th class="text-center" scope="col">Edad</th>
                                            <th class="text-center" scope="col">Diagnostico Médico y Nutricional</th>
                                            <th class="text-center" scope="col">Prescripción Nutricional</th>
                                            <th class="text-center" scope="col">Observaciones</th>
                                            <th class="text-center" scope="col">Control de Tamizaje</th>
                                            <th class="text-center" scope="col">Privados</th>
                                            <th class="text-center" scope="col">Area</th>
                                            <th class="text-center" scope="col">Status</th>
                                            <th class="text-center" scope="col">Creado por</th>
                                            <th class="text-center" scope="col">Acciones</th>
                                            <th class="text-center" scope="col" hidden>Usuario</th>
                                        </tr>|
                                    </thead>
                                    <tbody>
                                        <?php while ($dataRow = mysqli_fetch_array($query)) { ?>
                                            <tr>
                                                <td class="text-center" data-campo="id" hidden><input type="" id="id" name="id" maxlength="10" class="form-control" value="<?php echo $dataRow["id"]; ?>"></td>
                                                <td class="text-center" data-campo="nombre"><input name="nombre" class="form-control-plaintext" type="text" value="<?php echo $dataRow["nombre"]; ?>"></td>
                                                <td class="text-center" data-campo="fechaNacimiento"><input name="fechaNacimiento" class="form-control-plaintext" type="text" value="<?php echo $dataRow["fechaNacimiento"]; ?>"></td>
                                                <td class="text-center" data-campo="idPaciente"><input name="idPaciente" class="form-control-plaintext" type="text" value="<?php echo $dataRow["idPaciente"]; ?>"></td>
                                                <td class="text-center" data-campo="idPaciente2" hidden><input name="idPaciente2" class="form-control-plaintext" type="text" value="<?php echo $dataRow["idPaciente"]; ?>"></td>

                                                <td class="text-center" data-campo="cama">
                                                    <select name="cama" id="camaSelect" class="form-control-plaintext">
                                                        <option value="<?php echo $dataRow["cama"]; ?>"><?php echo $dataRow["cama"]; ?></option>
                                                    </select>
                                                </td>

                                                <td class="text-center" data-campo="cama2" hidden><input name="cama2" class="form-control-plaintext" type="text" value="<?php echo $dataRow["cama"]; ?>"></td>
                                                <td class="text-center" data-campo="edad"><input name="edad" class="form-control-plaintext" type="text" value="<?php echo $dataRow["edad"]; ?>" disabled></td>
                                                <td class="text-center" data-campo="diagnosticoMed"><textarea class="form-control-plaintext table-textarea text-center" name="diagnosticoMed" id=""><?php echo $dataRow["diagnosticoMed"]; ?></textarea></td>
                                                <td class="text-center" data-campo="prescripcionNutri"><textarea class="form-control-plaintext table-textarea text-center" name="prescripcionNutri" id=""><?php echo $dataRow["prescripcionNutri"]; ?></textarea></td>
                                                <td class="text-center"><textarea class="form-control-plaintext table-textarea text-center" data-campo="observaciones" name="observaciones"><?php echo $dataRow["observaciones"]; ?></textarea></td>
                                                <td class="text-center"><textarea class="form-control-plaintext table-textarea text-center " data-campo="controlTamizaje" name="controlTamizaje"><?php echo $dataRow["controlTamizaje"]; ?></textarea></td>


                                                <td class="text-center" data-campo="vip">
                                                    <?php $currentVip = $dataRow["vip"]; ?>
                                                    <select class="form-control-plaintext" name="vip" id="">
                                                        <option value="<?php echo $currentVip; ?>"><?php echo $currentVip; ?></option>
                                                        <?php foreach ($vipOptions as $option): ?>
                                                            <?php if ($option != $currentVip): ?>
                                                                <option value="<?php echo $option; ?>"><?php echo $option; ?></option>
                                                            <?php endif; ?>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </td>


                                                <td class="text-center" data-campo="usuario" hidden> <?php echo $sesionNombre; ?></td>


                                                <td class="text-center" data-campo="area">
                                                    <?php $currentArea = $dataRow["area"]; ?>
                                                    <select class="form-control-plaintext" name="area" id="areaSelect">
                                                        <option value="">Seleccione un área</option>
                                                        <?php foreach ($areaOptions as $option): ?>
                                                            <option value="<?php echo $option; ?>" <?php echo ($option == $currentArea) ? 'selected' : ''; ?>>
                                                                <?php echo $option; ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </td>

                                                <td class="text-center" data-campo="statusP">
                                                    <?php $currentStatus = $dataRow["statusP"]; ?>
                                                    <select class="form-control-plaintext" name="statusP" id="">
                                                        <option value="<?php echo $currentStatus; ?>"><?php echo $currentStatus; ?></option>
                                                        <?php foreach ($statusOptions as $option): ?>
                                                            <?php if ($option != $currentStatus): ?>
                                                                <option value="<?php echo $option; ?>"><?php echo $option; ?></option>
                                                            <?php endif; ?>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </td>

                                                <td class="text-center" data-campo="creadoPor"><?php echo $dataRow["creadoPor"] ?></td>

                                                <td class="text-center"> <button type="submit" class="btn btn-primary" name="editar"><a style="text-decoration: none; color: white;" id="">Actualizar</a></button>
                                                    <button class="btn btn-danger"><a href="pacientes.php" style="text-decoration: none; color: white;">Cancelar</a></button>
                                                </td>
                                                <td class="text-center" data-campo="fechaHoraActual" hidden><input type="datetime" id="fechaHoraActual" name="fechaHoraActual" readonly required></td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>

                            </div>
                        </form>
                    </div>
                </div>
            </div>


            <div class="modal" id="myModal">
                <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
                    <div class="modal-content">
                        <div class="modal-body">
                            <form class="row g-3 needs-validation" method="POST" action="" id="myFormAddPaciente">
                                <div class="modal-header">
                                    <h4 class="text-center fs-2 col-md-11">Datos del Paciente</h4>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="CerrarModal()">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>

                                <div class="form-group col-md-9">
                                    <label class="fs-4">Nombre del Paciente</label>
                                    <input type="text" class="form-control fs-4" id="nombrePaciente" name="nombrePaciente" required>
                                </div>

                                <div class="form-group col-md-9">
                                    <label class="fs-5">Fecha de Nacimiento</label>
                                    <input type="date" class="form-control fs-5" id="fechaNac" name="fechaNac" required>
                                </div>

                                <div class="form-group col-md-9">
                                    <label class="fs-5">ID</label>
                                    <input type="number" class="form-control fs-5" id="idPaciente" name="idPaciente" required>
                                </div>

                                <div class="form-group col-md-9">
                                    <label class="fs-5">Cama</label>
                                    <input type="text" class="form-control fs-5" id="cama" required>
                                </div>

                                <div class="form-group col-md-9">
                                    <label class="fs-5">Edad</label>
                                    <input type="text" class="form-control fs-5" id="edad" required>
                                </div>

                                <div class="form-group col-md-9">
                                    <label class="fs-5">Diagnostico Médico y Nutricional</label>
                                    <textarea class="form-control fs-5" aria-label="With textarea" id="diagMed" required></textarea>
                                </div>

                                <div class="form-group col-md-9">
                                    <label class="fs-5">Prescripción Nutricional</label>
                                    <textarea class="form-control fs-5" aria-label="With textarea" id="presNutri" required></textarea>
                                </div>

                                <div class="form-group col-md-9">
                                    <label class="fs-5">Área</label>
                                    <select name="area" id="area" class="form-control fs-5" required>
                                        <option value="">Elegir área..</option>
                                        <option value="CIRUGÍA GENERAL">CIRUGÍA GENERAL</option>
                                        <option value="MEDICINA INTERNA">MEDICINA INTERNA</option>
                                        <option value="GINECOLOGÍA Y PEDIATRÍA">GINECOLOGÍA Y PEDIATRÍA</option>
                                        <option value="UTIP">UTIP</option>
                                        <option value="UCIQUEMADOS">UCIQUEMADOS</option>
                                        <option value="UCIA">UCIA</option>
                                    </select>
                                </div>

                                <div class="form-group col-md-9">
                                    <label class="fs-5">VIP</label>
                                    <select name="vip" id="vip" class="form-control fs-5" required>
                                        <option value="">Elegir opción...</option>
                                        <option value="APLICA">APLICA</option>
                                        <option value="N/A">N/A</option>
                                    </select>
                                </div>

                                <div class="form-group col-md-9">
                                    <label class="fs-5">Observaciones</label>
                                    <textarea class="form-control fs-5" aria-label="With textarea" id="observaciones" required></textarea>
                                </div>

                                <div class="form-group col-md-9">
                                    <label class="fs-5">Control de Tamizaje</label>
                                    <textarea class="form-control fs-5" aria-label="With textarea" id="controlTami" required></textarea>
                                </div>

                                <div class="form-group col-md-9" hidden>
                                    <label class="fs-4">Usuario</label>
                                    <input type="text" class="form-control fs-4" id="nombreUsuario" value="<?php echo $sesionNombre ?>" required>
                                </div>

                                <div class="form-group col-md-9" hidden>
                                    <label class="fs-4">status</label>
                                    <input type="text" class="form-control fs-4" id="statusP" value="Activo" required>
                                </div>


                            </form>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-primary" name="addPaciente" id="addPaciente">Agregar Paciente</button>
                                <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#fileAddPaciente">
                                    <i class="bi bi-filetype-exe"></i> Importar
                                </button>
                                <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <div class="modal" id="fileAddPaciente" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Importar Archivo</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="clearFileInputAddPaciente();"></button>
                        </div>
                        <div class="modal-body">
                            <input type="file" id="fileInputAddPaciente" accept=".xls,.xlsx" onchange="updateFileNameAddPaciente();" />
                            <p id="fileNameAddPaciente">No se ha seleccionado ningún archivos.</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="clearFileInputAddPaciente();">Cerrar</button>
                            <button type="button" class="btn btn-primary" onclick="submitForm();">Cargar Achivo</button>
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


    <?php include 'footer.php'; ?>
</body>

</html>
<script src="js/editpacientes.js"></script>
<script src="js/seguridad.js"></script>