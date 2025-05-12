<?php
/*include('conexion.php');
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

$inputFileName = $_FILES['excelFile']['tmp_name'];

// Leer el archivo Excel
$spreadsheet = IOFactory::load($inputFileName);
$sheet = $spreadsheet->getActiveSheet();
$data = $sheet->toArray();

if ($con->connect_error) {
    die("Conexión fallida: " . $con->connect_error);
}

// Insertar datos en la base de datos
foreach ($data as $row) {
    $sql = "INSERT INTO dietas (Nombre_Paciente, Fecha_Nacimiento_Paciente, ID_Paciente, Cama_Paciente, Edad, Diag_Med_Nutri, Prescripcion, Desayuno, Col_Matutina, Comida, Col_Vespertina,
    Cena, Col_Nocturna, Observaciones, Control_Tamizaje, area, privados, Fecha_Hora_Creacion, Creado_por) VALUES ('{$row[0]}', '{$row[1]}', '{$row[2]}')";
    $query = mysqli_query($con, $sql);
}

$con->close();
echo "Datos importados exitosamente.";*/

include('conexion.php');
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file'])) {
    $inputFileName = $_FILES['file']['tmp_name'];

    // Leer el archivo Excel
    $spreadsheet = IOFactory::load($inputFileName);
    $sheet = $spreadsheet->getActiveSheet();
    $data = $sheet->toArray();

    if ($con->connect_error) {
        die("Conexión fallida: " . $con->connect_error);
    }

    // Insertar datos en la base de datos
    foreach ($data as $row) {
        $sql = "INSERT INTO dietas (Nombre_Paciente, Fecha_Nacimiento_Paciente, ID_Paciente, Cama_Paciente, Edad, Diag_Med_Nutri, Prescripcion, Desayuno, Col_Matutina, 
        Comida, Col_Vespertina,Cena, Col_Nocturna, Observaciones, Control_Tamizaje, area, privados, Fecha_Hora_Creacion, Creado_por) 
        VALUES ('{$row[0]}', '{$row[1]}', '{$row[2]}', '{$row[3]}', '{$row[4]}', '{$row[5]}', '{$row[6]}', '{$row[7]}', '{$row[8]}', '{$row[9]}', '{$row[10]}', '{$row[11]}')";
        $query = mysqli_query($con, $sql);
    }

    $con->close();
    echo "Datos importados exitosamente.";
} else {
    echo "No se ha seleccionado ningún archivo.";
}

?>
