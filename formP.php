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


/*if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idPaciente = $_POST['idPaciente'];
    $datos = json_decode($_POST['datos'], true); // Decodificar JSON a array
    $rangoHoraActual = $datos['rangoHoraActual']; // Obtener el rango de hora actual

    // Depuración: Verificar que los datos se están recibiendo
    error_log("Datos recibidos: idPaciente=$idPaciente, datos=" . json_encode($datos) . ", rangoHoraActual=$rangoHoraActual");

    guardarOActualizarDieta($idPaciente, $datos, $rangoHoraActual);
    exit;
}*/
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idPaciente = $_POST['idPaciente'];

    $datosPaciente = isset($_POST['datosPaciente']) ? json_decode($_POST['datosPaciente'], true) : null;
    $datosFamiliar = isset($_POST['datosFamiliar']) ? json_decode($_POST['datosFamiliar'], true) : null;

    if ($datosPaciente) {
        $rangoHoraActual = $datosPaciente['rangoHoraActual'];
        guardarOActualizarDieta($idPaciente, $datosPaciente, $rangoHoraActual);
    }

    if ($datosFamiliar) {
        $rangoHoraActual = $datosFamiliar['rangoHoraActual'];
        guardarOActualizarDietaFamiliar($datosFamiliar['idPacienteF'], $datosFamiliar, $rangoHoraActual);
    }

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
                       VALUES ('', '$idDieta', '$idPaciente', '$fechaHoraActual', '$desayuno', 'PEDIATRÍA', '$usuario')";
            mysqli_query($con, $insert);
            $sqlUpdate = "UPDATE dietas SET Nombre_Paciente = '$nombre', Fecha_Nacimiento_Paciente = '$fechaNacimiento', Cama_Paciente = '$cama', Edad = '$edad', Diag_Med_Nutri = '$diagnosticoMed', Prescripcion = '$prescripcionNutri', Desayuno = '$desayuno', Col_Matutina = '$colMatutina', Comida = '$comida', Col_Vespertina = '$colVespertina', Cena = '$cena', Col_Nocturna = '$colNocturna', Observaciones = '$observaciones', Control_Tamizaje = '$controlTamizaje', privados = '$vip', Fecha_Hora_Creacion = '$fechaHoraActual', Creado_por = '$usuario' WHERE idPaciente = '$idPaciente' AND DATE(Fecha_Hora_Creacion) = '$fechaHoy'";
            mysqli_query($con, $sqlUpdate);
        } elseif ($rangoHoraActual == 'Col_Matutina') {
            // Realizar la consulta correspondiente a Col_Matutina
            $insert = "INSERT INTO updatecol_matutina (ID, idDieta, IdPaciente, fechaHora, Col_Matutina, area, modificadoPor) 
            VALUES ('', '$idDieta', '$idPaciente', '$fechaHoraActual', '$colMatutina', 'PEDIATRÍA', '$usuario')";
            mysqli_query($con, $insert);
            $sqlUpdate = "UPDATE dietas SET Nombre_Paciente = '$nombre', Fecha_Nacimiento_Paciente = '$fechaNacimiento', Cama_Paciente = '$cama', Edad = '$edad', Diag_Med_Nutri = '$diagnosticoMed', Prescripcion = '$prescripcionNutri', Desayuno = '$desayuno', Col_Matutina = '$colMatutina', Comida = '$comida', Col_Vespertina = '$colVespertina', Cena = '$cena', Col_Nocturna = '$colNocturna', Observaciones = '$observaciones', Control_Tamizaje = '$controlTamizaje', privados = '$vip', Fecha_Hora_Creacion = '$fechaHoraActual', Creado_por = '$usuario' WHERE idPaciente = '$idPaciente' AND DATE(Fecha_Hora_Creacion) = '$fechaHoy'";
            mysqli_query($con, $sqlUpdate);
        } elseif ($rangoHoraActual == 'Comida') {
            // Realizar la consulta correspondiente a Comida
            $insert = "INSERT INTO updatecomida (ID, idDieta, IdPaciente, fechaHora, Comida, area, modificadoPor) 
            VALUES ('', '$idDieta', '$idPaciente', '$fechaHoraActual', '$comida', 'PEDIATRÍA', '$usuario')";
            mysqli_query($con, $insert);
            $sqlUpdate = "UPDATE dietas SET Nombre_Paciente = '$nombre', Fecha_Nacimiento_Paciente = '$fechaNacimiento', Cama_Paciente = '$cama', Edad = '$edad', Diag_Med_Nutri = '$diagnosticoMed', Prescripcion = '$prescripcionNutri', Desayuno = '$desayuno', Col_Matutina = '$colMatutina', Comida = '$comida', Col_Vespertina = '$colVespertina', Cena = '$cena', Col_Nocturna = '$colNocturna', Observaciones = '$observaciones', Control_Tamizaje = '$controlTamizaje', privados = '$vip', Fecha_Hora_Creacion = '$fechaHoraActual', Creado_por = '$usuario' WHERE idPaciente = '$idPaciente' AND DATE(Fecha_Hora_Creacion) = '$fechaHoy'";
            mysqli_query($con, $sqlUpdate);
        } elseif ($rangoHoraActual == 'Col_Vespertina') {
            // Realizar la consulta correspondiente a Col_Vespertina
            $insert = "INSERT INTO updatecol_vespertina (ID, idDieta, IdPaciente, fechaHora, Col_Vespertina, area, modificadoPor) 
            VALUES ('', '$idDieta', '$idPaciente', '$fechaHoraActual', '$colVespertina', 'PEDIATRÍA', '$usuario')";
            mysqli_query($con, $insert);
            $sqlUpdate = "UPDATE dietas SET Nombre_Paciente = '$nombre', Fecha_Nacimiento_Paciente = '$fechaNacimiento', Cama_Paciente = '$cama', Edad = '$edad', Diag_Med_Nutri = '$diagnosticoMed', Prescripcion = '$prescripcionNutri', Desayuno = '$desayuno', Col_Matutina = '$colMatutina', Comida = '$comida', Col_Vespertina = '$colVespertina', Cena = '$cena', Col_Nocturna = '$colNocturna', Observaciones = '$observaciones', Control_Tamizaje = '$controlTamizaje', privados = '$vip', Fecha_Hora_Creacion = '$fechaHoraActual', Creado_por = '$usuario' WHERE idPaciente = '$idPaciente' AND DATE(Fecha_Hora_Creacion) = '$fechaHoy'";
            mysqli_query($con, $sqlUpdate);
        } elseif ($rangoHoraActual == 'Cena') {
            // Realizar la consulta correspondiente a Cena
            $insert = "INSERT INTO updatecena (ID, idDieta, IdPaciente, fechaHora, Cena, area, modificadoPor) 
            VALUES ('', '$idDieta', '$idPaciente', '$fechaHoraActual', '$cena', 'PEDIATRÍA', '$usuario')";
            mysqli_query($con, $insert);
            $sqlUpdate = "UPDATE dietas SET Nombre_Paciente = '$nombre', Fecha_Nacimiento_Paciente = '$fechaNacimiento', Cama_Paciente = '$cama', Edad = '$edad', Diag_Med_Nutri = '$diagnosticoMed', Prescripcion = '$prescripcionNutri', Desayuno = '$desayuno', Col_Matutina = '$colMatutina', Comida = '$comida', Col_Vespertina = '$colVespertina', Cena = '$cena', Col_Nocturna = '$colNocturna', Observaciones = '$observaciones', Control_Tamizaje = '$controlTamizaje', privados = '$vip', Fecha_Hora_Creacion = '$fechaHoraActual', Creado_por = '$usuario' WHERE idPaciente = '$idPaciente' AND DATE(Fecha_Hora_Creacion) = '$fechaHoy'";
            mysqli_query($con, $sqlUpdate);
        } elseif ($rangoHoraActual == 'Col_Nocturna') {
            // Realizar la consulta correspondiente a Col_Nocturna
            $insert = "INSERT INTO updatecol_nocturna (ID, idDieta, IdPaciente, fechaHora, Col_Nocturna, area, modificadoPor) 
            VALUES ('', '$idDieta', '$idPaciente', '$fechaHoraActual', '$colNocturna', 'PEDIATRÍA', '$usuario')";
            mysqli_query($con, $insert);
            $sqlUpdate = "UPDATE dietas SET Nombre_Paciente = '$nombre', Fecha_Nacimiento_Paciente = '$fechaNacimiento', Cama_Paciente = '$cama', Edad = '$edad', Diag_Med_Nutri = '$diagnosticoMed', Prescripcion = '$prescripcionNutri', Desayuno = '$desayuno', Col_Matutina = '$colMatutina', Comida = '$comida', Col_Vespertina = '$colVespertina', Cena = '$cena', Col_Nocturna = '$colNocturna', Observaciones = '$observaciones', Control_Tamizaje = '$controlTamizaje', privados = '$vip', Fecha_Hora_Creacion = '$fechaHoraActual', Creado_por = '$usuario' WHERE idPaciente = '$idPaciente' AND DATE(Fecha_Hora_Creacion) = '$fechaHoy'";
            mysqli_query($con, $sqlUpdate);
        }

        //$sqlUpdate = "UPDATE dietas SET Nombre_Paciente = '$nombre', Fecha_Nacimiento_Paciente = '$fechaNacimiento', Cama_Paciente = '$cama', Edad = '$edad', Diag_Med_Nutri = '$diagnosticoMed', Prescripcion = '$prescripcionNutri', Desayuno = '$desayuno', Col_Matutina = '$colMatutina', Comida = '$comida', Col_Vespertina = '$colVespertina', Cena = '$cena', Col_Nocturna = '$colNocturna', Observaciones = '$observaciones', Control_Tamizaje = '$controlTamizaje', privados = '$vip', Fecha_Hora_Creacion = '$fechaHoraActual', Creado_por = '$usuario' WHERE idPaciente = '$idPaciente' AND DATE(Fecha_Hora_Creacion) = '$fechaHoy'";
        //mysqli_query($con, $sqlUpdate);
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

        if ($rangoHoraActual == 'Desayuno') {

            $sqlInsert = "INSERT INTO dietas 
               (ID, Nombre_Paciente, Fecha_Nacimiento_Paciente, idPaciente, Cama_Paciente, Edad, Diag_Med_Nutri, Prescripcion, Desayuno, Col_Matutina, Comida, Col_Vespertina, Cena, Col_Nocturna, Observaciones, Control_Tamizaje, area, privados, Fecha_Hora_Creacion, Creado_por) 
        VALUES ('', '$nombre', '$fechaNacimiento','$idPaciente', '$cama', '$edad', '$diagnosticoMed', '$prescripcionNutri', '$desayuno', '', '', '', '', '', '$observaciones', '$controlTamizaje', 'PEDIATRÍA', '$vip', '$fechaHoraActual', '$usuario')";
            mysqli_query($con, $sqlInsert);

            $fechaHoy1 = date("Y-m-d");
            $obtenerIdDieta = "SELECT * FROM dietas WHERE idPaciente = '$idPaciente' AND DATE(Fecha_Hora_Creacion) = '$fechaHoy1'";
            $queryDietas = mysqli_query($con, $obtenerIdDieta);
            $result = mysqli_fetch_assoc($queryDietas);
            $idDieta = $result['ID'];

            $insert = "INSERT INTO updatedesayunos (ID, idDieta, IdPaciente, fechaHora, Desayuno, area, modificadoPor) 
                       VALUES ('', '$idDieta', '$idPaciente', '$fechaHoraActual', '$desayuno', 'PEDIATRÍA', '$usuario')";
            mysqli_query($con, $insert);
        } elseif ($rangoHoraActual == 'Col_Matutina') {

            $sqlInsert = "INSERT INTO dietas 
               (ID, Nombre_Paciente, Fecha_Nacimiento_Paciente, idPaciente, Cama_Paciente, Edad, Diag_Med_Nutri, Prescripcion, Desayuno, Col_Matutina, Comida, Col_Vespertina, Cena, Col_Nocturna, Observaciones, Control_Tamizaje, area, privados, Fecha_Hora_Creacion, Creado_por) 
        VALUES ('', '$nombre', '$fechaNacimiento','$idPaciente', '$cama', '$edad', '$diagnosticoMed', '$prescripcionNutri', '', '$colMatutina', '', '', '', '', '$observaciones', '$controlTamizaje', 'PEDIATRÍA', '$vip', '$fechaHoraActual', '$usuario')";
            mysqli_query($con, $sqlInsert);

            $fechaHoy1 = date("Y-m-d");
            $obtenerIdDieta = "SELECT * FROM dietas WHERE idPaciente = '$idPaciente' AND DATE(Fecha_Hora_Creacion) = '$fechaHoy1'";
            $queryDietas = mysqli_query($con, $obtenerIdDieta);
            $result = mysqli_fetch_assoc($queryDietas);
            $idDieta = $result['ID'];

            $insert = "INSERT INTO updatecol_matutina (ID, idDieta, IdPaciente, fechaHora, Col_Matutina, area, modificadoPor) 
            VALUES ('', '$idDieta', '$idPaciente', '$fechaHoraActual', '$colMatutina', 'PEDIATRÍA', '$usuario')";
            mysqli_query($con, $insert);
        } elseif ($rangoHoraActual == 'Comida') {

            $sqlInsert = "INSERT INTO dietas 
               (ID, Nombre_Paciente, Fecha_Nacimiento_Paciente, idPaciente, Cama_Paciente, Edad, Diag_Med_Nutri, Prescripcion, Desayuno, Col_Matutina, Comida, Col_Vespertina, Cena, Col_Nocturna, Observaciones, Control_Tamizaje, area, privados, Fecha_Hora_Creacion, Creado_por) 
        VALUES ('', '$nombre', '$fechaNacimiento','$idPaciente', '$cama', '$edad', '$diagnosticoMed', '$prescripcionNutri', '', '', '$comida', '', '', '', '$observaciones', '$controlTamizaje', 'PEDIATRÍA', '$vip', '$fechaHoraActual', '$usuario')";
            mysqli_query($con, $sqlInsert);

            $fechaHoy1 = date("Y-m-d");
            $obtenerIdDieta = "SELECT * FROM dietas WHERE idPaciente = '$idPaciente' AND DATE(Fecha_Hora_Creacion) = '$fechaHoy1'";
            $queryDietas = mysqli_query($con, $obtenerIdDieta);
            $result = mysqli_fetch_assoc($queryDietas);
            $idDieta = $result['ID'];

            $insert = "INSERT INTO updatecomida (ID, idDieta, IdPaciente, fechaHora, Comida, area, modificadoPor) 
            VALUES ('', '$idDieta', '$idPaciente', '$fechaHoraActual', '$comida', 'PEDIATRÍA', '$usuario')";
            mysqli_query($con, $insert);
        } elseif ($rangoHoraActual == 'Col_Vespertina') {

            $sqlInsert = "INSERT INTO dietas 
               (ID, Nombre_Paciente, Fecha_Nacimiento_Paciente, idPaciente, Cama_Paciente, Edad, Diag_Med_Nutri, Prescripcion, Desayuno, Col_Matutina, Comida, Col_Vespertina, Cena, Col_Nocturna, Observaciones, Control_Tamizaje, area, privados, Fecha_Hora_Creacion, Creado_por) 
        VALUES ('', '$nombre', '$fechaNacimiento','$idPaciente', '$cama', '$edad', '$diagnosticoMed', '$prescripcionNutri', '', '', '', '$colVespertina', '', '', '$observaciones', '$controlTamizaje', 'PEDIATRÍA', '$vip', '$fechaHoraActual', '$usuario')";
            mysqli_query($con, $sqlInsert);

            $fechaHoy1 = date("Y-m-d");
            $obtenerIdDieta = "SELECT * FROM dietas WHERE idPaciente = '$idPaciente' AND DATE(Fecha_Hora_Creacion) = '$fechaHoy1'";
            $queryDietas = mysqli_query($con, $obtenerIdDieta);
            $result = mysqli_fetch_assoc($queryDietas);
            $idDieta = $result['ID'];

            $insert = "INSERT INTO updatecol_vespertina (ID, idDieta, IdPaciente, fechaHora, Col_Vespertina, area, modificadoPor) 
            VALUES ('', '$idDieta', '$idPaciente', '$fechaHoraActual', '$colVespertina', 'PEDIATRÍA', '$usuario')";
            mysqli_query($con, $insert);
        } elseif ($rangoHoraActual == 'Cena') {

            $sqlInsert = "INSERT INTO dietas 
               (ID, Nombre_Paciente, Fecha_Nacimiento_Paciente, idPaciente, Cama_Paciente, Edad, Diag_Med_Nutri, Prescripcion, Desayuno, Col_Matutina, Comida, Col_Vespertina, Cena, Col_Nocturna, Observaciones, Control_Tamizaje, area, privados, Fecha_Hora_Creacion, Creado_por) 
        VALUES ('', '$nombre', '$fechaNacimiento','$idPaciente', '$cama', '$edad', '$diagnosticoMed', '$prescripcionNutri', '', '', '', '', '$cena', '', '$observaciones', '$controlTamizaje', 'PEDIATRÍA', '$vip', '$fechaHoraActual', '$usuario')";
            mysqli_query($con, $sqlInsert);

            $fechaHoy1 = date("Y-m-d");
            $obtenerIdDieta = "SELECT * FROM dietas WHERE idPaciente = '$idPaciente' AND DATE(Fecha_Hora_Creacion) = '$fechaHoy1'";
            $queryDietas = mysqli_query($con, $obtenerIdDieta);
            $result = mysqli_fetch_assoc($queryDietas);
            $idDieta = $result['ID'];

            $insert = "INSERT INTO updatecena (ID, idDieta, IdPaciente, fechaHora, Cena, area, modificadoPor) 
            VALUES ('', '$idDieta', '$idPaciente', '$fechaHoraActual', '$cena', 'PEDIATRÍA', '$usuario')";
            mysqli_query($con, $insert);
        } elseif ($rangoHoraActual == 'Col_Nocturna') {
            $sqlInsert = "INSERT INTO dietas 
               (ID, Nombre_Paciente, Fecha_Nacimiento_Paciente, idPaciente, Cama_Paciente, Edad, Diag_Med_Nutri, Prescripcion, Desayuno, Col_Matutina, Comida, Col_Vespertina, Cena, Col_Nocturna, Observaciones, Control_Tamizaje, area, privados, Fecha_Hora_Creacion, Creado_por) 
        VALUES ('', '$nombre', '$fechaNacimiento','$idPaciente', '$cama', '$edad', '$diagnosticoMed', '$prescripcionNutri', '', '', '', '', '', '$colNocturna', '$observaciones', '$controlTamizaje', 'PEDIATRÍA', '$vip', '$fechaHoraActual', '$usuario')";
            mysqli_query($con, $sqlInsert);

            $fechaHoy1 = date("Y-m-d");
            $obtenerIdDieta = "SELECT * FROM dietas WHERE idPaciente = '$idPaciente' AND DATE(Fecha_Hora_Creacion) = '$fechaHoy1'";
            $queryDietas = mysqli_query($con, $obtenerIdDieta);
            $result = mysqli_fetch_assoc($queryDietas);
            $idDieta = $result['ID'];

            $insert = "INSERT INTO updatecol_nocturna (ID, idDieta, IdPaciente, fechaHora, Col_Nocturna, area, modificadoPor) 
            VALUES ('', '$idDieta', '$idPaciente', '$fechaHoraActual', '$colNocturna', 'PEDIATRÍA', '$usuario')";
            mysqli_query($con, $insert);
        }
    }
}



