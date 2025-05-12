<?php
include 'conexion.php'; // Asegúrate de incluir tu archivo de conexión a la base de datos
date_default_timezone_set("America/Mexico_City");
$fechaHoraActual = date("Y-m-d H:i:s");
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $idPaciente = $_POST['idPaciente'];
    $nota = $_POST['nota'];
    $usuario = $_POST['usuario'];
    $fechaHora = $_POST['fechaHora'];

    $query = "INSERT INTO notas (idDieta, Creada_por, Nota, Fecha_Creacion) VALUES ('$idPaciente', '$usuario', '$nota', '$fechaHoraActual')";
    if (mysqli_query($con, $query)) {
        echo "Nota guardada exitosamente";
    } else {
        echo "Error: " . $query . "<br>" . mysqli_error($con);
    }

    mysqli_close($con);
}
?>
