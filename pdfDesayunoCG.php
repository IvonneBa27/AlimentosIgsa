<?php
require 'vendor/autoload.php';
date_default_timezone_set("America/Mexico_City");
use Dompdf\Dompdf;

include("conexion.php");

session_start();
if (!isset($_SESSION['resultado'])) {
  header('Location: index.html');
  exit;
} else {
  $sesi = $_SESSION['resultado'];
}
$sesionUsuario = $sesi['usuario']; 
$sesionNombre = $sesi['nombre'];   

$fechaHoraActual1 = date('Y-m-d', strtotime('+1 day'));
$sql = "SELECT    
    p.idPaciente AS pacienteID_pacientes, 
    p.area AS area_pacientes,
    p.nombre,
    p.fechaNacimiento,
    p.cama,
    p.edad,
    p.diagnosticoMed,
    p.prescripcionNutri,
    p.observaciones,
    p.controlTamizaje,
    d.idPaciente AS pacienteID_dietas,
    d.area AS area_dietas,
    d.Fecha_Hora_Creacion,
    d.Desayuno,
    d.Col_Matutina,
    d.Comida,
    d.Col_Vespertina,
    d.Cena,
    d.Col_Nocturna,
    d.Creado_por
FROM pacientes p
LEFT JOIN (
    SELECT d1.*
    FROM dietas d1
    INNER JOIN ( 
        SELECT MAX(ID) AS ID, idPaciente
        FROM dietas
        WHERE area = 'CIRUGÍA GENERAL' AND DATE(Fecha_Hora_Creacion) = '$fechaHoraActual1'
        GROUP BY idPaciente
    ) d2 ON d1.ID = d2.ID
) d ON p.idPaciente = d.idPaciente
WHERE p.statusP = 'Activo' AND p.area = 'CIRUGÍA GENERAL'
ORDER BY 
CASE 
 WHEN p.cama LIKE 'A-%' THEN 1
 ELSE 2
END, 
CAST(
CASE 
WHEN p.cama LIKE '%-%' THEN SUBSTRING_INDEX(p.cama, '-', -1)
ELSE p.cama
END AS UNSIGNED
)";



$query = mysqli_query($con, $sql);

$datosCombinados = array();

if ($query->num_rows > 0) {
  while ($row = $query->fetch_assoc()) {
    $datosCombinados[] = $row;
  }
} else {
  echo "0 resultados";
}

$html = '<h2 style="text-align: center;">Reporte de Dietas (Cirugía General)</h2>';
$html .= '<table border="1" style="width: 100%; border-collapse: collapse; font-size: 7px;">';
$html .= '<thead>
            <tr>
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
            </tr>
          </thead>';
$html .= '<tbody>';
foreach ($datosCombinados as $dataRow) {
  $html .= '<tr>
                <td>' . $dataRow["pacienteID_pacientes"] . '</td>
                <td>' . $dataRow["nombre"] . '</td>
                <td>' . $dataRow["fechaNacimiento"] . '</td>
                <td>' . $dataRow["cama"] . '</td>
                <td>' . $dataRow["edad"] . '</td>
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
              </tr>';
}
$html .= '</tbody></table>';

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();
$dompdf->stream("reporte_dietas.pdf", array("Attachment" => 0));