function guardarOActualizarDietaFamiliar($idPacienteF, $datosFamiliar, $rangoHoraActual)
{
    global $con;
    $fechaHoy = date("Y-m-d");
    $fechaHoraActual = date("Y-m-d H:i:s");

    // Verificar si ya existe un registro para hoy
    $sql = "SELECT * FROM dietas WHERE idPaciente = '$idPacienteF' AND DATE(Fecha_Hora_Creacion) = '$fechaHoy'";
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


    //si se encontro un registro
    if ($query->num_rows > 0) {
        // Actualizar el registro existente
        $idFamiliar = $datosFamiliar['idPacienteF'];
        $nombreF = $datosFamiliar['nombreFamiliar'];
        $camaF = $datosFamiliar['camaF'];
        $usuarioF = $datosFamiliar['usuarioF'];
        $desayunoF = $datosFamiliar['DesayunoF'];
        $colMatutinaF = $datosFamiliar['Col_MatutinaF'];
        $comidaF = $datosFamiliar['ComidaF'];
        $colVespertinaF = $datosFamiliar['Col_VespertinaF'];
        $cenaF = $datosFamiliar['CenaF'];
        $colNocturnaF = $datosFamiliar['Col_NocturnaF'];

        // Dependiendo del rango de hora actual, realizar la consulta correspondiente
        if ($rangoHoraActual == 'Desayuno') {
            $insert = "INSERT INTO updatedesayunos (ID, idDieta, IdPaciente, fechaHora, Desayuno, area, modificadoPor) 
                       VALUES ('', '$idDieta', '$idPacienteF', '$fechaHoraActual', '$desayunoF', 'PEDIATRÍA', '$usuarioF')";
            mysqli_query($con, $insert);
            $sqlUpdate = "UPDATE dietas SET Nombre_Paciente = '$nombreF', Fecha_Nacimiento_Paciente = '', Cama_Paciente = '$camaF', Edad = '', Diag_Med_Nutri = '', Prescripcion = '', Desayuno = '$desayunoF', Col_Matutina = '$colMatutinaF', Comida = '$comidaF', Col_Vespertina = '$colVespertinaF', Cena = '$cenaF', Col_Nocturna = '$colNocturnaF', Observaciones = '', Control_Tamizaje = '', privados = '', Fecha_Hora_Creacion = '$fechaHoraActual', Creado_por = '$usuarioF' WHERE idPaciente = '$idFamiliar' AND DATE(Fecha_Hora_Creacion) = '$fechaHoy'";
            mysqli_query($con, $sqlUpdate);
        } elseif ($rangoHoraActual == 'Col_Matutina') {
            // Realizar la consulta correspondiente a Col_Matutina
            $insert = "INSERT INTO updatecol_matutina (ID, idDieta, IdPaciente, fechaHora, Col_Matutina, area, modificadoPor) 
            VALUES ('', '$idDieta', '$idPacienteF', '$fechaHoraActual', '$colMatutinaF', 'PEDIATRÍA', '$usuarioF')";
            mysqli_query($con, $insert);
            $sqlUpdate = "UPDATE dietas SET Nombre_Paciente = '$nombreF', Fecha_Nacimiento_Paciente = '', Cama_Paciente = '$camaF', Edad = '', Diag_Med_Nutri = '', Prescripcion = '', Desayuno = '$desayunoF', Col_Matutina = '$colMatutinaF', Comida = '$comidaF', Col_Vespertina = '$colVespertinaF', Cena = '$cenaF', Col_Nocturna = '$colNocturnaF', Observaciones = '', Control_Tamizaje = '', privados = '', Fecha_Hora_Creacion = '$fechaHoraActual', Creado_por = '$usuarioF' WHERE idPaciente = '$idFamiliar' AND DATE(Fecha_Hora_Creacion) = '$fechaHoy'";
            mysqli_query($con, $sqlUpdate);
        } elseif ($rangoHoraActual == 'Comida') {
            // Realizar la consulta correspondiente a Comida
            $insert = "INSERT INTO updatecomida (ID, idDieta, IdPaciente, fechaHora, Comida, area, modificadoPor) 
                VALUES ('', '$idDieta', '$idPacienteF', '$fechaHoraActual', '$comidaF', 'PEDIATRÍA', '$usuarioF')";
            mysqli_query($con, $insert);
            $sqlUpdate = "UPDATE dietas SET Nombre_Paciente = '$nombreF', Fecha_Nacimiento_Paciente = '', Cama_Paciente = '$camaF', Edad = '', Diag_Med_Nutri = '', Prescripcion = '', Desayuno = '$desayunoF', Col_Matutina = '$colMatutinaF', Comida = '$comidaF', Col_Vespertina = '$colVespertinaF', Cena = '$cenaF', Col_Nocturna = '$colNocturnaF', Observaciones = '', Control_Tamizaje = '', privados = '', Fecha_Hora_Creacion = '$fechaHoraActual', Creado_por = '$usuarioF' WHERE idPaciente = '$idFamiliar' AND DATE(Fecha_Hora_Creacion) = '$fechaHoy'";
            mysqli_query($con, $sqlUpdate);
        } elseif ($rangoHoraActual == 'Col_Vespertina') {
            // Realizar la consulta correspondiente a Col_Vespertina
            $insert = "INSERT INTO updatecol_vespertina (ID, idDieta, IdPaciente, fechaHora, Col_Vespertina, area, modificadoPor) 
                VALUES ('', '$idDieta', '$idPacienteF', '$fechaHoraActual', '$colVespertinaF', 'PEDIATRÍA', '$usuarioF')";
            mysqli_query($con, $insert);
            $sqlUpdate = "UPDATE dietas SET Nombre_Paciente = '$nombreF', Fecha_Nacimiento_Paciente = '', Cama_Paciente = '$camaF', Edad = '', Diag_Med_Nutri = '', Prescripcion = '', Desayuno = '$desayunoF', Col_Matutina = '$colMatutinaF', Comida = '$comidaF', Col_Vespertina = '$colVespertinaF', Cena = '$cenaF', Col_Nocturna = '$colNocturnaF', Observaciones = '', Control_Tamizaje = '', privados = '', Fecha_Hora_Creacion = '$fechaHoraActual', Creado_por = '$usuarioF' WHERE idPaciente = '$idFamiliar' AND DATE(Fecha_Hora_Creacion) = '$fechaHoy'";
            mysqli_query($con, $sqlUpdate);
        } elseif ($rangoHoraActual == 'Cena') {
            // Realizar la consulta correspondiente a Cena
            $insert = "INSERT INTO updatecena (ID, idDieta, IdPaciente, fechaHora, Cena, area, modificadoPor) 
                VALUES ('', '$idDieta', '$idPacienteF', '$fechaHoraActual', '$cenaF', 'PEDIATRÍA', '$usuarioF')";
            mysqli_query($con, $insert);
            $sqlUpdate = "UPDATE dietas SET Nombre_Paciente = '$nombreF', Fecha_Nacimiento_Paciente = '', Cama_Paciente = '$camaF', Edad = '', Diag_Med_Nutri = '', Prescripcion = '', Desayuno = '$desayunoF', Col_Matutina = '$colMatutinaF', Comida = '$comidaF', Col_Vespertina = '$colVespertinaF', Cena = '$cenaF', Col_Nocturna = '$colNocturnaF', Observaciones = '', Control_Tamizaje = '', privados = '', Fecha_Hora_Creacion = '$fechaHoraActual', Creado_por = '$usuarioF' WHERE idPaciente = '$idFamiliar' AND DATE(Fecha_Hora_Creacion) = '$fechaHoy'";
            mysqli_query($con, $sqlUpdate);
        } elseif ($rangoHoraActual == 'Col_Nocturna') {
            // Realizar la consulta correspondiente a Col_Nocturna
            $insert = "INSERT INTO updatecol_nocturna (ID, idDieta, IdPaciente, fechaHora, Col_Nocturna, area, modificadoPor) 
                VALUES ('', '$idDieta', '$idPacienteF', '$fechaHoraActual', '$colNocturnaF', 'PEDIATRÍA', '$usuarioF')";
            mysqli_query($con, $insert);
            $sqlUpdate = "UPDATE dietas SET Nombre_Paciente = '$nombreF', Fecha_Nacimiento_Paciente = '', Cama_Paciente = '$camaF', Edad = '', Diag_Med_Nutri = '', Prescripcion = '', Desayuno = '$desayunoF', Col_Matutina = '$colMatutinaF', Comida = '$comidaF', Col_Vespertina = '$colVespertinaF', Cena = '$cenaF', Col_Nocturna = '$colNocturnaF', Observaciones = '', Control_Tamizaje = '', privados = '', Fecha_Hora_Creacion = '$fechaHoraActual', Creado_por = '$usuarioF' WHERE idPaciente = '$idFamiliar' AND DATE(Fecha_Hora_Creacion) = '$fechaHoy'";
            mysqli_query($con, $sqlUpdate);
        }
        //si no se encontro ningun registro
    } else {
        // Insertar un nuevo registro
        $idFamiliar = $datosFamiliar['idPacienteF'];
        $nombreF = $datosFamiliar['nombreFamiliar'];
        $camaF = $datosFamiliar['camaF'];
        $usuarioF = $datosFamiliar['usuarioF'];
        $desayunoF = $datosFamiliar['DesayunoF'];
        $colMatutinaF = $datosFamiliar['Col_MatutinaF'];
        $comidaF = $datosFamiliar['ComidaF'];
        $colVespertinaF = $datosFamiliar['Col_VespertinaF'];
        $cenaF = $datosFamiliar['CenaF'];
        $colNocturnaF = $datosFamiliar['Col_NocturnaF'];
        if ($rangoHoraActual == 'Desayuno') {

            $sqlInsert = "INSERT INTO dietas 
                (ID, Nombre_Paciente, Fecha_Nacimiento_Paciente, idPaciente, Cama_Paciente, Edad, Diag_Med_Nutri, Prescripcion, Desayuno, Col_Matutina, Comida, Col_Vespertina, Cena, Col_Nocturna, Observaciones, Control_Tamizaje, area, privados, Fecha_Hora_Creacion, Creado_por) 
            VALUES ('', '$nombreF', '','$idPacienteF', '$camaF', '', '', '', '$desayunoF', '', '', '', '', '', '', '', 'PEDIATRÍA', '', '$fechaHoraActual', '$usuarioF')";
            mysqli_query($con, $sqlInsert);

            $fechaHoy1 = date("Y-m-d");
            $obtenerIdDieta = "SELECT * FROM dietas WHERE idPaciente = '$idPacienteF' AND DATE(Fecha_Hora_Creacion) = '$fechaHoy1'";
            $queryDietas = mysqli_query($con, $obtenerIdDieta);
            $result = mysqli_fetch_assoc($queryDietas);
            $idDieta = $result['ID'];

            $insert = "INSERT INTO updatedesayunos (ID, idDieta, IdPaciente, fechaHora, Desayuno, area, modificadoPor) 
                        VALUES ('', '$idDieta', '$idPacienteF', '$fechaHoraActual', '$desayunoF', 'PEDIATRÍA', '$usuarioF')";
            mysqli_query($con, $insert);
        } elseif ($rangoHoraActual == 'Col_Matutina') {

            $sqlInsert = "INSERT INTO dietas 
                (ID, Nombre_Paciente, Fecha_Nacimiento_Paciente, idPaciente, Cama_Paciente, Edad, Diag_Med_Nutri, Prescripcion, Desayuno, Col_Matutina, Comida, Col_Vespertina, Cena, Col_Nocturna, Observaciones, Control_Tamizaje, area, privados, Fecha_Hora_Creacion, Creado_por) 
            VALUES ('', '$nombreF', '','$idPacienteF', '$camaF', '', '', '', '', '$colMatutinaF', '', '', '', '', '', '', 'PEDIATRÍA', '', '$fechaHoraActual', '$usuarioF')";
            mysqli_query($con, $sqlInsert);

            $fechaHoy1 = date("Y-m-d");
            $obtenerIdDieta = "SELECT * FROM dietas WHERE idPaciente = '$idPacienteF' AND DATE(Fecha_Hora_Creacion) = '$fechaHoy1'";
            $queryDietas = mysqli_query($con, $obtenerIdDieta);
            $result = mysqli_fetch_assoc($queryDietas);
            $idDieta = $result['ID'];

            $insert = "INSERT INTO updatecol_matutina (ID, idDieta, IdPaciente, fechaHora, Col_Matutina, area, modificadoPor) 
                VALUES ('', '$idDieta', '$idPacienteF', '$fechaHoraActual', '$colMatutinaF', 'PEDIATRÍA', '$usuarioF')";
            mysqli_query($con, $insert);
        } elseif ($rangoHoraActual == 'Comida') {

            $sqlInsert = "INSERT INTO dietas 
               (ID, Nombre_Paciente, Fecha_Nacimiento_Paciente, idPaciente, Cama_Paciente, Edad, Diag_Med_Nutri, Prescripcion, Desayuno, Col_Matutina, Comida, Col_Vespertina, Cena, Col_Nocturna, Observaciones, Control_Tamizaje, area, privados, Fecha_Hora_Creacion, Creado_por) 
        VALUES ('', '$nombreF', '','$idPacienteF', '', '', '', '', '', '', '$comidaF', '', '', '', '', '', 'PEDIATRÍA', '', '$fechaHoraActual', '$usuarioF')";
            mysqli_query($con, $sqlInsert);

            $fechaHoy1 = date("Y-m-d");
            $obtenerIdDieta = "SELECT * FROM dietas WHERE idPaciente = '$idPacienteF' AND DATE(Fecha_Hora_Creacion) = '$fechaHoy1'";
            $queryDietas = mysqli_query($con, $obtenerIdDieta);
            $result = mysqli_fetch_assoc($queryDietas);
            $idDieta = $result['ID'];

            $insert = "INSERT INTO updatecomida (ID, idDieta, IdPaciente, fechaHora, Comida, area, modificadoPor) 
            VALUES ('', '$idDieta', '$idPacienteF', '$fechaHoraActual', '$comidaF', 'PEDIATRÍA', '$usuarioF')";
            mysqli_query($con, $insert);
        } elseif ($rangoHoraActual == 'Col_Vespertina') {

            $sqlInsert = "INSERT INTO dietas 
               (ID, Nombre_Paciente, Fecha_Nacimiento_Paciente, idPaciente, Cama_Paciente, Edad, Diag_Med_Nutri, Prescripcion, Desayuno, Col_Matutina, Comida, Col_Vespertina, Cena, Col_Nocturna, Observaciones, Control_Tamizaje, area, privados, Fecha_Hora_Creacion, Creado_por) 
        VALUES ('', '$nombreF', '','$idPacienteF', '$camaF', '', '', '', '', '', '', '$colVespertinaF', '', '', '', '', 'PEDIATRÍA', '', '$fechaHoraActual', '$usuarioF')";
            mysqli_query($con, $sqlInsert);

            $fechaHoy1 = date("Y-m-d");
            $obtenerIdDieta = "SELECT * FROM dietas WHERE idPaciente = '$idPacienteF' AND DATE(Fecha_Hora_Creacion) = '$fechaHoy1'";
            $queryDietas = mysqli_query($con, $obtenerIdDieta);
            $result = mysqli_fetch_assoc($queryDietas);
            $idDieta = $result['ID'];

            $insert = "INSERT INTO updatecol_vespertina (ID, idDieta, IdPaciente, fechaHora, Col_Vespertina, area, modificadoPor) 
            VALUES ('', '$idDieta', '$idPacienteF', '$fechaHoraActual', '$colVespertinaF', 'PEDIATRÍA', '$usuarioF')";
            mysqli_query($con, $insert);
        } elseif ($rangoHoraActual == 'Cena') {

            $sqlInsert = "INSERT INTO dietas 
               (ID, Nombre_Paciente, Fecha_Nacimiento_Paciente, idPaciente, Cama_Paciente, Edad, Diag_Med_Nutri, Prescripcion, Desayuno, Col_Matutina, Comida, Col_Vespertina, Cena, Col_Nocturna, Observaciones, Control_Tamizaje, area, privados, Fecha_Hora_Creacion, Creado_por) 
        VALUES ('', '$nombreF', '','$idPacienteF', '$camaF', '', '', '', '', '', '', '', '$cenaF', '', '', '', 'PEDIATRÍA', '', '$fechaHoraActual', '$usuarioF')";
            mysqli_query($con, $sqlInsert);

            $fechaHoy1 = date("Y-m-d");
            $obtenerIdDieta = "SELECT * FROM dietas WHERE idPaciente = '$idPacienteF' AND DATE(Fecha_Hora_Creacion) = '$fechaHoy1'";
            $queryDietas = mysqli_query($con, $obtenerIdDieta);
            $result = mysqli_fetch_assoc($queryDietas);
            $idDieta = $result['ID'];

            $insert = "INSERT INTO updatecena (ID, idDieta, IdPaciente, fechaHora, Cena, area, modificadoPor) 
            VALUES ('', '$idDieta', '$idPacienteF', '$fechaHoraActual', '$cenaF', 'PEDIATRÍA', '$usuarioF')";
            mysqli_query($con, $insert);
        } elseif ($rangoHoraActual == 'Col_Nocturna') {
            $sqlInsert = "INSERT INTO dietas 
               (ID, Nombre_Paciente, Fecha_Nacimiento_Paciente, idPaciente, Cama_Paciente, Edad, Diag_Med_Nutri, Prescripcion, Desayuno, Col_Matutina, Comida, Col_Vespertina, Cena, Col_Nocturna, Observaciones, Control_Tamizaje, area, privados, Fecha_Hora_Creacion, Creado_por) 
        VALUES ('', '$nombreF', '','$idPacienteF', '$camaF', '', '', '', '', '', '', '', '', '$colNocturnaF', '', '', 'PEDIATRÍA', '', '$fechaHoraActual', '$usuarioF')";
            mysqli_query($con, $sqlInsert);

            $fechaHoy1 = date("Y-m-d");
            $obtenerIdDieta = "SELECT * FROM dietas WHERE idPaciente = '$idPacienteF' AND DATE(Fecha_Hora_Creacion) = '$fechaHoy1'";
            $queryDietas = mysqli_query($con, $obtenerIdDieta);
            $result = mysqli_fetch_assoc($queryDietas);
            $idDieta = $result['ID'];

            $insert = "INSERT INTO updatecol_nocturna (ID, idDieta, IdPaciente, fechaHora, Col_Nocturna, area, modificadoPor) 
            VALUES ('', '$idDieta', '$idPacienteF', '$fechaHoraActual', '$colNocturnaF', 'PEDIATRÍA', '$usuarioF')";
            mysqli_query($con, $insert);
        }
    }
}





