<?php
// Configuración de la base de datos
include('conexion.php');

// Obtener los datos del formulario
$nombrePaciente = $_POST['nombrePaciente'];
$fechaNac = $_POST['fechaNac'];
$idPaciente = $_POST['idPaciente'];
$cama = $_POST['cama'];
$edadAnios = $_POST['edadAnios'];
$edadMeses = $_POST['edadMeses'];
$edadDias = $_POST['edadDias'];
$diagMed = $_POST['diagMed'];
$presNutri = $_POST['presNutri'];
$vip = $_POST['vip'];
$observaciones = $_POST['observaciones'];
$controlTami = $_POST['controlTami'];
$nombreUsuario = $_POST['nombreUsuario'];
$statusP = $_POST['statusP'];

$response = array();

// Validar que el idPaciente no exista
$paciente = "SELECT * FROM pacientes WHERE idPaciente = '$idPaciente'";
$query1 = mysqli_query($con, $paciente);

if ($query1->num_rows > 0) {

    $response['status'] = 'error';
    $response['message'] = 'El ID ingresado para paciente ya existe, valide los datos';
} else {
    $camaQuery = "SELECT * FROM pacientes WHERE cama = '$cama' AND area = 'UCIA' AND statusP = 'Activo'";
    $query2 = mysqli_query($con, $camaQuery);

    if ($query2->num_rows > 0) {

        $response['status'] = 'error';
        $response['message'] = 'La cama ingresada ya está ocupada en el área de UCIA, por favor elija otra cama';
    } else {
        $consulta = "INSERT INTO pacientes (id, nombre, fechaNacimiento, idPaciente, cama, edad, edadMeses, edadDias, diagnosticoMed, prescripcionNutri, area, vip, observaciones, controlTamizaje, creadoPor, statusP) 
            VALUES ('', '$nombrePaciente', '$fechaNac', '$idPaciente', '$cama', '$edadAnios', '$edadMeses', '$edadDias', '$diagMed', '$presNutri', 'UCIA', '$vip', '$observaciones', '$controlTami', '$nombreUsuario', '$statusP')";
        $query = mysqli_query($con, $consulta);

        if ($query) {

            $response['status'] = 'success';
            $response['message'] = 'Paciente agregado exitosamente';
        } else {

            $response['status'] = 'error';
            $response['message'] = 'Error al agregar el paciente, por favor intente nuevamente';
        }
    }
}



echo json_encode($response);



