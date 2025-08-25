<?php
require 'vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

include 'conexion.php';

// Recibir datos del formulario
$tipo = $_POST['selectTipo'];
$pacientes = $_POST['selectPacientes']; // array de pacientes

// Funciones para generar etiquetas
function generarEtiquetaDesayuno($dato) {
    return [
        "PACIENTE: " . $dato['Nombre_Paciente'],
        "FECHA DE NACIMIENTO: " . $dato['Fecha_Nacimiento_Paciente'],
        "ID: " . $dato['idPaciente'],
        "NUM. CAMA: " . $dato['Cama_Paciente'],
        "SERVICIO: Desayuno",
        "TIPO DE DIETA: " . $dato['Desayuno'],
        "OBSERVACIONES: " . $dato['Observaciones']
    ];
}
// Repite para las demás funciones...
// generarEtiquetaColMatutina, generarEtiquetaComida, etc.

switch ($tipo) {
    case 'Desayuno': $generarEtiqueta = 'generarEtiquetaDesayuno'; break;
    case 'Col_Matutina': $generarEtiqueta = 'generarEtiquetaColMatutina'; break;
    case 'Comida': $generarEtiqueta = 'generarEtiquetaComida'; break;
    case 'Col_Vespertina': $generarEtiqueta = 'generarEtiquetaColVespertina'; break;
    case 'Cena': $generarEtiqueta = 'generarEtiquetaCena'; break;
    case 'Col_Nocturna': $generarEtiqueta = 'generarEtiquetaColNocturna'; break;
}

$datos = [];

if (in_array('Todos', $pacientes)) {
    $query = "SELECT * FROM dietas WHERE area = 'PRIVADOS' AND DATE(Fecha_Hora_Creacion) = CURDATE()";
    $result = mysqli_query($con, $query);
    $datos = mysqli_fetch_all($result, MYSQLI_ASSOC);
} else {
    foreach ($pacientes as $paciente) {
        $query = "SELECT * FROM dietas WHERE area = 'PRIVADOS' AND Nombre_Paciente = '$paciente' AND DATE(Fecha_Hora_Creacion) = CURDATE()";
        $result = mysqli_query($con, $query);
        $datosPaciente = mysqli_fetch_all($result, MYSQLI_ASSOC);
        $datos = array_merge($datos, $datosPaciente);
    }
}

// Configurar DOMPDF
$options = new Options();
$options->set('defaultFont', 'Arial');
$dompdf = new Dompdf($options);

// Generar HTML
$html = '
<style>
    @page {
        margin: 5px;
    }
    body {
        font-family: Arial, sans-serif;
        font-size: 10px;
        margin: 0;
        padding: 0;
    }
    .etiqueta {
        width: 330px;
        height: 183px;
        border: 1px solid #000;
        padding: 6px;
        margin: 2px;
        display: inline-block;
        vertical-align: top;
        box-sizing: border-box;
        overflow: hidden;
    }
    .titulo {
        font-weight: bold;
        font-size: 10px;
        text-align: center;
        margin-bottom: 4px;
    }
    .footer {
        font-weight: bold;
        font-size: 10px;
        text-align: center;
        margin-top: 6px;
        border-top: 2px solid #000;
        padding-top: 3px;
    }
    table {
        width: 100%;
        font-size: 9px;
        border-collapse: collapse;
    }
    td {
        border: 1px solid #000;
        padding: 2px;
    }
</style>
';

foreach ($datos as $dato) {
    $html .= '<div class="etiqueta">';
    $html .= '<div class="titulo">SERVICIO PROVISIÓN DE ALIMENTOS</div>';
    $html .= '<div style="text-align:center;">Fecha: Marzo 2021 &nbsp; Página: 1 &nbsp; Revisión: 0</div>';
    $html .= '<table>';
    $html .= '<tr><td><strong>NOMBRE DEL PACIENTE:</strong></td><td>' . $dato['Nombre_Paciente'] . '</td></tr>';
    $html .= '<tr><td><strong>FECHA DE NACIMIENTO:</strong></td><td>' . $dato['Fecha_Nacimiento_Paciente'] . '</td></tr>';
    $html .= '<tr><td><strong>ID:</strong></td><td>' . $dato['idPaciente'] . '</td></tr>';
    $html .= '<tr><td><strong>SERVICIO:</strong></td><td>' . $dato['area'] . '</td></tr>';
    $html .= '<tr><td><strong>TIPO DE DIETA:</strong></td><td>' . $dato[$tipo] . '</td></tr>';
    $html .= '<tr><td><strong>OBSERVACIONES:</strong></td><td></td></tr>';
    $html .= '</table>';
    $html .= '<div class="footer">SE RECOMIENDA CONSUMIR LOS ALIMENTOS AL MOMENTO DE LA ENTREGA</div>';
    $html .= '</div>';
}

// Renderizar PDF
$dompdf->loadHtml($html);
$dompdf->setPaper('letter', 'landscape');
$dompdf->render();
$dompdf->stream("etiquetas_pacientes.pdf", ["Attachment" => false]);
