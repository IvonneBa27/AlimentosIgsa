<?php
require 'vendor/autoload.php';
use Dompdf\Dompdf;

include("conexion.php");
date_default_timezone_set("America/Mexico_City");
$fechaHoraActual = date("Y-m-d");

session_start();
if (!isset($_SESSION['resultado'])) {
  header('Location: index.html');
  exit;
} else {
  $sesi = $_SESSION['resultado'];
}
$sesionUsuario = $sesi['usuario']; // Reemplaza con el nombre exacto de la columna
$sesionNombre = $sesi['nombre'];   // Reemplaza con el nombre exacto de la columna


$fechaFiltro = isset($_GET['fechaFiltro']) && !empty($_GET['fechaFiltro']) ? $_GET['fechaFiltro'] : $fechaHoraActual;

/*$consulta = "SELECT * FROM dietas WHERE (area = 'QUEMADOS' OR privados = 'APLICA') AND DATE(Fecha_Hora_Creacion) = '$fechaFiltro' ORDER BY ID DESC";*/
$consulta = "SELECT * FROM dietas WHERE area = 'QUEMADOS' AND DATE(Fecha_Hora_Creacion) = '$fechaFiltro' ORDER BY ID DESC";
$query = mysqli_query($con, $consulta);

$html = '<h1 style="text-align: center;">Reporte de Dietas</h1>';
$html .= '<table border="1" style="width:100%; border-collapse: collapse; font-size: 10px;">';
$html .= '<thead>
            <tr>
              <th>Fecha de Solicitud</th>
              <th>Área</th>
              <th>ID</th>
              <th>Nombre del Paciente</th>
              <th>Fecha de Nacimiento</th>
              <th>Cama</th>
              <th>Edad</th>
              <th>Diagnostico Medico y Nutricional</th>
              <th>Prescripcion Nutricional</th>
              <th>Desayuno</th>
              <th>Colación Matutina</th>
              <th>Comida</th>
              <th>Colación Vespertina</th>
              <th>Cena</th>
              <th>Colación Nocturna</th>
              <th>Observaciones</th>
              <th>Control de Tamizaje</th>
              <th>Solicitado por</th>
            </tr>
          </thead>';
$html .= '<tbody>';
while ($dataRow = mysqli_fetch_array($query)) {
    $html .= '<tr>
                <td>' . $dataRow["Fecha_Hora_Creacion"] . '</td>
                <td>' . $dataRow["area"] . '</td>
                <td>' . $dataRow["idPaciente"] . '</td>
                <td>' . $dataRow["Nombre_Paciente"] . '</td>
                <td>' . $dataRow["Fecha_Nacimiento_Paciente"] . '</td>
                <td>' . $dataRow["Cama_Paciente"] . '</td>
                <td>' . $dataRow["Edad"] . '</td>
                <td>' . $dataRow["Diag_Med_Nutri"] . '</td>
                <td>' . $dataRow["Prescripcion"] . '</td>
                <td>' . $dataRow["Desayuno"] . '</td>
                <td>' . $dataRow["Col_Matutina"] . '</td>
                <td>' . $dataRow["Comida"] . '</td>
                <td>' . $dataRow["Col_Vespertina"] . '</td>
                <td>' . $dataRow["Cena"] . '</td>
                <td>' . $dataRow["Col_Nocturna"] . '</td>
                <td>' . $dataRow["Observaciones"] . '</td>
                <td>' . $dataRow["Control_Tamizaje"] . '</td>
                <td>' . $dataRow["Creado_por"] . '</td>
              </tr>';
}
$html .= '</tbody></table>';

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();
$dompdf->stream("reporte_dietas.pdf", array("Attachment" => 0));
?>
