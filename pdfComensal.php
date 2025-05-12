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
$sesionUsuario = $sesi[1];
$sesionNombre = $sesi[3];
$sesionCargo = $sesi[4];

// Obtener el filtro de empresa
$empresaFiltro = isset($_GET['empresaFiltro']) ? $_GET['empresaFiltro'] : 'TODAS';

// Construir la consulta SQL con el filtro
$filtroSQL = $empresaFiltro == 'TODAS' ? "" : "WHERE empresa = '$empresaFiltro'";

// Consulta SQL con el filtro
$consulta = "SELECT * FROM comensales $filtroSQL ORDER BY id DESC";
$query = mysqli_query($con, $consulta);

$html = '<h1 style="text-align: center;">Reporte de Comensales</h1>';
$html .= '<table border="1" style="width:100%; border-collapse: collapse; font-size: 10px;">';
$html .= '<thead>
            <tr>
              <th>Codigo</th>
              <th>Comensal</th>
              <th>No.Empleado</th>
              <th>Empresa</th>
              <th>Departamento</th>
            </tr>
          </thead>';
$html .= '<tbody>';
while ($dataRow = mysqli_fetch_array($query)) {
    $html .= '<tr>
                <td>' . $dataRow["codigo"] . '</td>
                <td>' . $dataRow["comensal"] . '</td>
                <td>' . $dataRow["noEmpleado"] . '</td>
                <td>' . $dataRow["empresa"] . '</td>
                <td>' . $dataRow["departamento"] . '</td>
              </tr>';
}
$html .= '</tbody></table>';

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();
$dompdf->stream("reporte_comensales.pdf", array("Attachment" => 0));
?>
