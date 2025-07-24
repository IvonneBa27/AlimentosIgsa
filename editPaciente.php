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
$sql1 = "SELECT * FROM pacientes WHERE id = '$id'";
$query1 = mysqli_query($con, $sql1);

if ($query1 && mysqli_num_rows($query1) > 0) {
    $datosP = mysqli_fetch_assoc($query1);
    $area = mysqli_real_escape_string($con, $datosP['area']); // Escapa el valor del área

    $sqlCamas = "
    SELECT numero 
    FROM camas 
    WHERE area = '$area' 
    AND (
        numero NOT IN (
            SELECT cama 
            FROM pacientes 
            WHERE area = '$area' 
            AND statusP = 'Activo'
        )
        OR numero = '{$datosP['cama']}'
    )";

    $queryCamas = mysqli_query($con, $sqlCamas);

    $datosC = []; // Arreglo para almacenar los números de cama

    if ($queryCamas && mysqli_num_rows($queryCamas) > 0) {
        while ($fila = mysqli_fetch_assoc($queryCamas)) {
            $datosC[] = $fila['numero'];
        }
    } else {
        echo "No se encontraron camas para el área: $area";
    }
} else {
    echo "No se encontró el paciente con ID: $id";
}

$sql = "SELECT * FROM pacientes WHERE id = '$id'";
$query = mysqli_query($con, $sql);

$vipOptions = ["APLICA", "N/A"];
$statusOptions = ["Activo", "Inactivo"];
$areaOptions = ["CIRUGÍA GENERAL", "MEDICINA INTERNA", "GINECOLOGÍA", "PEDIATRÍA", "LACTANTES", "PRIVADOS", "QUEMADOS", "UTIP", "UCIA"];

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
                    </div>

                    <form method="post" action="pdfCG.php" target="_blank">
                        <div class="btn-group me-2">
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
                                                        <option value="">Seleccione una cama</option>
                                                        <?php foreach ($datosC as $numeroCama): ?>
                                                            <option value="<?php echo $numeroCama; ?>" <?php echo ($numeroCama == $dataRow['cama']) ? 'selected' : ''; ?>>
                                                                <?php echo $numeroCama; ?>
                                                            </option>
                                                        <?php endforeach; ?>
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