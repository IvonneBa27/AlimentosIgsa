<?php
include('conexion.php');

/*$sql = "SELECT empresa, COUNT(*) AS cantidad_usuarios
        FROM comensales
        WHERE empresa IN ('IGSA', 'GIHZ', 'HRAEZ')
        GROUP BY empresa";*/


$sql = "SELECT estatus, COUNT(*) AS cantidad_usuarios
        FROM comensal
        WHERE estatus IN ('1', '2', '3')
        GROUP BY estatus";

$result = mysqli_query($con, $sql);

$data = array();
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    // Enviar encabezados para JSON
    header('Content-Type: application/json');
    echo json_encode($data);
} else {
    echo json_encode(['error' => 'Error en la consulta: ' . $con->error]);
}
?>
