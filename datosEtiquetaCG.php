<?php
require_once('src/Codadry/JY/Epl/ExisteImpresoraWindows.php');
require_once('src/Codadry/JY/Epl/ImprimirEPL.php');

include 'conexion.php';

$tipo = $_POST['selectTipo'];
$paciente = $_POST['selectPacientes'];

function generarEtiquetaDesayuno($dato)
{
    return [
        "PACIENTE: " . $dato['Nombre_Paciente'],
        "FECHA DE NACIMIENTO: " . $dato['Fecha_Nacimiento_Paciente'],
        "ID: " . $dato['idPaciente'],
        "NUM. CAMA: " . $dato['Cama_Paciente'],
        "SERVICIO: " . 'Desayuno',
        "TIPO DE DIETA: " . $dato['Desayuno'],
        "OBSERVACIONES: " . $dato['Observaciones']
    ];
}

function generarEtiquetaColMatutina($dato)
{
    return [
        "PACIENTE: " . $dato['Nombre_Paciente'],
        "FECHA DE NACIMIENTO: " . $dato['Fecha_Nacimiento_Paciente'],
        "ID: " . $dato['idPaciente'],
        "NUM. CAMA: " . $dato['Cama_Paciente'],
        "SERVICIO: " . 'Colacion Matutina',
        "TIPO DE DIETA: " . $dato['Col_Matutina'],
        "OBSERVACIONES: " . $dato['Observaciones']
    ];
}

function generarEtiquetaComida($dato)
{
    return [
        "PACIENTE: " . $dato['Nombre_Paciente'],
        "FECHA DE NACIMIENTO: " . $dato['Fecha_Nacimiento_Paciente'],
        "ID: " . $dato['idPaciente'],
        "NUM. CAMA: " . $dato['Cama_Paciente'],
        "SERVICIO: " . 'Comida',
        "TIPO DE DIETA: " . $dato['Comida'],
        "OBSERVACIONES: " . $dato['Observaciones']
    ];
}

function generarEtiquetaColVespertina($dato)
{
    return [
        "PACIENTE: " . $dato['Nombre_Paciente'],
        "FECHA DE NACIMIENTO: " . $dato['Fecha_Nacimiento_Paciente'],
        "ID: " . $dato['idPaciente'],
        "NUM. CAMA: " . $dato['Cama_Paciente'],
        "SERVICIO: " . 'Colacion Vespertina',
        "TIPO DE DIETA: " . $dato['Col_Vespertina'],
        "OBSERVACIONES: " . $dato['Observaciones']
    ];
}

function generarEtiquetaCena($dato)
{
    return [
        "PACIENTE: " . $dato['Nombre_Paciente'],
        "FECHA DE NACIMIENTO: " . $dato['Fecha_Nacimiento_Paciente'],
        "ID: " . $dato['idPaciente'],
        "NUM. CAMA: " . $dato['Cama_Paciente'],
        "SERVICIO: " . 'Cena',
        "TIPO DE DIETA: " . $dato['Cena'],
        "OBSERVACIONES: " . $dato['Observaciones']
    ];
}

function generarEtiquetaColNocturna($dato)
{
    return [
        "PACIENTE: " . $dato['Nombre_Paciente'],
        "FECHA DE NACIMIENTO: " . $dato['Fecha_Nacimiento_Paciente'],
        "ID: " . $dato['idPaciente'],
        "NUM. CAMA: " . $dato['Cama_Paciente'],
        "SERVICIO: " . 'Colacion Nocturna',
        "TIPO DE DIETA: " . $dato['Col_Nocturna'],
        "OBSERVACIONES: " . $dato['Observaciones']
    ];
}

