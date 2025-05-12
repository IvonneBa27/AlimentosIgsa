<?php
include('conexion.php');
date_default_timezone_set("America/Mexico_City");
$fechaHoraActual = date("Y-m-d");
$dieta = $_POST['id'];
//$consulta1 = "SELECT * FROM notas WHERE idDieta = $dieta";
$consulta1 = "SELECT * FROM notas WHERE idDieta = $dieta AND DATE(Fecha_Creacion) = '$fechaHoraActual'";
$sql1 = mysqli_query($con, $consulta1);
$data = array();

if ($sql1->num_rows > 0) {
    while($row = $sql1->fetch_assoc()) {
        $data[] = $row;
    }
} 

echo json_encode($data);
mysqli_close($con);
?>