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


$sql = "SELECT * FROM pacientes";
$query = mysqli_query($con, $sql);

$html = '<h1 style="text-align: center;">Reporte de Pacientes</h1>';
$html .= '<table border="1" style="width:100%; border-collapse: collapse; font-size: 7px;">';
$html .= '<thead>
            <tr>
              <th>Nombre del Paciente</th>
              <th>Fecha de Nacimiento</th>
              <th>ID</th>
              <th>Cama</th>
              <th>Edad Años</th>
              <th>Edad Meses</th>
              <th>Edad Dias</th>
              <th>Diagnostico Medico y Nutricional</th>
              <th>Prescripcion Nutricional</th>
              <th>Observaciones</th>
              <th>Control de Tamizaje</th>
              <th>Aislados</th>
              <th>Área</th>e
              <th>Status</th>
              <th>Creado por</th>
            </tr>
          </thead>';
$html .= '<tbody>';
while ($dataRow = mysqli_fetch_array($query)) {
    $html .= '<tr>
                <td>' . $dataRow["nombre"] . '</td>
                <td>' . $dataRow["fechaNacimiento"] . '</td>
                <td>' . $dataRow["idPaciente"] . '</td>
                <td>' . $dataRow["cama"] . '</td>
                <td>' . $dataRow["edad"] . '</td>
                <td>' . $dataRow["edadMeses"] . '</td>
                <td>' . $dataRow["edadDias"] . '</td>
                <td>' . $dataRow["diagnosticoMed"] . '</td>
                <td>' . $dataRow["prescripcionNutri"] . '</td>
                <td>' . $dataRow["observaciones"] . '</td>
                <td>' . $dataRow["controlTamizaje"] . '</td>
                <td>' . $dataRow["vip"] . '</td>
                <td>' . $dataRow["area"] . '</td>
                <td>' . $dataRow["statusP"] . '</td>
                <td>' . $dataRow["creadoPor"] . '</td>
              </tr>';
}
$html .= '</tbody></table>';

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();
$dompdf->stream("reporte_dietas.pdf", array("Attachment" => 0));
