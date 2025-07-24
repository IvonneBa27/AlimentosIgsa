<?php
include 'conexion.php';
date_default_timezone_set('America/Mexico_City');
if (!isset($_GET['idPaciente'])) {
    echo json_encode(['error' => 'Falta el parámetro idPaciente']);
    exit;
}

$idPaciente = $_GET['idPaciente'];
$fechaHoy = isset($_GET['fecha']) ? $_GET['fecha'] : date("Y-m-d");

$numeroPaciente = str_replace('RN-', '', $idPaciente);

$sql = "SELECT * FROM rnginecologia 
        WHERE idPaciente LIKE 'RN%-$numeroPaciente' 
        AND DATE(fecha_solicitud) = '$fechaHoy' 
        ORDER BY CAST(SUBSTRING(idPaciente, 3, 1) AS UNSIGNED) ASC 
        LIMIT 3";

//echo $sql;
$result = mysqli_query($con, $sql);

$familiares = [];
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $familiares[] = $row;
    }
}

echo json_encode($familiares);
?>