/*if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idPaciente = $_POST['idPaciente'];
    $datos = json_decode($_POST['datos'], true); // Decodificar JSON a array

    // Depuración: Verificar que los datos se están recibiendo
    error_log("Datos recibidos: idPaciente=$idPaciente, datos=" . json_encode($datos));

    guardarOActualizarDieta($idPaciente, $datos, $rangoHoraActual);
    exit;
}*/

$fechaHoraActual1 = date("Y-m-d");
$sql = "SELECT p.*, d.*, p.idPaciente AS pacienteID, d.idPaciente AS dietaID
FROM pacientes p
LEFT JOIN (
    SELECT d1.*
    FROM dietas d1
    INNER JOIN (
        SELECT MAX(ID) AS ID, idPaciente
        FROM dietas
        WHERE area = 'PEDIATRÍA' AND DATE(Fecha_Hora_Creacion) = '$fechaHoraActual1'
        GROUP BY idPaciente
    ) d2 ON d1.ID = d2.ID
) d ON p.idPaciente = d.idPaciente
WHERE p.statusP = 'Activo' AND p.area = 'PEDIATRÍA'
ORDER BY 
CASE 
WHEN p.cama LIKE 'AE-%' THEN 1
ELSE 2
END,
CAST(
CASE 
WHEN p.cama LIKE '%-%' THEN SUBSTRING_INDEX(p.cama, '-', -1)
ELSE p.cama
END AS UNSIGNED
)";

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
    <style>
        /* Permitir que los textarea se redimensionen libremente */
        .table-textarea {
            resize: both;
            /* Permite redimensionar en ambas direcciones */
            overflow: auto;
            /* Muestra scroll si es necesario */
            min-width: 100px;
            /* Evita que se haga muy pequeño */
            min-height: 50px;
            /* Altura mínima */
            max-width: none;
            /* Permite crecer más allá del ancho del contenedor */
            max-height: none;
            /* Permite crecer verticalmente sin límite */
            display: block;
            /* Asegura que no esté restringido por el layout inline */
            width: 100%;
            /* Opcional: ajusta al ancho de la celda inicialmente */
        }

        /* Asegurar que el contenedor no limite el crecimiento horizontal */
        .flex-grow-1 {
            overflow-x: visible;
        }

        /* Asegurar que la tabla no limite el ancho de las celdas */
        table {
            table-layout: auto;
            width: 100%;
        }
    </style>
    <script src="js/color-modes.js"></script>
    <link rel="stylesheet" href="node_modules/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="node_modules/sweetalert2/dist/sweetalert2.min.css">
    <script src="node_modules/sweetalert2/dist/sweetalert2.min.js"></script>
    <script src="js/color-modes.js"></script>
    <link rel="stylesheet" href="node_modules/bootstrap-icons/font/bootstrap-icons.min.css">

