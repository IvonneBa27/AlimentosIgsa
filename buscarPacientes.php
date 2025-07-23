<?php
include("conexion.php");

$nombre = $_POST['nombre'] ?? '';
$id = $_POST['id'] ?? '';

$sql = "SELECT * FROM pacientes WHERE 1=1";

if (!empty($nombre)) {
  $sql .= " AND nombre LIKE '%" . mysqli_real_escape_string($con, $nombre) . "%'";
}
if (!empty($id)) {
  $sql .= " AND idPaciente LIKE '%" . mysqli_real_escape_string($con, $id) . "%'";
}

$query = mysqli_query($con, $sql);

while ($dataRow = mysqli_fetch_array($query)) {
  $resaltar = '';

  if (
    (!empty($nombre) && stripos($dataRow['nombre'], $nombre) !== false) ||
    (!empty($id) && stripos($dataRow['idPaciente'], $id) !== false)
  ) {
    $resaltar = 'resaltado';
  }

  echo "<tr class='$resaltar'>
    <td class='text-center'>{$dataRow['nombre']}</td>
    <td class='text-center'>{$dataRow['fechaNacimiento']}</td>
    <td class='text-center'>{$dataRow['idPaciente']}</td>
    <td class='text-center'>{$dataRow['cama']}</td>
    <td class='text-center'>{$dataRow['edad']}</td>
    <td class='text-center'>{$dataRow['edadMeses']}</td>
    <td class='text-center'>{$dataRow['edadDias']}</td>
    <td class='text-center'>{$dataRow['diagnosticoMed']}</td>
    <td class='text-center'>{$dataRow['prescripcionNutri']}</td>
    <td class='text-center'><textarea class='form-control-plaintext table-textarea' disabled>{$dataRow['observaciones']}</textarea></td>
    <td class='text-center'><textarea class='form-control-plaintext table-textarea' disabled>{$dataRow['controlTamizaje']}</textarea></td>
    <td class='text-center'>{$dataRow['vip']}</td>
    <td class='text-center'>{$dataRow['area']}</td>
    <td class='text-center'>{$dataRow['statusP']}</td>
    <td class='text-center'>{$dataRow['creadoPor']}</td>
    <td class='text-center'><a href='editPaciente.php?id={$dataRow['id']}' class='btn btn-warning btn-sm text-dark text-decoration-none'>Editar</a></td>
  </tr>";
}
?>
