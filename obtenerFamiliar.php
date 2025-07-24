<?php
include 'conexion.php';
date_default_timezone_set('America/Mexico_City');



if (!isset($_GET['idPaciente'])) {
    echo json_encode(['error' => 'Falta el parámetro idPaciente']);
    exit;
}

$idPacienteF = $_GET['idPaciente'];
$fechaHoraActual1 = date("Y-m-d");

$sql = "SELECT * FROM dietas WHERE idPaciente = '$idPacienteF' AND DATE(Fecha_Hora_Creacion) = '$fechaHoraActual1'";
$result = mysqli_query($con, $sql);

if ($result && mysqli_num_rows($result) > 0) {
    $familiar = mysqli_fetch_assoc($result);
    echo json_encode($familiar);
} else {
    echo json_encode(null);
}
?>



