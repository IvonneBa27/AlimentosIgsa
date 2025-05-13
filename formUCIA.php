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


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idPaciente = $_POST['idPaciente'];
    $datos = json_decode($_POST['datos'], true); // Decodificar JSON a array
    $rangoHoraActual = $datos['rangoHoraActual']; // Obtener el rango de hora actual

    // Depuración: Verificar que los datos se están recibiendo
    error_log("Datos recibidos: idPaciente=$idPaciente, datos=" . json_encode($datos) . ", rangoHoraActual=$rangoHoraActual");

    guardarOActualizarDieta($idPaciente, $datos, $rangoHoraActual);
    exit;
}


function guardarOActualizarDieta($idPaciente, $datos, $rangoHoraActual)
{
    global $con;
    $fechaHoy = date("Y-m-d");
    $fechaHoraActual = date("Y-m-d H:i:s");

    // Verificar si ya existe un registro para hoy
    $sql = "SELECT * FROM dietas WHERE idPaciente = '$idPaciente' AND DATE(Fecha_Hora_Creacion) = '$fechaHoy'";
    $query = mysqli_query($con, $sql);
    $result = mysqli_fetch_assoc($query);
    $area = $result['area'];
    $idDieta = $result['ID'];

    if ($result) {
        $area = $result['area'];
        $idDieta = $result['ID'];
    } else {
        $area = null;
        $idDieta = null;
    }

    if ($query->num_rows > 0) {
        // Actualizar el registro existente
        $nombre = $datos['nombre'];
        $fechaNacimiento = $datos['fechaNacimiento'];
        $cama = $datos['cama'];
        $edad = $datos['edad'];
        $diagnosticoMed = $datos['diagnosticoMed'];
        $prescripcionNutri = $datos['prescripcionNutri'];
        $desayuno = $datos['Desayuno'];
        $colMatutina = $datos['Col_Matutina'];
        $comida = $datos['Comida'];
        $colVespertina = $datos['Col_Vespertina'];
        $cena = $datos['Cena'];
        $colNocturna = $datos['Col_Nocturna'];
        $observaciones = $datos['observaciones'];
        $controlTamizaje = $datos['controlTamizaje'];
        $vip = $datos['vip'];
        $usuario = $datos['usuario'];

        // Dependiendo del rango de hora actual, realizar la consulta correspondiente
        if ($rangoHoraActual == 'Desayuno') {
            $insert = "INSERT INTO updatedesayunos (ID, idDieta, IdPaciente, fechaHora, Desayuno, area, modificadoPor) 
                       VALUES ('', '$idDieta', '$idPaciente', '$fechaHoraActual', '$desayuno', '$area', '$usuario')";
            mysqli_query($con, $insert);
            $sqlUpdate = "UPDATE dietas SET Nombre_Paciente = '$nombre', Fecha_Nacimiento_Paciente = '$fechaNacimiento', Cama_Paciente = '$cama', Edad = '$edad', Diag_Med_Nutri = '$diagnosticoMed', Prescripcion = '$prescripcionNutri', Desayuno = '$desayuno', Col_Matutina = '$colMatutina', Comida = '$comida', Col_Vespertina = '$colVespertina', Cena = '$cena', Col_Nocturna = '$colNocturna', Observaciones = '$observaciones', Control_Tamizaje = '$controlTamizaje', privados = '$vip', Fecha_Hora_Creacion = '$fechaHoraActual', Creado_por = '$usuario' WHERE idPaciente = '$idPaciente' AND DATE(Fecha_Hora_Creacion) = '$fechaHoy'";
            mysqli_query($con, $sqlUpdate);
        } elseif ($rangoHoraActual == 'Col_Matutina') {
            // Realizar la consulta correspondiente a Col_Matutina
            $insert = "INSERT INTO updatecol_matutina (ID, idDieta, IdPaciente, fechaHora, Col_Matutina, area, modificadoPor) 
            VALUES ('', '$idDieta', '$idPaciente', '$fechaHoraActual', '$colMatutina', '$area', '$usuario')";
            mysqli_query($con, $insert);
            $sqlUpdate = "UPDATE dietas SET Nombre_Paciente = '$nombre', Fecha_Nacimiento_Paciente = '$fechaNacimiento', Cama_Paciente = '$cama', Edad = '$edad', Diag_Med_Nutri = '$diagnosticoMed', Prescripcion = '$prescripcionNutri', Desayuno = '$desayuno', Col_Matutina = '$colMatutina', Comida = '$comida', Col_Vespertina = '$colVespertina', Cena = '$cena', Col_Nocturna = '$colNocturna', Observaciones = '$observaciones', Control_Tamizaje = '$controlTamizaje', privados = '$vip', Fecha_Hora_Creacion = '$fechaHoraActual', Creado_por = '$usuario' WHERE idPaciente = '$idPaciente' AND DATE(Fecha_Hora_Creacion) = '$fechaHoy'";
            mysqli_query($con, $sqlUpdate);
        } elseif ($rangoHoraActual == 'Comida') {
            // Realizar la consulta correspondiente a Comida
            $insert = "INSERT INTO updatecomida (ID, idDieta, IdPaciente, fechaHora, Comida, area, modificadoPor) 
            VALUES ('', '$idDieta', '$idPaciente', '$fechaHoraActual', '$comida', '$area', '$usuario')";
            mysqli_query($con, $insert);
            $sqlUpdate = "UPDATE dietas SET Nombre_Paciente = '$nombre', Fecha_Nacimiento_Paciente = '$fechaNacimiento', Cama_Paciente = '$cama', Edad = '$edad', Diag_Med_Nutri = '$diagnosticoMed', Prescripcion = '$prescripcionNutri', Desayuno = '$desayuno', Col_Matutina = '$colMatutina', Comida = '$comida', Col_Vespertina = '$colVespertina', Cena = '$cena', Col_Nocturna = '$colNocturna', Observaciones = '$observaciones', Control_Tamizaje = '$controlTamizaje', privados = '$vip', Fecha_Hora_Creacion = '$fechaHoraActual', Creado_por = '$usuario' WHERE idPaciente = '$idPaciente' AND DATE(Fecha_Hora_Creacion) = '$fechaHoy'";
            mysqli_query($con, $sqlUpdate);
        } elseif ($rangoHoraActual == 'Col_Vespertina') {
            // Realizar la consulta correspondiente a Col_Vespertina
            $insert = "INSERT INTO updatecol_vespertina (ID, idDieta, IdPaciente, fechaHora, Col_Vespertina, area, modificadoPor) 
            VALUES ('', '$idDieta', '$idPaciente', '$fechaHoraActual', '$colVespertina', '$area', '$usuario')";
            mysqli_query($con, $insert);
            $sqlUpdate = "UPDATE dietas SET Nombre_Paciente = '$nombre', Fecha_Nacimiento_Paciente = '$fechaNacimiento', Cama_Paciente = '$cama', Edad = '$edad', Diag_Med_Nutri = '$diagnosticoMed', Prescripcion = '$prescripcionNutri', Desayuno = '$desayuno', Col_Matutina = '$colMatutina', Comida = '$comida', Col_Vespertina = '$colVespertina', Cena = '$cena', Col_Nocturna = '$colNocturna', Observaciones = '$observaciones', Control_Tamizaje = '$controlTamizaje', privados = '$vip', Fecha_Hora_Creacion = '$fechaHoraActual', Creado_por = '$usuario' WHERE idPaciente = '$idPaciente' AND DATE(Fecha_Hora_Creacion) = '$fechaHoy'";
            mysqli_query($con, $sqlUpdate);
        } elseif ($rangoHoraActual == 'Cena') {
            // Realizar la consulta correspondiente a Cena
            $insert = "INSERT INTO updatecena (ID, idDieta, IdPaciente, fechaHora, Cena, area, modificadoPor) 
            VALUES ('', '$idDieta', '$idPaciente', '$fechaHoraActual', '$cena', '$area', '$usuario')";
            mysqli_query($con, $insert);
            $sqlUpdate = "UPDATE dietas SET Nombre_Paciente = '$nombre', Fecha_Nacimiento_Paciente = '$fechaNacimiento', Cama_Paciente = '$cama', Edad = '$edad', Diag_Med_Nutri = '$diagnosticoMed', Prescripcion = '$prescripcionNutri', Desayuno = '$desayuno', Col_Matutina = '$colMatutina', Comida = '$comida', Col_Vespertina = '$colVespertina', Cena = '$cena', Col_Nocturna = '$colNocturna', Observaciones = '$observaciones', Control_Tamizaje = '$controlTamizaje', privados = '$vip', Fecha_Hora_Creacion = '$fechaHoraActual', Creado_por = '$usuario' WHERE idPaciente = '$idPaciente' AND DATE(Fecha_Hora_Creacion) = '$fechaHoy'";
            mysqli_query($con, $sqlUpdate);
        } elseif ($rangoHoraActual == 'Col_Nocturna') {
            // Realizar la consulta correspondiente a Col_Nocturna
            $insert = "INSERT INTO updatecol_nocturna (ID, idDieta, IdPaciente, fechaHora, Col_Nocturna, area, modificadoPor) 
            VALUES ('', '$idDieta', '$idPaciente', '$fechaHoraActual', '$colNocturna', '$area', '$usuario')";
            mysqli_query($con, $insert);
            $sqlUpdate = "UPDATE dietas SET Nombre_Paciente = '$nombre', Fecha_Nacimiento_Paciente = '$fechaNacimiento', Cama_Paciente = '$cama', Edad = '$edad', Diag_Med_Nutri = '$diagnosticoMed', Prescripcion = '$prescripcionNutri', Desayuno = '$desayuno', Col_Matutina = '$colMatutina', Comida = '$comida', Col_Vespertina = '$colVespertina', Cena = '$cena', Col_Nocturna = '$colNocturna', Observaciones = '$observaciones', Control_Tamizaje = '$controlTamizaje', privados = '$vip', Fecha_Hora_Creacion = '$fechaHoraActual', Creado_por = '$usuario' WHERE idPaciente = '$idPaciente' AND DATE(Fecha_Hora_Creacion) = '$fechaHoy'";
            mysqli_query($con, $sqlUpdate);
        }

    } else {
        // Insertar un nuevo registro
        $nombre = $datos['nombre'];
        $fechaNacimiento = $datos['fechaNacimiento'];
        $cama = $datos['cama'];
        $edad = $datos['edad'];
        $diagnosticoMed = $datos['diagnosticoMed'];
        $prescripcionNutri = $datos['prescripcionNutri'];
        $desayuno = $datos['Desayuno'];
        $colMatutina = $datos['Col_Matutina'];
        $comida = $datos['Comida'];
        $colVespertina = $datos['Col_Vespertina'];
        $cena = $datos['Cena'];
        $colNocturna = $datos['Col_Nocturna'];
        $observaciones = $datos['observaciones'];
        $controlTamizaje = $datos['controlTamizaje'];
        $vip = $datos['vip'];
        $usuario = $datos['usuario'];

        $sqlInsert = "INSERT INTO dietas 
               (ID, Nombre_Paciente, Fecha_Nacimiento_Paciente, idPaciente, Cama_Paciente, Edad, Diag_Med_Nutri, Prescripcion, Desayuno, Col_Matutina, Comida, Col_Vespertina, Cena, Col_Nocturna, Observaciones, Control_Tamizaje, area, privados, Fecha_Hora_Creacion, Creado_por) 
        VALUES ('', '$nombre', '$fechaNacimiento','$idPaciente', '$cama', '$edad', '$diagnosticoMed', '$prescripcionNutri', '$desayuno', '$colMatutina', '$comida', '$colVespertina', '$cena', '$colNocturna', '$observaciones', '$controlTamizaje', 'UCIA', '$vip', '$fechaHoraActual', '$usuario')";
        mysqli_query($con, $sqlInsert);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idPaciente = $_POST['idPaciente'];
    $datos = json_decode($_POST['datos'], true); // Decodificar JSON a array

    // Depuración: Verificar que los datos se están recibiendo
    error_log("Datos recibidos: idPaciente=$idPaciente, datos=" . json_encode($datos));

    guardarOActualizarDieta($idPaciente, $datos, $rangoHoraActual);
    exit;
}


$sql = "SELECT p.*, d.*, p.idPaciente AS pacienteID, d.idPaciente AS dietaID
FROM pacientes p
LEFT JOIN (
    SELECT d1.*
    FROM dietas d1
    INNER JOIN (
        SELECT MAX(ID) AS ID, idPaciente
        FROM dietas
        WHERE area = 'UCIA'
        GROUP BY idPaciente
    ) d2 ON d1.ID = d2.ID
) d ON p.idPaciente = d.idPaciente
WHERE p.statusP = 'Alta' AND p.area = 'UCIA'ORDER BY 
CASE 
 WHEN p.cama LIKE 'A-%' THEN 1
 ELSE 2
END, 
p.cama ASC";

$query = mysqli_query($con, $sql);

$datosCombinados = array();

if ($query->num_rows > 0) {
    while ($row = $query->fetch_assoc()) {
        $datosCombinados[] = $row;
    }
} else {
    //echo "0 resultados";
}

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
                <h3 class="h3">SOLICITUD DE ORDEN UCIA</h3>
                <div class="btn-toolbar mb-2 mb-md-0">


                    <form method="post" action="pdfUCIA.php" target="_blank">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#fileAddPaciente">
                                <i class="bi bi-filetype-exe"></i> Importar Paciente
                            </button>
                            </button>
                        </div>

                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="openModal();">
                                <i class="bi bi-person-plus"></i> Agregar Paciente
                            </button>
                        </div>

                        <div class="btn-group me-2">
                            <button type="submit" class="btn btn-sm btn-outline-secondary"><i class="bi bi-filetype-pdf"></i> Exportar</button>
                        </div>
                    </form>


                    <form action="">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="location.href = 'formUCIA.php';">
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
                        <div class="table-responsive" style="max-height: 830px; overflow-y: auto;">
                            <table id="miTabla" class="table table-light table-sm table-striped table-bordered">
                                <thead>
                                    <tr style="border: none;">
                                        <th class="text-center table-light" style="border: none; background-color: white;" scope="col"></th>
                                        <th class="text-center table-light" style="border: none; background-color: white;" scope="col"></th>
                                        <th class="text-center table-light" style="border: none; background-color: white;" scope="col"></th>
                                        <th class="text-center table-light" style="border: none; background-color: white;" scope="col"></th>
                                        <th class="text-center table-light" style="border: none; background-color: white;" scope="col"></th>
                                        <th class="text-center table-light" style="border: none; background-color: white;" scope="col"></th>
                                        <th class="text-center table-light" style="border: none; background-color: white;" scope="col"></th>
                                        <th class="text-center table-light" style="border: none; background-color: white;" scope="col"></th>
                                        <th class="text-center table-light" style="border: none; background-color: white;" scope="col"></th>
                                        <th class="text-center table-light" style="border: none; background-color: white;" scope="col"></th>
                                        <th class="text-center" scope="col">Rango de Hora (7:00 - 8:00)</th>
                                        <th class="text-center" scope="col">Rango de Hora (9:30 - 10:30)</th>
                                        <th class="text-center" scope="col">Rango de Hora (11:30 - 12:30)</th>
                                        <th class="text-center" scope="col">Rango de Hora (15:00 - 16:00)</th>
                                        <th class="text-center" scope="col">Rango de Hora (16:30 - 17:30)</th>
                                        <th class="text-center" scope="col">Rango de Hora (20:00 - 21:00)</th>
                                        <th class="text-center table-light" style="border: none; background-color: white;" scope="col"></th>
                                        <th class="text-center table-light" style="border: none; background-color: white;" scope="col"></th>
                                        <th class="text-center table-light" style="border: none; background-color: white;" scope="col"></th>
                                        <th class="text-center" scope="col" hidden></th>
                                        <th class="text-center" scope="col" hidden></th>
                                    </tr>
                                    <tr>
                                        <th class="text-center" scope="col"></th>
                                        <th class="text-center" scope="col">Nombre del Paciente</th>
                                        <th class="text-center" scope="col">Fecha de Nacimiento</th>
                                        <th class="text-center" scope="col">ID</th>
                                        <th class="text-center" scope="col">Cama</th>
                                        <th class="text-center" scope="col">Edad Años</th>
                                        <th class="text-center" scope="col">Edad Meses</th>
                                        <th class="text-center" scope="col">Edad Dias</th>
                                        <th class="text-center" scope="col">Diagnostico Médico y Nutricional</th>
                                        <th class="text-center" scope="col">Prescripción Nutricional</th>
                                        <th class="text-center" scope="col">Desayuno</th>
                                        <th class="text-center" scope="col">Colación Matutina</th>
                                        <th class="text-center" scope="col">Comida</th>
                                        <th class="text-center" scope="col">Colación Vespertina</th>
                                        <th class="text-center" scope="col">Cena</th>
                                        <th class="text-center" scope="col">Colación Nocturna</th>
                                        <th class="text-center" scope="col">Observaciones</th>
                                        <th class="text-center" scope="col">Control de Tamizaje</th>
                                        <th class="text-center" scope="col">Aislados</th>
                                        <th class="text-center" scope="col" hidden>Usuario</th>
                                        <th class="text-center" scope="col" hidden>Fecha_Hora_Solicitud</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($datosCombinados as $dataRow) { ?>
                                        <tr>
                                            <td scope="col"><input type="checkbox" onclick="toggleEdit(this)"></td>
                                            <td class="text-center" data-campo="nombre"><?php echo $dataRow["nombre"]; ?></td>
                                            <td class="text-center" data-campo="fechaNacimiento"><?php echo $dataRow["fechaNacimiento"]; ?></td>
                                            <td class="text-center" data-campo="idPaciente"><?php echo $dataRow["pacienteID"]; ?></td>
                                            <td class="text-center" data-campo="cama"><?php echo $dataRow["cama"]; ?></td>
                                            <td class="text-center" data-campo="edad"><?php echo $dataRow["edad"]; ?></td>
                                            <td class="text-center" data-campo="edad"><?php echo $dataRow["edadMeses"]; ?></td>
                                            <td class="text-center" data-campo="edad"><?php echo $dataRow["edadDias"]; ?></td>
                                            <td class="text-center" data-campo="diagnosticoMed"><?php echo $dataRow["diagnosticoMed"]; ?></td>
                                            <td class="text-center" data-campo="prescripcionNutri"><?php echo $dataRow["prescripcionNutri"]; ?></td>
                                            <td class="text-center" data-campo="Desayuno"><textarea class="form-control-plaintext table-textarea" data-campo="Desayuno" disabled><?php echo $dataRow["Desayuno"]; ?></textarea></td>
                                            <td class="text-center" data-campo="Col_Matutina"><textarea class="form-control-plaintext table-textarea" data-campo="Col_Matutina" disabled><?php echo $dataRow["Col_Matutina"]; ?></textarea></td>
                                            <td class="text-center" data-campo="Comida"><textarea class="form-control-plaintext table-textarea" data-campo="Comida" disabled><?php echo $dataRow["Comida"]; ?></textarea></td>
                                            <td class="text-center" data-campo="Col_Vespertina"><textarea class="form-control-plaintext table-textarea" data-campo="Col_Vespertina" disabled><?php echo $dataRow["Col_Vespertina"]; ?></textarea></td>
                                            <td class="text-center" data-campo="Cena"><textarea class="form-control-plaintext table-textarea" data-campo="Cena" disabled><?php echo $dataRow["Cena"]; ?></textarea></td>
                                            <td class="text-center" data-campo="Col_Nocturna"><textarea class="form-control-plaintext table-textarea" data-campo="Col_Nocturna" disabled><?php echo $dataRow["Col_Nocturna"]; ?></textarea></td>
                                            <td class="text-center" data-campo="observaciones"><textarea class="form-control-plaintext table-textarea" data-campo="observaciones" disabled><?php echo $dataRow["observaciones"]; ?></textarea></td>
                                            <td class="text-center" data-campo="controlTamizaje"><textarea class="form-control-plaintext table-textarea" data-campo="controlTamizaje" disabled><?php echo $dataRow["controlTamizaje"]; ?></textarea></td>
                                            <td class="text-center" data-campo="vip"><?php echo $dataRow["vip"]; ?></td>
                                            <td class="text-center" data-campo="usuario" hidden> <?php echo $sesionNombre; ?></td>
                                            <td class="text-center" data-campo="fechaHoraActual" hidden><input type="datetime" id="fechaHoraActual" name="fechaHoraActual" readonly required></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>

                        </div>
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
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>

                                <div class="form-group col-md-9">
                                    <label class="fs-4">Nombre del Paciente</label>
                                    <input type="text" class="form-control fs-4" id="nombrePaciente" name="nombrePaciente" required>
                                </div>

                                <div class="form-group col-md-9">
                                    <label class="fs-5" for="fechaNac">Fecha de Nacimiento</label>
                                    <input class="form-control fs-5" type="date" id="fechaNac" required>
                                </div>

                                <div class="form-group col-md-9">
                                    <label class="fs-5">ID</label>
                                    <input type="number" class="form-control fs-5" id="idPaciente" name="idPaciente" required>
                                </div>

                                <div class="form-group col-md-9">
                                    <label class="fs-5">Cama</label>
                                    <select name="cama" id="cama" class="form-control fs-5" required>
                                        <?php
                                        $sqlCama = "SELECT numero FROM camas WHERE area = 'UCIA'";
                                        $queryCama = mysqli_query($con, $sqlCama);

                                        while ($row = mysqli_fetch_assoc($queryCama)) {
                                            echo '<option value="' . $row['numero'] . '">' . $row['numero'] . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="form-group col-md-9">
                                    <label for="edadAnios" class="fs-5">Años</label>
                                    <input class="form-control fs-5" type="text" id="edadAnios" required readonly>

                                    <label for="edadMeses" class="fs-5">Meses</label>
                                    <input class="form-control fs-5" type="text" id="edadMeses" required readonly>

                                    <label for="edadDias" class="fs-5">Días</label>
                                    <input class="form-control fs-5" type="text" id="edadDias" required readonly>
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
                                    <label class="fs-5">AISLADOS</label>
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
                                    <input type="text" class="form-control fs-4" id="statusP" value="Alta" required>
                                </div>


                            </form>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-primary" name="addPaciente" id="addPaciente">Agregar Paciente</button>
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
    <script src="js/relojUCIA.js"></script>
    <script src="js/seguridad.js"></script>

    <?php include 'footer.php'; ?>
</body>

</html>