</head>

<body>
    <div class="d-flex h-100">
        <?php include 'sidebar.php'; ?>

        <!-- ========== MAIN CONTENT ========== -->
        <main class="main-content d-flex flex-column vh-100">

            <div class="encabezado-fijo bg-white border-bottom px-4 pt-3 pb-2" style="position: sticky; top: 0; z-index: 10;">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h3 class="h3">SOLICITUD DE ORDEN PEDIATRÍA</h3>
                    <div class="btn-toolbar mb-2 mb-md-0">


                        <form method="post" action="pdfP.php" target="_blank">

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
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="location.href = 'formP.php';">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-clockwise" viewBox="0 0 16 16">
                                        <path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2z" />
                                        <path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466" />
                                    </svg>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>


            <div class="flex-grow-1 overflow-auto px-4 pb-4">
                <div class="row g-12">
                    <div class="mx-auto col-md-12 col-lg-12 ">
                        <h1 class="mb-3"></h1>
                        <div class="row g-3">
                            <div id="contenedorScroll" class="table-responsive" style="max-height: 830px; overflow-y: auto;">
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
                                    <?php
                                    include 'conexion.php';
                                    date_default_timezone_set('America/Mexico_City');
                                    // Obtener la fecha actual
                                    $fechaHoy = date("Y-m-d");

                                    // Crear un arreglo para guardar los familiares con registro
                                    $familiaresConRegistro = [];

                                    // Primero, recorremos los datos para verificar si el familiar tiene registro
                                    foreach ($datosCombinados as $dataRow) {
                                        $idFamiliar = 'F-' . $dataRow["pacienteID"];
                                        $sqlFamiliar = "SELECT 1 FROM dietas WHERE idPaciente = '$idFamiliar' AND DATE(Fecha_Hora_Creacion) = '$fechaHoy' LIMIT 1";
                                        $resultFamiliar = mysqli_query($con, $sqlFamiliar);

                                        if ($resultFamiliar && mysqli_num_rows($resultFamiliar) > 0) {
                                            $familiaresConRegistro[$dataRow["pacienteID"]] = true;
                                        }
                                    }
                                    ?>
                                    <tbody>
                                        <?php foreach ($datosCombinados as $dataRow) {
                                            $pacienteID = $dataRow["pacienteID"];
                                            $mostrarIconoFamiliar = isset($familiaresConRegistro[$pacienteID]); ?>
                                            <tr>
                                                <td scope="col"><input type="checkbox" class="form-check-input custom-checkbox" style="width: 20px; height: 20px;" onclick="toggleEdit(this)">
                                                    <svg class="newColum d-none" data-id="<?php echo $dataRow["pacienteID"]; ?>" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-plus-circle-fill" viewBox="0 0 16 16">
                                                        <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M8.5 4.5a.5.5 0 0 0-1 0v3h-3a.5.5 0 0 0 0 1h3v3a.5.5 0 0 0 1 0v-3h3a.5.5 0 0 0 0-1h-3z" />
                                                    </svg>

                                                    <!-- Icono si ya existe registro del familiar -->
                                                    <?php if ($mostrarIconoFamiliar): ?>
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="green" class="bi bi-person-fill-check mt-1" viewBox="0 0 16 16">
                                                            <path d="M12.5 16a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7m1.679-4.493-1.335 2.226a.75.75 0 0 1-1.174.144l-.774-.773a.5.5 0 0 1 .708-.708l.547.548 1.17-1.951a.5.5 0 1 1 .858.514M11 5a3 3 0 1 1-6 0 3 3 0 0 1 6 0" />
                                                            <path d="M2 13c0 1 1 1 1 1h5.256A4.5 4.5 0 0 1 8 12.5a4.5 4.5 0 0 1 1.544-3.393Q8.844 9.002 8 9c-5 0-6 3-6 4" />
                                                        </svg>
                                                    <?php endif; ?>
                                                </td>
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
                                    <input type="text" class="form-control fs-4" id="nombrePaciente" name="nombrePaciente" style="text-transform: uppercase;" style="text-transform: uppercase;" required>
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
                                        // Consulta para obtener las camas disponibles
                                        $sqlCama = "
                                        SELECT numero 
                                        FROM camas 
                                        WHERE area = 'PEDIATRÍA' 
                                        AND numero NOT IN (
                                            SELECT cama 
                                            FROM pacientes 
                                            WHERE area = 'PEDIATRÍA' 
                                            AND statusP = 'Activo'
                                        )
                                    ";
                                        $queryCama = mysqli_query($con, $sqlCama);

                                        if (mysqli_num_rows($queryCama) > 0) {
                                            echo '<option value="">Seleccione una cama</option>';
                                            while ($row = mysqli_fetch_assoc($queryCama)) {
                                                echo '<option value="' . $row['numero'] . '">' . $row['numero'] . '</option>';
                                            }
                                        } else {
                                            echo '<option value="">No hay camas disponibles</option>';
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
                                    <textarea class="form-control fs-5" aria-label="With textarea" id="diagMed" style="text-transform: uppercase;" required></textarea>
                                </div>

                                <div class="form-group col-md-9">
                                    <label class="fs-5">Prescripción Nutricional</label>
                                    <textarea class="form-control fs-5" aria-label="With textarea" id="presNutri" style="text-transform: uppercase;" required></textarea>
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
                                    <textarea class="form-control fs-5" aria-label="With textarea" id="observaciones" style="text-transform: uppercase;" required></textarea>
                                </div>

                                <div class="form-group col-md-9">
                                    <label class="fs-5">Control de Tamizaje</label>
                                    <textarea class="form-control fs-5" aria-label="With textarea" id="controlTami" style="text-transform: uppercase;" required></textarea>
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
    <script src="js/relojP.js"></script>
    <script src="js/seguridad.js"></script>

    <?php include 'footer.php'; ?>
</body>

</html>