if ($tipo == 'Desayuno' && $paciente == 'Todos') {

    $query = "SELECT * FROM dietas WHERE area = 'CIRUGÍA GENERAL' AND DATE(Fecha_Hora_Creacion) = CURDATE()";
    $result = mysqli_query($con, $query);
    $datos = mysqli_fetch_all($result, MYSQLI_ASSOC);
    $generarEtiqueta = 'generarEtiquetaDesayuno';
} elseif ($tipo == 'Col_Matutina' && $paciente == 'Todos') {

    $query = "SELECT * FROM dietas WHERE area = 'CIRUGÍA GENERAL' AND DATE(Fecha_Hora_Creacion) = CURDATE()";
    $result = mysqli_query($con, $query);
    $datos = mysqli_fetch_all($result, MYSQLI_ASSOC);
    $generarEtiqueta = 'generarEtiquetaColMatutina';
} elseif ($tipo == 'Comida' && $paciente == 'Todos') {

    $query = "SELECT * FROM dietas WHERE area = 'CIRUGÍA GENERAL' AND DATE(Fecha_Hora_Creacion) = CURDATE()";
    $result = mysqli_query($con, $query);
    $datos = mysqli_fetch_all($result, MYSQLI_ASSOC);
    $generarEtiqueta = 'generarEtiquetaComida';
} elseif ($tipo == 'Col_Vespertina' && $paciente == 'Todos') {

    $query = "SELECT * FROM dietas WHERE area = 'CIRUGÍA GENERAL' AND DATE(Fecha_Hora_Creacion) = CURDATE()";
    $result = mysqli_query($con, $query);
    $datos = mysqli_fetch_all($result, MYSQLI_ASSOC);
    $generarEtiqueta = 'generarEtiquetaColVespertina';
} elseif ($tipo == 'Cena' && $paciente == 'Todos') {

    $query = "SELECT * FROM dietas WHERE area = 'CIRUGÍA GENERAL' AND DATE(Fecha_Hora_Creacion) = CURDATE()";
    $result = mysqli_query($con, $query);
    $datos = mysqli_fetch_all($result, MYSQLI_ASSOC);
    $generarEtiqueta = 'generarEtiquetaCena';
} elseif ($tipo == 'Col_Vespertina' && $paciente == 'Todos') {

    $query = "SELECT * FROM dietas WHERE area = 'CIRUGÍA GENERAL' AND DATE(Fecha_Hora_Creacion) = CURDATE()";
    $result = mysqli_query($con, $query);
    $datos = mysqli_fetch_all($result, MYSQLI_ASSOC);
    $generarEtiqueta = 'generarEtiquetaColNocturna';
} elseif ($tipo == 'Desayuno' && $paciente != 'Todos') {

    $query = "SELECT * FROM dietas WHERE area = 'CIRUGÍA GENERAL' AND Nombre_Paciente = '$paciente' AND DATE(Fecha_Hora_Creacion) = CURDATE()";
    $result = mysqli_query($con, $query);
    $datos = mysqli_fetch_all($result, MYSQLI_ASSOC);
    $generarEtiqueta = 'generarEtiquetaDesayuno';
} elseif ($tipo == 'Col_Matutina' && $paciente != 'Todos') {

    $query = "SELECT * FROM dietas WHERE area = 'CIRUGÍA GENERAL' AND Nombre_Paciente = '$paciente' AND DATE(Fecha_Hora_Creacion) = CURDATE()";
    $result = mysqli_query($con, $query);
    $datos = mysqli_fetch_all($result, MYSQLI_ASSOC);
    $generarEtiqueta = 'generarEtiquetaColMatutina';
} elseif ($tipo == 'Comida' && $paciente != 'Todos') {

    $query = "SELECT * FROM dietas WHERE area = 'CIRUGÍA GENERAL' AND Nombre_Paciente = '$paciente' AND DATE(Fecha_Hora_Creacion) = CURDATE()";
    $result = mysqli_query($con, $query);
    $datos = mysqli_fetch_all($result, MYSQLI_ASSOC);
    $generarEtiqueta = 'generarEtiquetaComida';
} elseif ($tipo == 'Col_Vespertina' && $paciente != 'Todos') {

    $query = "SELECT * FROM dietas WHERE area = 'CIRUGÍA GENERAL' AND Nombre_Paciente = '$paciente' AND DATE(Fecha_Hora_Creacion) = CURDATE()";
    $result = mysqli_query($con, $query);
    $datos = mysqli_fetch_all($result, MYSQLI_ASSOC);
    $generarEtiqueta = 'generarEtiquetaColVespertina';
} elseif ($tipo == 'Cena' && $paciente != 'Todos') {

    $query = "SELECT * FROM dietas WHERE area = 'CIRUGÍA GENERAL' AND Nombre_Paciente = '$paciente' AND DATE(Fecha_Hora_Creacion) = CURDATE()";
    $result = mysqli_query($con, $query);
    $datos = mysqli_fetch_all($result, MYSQLI_ASSOC);
    $generarEtiqueta = 'generarEtiquetaCena';
} elseif ($tipo == 'Col_Nocturna' && $paciente != 'Todos') {

    $query = "SELECT * FROM dietas WHERE area = 'CIRUGÍA GENERAL' AND Nombre_Paciente = '$paciente' AND DATE(Fecha_Hora_Creacion) = CURDATE()";
    $result = mysqli_query($con, $query);
    $datos = mysqli_fetch_all($result, MYSQLI_ASSOC);
    $generarEtiqueta = 'generarEtiquetaColNocturna';
}

$exiteImpresora = new ExisteImpresoraWindows();
$epl = new ImprimirEpl();
$nombre_impresora = "TSC_DA210";

if ($exiteImpresora->verificarImpresora($nombre_impresora, true)) { 
    // Imprimir etiquetas                 
    foreach ($datos as $dato) {
        $etiqueta = $generarEtiqueta($dato);
        $yPos = 15; // Posición inicial en Y
        $eplEtiqueta = '';

        $titulo = "PROVISION DE ALIMENTOS";
        $eplEtiqueta .= $epl->escribirTexto($titulo, 250, $yPos, 4, false, 0, 1, 1);
        $yPos += 40; // Incrementar la posición en Y para la siguiente línea

        foreach ($etiqueta as $linea) {
            $eplEtiqueta .= $epl->escribirTexto($linea, 110, $yPos, 3, false, 0, 1, 1);
            $yPos += 30; // Incrementar la posición en Y para la siguiente línea
        }

        $epl->imprimir($epl->construirEtiqueta($eplEtiqueta, 1), $nombre_impresora, true, false);
    }
} else {
    echo "<h1>No existe Impresora</h1>";
}

echo json_encode($datos);
