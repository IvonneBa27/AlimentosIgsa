<?php
// Configuración de la base de datos
include('conexion.php');

// Obtener los datos del formulario
$nombrePaciente = $_POST['nombrePaciente'];
$fechaNac = $_POST['fechaNac'];
$idPaciente = $_POST['idPaciente'];
$cama = $_POST['cama'];
$edad = $_POST['edad'];
$diagMed = $_POST['diagMed'];
$presNutri = $_POST['presNutri'];
$area = $_POST['area'];
$vip = $_POST['vip'];
$observaciones = $_POST['observaciones'];
$controlTami = $_POST['controlTami'];
$nombreUsuario = $_POST['nombreUsuario'];
$statusP = $_POST['statusP'];

$paciente = "SELECT * FROM pacientes WHERE idPaciente = '$idPaciente'";
$query1 = mysqli_query($con, $paciente);

$response = array();

if ($query1->num_rows > 0) {
    $response['status'] = 'error';
    $response['message'] = 'El ID ingresado para paciente ya existe, valide los datos';
} else {
    // Preparar y ejecutar la consulta de inserción
    $consulta = "INSERT INTO pacientes (id, nombre, fechaNacimiento, idPaciente, cama, edad, diagnosticoMed, prescripcionNutri, area, vip, observaciones, controlTamizaje, creadoPor, statusP) 
        VALUES ('','$nombrePaciente', '$fechaNac', '$idPaciente', '$cama', '$edad', '$diagMed', '$presNutri', '$area', '$vip', '$observaciones', '$controlTami', '$nombreUsuario', '$statusP')";
    $query = mysqli_query($con, $consulta);

    if ($query) {
        $response['status'] = 'success';
        $response['message'] = 'Paciente agregado exitosamente';
    } else {
        //$response['status'] = 'error';
        //$response['message'] = 'Error al agregar el paciente';
    }
}

echo json_encode($response);