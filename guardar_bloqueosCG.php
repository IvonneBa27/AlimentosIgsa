<?php
include 'conexion.php';
date_default_timezone_set("America/Mexico_City");
$fechaHoraActual = date("Y-m-d H:i:s");

// Si es una solicitud GET, devolvemos los datos guardados
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $fecha = date('Y-m-d', strtotime('+1 day'));

    $query = "SELECT idPaciente, Desayuno FROM dietas WHERE DATE(Fecha_Hora_Creacion) = '$fecha' AND area = 'CIRUGÍA GENERAL'";
    $result = mysqli_query($con, $query);

    $datos = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $datos[$row['idPaciente']] = $row['Desayuno'];
    }

    echo json_encode($datos);
    exit;
}

// Si es POST, procesamos el guardado
$input = file_get_contents("php://input");
$data = json_decode($input, true);

if (!isset($data["registros"]) || !is_array($data["registros"])) {
    echo json_encode(["error" => "Datos inválidos"]);
    exit;
}

$errores = [];
$exitos = [];

foreach ($data["registros"] as $registro) {
    // Escapar y preparar los datos
    $idPaciente = mysqli_real_escape_string($con, $registro["idPaciente"]);
    $nombre = mysqli_real_escape_string($con, $registro["nombre"]);
    $fechaNacimiento = mysqli_real_escape_string($con, $registro["fechaNacimiento"]);
    $cama = mysqli_real_escape_string($con, $registro["cama"]);
    $edad = mysqli_real_escape_string($con, $registro["edad"]);
    $edadMeses = mysqli_real_escape_string($con, $registro["edadMeses"]);
    $edadDias = mysqli_real_escape_string($con, $registro["edadDias"]);
    $diagnosticoMed = mysqli_real_escape_string($con, $registro["diagnosticoMed"]);
    $prescripcionNutri = mysqli_real_escape_string($con, $registro["prescripcionNutri"]);
    $observaciones = mysqli_real_escape_string($con, $registro["observaciones"]);
    $controlTamizaje = mysqli_real_escape_string($con, $registro["controlTamizaje"]);
    $vip = mysqli_real_escape_string($con, $registro["vip"]);
    $usuario = mysqli_real_escape_string($con, $registro["usuario"]);
    $desayuno = mysqli_real_escape_string($con, $registro["desayuno"]);
    $fecha = date('Y-m-d H:i:s', strtotime($fechaHoraActual . ' +1 day'));

    // Verificar si ya existe un registro para ese paciente y fecha
    $verificar = "SELECT ID FROM dietas WHERE idPaciente = '$idPaciente' AND DATE(Fecha_Hora_Creacion) = DATE('$fecha')";
    $resultadoVerificar = mysqli_query($con, $verificar);

    if (mysqli_num_rows($resultadoVerificar) > 0) {
        $row = mysqli_fetch_assoc($resultadoVerificar);
        $idDieta = $row['ID'];

        $sqlUpdate = "UPDATE dietas SET Nombre_Paciente = '$nombre', Fecha_Nacimiento_Paciente = '$fechaNacimiento', Cama_Paciente = '$cama', Edad = '$edad', 
        Diag_Med_Nutri = '$diagnosticoMed', Prescripcion = '$prescripcionNutri', Desayuno = '$desayuno', Observaciones = '$observaciones', Control_Tamizaje = '$controlTamizaje',
        privados = '$vip', Fecha_Hora_Creacion = '$fecha', Creado_por = '$usuario' WHERE ID = '$idDieta'";

        if (mysqli_query($con, $sqlUpdate)) {
            $exitos[] = $idPaciente;
            $update = "INSERT INTO updatedesayunos(ID, idDieta, IdPaciente, fechaHora, Desayuno, area, modificadoPor) 
                       VALUES ('','$idDieta','$idPaciente','$fecha','$desayuno','CIRUGÍA GENERAL','$usuario')";
            mysqli_query($con, $update);
        } else {
            $errores[] = ["idPaciente" => $idPaciente, "error" => mysqli_error($con)];
        }
    } else {
        $sql = "INSERT INTO dietas (ID, Nombre_Paciente, Fecha_Nacimiento_Paciente, idPaciente, Cama_Paciente, Edad, Diag_Med_Nutri, Prescripcion, 
        Desayuno, Col_Matutina, Comida, Col_Vespertina, Cena, Col_Nocturna, Observaciones, Control_Tamizaje, area, privados, Fecha_Hora_Creacion, Creado_por) VALUES 
        ('', '$nombre', '$fechaNacimiento','$idPaciente','$cama','$edad','$diagnosticoMed', '$prescripcionNutri', '$desayuno', '', '', '', '', '',
        '$observaciones', '$controlTamizaje', 'CIRUGÍA GENERAL', '$vip','$fecha', '$usuario')";

        if (mysqli_query($con, $sql)) {
            $exitos[] = $idPaciente;
            $idDieta = mysqli_insert_id($con);
            $update = "INSERT INTO updatedesayunos(ID, idDieta, IdPaciente, fechaHora, Desayuno, area, modificadoPor) 
                       VALUES ('','$idDieta','$idPaciente','$fecha','$desayuno','CIRUGÍA GENERAL','$usuario')";
            mysqli_query($con, $update);
        } else {
            $errores[] = ["idPaciente" => $idPaciente, "error" => mysqli_error($con)];
        }
    }
}

echo json_encode([
    "mensaje" => "Proceso completado",
    "insertados" => $exitos,
    "errores" => $errores
]);
?>
