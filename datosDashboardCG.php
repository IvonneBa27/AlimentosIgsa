<?php
include('conexion.php');

// Obtener la fecha seleccionada o la fecha actual si no se selecciona ninguna
$fechaFiltro = isset($_GET['fechaFiltro']) && !empty($_GET['fechaFiltro']) ? $_GET['fechaFiltro'] : date('Y-m-d');

// Calcular el inicio y el fin de la semana de la fecha seleccionada
$inicio_semana = date('Y-m-d', strtotime('monday this week', strtotime($fechaFiltro)));
$fin_semana = date('Y-m-d', strtotime('sunday this week', strtotime($fechaFiltro)));

/*$sql = "SELECT 
    ds.nombre_dia,
    IFNULL(COUNT(d.Fecha_Hora_Creacion), 0) AS cantidad
FROM 
    dias_semana ds
LEFT JOIN 
    dietas d ON DAYOFWEEK(DATE(d.Fecha_Hora_Creacion)) = ds.dia_semana
    AND DATE(d.Fecha_Hora_Creacion) BETWEEN '$inicio_semana' AND '$fin_semana' AND area = 'CIRUGÍA GENERAL'
GROUP BY 
    ds.dia_semana
ORDER BY 
    ds.dia_semana";*/
    $sql = "SELECT 
    ds.nombre_dia,
    IFNULL(SUM(
        (d.Desayuno IS NOT NULL AND d.Desayuno != '') +
        (d.Col_Matutina IS NOT NULL AND d.Col_Matutina != '') +
        (d.Comida IS NOT NULL AND d.Comida != '') +
        (d.Col_Vespertina IS NOT NULL AND d.Col_Vespertina != '') +
        (d.Cena IS NOT NULL AND d.Cena != '') +
        (d.Col_Nocturna IS NOT NULL AND d.Col_Nocturna != '')
    ), 0) AS cantidad
FROM 
    dias_semana ds
LEFT JOIN 
    dietas d ON DAYOFWEEK(DATE(d.Fecha_Hora_Creacion)) = ds.dia_semana
    AND DATE(d.Fecha_Hora_Creacion) BETWEEN '$inicio_semana' AND '$fin_semana' AND area = 'CIRUGÍA GENERAL'
GROUP BY 
    ds.dia_semana
ORDER BY 
    ds.dia_semana";


$result = mysqli_query($con, $sql);
$data = array();
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}
//header('Content-Type: application/json');
echo json_encode($data);

?>

