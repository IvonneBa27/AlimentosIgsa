<?php
require 'vendor/autoload.php';
use Dompdf\Dompdf;

include("conexion.php");

session_start();
if (!isset($_SESSION['resultado'])) {
  header('Location: index.html');
  exit;
} else {
  $sesi = $_SESSION['resultado'];
}
$sesionUsuario = $sesi['usuario']; // Reemplaza con el nombre exacto de la columna
$sesionNombre = $sesi['nombre'];   // Reemplaza con el nombre exacto de la columna

$sql = "SELECT p.*, d.*, p.idPaciente AS pacienteID, d.idPaciente AS dietaID
FROM pacientes p
LEFT JOIN (
    SELECT d1.*
    FROM dietas d1
    INNER JOIN ( 
        SELECT MAX(ID) AS ID, idPaciente
        FROM dietas
        WHERE area = 'CIRUGÍA GENERAL'
        GROUP BY idPaciente
    ) d2 ON d1.ID = d2.ID
) d ON p.idPaciente = d.idPaciente
WHERE p.statusP = 'Alta' AND p.area = 'CIRUGÍA GENERAL'";

    
$query = mysqli_query($con, $sql);

$datosCombinados = array();

if ($query->num_rows > 0) {
    while ($row = $query->fetch_assoc()) {
        $datosCombinados[] = $row;
    }
} else {
    echo "0 resultados";
}

$html = '<h1 style="text-align: center;">Reporte de Dietas (Cirugía General)</h1>';
$html .= '<table border="1" style="width: 100%; border-collapse: collapse; font-size: 10px;">';
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
foreach ($datosCombinados as $dataRow) {
    $html .= '<tr>
                <td>' . $dataRow["Fecha_Hora_Creacion"] . '</td>
                <td>' . $dataRow["area"] . '</td>
                <td>' . $dataRow["idPaciente"] . '</td>
                <td>' . $dataRow["nombre"] . '</td>
                <td>' . $dataRow["Fecha_Nacimiento_Paciente"] . '</td>
                <td>' . $dataRow["Cama_Paciente"] . '</td>
                <td>' . $dataRow["Edad"] . '</td>
                <td>' . $dataRow["diagnosticoMed"] . '</td>
                <td>' . $dataRow["prescripcionNutri"] . '</td>
                <td>' . $dataRow["Desayuno"] . '</td>
                <td>' . $dataRow["Col_Matutina"] . '</td>
                <td>' . $dataRow["Comida"] . '</td>
                <td>' . $dataRow["Col_Vespertina"] . '</td>
                <td>' . $dataRow["Cena"] . '</td>
                <td>' . $dataRow["Col_Nocturna"] . '</td>
                <td>' . $dataRow["observaciones"] . '</td>
                <td>' . $dataRow["controlTamizaje"] . '</td>
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
