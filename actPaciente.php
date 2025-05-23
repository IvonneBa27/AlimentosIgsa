<?php
include('conexion.php');

$id = $_POST['id'];
$nombre = $_POST['nombre'];
$fechaNac = $_POST['fechaNacimiento'];
$idPaciente = $_POST['idPaciente'];
$idPaciente2 = $_POST['idPaciente2'];
$cama = $_POST['cama'];
$cama2 = $_POST['cama2'];
$diagnosticoMed = $_POST['diagnosticoMed'];
$prescripcionNutri = $_POST['prescripcionNutri'];
$observaciones = $_POST['observaciones'];
$control = $_POST['controlTamizaje'];
$vip = $_POST['vip'];
$area = $_POST['area'];
$acceso = $_POST['statusP'];

//Los IDs de los pacientes son iguales
if ($idPaciente == $idPaciente2 && $cama == $cama2) {

    // Calcular la edad
    $fechaNacimiento = new DateTime($fechaNac);
    $hoy = new DateTime();
    $edad = $hoy->diff($fechaNacimiento);

    $edadAnios = $edad->y;
    $edadMeses = $edad->m;
    $edadDias = $edad->d;


    $actualizar = "UPDATE pacientes SET nombre = '$nombre', fechaNacimiento = '$fechaNac', idPaciente = '$idPaciente2', cama = '$cama2', edad = '$edadAnios', edadMeses = '$edadMeses',
               edadDias = '$edadDias', diagnosticoMed = '$diagnosticoMed', prescripcionNutri = '$prescripcionNutri', area = '$area', vip = '$vip', observaciones = '$observaciones', 
               controlTamizaje = '$control', statusP ='$acceso' WHERE id='$id'";
    $query0 = mysqli_query($con, $actualizar);
    if ($query0) {
        $response['status'] = 'success';
        $response['message'] = 'Paciente actualizado exitosamente.';
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Ocurrió un error al actualizar el paciente';
    }
} elseif ($idPaciente != $idPaciente2 && $cama != $cama2) { //Los IDs de los pacientes son diferentes

    $paciente = "SELECT * FROM pacientes WHERE idPaciente = '$idPaciente'";
    $query1 = mysqli_query($con, $paciente);

    $camaQuery = "SELECT * FROM pacientes WHERE cama = '$cama' AND area = '$area' AND statusP = 'Activo'";
    $query2 = mysqli_query($con, $camaQuery);

    $response = array();

    // Calcular la edad
    $fechaNacimiento = new DateTime($fechaNac);
    $hoy = new DateTime();
    $edad = $hoy->diff($fechaNacimiento);

    $edadAnios = $edad->y;
    $edadMeses = $edad->m;
    $edadDias = $edad->d;

    if ($query1->num_rows > 0) {
        $response['status'] = 'error';
        $response['message'] = 'El ID ingresado para paciente ya existe, valide los datos.';
    } else {

        if ($query2->num_rows > 0) {

            $response['status'] = 'error';

            $response['message'] = 'La cama ingresada ya está ocupada en el área de ' . $area . ', por favor elija otra cama.';
        } else {

            $actualizar = "UPDATE pacientes SET nombre = '$nombre', fechaNacimiento = '$fechaNac', idPaciente = '$idPaciente', cama = '$cama', edad = '$edadAnios', edadMeses = '$edadMeses',
            edadDias = '$edadDias', diagnosticoMed = '$diagnosticoMed', prescripcionNutri = '$prescripcionNutri', area = '$area', vip = '$vip', observaciones = '$observaciones', 
            controlTamizaje = '$control', statusP ='$acceso' WHERE id='$id'";
            $query = mysqli_query($con, $actualizar);
            if ($query) {
                $response['status'] = 'success';
                $response['message'] = 'Paciente actualizado exitosamente.';
            } else {
                $response['status'] = 'error';
                $response['message'] = 'Ocurrió un error al actualizar el paciente';
            }
        }
    }
} elseif ($idPaciente == $idPaciente2 && $cama != $cama2) {

    $camaQuery = "SELECT * FROM pacientes WHERE cama = '$cama' AND area = '$area' AND statusP = 'Activo'";
    $query2 = mysqli_query($con, $camaQuery);

    $response = array();

    // Calcular la edad
    $fechaNacimiento = new DateTime($fechaNac);
    $hoy = new DateTime();
    $edad = $hoy->diff($fechaNacimiento);

    $edadAnios = $edad->y;
    $edadMeses = $edad->m;
    $edadDias = $edad->d;

    if ($query2->num_rows > 0) {

        $response['status'] = 'error';
        $response['message'] = 'La cama ingresada ya está ocupada en el área de ' . $area . ', por favor elija otra cama.';
    } else {

        $actualizar = "UPDATE pacientes SET nombre = '$nombre', fechaNacimiento = '$fechaNac', idPaciente = '$idPaciente2', cama = '$cama', edad = '$edadAnios', edadMeses = '$edadMeses',
            edadDias = '$edadDias', diagnosticoMed = '$diagnosticoMed', prescripcionNutri = '$prescripcionNutri', area = '$area', vip = '$vip', observaciones = '$observaciones', 
            controlTamizaje = '$control', statusP ='$acceso' WHERE id='$id'";
        $query = mysqli_query($con, $actualizar);
        if ($query) {
            $response['status'] = 'success';
            $response['message'] = 'Paciente actualizado exitosamente.';
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Ocurrió un error al actualizar el paciente.';
        }
    }
} elseif ($idPaciente != $idPaciente2 && $cama == $cama2) {

    $paciente = "SELECT * FROM pacientes WHERE idPaciente = '$idPaciente'";
    $query1 = mysqli_query($con, $paciente);

    $response = array();

    // Calcular la edad
    $fechaNacimiento = new DateTime($fechaNac);
    $hoy = new DateTime();
    $edad = $hoy->diff($fechaNacimiento);

    $edadAnios = $edad->y;
    $edadMeses = $edad->m;
    $edadDias = $edad->d;

    if ($query1->num_rows > 0) {
        $response['status'] = 'error';
        $response['message'] = 'El ID ingresado para paciente ya existe, valide los datos.';
    } else {

        $actualizar = "UPDATE pacientes SET nombre = '$nombre', fechaNacimiento = '$fechaNac', idPaciente = '$idPaciente', cama = '$cama2', edad = '$edadAnios', edadMeses = '$edadMeses',
            edadDias = '$edadDias', diagnosticoMed = '$diagnosticoMed', prescripcionNutri = '$prescripcionNutri', area = '$area', vip = '$vip', observaciones = '$observaciones', 
            controlTamizaje = '$control', statusP ='$acceso' WHERE id='$id'";
        $query = mysqli_query($con, $actualizar);
        if ($query) {
            $response['status'] = 'success';
            $response['message'] = 'Paciente actualizado exitosamente.';
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Ocurrió un error al actualizar el paciente';
        }
    }
}



echo json_encode($response);
